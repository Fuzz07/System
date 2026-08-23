<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    public $timestamps = false;
    protected $fillable = ['student_id', 'message', 'status', 'reply', 'replied_by'];
    protected $casts = ['created_at' => 'datetime'];

    protected static function booted()
    {
        static::updated(function ($feedback) {
            // Push the reply to the student who raised it. Hooked to the model
            // rather than the admin controller so any future reply path -- an
            // officer screen, an API, a console command -- notifies too.
            //
            // wasChanged() rather than a plain "is there a reply" check: editing
            // an existing reply is a new message to that student and should ping
            // them again, while saving the row for any other reason must not.
            if ($feedback->wasChanged('reply') && filled($feedback->reply)) {
                \App\Services\PushNotificationService::sendFeedbackReplyNotification($feedback);
            }
        });
    }

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function replier() { return $this->belongsTo(User::class, 'replied_by'); }
}
