<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'no',
        'nama',
        'jabatan',
        'opd_institusi',
        'role',
        'tanggal_presensi',
    ];

    protected $casts = [
        'no' => 'integer',
        'tanggal_presensi' => 'date',
    ];
}
