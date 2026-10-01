<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalComment extends Model
{
    protected $table = 'proposal_comments';
    // Stamped from PHP so the time is on the app's clock; see AnnouncementComment.
    public const UPDATED_AT = null;
    protected $fillable = ['proposal_id', 'user_id', 'parent_id', 'comment'];
    protected $casts = ['created_at' => 'datetime'];

    public function proposal() { return $this->belongsTo(Proposal::class); }
    public function user() { return $this->belongsTo(User::class); }
}
