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

    /** Every enrollment fee budget, including the pooled "Enrollment Fees" row. */
    public function scopeEnrollmentFees($query)
    {
        return $query->where('title', 'like', self::ENROLLMENT_TITLE_PREFIX . '%');
    }

    public static function consolidateEnrollmentBudgets(?string $schoolYear = null): ?self
    {
        $schoolYear = $schoolYear ?: \App\Helpers\SscHelper::getActiveSchoolYear();
        if (!$schoolYear || $schoolYear === 'N/A') {
            return null;
        }

        // Find or create the single consolidated Enrollment Fees master budget
        $mainBudget = self::firstOrCreate(
            [
                'title'       => self::ENROLLMENT_TITLE_PREFIX,
                'school_year' => $schoolYear,
            ],
            [
                'department'        => 'All Departments',
                'allocated_amount'  => 0,
                'remaining_balance' => 0,
                'status'            => 'Approved',
                'created_by'        => \Illuminate\Support\Facades\Auth::id() ?: 1,
                'notes'             => 'Consolidated enrollment fees collection for all departments.',
            ]
        );

        // Sum all paid enrollment payments for this school year across all departments
        $totalPaid = (float) \App\Models\EnrollmentPayment::where('semester', $schoolYear)
            ->where('status', 'paid')
            ->sum('amount');

        // Check for any split per-department enrollment fee rows and merge them
        $splitBudgets = self::where('school_year', $schoolYear)
            ->where('id', '!=', $mainBudget->id)
            ->where('title', 'like', self::ENROLLMENT_TITLE_PREFIX . '%')
            ->get();

        foreach ($splitBudgets as $sb) {
            $sb->expenses()->update(['budget_id' => $mainBudget->id]);
            $sb->delete();
        }

        $totalSpent = (float) $mainBudget->expenses()->sum('amount');
        $allocated = max((float) $mainBudget->allocated_amount, $totalPaid);
        $remaining = max(0, $allocated - $totalSpent);

        $mainBudget->update([
            'department'        => 'All Departments',
            'allocated_amount'  => $allocated,
            'remaining_balance' => $remaining,
            'status'            => 'Approved',
        ]);

        return $mainBudget;
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
