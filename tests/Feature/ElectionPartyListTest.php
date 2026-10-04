<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Candidacy;
use App\Models\PartyList;
use App\Models\SchoolYear;
use App\Models\User;
use App\Models\Vote;
use App\Services\ElectionResultsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElectionPartyListTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private SchoolYear $schoolYear;
    private int $userCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        $this->schoolYear = SchoolYear::create(['label' => '2026-2027', 'is_active' => true, 'candidacy_open' => true]);
        $this->admin = $this->user('admin', 'Site Admin');
    }

    public function test_admin_registers_and_manages_party_lists(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.party_lists.store'), [
                'name' => 'Abante Party',
                'acronym' => 'abante',
                'color' => '#1d4ed8',
                'description' => 'Forward together for a transparent council.',
            ])
            ->assertRedirect(route('admin.candidacies'))
            ->assertSessionHas('success');

        $party = PartyList::firstOrFail();
        $this->assertSame('ABANTE', $party->acronym);
        $this->assertSame('#1D4ED8', $party->color);
        $this->assertTrue($party->is_active);

        $this->actingAs($this->admin)
            ->post(route('admin.party_lists.store'), ['name' => 'Abante Party', 'color' => '#000000'])
            ->assertSessionHasErrors(['name' => 'A party list with this name is already registered.']);
        $this->actingAs($this->admin)
            ->post(route('admin.party_lists.store'), ['name' => 'Bagong Lakas', 'color' => 'blue'])
            ->assertSessionHasErrors('color');

        $this->actingAs($this->admin)
            ->put(route('admin.party_lists.update', $party), ['name' => 'Abante Youth Party', 'acronym' => 'AYP', 'color' => '#DC2626'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Abante Youth Party', $party->fresh()->name);

        $this->actingAs($this->admin)->patch(route('admin.party_lists.toggle', $party));
        $this->assertFalse($party->fresh()->is_active);
        $this->actingAs($this->admin)->patch(route('admin.party_lists.toggle', $party));
        $this->assertTrue($party->fresh()->is_active);

        $this->actingAs($this->admin)
            ->get(route('admin.candidacies'))
            ->assertOk()
            ->assertSee('Registered Party Lists')
            ->assertSee('Abante Youth Party')
            ->assertSee('Open for filing');
    }

    public function test_a_party_list_with_candidates_cannot_be_deleted(): void
    {
        $used = $this->party('Abante Party', 'ABANTE');
        $unused = $this->party('Unused Party', 'UNUSED');
        $this->candidacy($this->user('student', 'Party Member'), 'SSC President', $used);

        $this->actingAs($this->admin)
            ->delete(route('admin.party_lists.destroy', $used))
            ->assertSessionHas('danger');
        $this->assertModelExists($used);

        $this->actingAs($this->admin)
            ->delete(route('admin.party_lists.destroy', $unused))
            ->assertSessionHas('success');
        $this->assertModelMissing($unused);
    }

    public function test_student_files_under_a_party_list_or_as_an_independent(): void
    {
        $party = $this->party('Abante Party', 'ABANTE');
        $member = $this->user('student', 'Party Member');
        $independent = $this->user('student', 'Lone Runner');

        $this->actingAs($member)
            ->post(route('student.candidacy.store'), $this->filing('SSC President', $party->id))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
        $this->assertSame($party->id, $member->candidacies()->first()->party_list_id);

        $this->actingAs($member)
            ->get(route('student.candidacy'))
            ->assertOk()
            ->assertSee('Abante Party');

        $this->actingAs($independent)
            ->post(route('mobile.student.candidacy.store'), $this->filing('SSC Secretary', ''))
            ->assertSessionHasNoErrors();
        $this->assertNull($independent->candidacies()->first()->party_list_id);

        $this->actingAs($independent)
            ->get(route('mobile.student.candidacy'))
            ->assertOk()
            ->assertSee('Independent');
    }

    public function test_the_filing_form_lists_only_open_party_lists(): void
    {
        $this->party('Abante Party', 'ABANTE');
        $this->party('Closed Party', 'CLOSED', active: false);

        $this->actingAs($this->user('student', 'Would Be Candidate'))
            ->get(route('student.candidacy'))
            ->assertOk()
            ->assertSee('name="party_list_id"', false)
            ->assertSee('ABANTE — Abante Party')
            ->assertDontSee('Closed Party')
            ->assertSee('Independent (no party list)');
    }

    public function test_a_party_list_fields_one_candidate_per_position(): void
    {
        $party = $this->party('Abante Party', 'ABANTE');
        $first = $this->user('student', 'First Member');
        $second = $this->user('student', 'Second Member');
        $third = $this->user('student', 'Third Member');

        $this->actingAs($first)->post(route('student.candidacy.store'), $this->filing('SSC President', $party->id));

        $this->actingAs($second)
            ->post(route('student.candidacy.store'), $this->filing('SSC President', $party->id))
            ->assertSessionHasErrors(['party_list_id' => 'Abante Party already has a candidate for SSC President this school year. Choose another position, another party list, or run as an independent.']);
        $this->assertSame(0, $second->candidacies()->count());

        // Another seat is fine.
        $this->actingAs($second)
            ->post(route('student.candidacy.store'), $this->filing('SSC Vice President', $party->id))
            ->assertSessionHasNoErrors();

        // A declined filing frees the slot again.
        $first->candidacies()->update(['status' => 'rejected']);
        $this->actingAs($third)
            ->post(route('student.candidacy.store'), $this->filing('SSC President', $party->id))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $third->candidacies()->count());
    }

    public function test_filing_under_a_closed_party_list_is_rejected(): void
    {
        $closed = $this->party('Closed Party', 'CLOSED', active: false);
        $student = $this->user('student', 'Late Filer');

        $this->actingAs($student)
            ->post(route('student.candidacy.store'), $this->filing('SSC President', $closed->id))
            ->assertSessionHasErrors('party_list_id');
        $this->assertSame(0, $student->candidacies()->count());
    }

    public function test_party_lists_show_on_review_screens_and_results_with_standings(): void
    {
        $party = $this->party('Abante Party', 'ABANTE', '#1D4ED8');
        $presidentA = $this->candidacy($this->user('student', 'Party President'), 'SSC President', $party, 'approved');
        $presidentB = $this->candidacy($this->user('student', 'Independent President'), 'SSC President', null, 'approved');
        $vice = $this->candidacy($this->user('student', 'Party Vice'), 'SSC Vice President', $party, 'approved');
        $this->votes($presidentA, 2);
        $this->votes($presidentB, 1);
        $this->votes($vice, 1);

        $standings = ElectionResultsService::forYear()['partyStandings'];
        $this->assertSame('Abante Party', $standings[0]['party']->name);
        $this->assertSame(2, $standings[0]['seats']);
        $this->assertSame(3, $standings[0]['votes']);
        $this->assertNull($standings[1]['party']);
        $this->assertSame(0, $standings[1]['seats']);

        $this->actingAs($this->admin)
            ->get(route('admin.election.results'))
            ->assertOk()
            ->assertSee('Party List Standings')
            ->assertSee('Seats currently leading')
            ->assertSee('ABANTE')
            ->assertSee('Independent')
            ->assertSee('Party President (ABANTE)');

        $this->actingAs($this->admin)
            ->get(route('admin.candidacies'))
            ->assertSee('title="Abante Party"', false);

        $dean = $this->user('dean', 'BSIT Dean');
        $this->actingAs($dean)
            ->get(route('dean.dashboard'))
            ->assertOk()
            ->assertSee('title="Abante Party"', false);

        $this->actingAs($this->user('student', 'Voter'))
            ->get(route('mobile.student.election.results'))
            ->assertOk()
            ->assertSee('Party List Standings');
    }

    public function test_announcing_results_records_the_winners_party(): void
    {
        $party = $this->party('Abante Party', 'ABANTE');
        $winner = $this->user('student', 'Winning Candidate');
        $this->votes($this->candidacy($winner, 'SSC President', $party, 'approved'), 3);
        $this->schoolYear->update([
            'candidacy_open' => false,
            'voting_open' => false,
            'voting_starts_at' => now()->subHour(),
            'voting_ends_at' => now()->subMinute(),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.election.announce'))
            ->assertSessionHas('success');

        $winner->refresh();
        $this->assertSame('officer', $winner->role);
        $this->assertSame('Abante Party', $winner->party);
        $this->assertStringContainsString('Winning Candidate, Abante Party', Announcement::latest('id')->value('content'));
    }

    private function party(string $name, string $acronym, string $color = '#4F46E5', bool $active = true): PartyList
    {
        return PartyList::create(['name' => $name, 'acronym' => $acronym, 'color' => $color, 'is_active' => $active]);
    }

    private function candidacy(User $user, string $position, ?PartyList $party, string $status = 'pending'): Candidacy
    {
        return Candidacy::create([
            'user_id' => $user->id,
            'department' => 'BSIT',
            'position' => $position,
            'platform' => 'A clear and transparent council for every student.',
            'status' => $status,
            'school_year' => $this->schoolYear->label,
            'party_list_id' => $party?->id,
        ]);
    }

    private function votes(Candidacy $candidacy, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            Vote::create([
                'user_id' => $this->user('student', "Voter {$candidacy->id}-{$i}")->id,
                'candidacy_id' => $candidacy->id,
                'position' => $candidacy->position,
                'school_year' => $candidacy->school_year,
            ]);
        }
    }

    private function filing(string $position, int|string $partyListId): array
    {
        return [
            'position' => $position,
            'party_list_id' => $partyListId,
            'platform' => 'A clear and transparent council for every student.',
        ];
    }

    private function user(string $role, string $name): User
    {
        $this->userCount++;

        return User::create([
            'fullname' => $name,
            'email' => "user{$this->userCount}@example.com",
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
            'department' => 'BSIT',
            'year_level' => '3rd Year',
            'student_id' => sprintf('2026-%04d', $this->userCount),
        ]);
    }
}
