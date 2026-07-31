<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $table = 'school_years';
    public $timestamps = false;
    protected $fillable = [
        'label',
        'is_active',
        'candidacy_open',
        'voting_open',
        'voting_starts_at',
        'voting_ends_at',
        'results_announced',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean',
        'candidacy_open' => 'boolean',
        'voting_open' => 'boolean',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'results_announced' => 'boolean',
    ];
}
