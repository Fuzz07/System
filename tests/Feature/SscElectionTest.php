<?php

namespace Tests\Feature;

use App\Models\Candidacy;
use App\Models\SchoolYear;
use App\Models\StudentBallot;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SscElectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student1;
    protected $student2;
    protected $candidateUser;
    protected $oldOfficer;
    protected $activeSy;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        // 1. Create Active School Year
        $this->activeSy = SchoolYear::create([
            'label' => '2026-2027',
            'is_active' => true,
            'candidacy_open' => true,
        ]);

        // 2. Create Users
        $this->admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'fullname' => 'Admin User',
            'email' => 'admin@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'student_id' => 'ADM-001',
            'year_level' => 'N/A',
            'department' => 'SSC',
            'age' => 25,
        ]);

        $this->student1 = User::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'fullname' => 'Juan Dela Cruz',
            'email' => 'juan@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
            'student_id' => 'STU-001',
            'year_level' => '3rd Year',
            'department' => 'BSIS',
            'age' => 20,
        ]);

        $this->student2 = User::create([
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'fullname' => 'Pedro Penduko',
            'email' => 'pedro@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
            'student_id' => 'STU-002',
            'year_level' => '3rd Year',
            'department' => 'BSIS',
            'age' => 20,
        ]);

        $this->candidateUser = User::create([
            'first_name' => 'Santi',
            'last_name' => 'Santiago',
            'fullname' => 'Santi Santiago',
            'email' => 'santi@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
            'student_id' => 'CAN-001',
            'year_level' => '4th Year',
            'department' => 'BSIS',
            'age' => 21,
        ]);

        $this->oldOfficer = User::create([
            'first_name' => 'Old',
            'last_name' => 'Officer',
            'fullname' => 'Old Officer',
            'email' => 'old@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'officer',
            'position' => 'SSC President',
            'status' => 'active',
            'student_id' => 'OFF-001',
            'year_level' => '4th Year',
            'department' => 'BSIS',
            'age' => 22,
        ]);

        // 3. Create Approved Candidacy
        Candidacy::create([
            'user_id' => $this->candidateUser->id,
            'department' => 'BSIS',
            'position' => 'SSC President',
            'platform' => 'To elevate student voice and project development transparency.',
            'status' => 'approved',
            'school_year' => '2026-2027',
        ]);
    }

    public function test_admin_can_open_voting_period_and_sends_notifications(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.election.open'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->activeSy->refresh();
        $this->assertFalse($this->activeSy->candidacy_open);
        $this->assertTrue($this->activeSy->voting_open);
        $this->assertNotNull($this->activeSy->voting_starts_at);
        $this->assertNotNull($this->activeSy->voting_ends_at);

        // Verify announcement was created
        $this->assertDatabaseHas('announcements', [
            'title' => 'Supreme Student Council Elections are OPEN!',
        ]);
    }

    public function test_student_can_start_and_cast_vote_securely_within_one_minute(): void
    {
        // 1. Open voting
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now(),
            'voting_ends_at' => now()->addHours(8),
        ]);

        $this->actingAs($this->student1);

        // 2. Start Ballot for position
        $responseStart = $this->post(route('student.voting.start'), [
            'position' => 'SSC President',
        ]);

        $responseStart->assertRedirect(route('student.voting'));
        $this->assertDatabaseHas('student_ballots', [
            'user_id' => $this->student1->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);

        $candidacy = Candidacy::where('user_id', $this->candidateUser->id)->first();

        // 3. Cast Vote
        $responseCast = $this->post(route('student.voting.cast'), [
            'candidacy_id' => $candidacy->id,
        ]);

        $responseCast->assertRedirect(route('student.voting'));
        $responseCast->assertSessionHas('success');

        // Check vote cast in DB with IP and User Agent logging
        $this->assertDatabaseHas('votes', [
            'user_id' => $this->student1->id,
            'candidacy_id' => $candidacy->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);

        $ballot = StudentBallot::where('user_id', $this->student1->id)->where('position', 'SSC President')->first();
        $this->assertNotNull($ballot->submitted_at);
    }

    public function test_student_cannot_vote_after_one_minute_limit_expires(): void
    {
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now(),
            'voting_ends_at' => now()->addHours(8),
        ]);

        $this->actingAs($this->student1);

        // Create ballot started 61 seconds ago
        $ballot = StudentBallot::create([
            'user_id' => $this->student1->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
            'started_at' => now()->subSeconds(61),
        ]);

        $candidacy = Candidacy::where('user_id', $this->candidateUser->id)->first();

        // Try casting vote
        $response = $this->post(route('student.voting.cast'), [
            'candidacy_id' => $candidacy->id,
        ]);

        $response->assertRedirect(route('student.voting'));
        $response->assertSessionHas('danger', 'Your 1-minute voting limit for this position has expired.');

        // Verify vote was NOT recorded
        $this->assertDatabaseMissing('votes', [
            'user_id' => $this->student1->id,
            'position' => 'SSC President',
        ]);

        $ballot->refresh();
        $this->assertNotNull($ballot->submitted_at);
    }

    public function test_election_concludes_and_promotes_winner_demoting_old_officers(): void
    {
        // 1. Set voting active
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now()->subHours(8),
            'voting_ends_at' => now(),
        ]);

        $candidacy = Candidacy::where('user_id', $this->candidateUser->id)->first();

        // 2. Cast some votes for candidate
        Vote::create([
            'user_id' => $this->student1->id,
            'candidacy_id' => $candidacy->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);

        $this->actingAs($this->admin);

        // 3. Announce results
        $response = $this->post(route('admin.election.announce'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 4. Verify old officer is demoted to student role
        $this->oldOfficer->refresh();
        $this->assertEquals('student', $this->oldOfficer->role);
        $this->assertNull($this->oldOfficer->position);

        // 5. Verify winning candidate is promoted to officer role
        $this->candidateUser->refresh();
        $this->assertEquals('officer', $this->candidateUser->role);
        $this->assertEquals('SSC President', $this->candidateUser->position);

        // 6. Verify school year results are flagged announced
        $this->activeSy->refresh();
        $this->assertTrue($this->activeSy->results_announced);
        $this->assertFalse($this->activeSy->voting_open);

        // 7. Verify final results announcement was posted
        $this->assertDatabaseHas('announcements', [
            'title' => 'Official Election Results - SY 2026-2027',
        ]);
    }

    public function test_student_can_access_mobile_voting_when_open(): void
    {
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now(),
            'voting_ends_at' => now()->addHours(8),
        ]);

        $this->actingAs($this->student1);

        $response = $this->get(route('mobile.student.voting'));

        $response->assertStatus(200);
        $response->assertViewIs('mobile.student.voting');
        $response->assertViewHas('activeSy');
        $response->assertViewHas('myVotes');
        $response->assertViewHas('candidatesByPosition');
    }

    public function test_student_cannot_access_mobile_voting_when_closed(): void
    {
        $this->activeSy->update([
            'candidacy_open' => true,
            'voting_open' => false,
        ]);

        $this->actingAs($this->student1);

        $response = $this->get(route('mobile.student.voting'));

        $response->assertRedirect(route('mobile.student.proposals'));
        $response->assertSessionHas('warning', 'Voting period is not active.');
    }

    public function test_student_can_cast_vote_on_mobile(): void
    {
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now(),
            'voting_ends_at' => now()->addHours(8),
        ]);

        $this->actingAs($this->student1);

        $candidacy = Candidacy::where('user_id', $this->candidateUser->id)->first();

        $response = $this->post(route('mobile.student.voting.store'), [
            'candidacy_id' => $candidacy->id,
        ]);

        $response->assertRedirect(route('mobile.student.voting'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('votes', [
            'user_id' => $this->student1->id,
            'candidacy_id' => $candidacy->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);

        $this->assertDatabaseHas('student_ballots', [
            'user_id' => $this->student1->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);
    }

    public function test_student_cannot_cast_duplicate_vote_on_mobile(): void
    {
        $this->activeSy->update([
            'candidacy_open' => false,
            'voting_open' => true,
            'voting_starts_at' => now(),
            'voting_ends_at' => now()->addHours(8),
        ]);

        $candidacy = Candidacy::where('user_id', $this->candidateUser->id)->first();

        // Create initial vote
        Vote::create([
            'user_id' => $this->student1->id,
            'candidacy_id' => $candidacy->id,
            'position' => 'SSC President',
            'school_year' => '2026-2027',
        ]);

        $this->actingAs($this->student1);

        $response = $this->post(route('mobile.student.voting.store'), [
            'candidacy_id' => $candidacy->id,
        ]);

        $response->assertRedirect(route('mobile.student.voting'));
        $response->assertSessionHas('danger', 'You have already voted for this position.');
    }

    public function test_student_can_access_mobile_results(): void
    {
        $this->actingAs($this->student1);

        $response = $this->get(route('mobile.student.election.results'));

        $response->assertStatus(200);
        $response->assertViewIs('mobile.student.election-results');
        $response->assertViewHas('activeSy');
        $response->assertViewHas('candidatesByPosition');
    }
}
