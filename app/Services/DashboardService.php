<?php

namespace App\Services;

use App\Models\AbsensiSummary;
use App\Models\MasterPegawai;
use App\Models\PegawaiHistory;
use App\Models\SubUnit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function build(array $filters, User $user): array
    {
        $date = $filters['date'];
        $unitId = $filters['unit_id'] ?? null;
        $subUnitId = $filters['sub_unit_id'] ?? null;

        $activePegawaiIds = $this->activePegawaiIds($date, $unitId, $subUnitId);
        $niks = $this->niksFromMasterPegawai($activePegawaiIds);

        return [
            'meta' => [
                'last_updated' => now()->toDateTimeString(),
                'scope_label' => $this->scopeLabel($unitId, $subUnitId),
            ],
            'kpi' => $this->kpi($date, $niks, $activePegawaiIds),
            'anomali' => $this->anomali($date, $niks, 10),
            'status_chart' => $this->statusChart($date, $niks),
            'trend' => $this->trend($date, $unitId, $subUnitId, 7),
            'unit_rank' => $this->unitRank($date, 10),
            'resolver_cov' => $this->resolverCoverage($date, $niks),
        ];
    }

    public function unitsFor(User $user): Collection
    {
        if ($user->hasRole('admin')) {
            return Unit::query()->orderBy('unit')->get(['id', 'unit']);
        }

        if ($user->hasRole('admin_unit') && $user->unit_id) {
            return Unit::query()
                ->where('id', $user->unit_id)
                ->orderBy('unit')
                ->get(['id', 'unit']);
        }

        return collect();
    }

    public function subUnitsFor(?int $unitId): Collection
    {
        if (!$unitId) {
            return collect();
        }

        return SubUnit::query()
            ->where('unit_id', $unitId)
            ->orderBy('sub_unit')
            ->get(['id', 'unit_id', 'sub_unit']);
    }

    public function normalizeFilters(array $input, User $user): array
    {
        $date = !empty($input['date'])
            ? Carbon::parse($input['date'])->toDateString()
            : now()->toDateString();

        $unitId = isset($input['unit_id']) && $input['unit_id'] !== ''
            ? (int) $input['unit_id']
            : null;

        $subUnitId = isset($input['sub_unit_id']) && $input['sub_unit_id'] !== ''
            ? (int) $input['sub_unit_id']
            : null;

        if ($user->hasRole('admin_unit')) {
            $unitId = (int) $user->unit_id;
        }

        if (!$unitId) {
            $subUnitId = null;
        }

        return [
            'date' => $date,
            'unit_id' => $unitId,
            'sub_unit_id' => $subUnitId,
        ];
    }

    private function activePegawaiIds(string $date, ?int $unitId, ?int $subUnitId): Collection
    {
        return PegawaiHistory::query()
            ->where('begin_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->when($unitId, fn($q) => $q->where('id_unit', $unitId))
            ->when($subUnitId, fn($q) => $q->where('id_sub_unit', $subUnitId))
            ->distinct()
            ->pluck('master_pegawai_id');
    }

    private function niksFromMasterPegawai(Collection $activePegawaiIds): Collection
    {
        if ($activePegawaiIds->isEmpty()) {
            return collect();
        }

        return MasterPegawai::query()
            ->whereIn('id', $activePegawaiIds)
            ->pluck('nik')
            ->filter()
            ->unique()
            ->values();
    }

    private function kpi(string $date, Collection $niks, Collection $activePegawaiIds): array
    {
        $summaryBase = $this->summaryBase($date, $niks);

        $hadir = (clone $summaryBase)->where('status_hari_final', 'HADIR')->count();

        $telat = (clone $summaryBase)
            ->where(function ($q) {
                $q->where('status_masuk_final', 'TELAT')
                    ->orWhere('status_masuk_final', 'TERLAMBAT');
            })
            ->count();

        $belumMasuk = (clone $summaryBase)
            ->whereNull('time_in_final')
            ->count();

        $belumPulang = (clone $summaryBase)
            ->whereNull('time_out_final')
            ->count();

        $tidakHadir = (clone $summaryBase)
            ->whereNotNull('status_hari_final')
            ->where('status_hari_final', '!=', 'HADIR')
            ->count();

        return [
            'total_active' => $activePegawaiIds->count(),
            'hadir' => $hadir,
            'telat' => $telat,
            'belum_masuk' => $belumMasuk,
            'belum_pulang' => $belumPulang,
            'tidak_hadir' => $tidakHadir,
        ];
    }

    private function anomali(string $date, Collection $niks, int $limit = 10): array
    {
        if ($niks->isEmpty()) {
            return [];
        }

        $rows = AbsensiSummary::query()
            ->leftJoin('master_pegawais', 'master_pegawais.nik', '=', 'absensi_summaries.nik')
            ->select(
                'absensi_summaries.nik',
                'master_pegawais.nama',
                'master_pegawais.nip',
                'absensi_summaries.time_in_final',
                'absensi_summaries.time_out_final',
                'absensi_summaries.valid_device_in',
                'absensi_summaries.valid_device_out',
                'absensi_summaries.status_hari_final'
            )
            ->whereDate('absensi_summaries.date', $date)
            ->whereIn('absensi_summaries.nik', $niks)
            ->where(function ($q) {
                $q->whereNull('absensi_summaries.time_in_final')
                    ->orWhereNull('absensi_summaries.time_out_final')
                    ->orWhere('absensi_summaries.valid_device_in', 0)
                    ->orWhere('absensi_summaries.valid_device_out', 0);
            })
            ->orderBy('master_pegawais.nama')
            ->limit($limit)
            ->get();

        return $rows->map(function ($row) {
            $issues = [];
            if (!$row->time_in_final) {
                $issues[] = 'NO_IN';
            }
            if (!$row->time_out_final) {
                $issues[] = 'NO_OUT';
            }
            if ((int) $row->valid_device_in === 0 || (int) $row->valid_device_out === 0) {
                $issues[] = 'INVALID_DEVICE';
            }

            return [
                'nik' => $row->nik,
                'nama' => $row->nama,
                'nip' => $row->nip,
                'issue' => implode(', ', array_unique($issues)),
                'status_hari' => $row->status_hari_final,
            ];
        })->values()->all();
    }

    private function statusChart(string $date, Collection $niks): array
    {
        if ($niks->isEmpty()) {
            return [];
        }

        return AbsensiSummary::query()
            ->select('status_hari_final', DB::raw('COUNT(*) as total'))
            ->whereDate('date', $date)
            ->whereIn('nik', $niks)
            ->groupBy('status_hari_final')
            ->orderByDesc('total')
            ->get()
            ->map(fn($row) => [
                'label' => $row->status_hari_final ?: 'UNSET',
                'count' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function trend(string $date, ?int $unitId, ?int $subUnitId, int $days = 7): array
    {
        $target = Carbon::parse($date);
        $rows = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $d = $target->copy()->subDays($i)->toDateString();
            $activeIds = $this->activePegawaiIds($d, $unitId, $subUnitId);
            $niks = $this->niksFromMasterPegawai($activeIds);
            $denom = max(1, $activeIds->count());

            $summaryBase = $this->summaryBase($d, $niks);
            $hadir = (clone $summaryBase)->where('status_hari_final', 'HADIR')->count();
            $telat = (clone $summaryBase)
                ->where(function ($q) {
                    $q->where('status_masuk_final', 'TELAT')
                        ->orWhere('status_masuk_final', 'TERLAMBAT');
                })
                ->count();

            $rows[] = [
                'date' => $d,
                'hadir_pct' => round(($hadir / $denom) * 100, 2),
                'telat_pct' => round(($telat / $denom) * 100, 2),
            ];
        }

        return $rows;
    }

    private function unitRank(string $date, int $limit = 10): array
    {
        $activeByUnit = PegawaiHistory::query()
            ->select('id_unit', DB::raw('COUNT(DISTINCT master_pegawai_id) as total_active'))
            ->where('begin_date', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->groupBy('id_unit')
            ->get();

        if ($activeByUnit->isEmpty()) {
            return [];
        }

        $unitIds = $activeByUnit->pluck('id_unit');

        $hadirByUnit = AbsensiSummary::query()
            ->join('master_pegawais', 'master_pegawais.nik', '=', 'absensi_summaries.nik')
            ->join('pegawai_histories', function ($join) use ($date) {
                $join->on('pegawai_histories.master_pegawai_id', '=', 'master_pegawais.id')
                    ->where('pegawai_histories.begin_date', '<=', $date)
                    ->where(function ($q) use ($date) {
                        $q->whereNull('pegawai_histories.end_date')
                            ->orWhere('pegawai_histories.end_date', '>=', $date);
                    });
            })
            ->select('pegawai_histories.id_unit', DB::raw('COUNT(*) as total_hadir'))
            ->whereDate('absensi_summaries.date', $date)
            ->where('absensi_summaries.status_hari_final', 'HADIR')
            ->whereIn('pegawai_histories.id_unit', $unitIds)
            ->groupBy('pegawai_histories.id_unit')
            ->pluck('total_hadir', 'id_unit');

        $unitNames = Unit::query()
            ->whereIn('id', $unitIds)
            ->pluck('unit', 'id');

        return $activeByUnit->map(function ($row) use ($hadirByUnit, $unitNames) {
            $active = (int) $row->total_active;
            $hadir = (int) ($hadirByUnit[$row->id_unit] ?? 0);
            $score = $active > 0 ? round(($hadir / $active) * 100, 2) : 0;

            return [
                'unit_id' => (int) $row->id_unit,
                'unit' => $unitNames[$row->id_unit] ?? "-",
                'score' => $score,
            ];
        })
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    private function resolverCoverage(string $date, Collection $niks): array
    {
        if ($niks->isEmpty()) {
            return [];
        }

        return AbsensiSummary::query()
            ->select('final_note', DB::raw('COUNT(*) as total'))
            ->whereDate('date', $date)
            ->whereIn('nik', $niks)
            ->groupBy('final_note')
            ->get()
            ->map(fn($row) => [
                'source' => $row->final_note ?: 'unknown',
                'count' => (int) $row->total,
            ])
            ->values()
            ->all();
    }

    private function summaryBase(string $date, Collection $niks)
    {
        if ($niks->isEmpty()) {
            return AbsensiSummary::query()->whereRaw('1=0');
        }

        return AbsensiSummary::query()
            ->whereDate('date', $date)
            ->whereIn('nik', $niks);
    }

    private function scopeLabel(?int $unitId, ?int $subUnitId): string
    {
        if ($subUnitId) {
            $sub = SubUnit::query()->find($subUnitId);
            if ($sub) {
                return "Sub Unit: {$sub->sub_unit}";
            }
        }

        if ($unitId) {
            $unit = Unit::query()->find($unitId);
            if ($unit) {
                return "Unit: {$unit->unit}";
            }
        }

        return 'Semua Unit';
    }
}
