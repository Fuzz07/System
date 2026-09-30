<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A registered party list (political party) for SSC elections. Candidates file
 * under one, or run as independents.
 */
class PartyList extends Model
{
    public const INDEPENDENT = 'Independent';

    public $timestamps = false;

    protected $fillable = ['name', 'acronym', 'color', 'description', 'is_active', 'created_by'];

    protected $casts = ['is_active' => 'boolean', 'created_at' => 'datetime'];

    public function candidacies()
    {
        return $this->hasMany(Candidacy::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** The short form shown on badges: the acronym, or the name when there is none. */
    public function getShortNameAttribute(): string
    {
        return $this->acronym ?: $this->name;
    }

    /**
     * Black or white, whichever reads better on the party colour, so a pale
     * party colour never leaves a badge unreadable.
     */
    public function getTextColorAttribute(): string
    {
        $hex = ltrim((string) $this->color, '#');
        if (strlen($hex) !== 6) {
            return '#FFFFFF';
        }

        [$r, $g, $b] = array_map(fn ($part) => hexdec($part) / 255, str_split($hex, 2));
        $linear = fn ($c) => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        $luminance = 0.2126 * $linear($r) + 0.7152 * $linear($g) + 0.0722 * $linear($b);

        return $luminance > 0.4 ? '#111827' : '#FFFFFF';
    }
}
