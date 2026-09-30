<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidacy extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'department',
        'position',
        'platform',
        'photo_path',
        'status',
        'school_year',
        'party_list_id',
    ];

    /** Every screen that shows a candidate also shows their party list. */
    protected $with = ['partyList'];

    /**
     * Resolved URL of the candidate's campaign photo, or null when they did not
     * upload one. Call sites fall back to the user's initials in that case.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? \App\Helpers\SscHelper::getUploadUrl($this->photo_path)
            : null;
    }

    /** The party list's full name, or "Independent". */
    public function getPartyNameAttribute(): string
    {
        return $this->partyList?->name ?? PartyList::INDEPENDENT;
    }

    /**
     * Whether $partyListId already fields a live (pending or approved) candidate
     * for $position in $schoolYear. A party list runs one candidate per seat;
     * a declined filing frees the slot again.
     */
    public static function partySlotTaken(?int $partyListId, string $position, string $schoolYear): bool
    {
        return $partyListId !== null && self::where('party_list_id', $partyListId)
            ->where('position', $position)
            ->where('school_year', $schoolYear)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partyList()
    {
        return $this->belongsTo(PartyList::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
