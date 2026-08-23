<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidacy extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'department',
        'position',
        'platform',
        'photo_path',
        'status',
        'school_year',
    ];

    /**
     * Resolved URL of the candidate's campaign photo, or null when they did not
     * upload one. Call sites fall back to the user's initials in that case.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? \App\Helpers\SscHelper::getUploadUrl($this->photo_path)
            : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
