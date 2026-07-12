<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'budget_id', 'officer_id', 'expense_title', 'amount',
        'receipt', 'description', 'status', 'approved_by', 'admin_notes',
    ];
    protected $casts = ['created_at' => 'datetime', 'amount' => 'decimal:2'];

    public function budget() { return $this->belongsTo(Budget::class); }
    public function officer() { return $this->belongsTo(User::class, 'officer_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
