<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\Proposal;
use App\Models\ProposalComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentCommentEditTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;
    private User $student;
    private User $otherStudent;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->officer = $this->user('officer', 'Posting Officer');
        $this->student = $this->user('student', 'Comment Author');
        $this->otherStudent = $this->user('student', 'Someone Else');
    }

    public function test_student_can_edit_and_delete_their_announcement_comment(): void
    {
        $announcement = $this->announcement();

        foreach (['student', 'mobile.student'] as $prefix) {
            $comment = $this->announcementComment($announcement, $this->student, 'First draft');

            $this->actingAs($this->student)
                ->put(route("{$prefix}.announcements.comments.update", [$announcement, $comment]), ['comment' => 'Reworded'])
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertSame('Reworded', $comment->refresh()->comment);

            $this->actingAs($this->student)
                ->delete(route("{$prefix}.announcements.comments.destroy", [$announcement, $comment]))
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertDatabaseMissing('announcement_comments', ['id' => $comment->id]);
        }
    }

    public function test_student_cannot_touch_another_students_announcement_comment(): void
    {
        $announcement = $this->announcement();
        $comment = $this->announcementComment($announcement, $this->otherStudent, 'Not yours');

        $this->actingAs($this->student)
            ->put(route('student.announcements.comments.update', [$announcement, $comment]), ['comment' => 'Hijacked'])
            ->assertForbidden();
        $this->actingAs($this->student)
            ->delete(route('student.announcements.comments.destroy', [$announcement, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('announcement_comments', ['id' => $comment->id, 'comment' => 'Not yours']);
    }

    public function test_edit_controls_show_only_on_the_students_own_comments(): void
    {
        $announcement = $this->announcement();
        $mine = $this->announcementComment($announcement, $this->student, 'Mine');
        $theirs = $this->announcementComment($announcement, $this->otherStudent, 'Theirs');

        foreach (['student', 'mobile.student'] as $prefix) {
            $html = $this->actingAs($this->student)->get(route("{$prefix}.announcements"))->assertOk()->getContent();

            $this->assertStringContainsString(route("{$prefix}.announcements.comments.update", [$announcement, $mine]), $html);
            $this->assertStringNotContainsString(route("{$prefix}.announcements.comments.update", [$announcement, $theirs]), $html);
        }
    }

    public function test_student_can_edit_and_delete_their_proposal_comment(): void
    {
        $proposal = $this->proposal();

        foreach (['student', 'mobile.student'] as $prefix) {
            $comment = $this->proposalComment($proposal, $this->student, 'First draft');

            $this->actingAs($this->student)
                ->get(route("{$prefix}.proposal.show", $proposal))
                ->assertOk()
                ->assertSee(route("{$prefix}.proposal.comments.update", [$proposal, $comment]));

            $this->actingAs($this->student)
                ->put(route("{$prefix}.proposal.comments.update", [$proposal, $comment]), ['comment' => 'Reworded'])
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertSame('Reworded', $comment->refresh()->comment);

            $this->actingAs($this->student)
                ->delete(route("{$prefix}.proposal.comments.destroy", [$proposal, $comment]))
                ->assertRedirect();
            $this->assertDatabaseMissing('proposal_comments', ['id' => $comment->id]);
        }
    }

    public function test_student_cannot_touch_another_users_proposal_comment(): void
    {
        $proposal = $this->proposal();
        $comment = $this->proposalComment($proposal, $this->officer, 'Officer reply');

        $this->actingAs($this->student)
            ->put(route('student.proposal.comments.update', [$proposal, $comment]), ['comment' => 'Hijacked'])
            ->assertForbidden();
        $this->actingAs($this->student)
            ->delete(route('student.proposal.comments.destroy', [$proposal, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('proposal_comments', ['id' => $comment->id, 'comment' => 'Officer reply']);
    }

    public function test_comment_must_belong_to_the_proposal_in_the_url(): void
    {
        $proposal = $this->proposal();
        $other = $this->proposal();
        $comment = $this->proposalComment($other, $this->student, 'Lives elsewhere');

        $this->actingAs($this->student)
            ->delete(route('student.proposal.comments.destroy', [$proposal, $comment]))
            ->assertNotFound();

        $this->assertDatabaseHas('proposal_comments', ['id' => $comment->id]);
    }

    private function announcement(): Announcement
    {
        // Inserted directly so the model's "created" hook doesn't send pushes.
        $id = DB::table('announcements')->insertGetId([
            'title' => 'Campus clean-up drive',
            'content' => 'Announcement body',
            'category' => Announcement::CATEGORY_GENERAL,
            'created_by' => $this->officer->id,
            'created_at' => now(),
        ]);

        return Announcement::findOrFail($id);
    }

    private function announcementComment(Announcement $announcement, User $author, string $text): AnnouncementComment
    {
        return AnnouncementComment::create([
            'announcement_id' => $announcement->id,
            'user_id' => $author->id,
            'comment' => $text,
        ]);
    }

    private function proposal(): Proposal
    {
        return Proposal::create([
            'officer_id' => $this->officer->id,
            'project_title' => 'Sports Fest',
            'description' => 'Project description.',
            'requested_budget' => 1000,
            'status' => 'Approved',
        ]);
    }

    private function proposalComment(Proposal $proposal, User $author, string $text): ProposalComment
    {
        return ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'comment' => $text,
        ]);
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
