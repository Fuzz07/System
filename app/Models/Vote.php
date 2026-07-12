<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'candidacy_id',
        'position',
        'school_year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function candidacy()
    {
        return $this->belongsTo(Candidacy::class);
    }
}
