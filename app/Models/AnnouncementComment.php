<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementComment extends Model
{
    protected $table = 'announcement_comments';
    public $timestamps = false;
    protected $fillable = ['announcement_id', 'user_id', 'comment'];
    protected $casts = ['created_at' => 'datetime'];

    protected static function booted()
    {
        static::created(function ($comment) {
            // Let the announcement's author know there is feedback to read.
            // Hooked to the model so the web, mobile and API comment paths all
            // notify, and nobody is pinged about their own comment.
            $author = $comment->announcement?->author;
            if (! $author || (int) $author->getKey() === (int) $comment->user_id) {
                return;
            }

            try {
                $author->notify(new \App\Notifications\AnnouncementCommentedNotification($comment));
            } catch (\Throwable $e) {
                // Fail silently; the comment itself is already saved.
            }
        });
    }

    public function announcement() { return $this->belongsTo(Announcement::class); }
    public function user() { return $this->belongsTo(User::class); }
}
