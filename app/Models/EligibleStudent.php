<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleStudent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'student_name',
        'department',
        'year_level',
        'notes',
        'imported_by',
    ];

    protected $dates = ['created_at'];

    public function importedBy()
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    /**
     * Check if this eligible email is already registered as a user.
     */
    public function getIsRegisteredAttribute(): bool
    {
        return User::where('email', $this->email)->exists();
    }
}
