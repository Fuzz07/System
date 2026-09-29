<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AnnouncementComment;
use App\Models\User;
use App\Notifications\AnnouncementCommentedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnnouncementCommentsTest extends TestCase
{
    use RefreshDatabase;

    private User $officer;
    private User $otherOfficer;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->officer = $this->user('officer', 'Posting Officer');
        $this->otherOfficer = $this->user('officer', 'Other Officer');
        $this->student = $this->user('student', 'Curious Student');
    }

    public function test_student_can_comment_on_a_general_announcement(): void
    {
        $announcement = $this->announcement($this->officer, 'Campus clean-up drive');

        $this->actingAs($this->student)
            ->post(route('student.announcements.comment', $announcement), ['comment' => 'Count me in!'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('announcement_comments', [
            'announcement_id' => $announcement->id,
            'user_id' => $this->student->id,
            'comment' => 'Count me in!',
        ]);
    }

    public function test_student_can_comment_from_the_mobile_page(): void
    {
        $announcement = $this->announcement($this->officer, 'Mobile post');

        $this->actingAs($this->student)
            ->post(route('mobile.student.announcements.comment', $announcement), ['comment' => 'Seen on my phone'])
            ->assertRedirect(route('mobile.student.announcements'));

        $this->assertDatabaseHas('announcement_comments', ['comment' => 'Seen on my phone']);
    }

    public function test_students_see_the_thread_on_general_announcements(): void
    {
        $announcement = $this->announcement($this->officer, 'Library hours');
        $this->comment($announcement, 'Please open on Saturdays too.');

        foreach (['student.announcements', 'mobile.student.announcements'] as $page) {
            $this->actingAs($this->student)
                ->get(route($page))
                ->assertOk()
                ->assertSee('Please open on Saturdays too.')
                ->assertSee('Share your thoughts or feedback');
        }
    }

    public function test_author_is_notified_without_revealing_the_student(): void
    {
        $announcement = $this->announcement($this->officer, 'Uniform policy');

        $this->actingAs($this->student)
            ->post(route('student.announcements.comment', $announcement), ['comment' => 'Please clarify the shoes rule.']);

        $notification = $this->officer->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertSame(AnnouncementCommentedNotification::class, $notification->type);
        $this->assertStringContainsString('Uniform policy', $notification->data['message']);
        $this->assertStringContainsString('Please clarify the shoes rule.', $notification->data['message']);
        $this->assertStringNotContainsString($this->student->fullname, $notification->data['message']);
        $this->assertSame(route('officer.announcements', ['mine' => 1]), $notification->data['url']);

        $this->assertSame(0, $this->otherOfficer->notifications()->count());
    }

    public function test_officer_reads_student_comments_anonymously(): void
    {
        $announcement = $this->announcement($this->officer, 'Foundation week');
        $this->comment($announcement, 'The schedule clashes with exams.');

        $this->actingAs($this->officer)
            ->get(route('officer.announcements'))
            ->assertOk()
            ->assertSee('The schedule clashes with exams.')
            ->assertSee('Anonymous Student')
            ->assertDontSee($this->student->fullname);
    }

    public function test_officer_can_remove_comments_only_on_their_own_posts(): void
    {
        $own = $this->announcement($this->officer, 'My post');
        $other = $this->announcement($this->otherOfficer, 'Their post');
        $ownComment = $this->comment($own, 'Spam on my post');
        $otherComment = $this->comment($other, 'Comment on their post');

        $html = $this->actingAs($this->officer)->get(route('officer.announcements'))->getContent();
        $this->assertStringContainsString(route('officer.announcements.comments.destroy', [$own, $ownComment]), $html);
        $this->assertStringNotContainsString(route('officer.announcements.comments.destroy', [$other, $otherComment]), $html);

        $this->actingAs($this->officer)
            ->delete(route('officer.announcements.comments.destroy', [$own, $ownComment]))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('announcement_comments', ['id' => $ownComment->id]);

        $this->actingAs($this->officer)
            ->delete(route('officer.announcements.comments.destroy', [$other, $otherComment]))
            ->assertForbidden();
        $this->assertDatabaseHas('announcement_comments', ['id' => $otherComment->id]);
    }

    public function test_comment_must_belong_to_the_announcement_in_the_url(): void
    {
        $own = $this->announcement($this->officer, 'My post');
        $other = $this->announcement($this->otherOfficer, 'Their post');
        $otherComment = $this->comment($other, 'Not under your post');

        // Pairing a comment with an announcement the officer owns must not
        // let them delete a comment that lives under someone else's post.
        $this->actingAs($this->officer)
            ->delete(route('officer.announcements.comments.destroy', [$own, $otherComment]))
            ->assertNotFound();

        $this->assertDatabaseHas('announcement_comments', ['id' => $otherComment->id]);
    }

    public function test_my_posts_filter_shows_only_the_officers_announcements(): void
    {
        $this->announcement($this->officer, 'Mine to manage');
        $this->announcement($this->otherOfficer, 'Someone else wrote this');

        $this->actingAs($this->officer)
            ->get(route('officer.announcements', ['mine' => 1]))
            ->assertOk()
            ->assertSee('Mine to manage')
            ->assertDontSee('Someone else wrote this');
    }

    public function test_admin_can_read_and_remove_any_comment(): void
    {
        $admin = $this->user('admin', 'Site Admin');
        $announcement = $this->announcement($this->officer, 'Officer post');
        $comment = $this->comment($announcement, 'Needs moderation');

        $this->actingAs($admin)
            ->get(route('admin.announcements'))
            ->assertOk()
            ->assertSee('Needs moderation');

        $this->actingAs($admin)
            ->delete(route('admin.announcements.comments.destroy', [$announcement, $comment]))
            ->assertRedirect();

        $this->assertDatabaseMissing('announcement_comments', ['id' => $comment->id]);
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

    private function comment(Announcement $announcement, string $text): AnnouncementComment
    {
        return AnnouncementComment::create([
            'announcement_id' => $announcement->id,
            'user_id' => $this->student->id,
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
