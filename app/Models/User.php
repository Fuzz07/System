<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\EnrollmentPayment;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'first_name', 'middle_name', 'last_name', 'age', 'year_level',
        'fullname', 'email', 'password', 'role', 'department',
        'student_id', 'profile_pic', 'status', 'position', 'party',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_at' => 'datetime',
        ];
    }

    public function proposals() { return $this->hasMany(Proposal::class, 'officer_id'); }
    public function expenses() { return $this->hasMany(Expense::class, 'officer_id'); }
    public function feedbacks() { return $this->hasMany(Feedback::class, 'student_id'); }
    public function activityLogs() { return $this->hasMany(ActivityLog::class); }
    public function candidacies() { return $this->hasMany(Candidacy::class); }
    public function votes() { return $this->hasMany(Vote::class); }
    public function enrollmentPayments() { return $this->hasMany(EnrollmentPayment::class); }

    public function getAvatarAttribute(): string
    {
        return strtoupper(substr($this->fullname, 0, 1));
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isTreasurer(): bool { return $this->role === 'treasurer'; }
    public function isOfficer(): bool { return $this->role === 'officer'; }
    public function isStudent(): bool { return $this->role === 'student'; }
    public function isDean(): bool { return $this->role === 'dean'; }
    public function isAdminOrTreasurer(): bool { return in_array($this->role, ['admin', 'treasurer']); }

    // Determine whether a student is considered graduated based on configured values
    public function isGraduated(): bool
    {
        $year = (string) ($this->year_level ?? '');
        if (trim($year) === '') return false;

        // Normalize
        $normalized = mb_strtolower(trim($year));

        // If the value contains the word 'graduat' or 'alumni', consider graduated
        if (str_contains($normalized, 'graduat') || str_contains($normalized, 'alumni')) {
            return true;
        }

        $configured = config('ssc.graduated_levels', []);
        foreach ($configured as $val) {
            if (mb_strtolower(trim($val)) === $normalized) return true;
        }

        return false;
    }
}
