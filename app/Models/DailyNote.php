<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyNote extends Model
{
    protected $connection = 'TEMP';
    protected $table = 'tbl_absent';
}
