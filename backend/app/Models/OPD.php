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

    /**
     * Get users in this OPD
     */
    public function users()
    {
        return $this->hasMany(User::class, 'opd_id');
    }

    /**
     * Get forms created by this OPD
     */
    public function forms()
    {
        return $this->hasMany(Form::class, 'opd_id');
    }
}
