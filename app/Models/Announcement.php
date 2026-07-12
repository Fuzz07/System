<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public $timestamps = false;
    protected $fillable = ['title', 'content', 'created_by', 'project_id'];
    protected $casts = ['created_at' => 'datetime'];

    public function author() { return $this->belongsTo(User::class, 'created_by'); }
    public function proposal() { return $this->belongsTo(Proposal::class, 'project_id'); }
}
