<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $table = 'school_years';
    public $timestamps = false;
    protected $fillable = ['label', 'is_active', 'candidacy_open'];
    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean',
        'candidacy_open' => 'boolean',
    ];
}
