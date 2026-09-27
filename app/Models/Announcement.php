<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    public const CATEGORY_GENERAL = 'general';
    public const CATEGORY_LOST_ITEM = 'lost_item';

    public const CATEGORIES = [
        self::CATEGORY_GENERAL => 'General',
        self::CATEGORY_LOST_ITEM => 'Lost & Found',
    ];

    public $timestamps = false;
    protected $fillable = ['title', 'content', 'image_path', 'category', 'created_by', 'project_id'];
    protected $casts = ['created_at' => 'datetime', 'created_by' => 'integer'];

    protected static function booted()
    {
        static::created(function ($announcement) {
            // Send push notification to all students when announcement is created
            \App\Services\PushNotificationService::sendAnnouncementNotification($announcement);
        });
    }

    public function author() { return $this->belongsTo(User::class, 'created_by'); }
    public function officer() { return $this->belongsTo(User::class, 'created_by'); }
    public function proposal() { return $this->belongsTo(Proposal::class, 'project_id'); }
    public function comments() { return $this->hasMany(AnnouncementComment::class)->orderByDesc('created_at'); }

    /**
     * Whether $user posted this announcement. Compared as integers because some
     * database drivers hand foreign keys back as strings.
     */
    public function isAuthoredBy(?User $user): bool
    {
        return $user !== null && (int) $this->created_by === (int) $user->getKey();
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES[self::CATEGORY_GENERAL];
    }
}
