<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnrollmentPayment extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'user_id', 'amount', 'semester', 'method', 'status', 'reference', 'admin_marked_by', 'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function adminMarker() { return $this->belongsTo(User::class, 'admin_marked_by'); }
}
