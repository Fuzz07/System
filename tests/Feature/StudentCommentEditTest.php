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

            // Quoted whole, since each comment's reply URL starts with its update URL.
            $this->assertStringContainsString('"' . route("{$prefix}.announcements.comments.update", [$announcement, $mine]) . '"', $html);
            $this->assertStringNotContainsString('"' . route("{$prefix}.announcements.comments.update", [$announcement, $theirs]) . '"', $html);
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

    public function test_student_can_reply_to_another_students_comment(): void
    {
        $announcement = $this->announcement();
        $proposal = $this->proposal();

        foreach (['student', 'mobile.student'] as $prefix) {
            $comment = $this->announcementComment($announcement, $this->otherStudent, 'Anyone joining?');
            $this->actingAs($this->student)
                ->post(route("{$prefix}.announcements.comments.reply", [$announcement, $comment]), ['comment' => 'I am!'])
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertDatabaseHas('announcement_comments', [
                'announcement_id' => $announcement->id,
                'user_id' => $this->student->id,
                'parent_id' => $comment->id,
                'comment' => 'I am!',
            ]);

            $comment = $this->proposalComment($proposal, $this->otherStudent, 'Good idea');
            $this->actingAs($this->student)
                ->post(route("{$prefix}.proposal.comments.reply", [$proposal, $comment]), ['comment' => 'Agreed'])
                ->assertRedirect()
                ->assertSessionHas('success');
            $this->assertDatabaseHas('proposal_comments', [
                'proposal_id' => $proposal->id,
                'user_id' => $this->student->id,
                'parent_id' => $comment->id,
                'comment' => 'Agreed',
            ]);
        }
    }

    public function test_replying_to_a_reply_joins_the_same_thread(): void
    {
        $announcement = $this->announcement();
        $comment = $this->announcementComment($announcement, $this->otherStudent, 'Top');
        $reply = $this->announcementComment($announcement, $this->student, 'First reply', $comment);

        $this->actingAs($this->otherStudent)
            ->post(route('student.announcements.comments.reply', [$announcement, $reply]), ['comment' => 'Second reply']);

        $this->assertSame($comment->id, AnnouncementComment::where('comment', 'Second reply')->value('parent_id'));
    }

    public function test_replies_show_under_their_comment_on_every_page(): void
    {
        $announcement = $this->announcement();
        $older = $this->announcementComment($announcement, $this->otherStudent, 'Older comment');
        $older->forceFill(['created_at' => now()->subMinutes(5)])->save();
        $this->announcementComment($announcement, $this->otherStudent, 'Newer comment');
        $this->announcementComment($announcement, $this->student, 'Reply to older', $older);

        // Comments run newest first; a reply stays under the one it answers.
        foreach (['student.announcements', 'mobile.student.announcements'] as $page) {
            $this->actingAs($this->student)
                ->get(route($page))
                ->assertSeeInOrder(['Newer comment', 'Older comment', 'Reply to older'])
                ->assertSee(route(str_replace('announcements', 'announcements.comments.reply', $page), [$announcement, $older]));
        }

        $proposal = $this->proposal();
        $top = $this->proposalComment($proposal, $this->otherStudent, 'Proposal comment');
        $this->proposalComment($proposal, $this->student, 'Proposal reply', $top);

        foreach (['student.proposal.show', 'mobile.student.proposal.show'] as $page) {
            $this->actingAs($this->student)
                ->get(route($page, $proposal))
                ->assertSeeInOrder(['Proposal comment', 'Proposal reply']);
        }

        $this->actingAs($this->officer)
            ->get(route('officer.proposals'))
            ->assertSeeInOrder(['Proposal comment', 'Proposal reply']);
    }

    public function test_reply_must_target_a_comment_on_the_same_post(): void
    {
        $announcement = $this->announcement();
        $elsewhere = $this->announcementComment($this->announcement(), $this->otherStudent, 'On another post');

        $this->actingAs($this->student)
            ->post(route('student.announcements.comments.reply', [$announcement, $elsewhere]), ['comment' => 'Misplaced'])
            ->assertNotFound();

        $this->assertDatabaseMissing('announcement_comments', ['comment' => 'Misplaced']);
    }

    public function test_deleting_a_comment_removes_its_replies(): void
    {
        $announcement = $this->announcement();
        $comment = $this->announcementComment($announcement, $this->student, 'My comment');
        $reply = $this->announcementComment($announcement, $this->otherStudent, 'Their reply', $comment);

        $this->actingAs($this->student)
            ->delete(route('student.announcements.comments.destroy', [$announcement, $comment]));

        $this->assertDatabaseMissing('announcement_comments', ['id' => $reply->id]);
    }

    public function test_new_comments_are_stamped_on_the_app_clock(): void
    {
        // The test database stamps CURRENT_TIMESTAMP in UTC while the app runs
        // on Asia/Manila, the same mismatch as a UTC production database.
        $announcement = $this->announcement();
        $proposal = $this->proposal();

        $this->actingAs($this->student)
            ->post(route('student.announcements.comment', $announcement), ['comment' => 'Just posted']);
        $this->actingAs($this->student)
            ->post(route('student.proposal.comment', $proposal), ['comment' => 'Just posted']);

        foreach ([AnnouncementComment::first(), ProposalComment::first()] as $comment) {
            $this->assertLessThan(60, abs($comment->created_at->diffInSeconds(now())));
        }

        $this->actingAs($this->student)
            ->get(route('student.proposal.show', $proposal))
            ->assertSee('seconds ago')
            ->assertDontSee('hours ago');
    }

    public function test_migration_moves_existing_comments_onto_the_app_clock(): void
    {
        $announcement = $this->announcement();
        // Stamped by the column default, the way comments were saved before.
        $id = DB::table('announcement_comments')->insertGetId([
            'announcement_id' => $announcement->id,
            'user_id' => $this->student->id,
            'comment' => 'Posted before the fix',
        ]);
        $this->assertGreaterThan(3600, abs(AnnouncementComment::find($id)->created_at->diffInSeconds(now())));

        (require database_path('migrations/2026_10_01_000000_move_comment_times_onto_app_clock.php'))->up();

        $this->assertLessThan(60, abs(AnnouncementComment::find($id)->created_at->diffInSeconds(now())));
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

    private function announcementComment(Announcement $announcement, User $author, string $text, ?AnnouncementComment $replyTo = null): AnnouncementComment
    {
        return AnnouncementComment::create([
            'announcement_id' => $announcement->id,
            'user_id' => $author->id,
            'comment' => $text,
            'parent_id' => $replyTo?->id,
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

    private function proposalComment(Proposal $proposal, User $author, string $text, ?ProposalComment $replyTo = null): ProposalComment
    {
        return ProposalComment::create([
            'proposal_id' => $proposal->id,
            'user_id' => $author->id,
            'comment' => $text,
            'parent_id' => $replyTo?->id,
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
