<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'officer_id', 'project_title', 'requested_budget', 'approved_budget',
        'description', 'status', 'approved_by', 'admin_notes',
        'project_status', 'completion_proof', 'proposal_event_date',
        'participant_count', 'objectives', 'budget_items', 'project_image',
    ];
    protected $casts = ['created_at' => 'datetime', 'requested_budget' => 'decimal:2', 'approved_budget' => 'decimal:2'];
    protected $attributes = ['project_status' => 'Ongoing'];

    public function officer() { return $this->belongsTo(User::class, 'officer_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function comments() { return $this->hasMany(ProposalComment::class); }
}
