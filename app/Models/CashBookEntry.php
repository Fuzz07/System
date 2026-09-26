<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashBookEntry extends Model
{
    /** Cash carried over from before the treasurer started using the cash book. */
    public const TYPE_OPENING = 'opening';
    /** Money received, e.g. membership and monthly dues (Cash DR / Fees CR). */
    public const TYPE_COLLECTION = 'collection';
    /** Money paid out (Cash CR / category DR). */
    public const TYPE_EXPENSE = 'expense';

    public const TYPES = [
        self::TYPE_OPENING    => 'Beginning Balance',
        self::TYPE_COLLECTION => 'Collection',
        self::TYPE_EXPENSE    => 'Expense',
    ];

    /** Expense columns of the SSC "Records of Expenses", in print order. */
    public const CATEGORIES = [
        'Cash Advances',
        'Office Supplies/Equipments',
        'Office Meals/Snacks',
        'Event Supplies',
        'Traveling and Transportation',
        "Officers' Uniform",
        'Subsidy',
    ];

    protected $fillable = [
        'entry_date', 'type', 'particulars', 'reference_no', 'category', 'amount', 'recorded_by',
    ];

    protected $casts = ['entry_date' => 'date', 'amount' => 'decimal:2'];

    public function recorder() { return $this->belongsTo(User::class, 'recorded_by'); }

    public function isExpense(): bool { return $this->type === self::TYPE_EXPENSE; }
}
