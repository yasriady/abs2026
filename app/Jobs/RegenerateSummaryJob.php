<?php

namespace App\Jobs;

use App\Models\EtlJobNik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $nik;
    public $date;
    public $jobId;

    public function __construct($nik,$date,$jobId)
    {
        $this->nik = $nik;
        $this->date = $date;
        $this->jobId = $jobId;
    }

    public function handle()
    {
        $job = EtlJobNik::find($this->jobId);
        $job->update(['status'=>'running']);

        $cmd = "python3 /opt/absensi-builder/etl/run_single.py {$this->nik} {$this->date}";
        exec($cmd." 2>&1", $output, $code);

        if($code===0){
            $job->update([
                'status'=>'done',
                'log'=>implode("\n",$output)
            ]);
        }else{
            $job->update([
                'status'=>'failed',
                'log'=>implode("\n",$output)
            ]);
        }
    }
}
