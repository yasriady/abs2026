<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

use function Laravel\Prompts\info;

class RegenerateUnitJob implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public $date;
    public $unitId;


    /**
     * Create a new job instance.
     */
    public function __construct($date, $unitId)
    {
        $this->date = $date;
        $this->unitId = $unitId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // info(__FUNCTION__);

        // TDK JALAN
        // $cmd = sprintf(
        //     '%s %s --from=%s --to=%s --unit-id=%s',
        //     '/opt/absensi-builder/venv/bin/python',
        //     '/opt/absensi-builder/etl/main.py',
        //     $this->date,
        //     $this->date,
        //     $this->unitId
        // );

        $cmd = sprintf(
            '/opt/absensi-builder/venv/bin/python /opt/absensi-builder/etl/main.py --from=%s --to=%s --unit-id=%s',
            $this->date,
            $this->date,
            $this->unitId
        );

        exec($cmd);
    }
}
