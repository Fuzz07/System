<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalComment extends Model
{
    protected $table = 'proposal_comments';
    public $timestamps = false;
    protected $fillable = ['proposal_id', 'user_id', 'comment'];
    protected $casts = ['created_at' => 'datetime'];

    public function proposal() { return $this->belongsTo(Proposal::class); }
    public function user() { return $this->belongsTo(User::class); }
}
