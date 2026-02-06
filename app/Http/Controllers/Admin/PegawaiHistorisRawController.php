<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PegawaiHistorisRaw;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class PegawaiHistorisRawController extends Controller
{
    public function index(Request $request)
    {
        $histories = PegawaiHistorisRaw::query()
            ->with('masterPegawai')
            ->join('master_pegawais', 'pegawai_histories.master_pegawai_id', '=', 'master_pegawais.id')
            ->select('pegawai_histories.*')
            // ->where('master_pegawais.nama', 'like', '%Dedy Y%')
            ->orderBy('master_pegawais.nik')
            ->orderBy('pegawai_histories.begin_date', 'desc')
            ->paginate(100)
            ->withQueryString();

        $items = collect($histories->items())
            ->groupBy('master_pegawai_id')
            ->flatMap(function ($rows) {

                // 🔑 Ambil histori dengan begin_date TERBESAR
                $currentRow = $rows
                    ->sortByDesc(fn($r) => $r->begin_date->timestamp)
                    ->first();

                return $rows->map(function ($row) use ($rows, $currentRow) {

                    // ===== FLAG HISTORI AKTIF (FIX FINAL) =====
                    $row->is_current = $currentRow && $row->id === $currentRow->id;

                    // ===== FLAG OVERLAP =====
                    $row->is_overlap = false;

                    foreach ($rows as $other) {
                        if ($row->id === $other->id) {
                            continue;
                        }

                        $rowBegin   = $row->begin_date->toDateString();
                        $rowEnd     = $row->end_date
                            ? $row->end_date->toDateString()
                            : '9999-12-31';

                        $otherBegin = $other->begin_date->toDateString();
                        $otherEnd   = $other->end_date
                            ? $other->end_date->toDateString()
                            : '9999-12-31';

                        if (
                            $rowBegin <= $otherEnd &&
                            $rowEnd >= $otherBegin
                        ) {
                            $row->is_overlap = true;
                            break;
                        }
                    }

                    return $row;
                });
            });

        $histories->setCollection($items);

        return Inertia::render('PegawaiHistorisRaw/Index', [
            'histories' => $histories,
        ]);
    }

    public function update(Request $request, PegawaiHistorisRaw $pegawaiHistorisRaw)
    {
        $request->validate([
            'begin_date' => ['required', 'date'],
            'end_date'   => ['nullable', 'date', 'after_or_equal:begin_date'],
        ]);

        $pegawaiHistorisRaw->update([
            'begin_date' => $request->begin_date,
            'end_date'   => $request->end_date,
        ]);

        return back();
    }

    public function autoFix(PegawaiHistorisRaw $pegawai)
    {
        $pegawaiId = $pegawai->master_pegawai_id;

        // Ambil SEMUA histori pegawai ini
        $rows = PegawaiHistorisRaw::where('master_pegawai_id', $pegawaiId)
            ->orderBy('begin_date', 'desc')
            ->get();

        if ($rows->count() <= 1) {
            return back();
        }

        foreach ($rows as $index => $row) {
            // Skip histori TERBARU
            if ($index === 0) {
                continue;
            }

            $prev = $rows[$index - 1];

            $newEndDate = Carbon::parse($prev->begin_date)->subDay();

            // Fix jika:
            // - overlap
            // - end_date null
            // - end_date >= begin_date berikutnya
            if (
                !$row->end_date ||
                $row->end_date >= $prev->begin_date ||
                $row->end_date < $row->begin_date
            ) {
                $row->update([
                    'end_date' => $newEndDate->toDateString(),
                ]);
            }
        }

        return back()->with('success', 'Histori pegawai berhasil diperbaiki otomatis.');
    }
}
