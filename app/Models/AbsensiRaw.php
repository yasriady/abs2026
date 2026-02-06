<?php

// app/Models/AbsensiRaw.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiRaw extends Model
{
    protected $connection = 'devel_db';
    protected $table = 'DB_ATT_tbl_attendance';

    protected $fillable = [
        'nik',
        'date',
        'time',
    ];

    public $timestamps = false;
}
