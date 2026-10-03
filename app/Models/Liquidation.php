<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Liquidation extends Model
{
    protected $table = 'liquidations';
    public $timestamps = false;
    protected $fillable = [
        'proposal_id', 'officer_id', 'title', 'file_path', 'notes', 'status',
        'reviewed_by', 'reviewed_at', 'review_notes',
    ];
    protected $casts = ['created_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function proposal() { return $this->belongsTo(Proposal::class); }
    public function officer() { return $this->belongsTo(User::class, 'officer_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
