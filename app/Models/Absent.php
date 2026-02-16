<?php

// app/Models/Absensi.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absent extends Model
{
    protected $connection = 'TEMP';
    protected $table = 'tbl_absent';

    protected $fillable = [
        'nik',
        'status',
        'date',
        'notes',
        'user',
        'ip_address',
    ];

    public $timestamps = true;
}
