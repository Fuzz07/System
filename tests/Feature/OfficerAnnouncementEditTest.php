<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OfficerAnnouncementEditTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;
    private User $otherOfficer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->officer = $this->user('officer', 'Posting Officer');
        $this->otherOfficer = $this->user('officer', 'Other Officer');
    }

    public function test_officer_can_edit_their_own_announcement(): void
    {
        $announcement = $this->announcement($this->officer, 'Original title');

        $this->actingAs($this->officer)
            ->put(route('officer.announcements.update', $announcement), [
                'title' => 'Updated title',
                'content' => 'Updated content',
                'category' => Announcement::CATEGORY_LOST_ITEM,
            ])
            ->assertRedirect(route('officer.announcements'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('announcements', [
            'id' => $announcement->id,
            'title' => 'Updated title',
            'content' => 'Updated content',
            'category' => Announcement::CATEGORY_LOST_ITEM,
        ]);
    }

    public function test_officer_cannot_edit_or_delete_another_officers_announcement(): void
    {
        $announcement = $this->announcement($this->otherOfficer, 'Not yours');

        $this->actingAs($this->officer)
            ->put(route('officer.announcements.update', $announcement), [
                'title' => 'Hijacked',
                'content' => 'Hijacked',
            ])
            ->assertForbidden();

        $this->actingAs($this->officer)
            ->delete(route('officer.announcements.destroy', $announcement))
            ->assertForbidden();

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => 'Not yours']);
    }

    public function test_edit_controls_appear_only_on_the_officers_own_posts(): void
    {
        $own = $this->announcement($this->officer, 'My post');
        $other = $this->announcement($this->otherOfficer, 'Their post');

        $html = $this->actingAs($this->officer)
            ->get(route('officer.announcements'))
            ->assertOk()
            ->assertSee('Your post')
            ->assertSee('id="editAnnModal' . $own->id . '"', false)
            ->assertSee('data-bs-target="#editAnnModal' . $own->id . '"', false)
            ->assertDontSee('id="editAnnModal' . $other->id . '"', false)
            ->getContent();

        $this->assertSame(1, substr_count($html, 'class="post-card-actions"'));
    }

    public function test_authorship_check_tolerates_ids_returned_as_strings(): void
    {
        $announcement = new Announcement();
        $announcement->setRawAttributes(['created_by' => (string) $this->officer->id]);

        $this->assertTrue($announcement->isAuthoredBy($this->officer));
        $this->assertFalse($announcement->isAuthoredBy($this->otherOfficer));
        $this->assertFalse($announcement->isAuthoredBy(null));
    }

    private function announcement(User $author, string $title): Announcement
    {
        // Inserted directly so the model's "created" hook doesn't send pushes.
        $id = DB::table('announcements')->insertGetId([
            'title' => $title,
            'content' => 'Announcement body',
            'category' => Announcement::CATEGORY_GENERAL,
            'created_by' => $author->id,
            'created_at' => now(),
        ]);

        return Announcement::findOrFail($id);
    }

    private function user(string $role, string $name): User
    {
        return User::create([
            'fullname' => $name,
            'email' => str_replace(' ', '.', strtolower($name)) . '@example.com',
            'password' => 'password',
            'role' => $role,
            'status' => 'active',
        ]);
    }
}
