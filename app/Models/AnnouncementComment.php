<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementComment extends Model
{
    protected $table = 'announcement_comments';
    public $timestamps = false;
    protected $fillable = ['announcement_id', 'user_id', 'comment'];
    protected $casts = ['created_at' => 'datetime'];

    public function announcement() { return $this->belongsTo(Announcement::class); }
    public function user() { return $this->belongsTo(User::class); }
}
