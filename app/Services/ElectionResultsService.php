<?php

namespace App\Services;

use App\Models\Candidacy;
use App\Models\SchoolYear;

class ElectionResultsService
{
    /**
     * Everything an election-results screen needs, for whichever school year is
     * being looked at.
     *
     * The student, mobile, dean and admin screens each used to carry their own
     * copy of this query, all four pinned to the active school year, so a
     * finished election became unreachable the moment the admin rolled the year
     * over. They now share this one lookup and all four get the same archive.
     *
     * @param  string|null  $requestedLabel  School year label from the request.
     * @return array{activeSy: ?SchoolYear, selectedSy: ?SchoolYear, archivedYears: \Illuminate\Support\Collection, isArchive: bool, candidatesByPosition: array}
     */
    public static function forYear(?string $requestedLabel = null): array
    {
        $activeSy = SchoolYear::where('is_active', 1)->first();

        // Only years that actually ran an election belong in the picker — an
        // empty year would just be a dead end for whoever selected it.
        $labelsWithResults = Candidacy::where('status', 'approved')
            ->distinct()
            ->pluck('school_year');

        $archivedYears = SchoolYear::whereIn('label', $labelsWithResults)
            ->orderByDesc('label')
            ->get();

        // The label arrives from a query string, so it is honoured only when it
        // names a year we genuinely hold results for.
        $selectedSy = $requestedLabel
            ? $archivedYears->firstWhere('label', $requestedLabel)
            : null;

        // Otherwise: this year's election if it has one, else the latest we kept.
        $selectedSy ??= $activeSy && $labelsWithResults->contains($activeSy->label)
            ? $activeSy
            : $archivedYears->first();

        $candidatesByPosition = [];

        if ($selectedSy) {
            $candidates = Candidacy::with('user')
                ->withCount('votes')
                ->where('school_year', $selectedSy->label)
                ->where('status', 'approved')
                ->get();

            foreach ($candidates as $c) {
                $candidatesByPosition[$c->position][] = $c;
            }

            foreach ($candidatesByPosition as &$cands) {
                usort($cands, fn ($a, $b) => $b->votes_count <=> $a->votes_count);
            }
            unset($cands);
        }

        return [
            'activeSy'             => $activeSy,
            'selectedSy'           => $selectedSy,
            'archivedYears'        => $archivedYears,
            'isArchive'            => $selectedSy && (!$activeSy || $selectedSy->label !== $activeSy->label),
            'candidatesByPosition' => $candidatesByPosition,
            'partyStandings'       => self::partyStandings($candidatesByPosition),
        ];
    }

    /**
     * How each party list (and the independents) did across every position:
     * seats won — the top candidate of a position, once they have votes, the
     * same rule the announcement uses to promote winners — then candidates
     * fielded and total votes. Most seats first, then most votes.
     *
     * @param  array<string, array<int, Candidacy>>  $candidatesByPosition  Each list sorted by votes, highest first.
     * @return array<int, array{party: ?\App\Models\PartyList, seats: int, candidates: int, votes: int}>
     */
    public static function partyStandings(array $candidatesByPosition): array
    {
        $standings = [];

        foreach ($candidatesByPosition as $candidates) {
            foreach ($candidates as $index => $candidate) {
                $key = $candidate->party_list_id ?? 'independent';
                $standings[$key] ??= ['party' => $candidate->partyList, 'seats' => 0, 'candidates' => 0, 'votes' => 0];
                $standings[$key]['candidates']++;
                $standings[$key]['votes'] += (int) $candidate->votes_count;
                if ($index === 0 && $candidate->votes_count > 0) {
                    $standings[$key]['seats']++;
                }
            }
        }

        usort($standings, fn ($a, $b) => [$b['seats'], $b['votes']] <=> [$a['seats'], $a['votes']]);

        return $standings;
    }
}
