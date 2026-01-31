<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubUnit extends Model
{
    use HasFactory;

    public $incrementing = false; // karena ID manual
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'sub_unit',
        'unit_id',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
