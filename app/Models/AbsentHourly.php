<?php

// app/Models/Absensi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsentHourly extends Model
{
    protected $connection = 'TEMP';
    protected $table = 'tbl_absent_hourly';

    protected $fillable = [
        'nik',
        'date',
        'hour',
        'status',
        'tm',
        'notes',
        'user',
        'ip_address',
    ];

    public $timestamps = true;
}
