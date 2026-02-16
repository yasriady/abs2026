<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiSummary extends Model
{
    protected $fillable = [
        'status_hari_final',
        'notes_hari',
        'time_in_final',
        'time_out_final',
        'status_masuk_final',
        'status_pulang_final',
        'notes_in',
        'notes_out',
        'attribute_in',
        'attribute_out',
    ];

    protected static function booted()
    {
        static::saved(function ($summary) {
            $summary->syncHourly();
        });
    }

    public function syncAbsent($status, $notes)
    {
        if ($status === 'HADIR') {
            Absent::where('nik', $this->nik)
                ->where('date', $this->date)
                ->delete();
            return;
        }

        Absent::updateOrCreate(
            [
                'nik' => $this->nik,
                'date' => $this->date
            ],
            [
                'status' => $status,
                'notes' => $notes,
                'user' => auth()->id(),
                'ip_address' => request()->ip()
            ]
        );
    }

    public function syncHourly()
    {
        if (!$this->nik || !$this->date) {
            return;
        }

        $rows = [];

        // IN
        if ($this->time_in_final) {
            $rows[] = [
                'hour' => substr($this->time_in_final, 0, 2),
                'status' => $this->status_masuk_final,
                'tm' => $this->time_in_final,
                'notes' => $this->notes_in
            ];
        }

        // OUT
        if ($this->time_out_final) {
            $rows[] = [
                'hour' => substr($this->time_out_final, 0, 2),
                'status' => $this->status_pulang_final,
                'tm' => $this->time_out_final,
                'notes' => $this->notes_out
            ];
        }

        foreach ($rows as $r) {

            \App\Models\AbsentHourly::updateOrCreate(
                [
                    'nik' => $this->nik,
                    'date' => $this->date,
                    'hour' => $r['hour'],
                ],
                [
                    'status' => $r['status'],
                    'tm' => $r['tm'],
                    'notes' => $r['notes'],
                    'user' => auth()->user()->name ?? 'system',
                    'ip_address' => request()->ip()
                ]
            );
        }
    }
}
