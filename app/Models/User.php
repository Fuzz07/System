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
        'admin_device_token', 'remember_token',
    ];

    protected $hidden = ['password', 'remember_token'];

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

    /**
     * Resolved URL of the officer's photo, or null when they have none.
     *
     * Two shapes live in profile_pic. Officers added by hand carry a bare
     * filename dropped into public/assets/img; officers promoted from an
     * election carry the campaign photo they filed with, which is a Cloudinary
     * URL or a path on the public disk. Both resolve here so the views do not
     * have to know which kind they were handed.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (blank($this->profile_pic)) {
            return null;
        }

        $isStoredUpload = str_contains($this->profile_pic, '/');

        return $isStoredUpload
            ? \App\Helpers\SscHelper::getUploadUrl($this->profile_pic)
            : asset('assets/img/' . $this->profile_pic);
    }

    /**
     * The public SSC roster, in council order rather than alphabetically, so the
     * President heads the list and department representatives sit below the
     * executive posts. Shared by the mobile and desktop officers pages.
     */
    public function scopeActiveOfficers($query)
    {
        return $query->whereIn('role', ['officer', 'treasurer'])
            ->where('status', 'active')
            ->orderByRaw("CASE
                WHEN position = 'SSC President' THEN 1
                WHEN position = 'SSC Vice President' THEN 2
                WHEN position = 'SSC Secretary' THEN 3
                WHEN position = 'SSC Treasurer' THEN 4
                WHEN position LIKE '%Representative%' THEN 5
                ELSE 6
            END")
            ->orderBy('fullname');
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
