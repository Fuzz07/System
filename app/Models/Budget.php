<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    /** Title prefix for the auto-maintained per-department enrollment fee budgets. */
    public const ENROLLMENT_TITLE_PREFIX = 'Enrollment Fees';

    /** Bucket for students whose department is missing or blank. */
    public const UNASSIGNED_DEPARTMENT = 'Unassigned';

    public $timestamps = false;
    protected $fillable = [
        'title', 'department', 'allocated_amount', 'remaining_balance',
        'school_year', 'status', 'created_by', 'approved_by', 'notes',
    ];
    protected $casts = ['created_at' => 'datetime', 'allocated_amount' => 'decimal:2', 'remaining_balance' => 'decimal:2'];

    public static function normalizeDepartment(?string $department): string
    {
        $department = trim((string) $department);
        return $department !== '' ? $department : self::UNASSIGNED_DEPARTMENT;
    }

    public static function enrollmentTitleFor(?string $department): string
    {
        return self::ENROLLMENT_TITLE_PREFIX . ' - ' . self::normalizeDepartment($department);
    }

    /** Every enrollment fee budget, including the legacy pooled "Enrollment Fees" row. */
    public function scopeEnrollmentFees($query)
    {
        return $query->where('title', 'like', self::ENROLLMENT_TITLE_PREFIX . '%');
    }

    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function expenses() { return $this->hasMany(Expense::class); }

    public function getUsedPercentAttribute(): int
    {
        if ($this->allocated_amount <= 0) return 0;
        return min(100, round(($this->allocated_amount - $this->remaining_balance) / $this->allocated_amount * 100));
    }
}
