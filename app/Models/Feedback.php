<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    public $timestamps = false;
    protected $fillable = ['student_id', 'message', 'status', 'reply', 'replied_by'];
    protected $casts = ['created_at' => 'datetime'];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function replier() { return $this->belongsTo(User::class, 'replied_by'); }
}
