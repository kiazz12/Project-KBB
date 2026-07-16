<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OPD extends Model
{
    use SoftDeletes;

    protected $table = 'opds';

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'opd_id');
    }

    public function forms()
    {
        return $this->hasMany(Form::class, 'opd_id');
    }
}
