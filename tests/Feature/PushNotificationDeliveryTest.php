<?php

namespace Tests\Feature;

use App\Models\Candidacy;
use App\Models\DeviceToken;
use App\Models\EnrollmentPayment;
use App\Models\Feedback;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Every update a student gets in the notification bell must also reach their
 * phone as an FCM push, so it shows up even while the app is closed.
 */
class PushNotificationDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private const FCM_URL = 'https://fcm.googleapis.com/v1/projects/test-project/messages:send';


    private User $admin;
    private User $student;

    /** What the fake FCM endpoint answers: [body, status]. */
    private array $fcmReply = [['name' => 'projects/test-project/messages/1'], 200];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        config(['services.firebase.project_id' => 'test-project']);
        // Skip the service-account JWT exchange: pretend a Firebase access
        // token is already cached, so only the FCM send itself is exercised.
        $this->primeAccessToken('test-access-token', now()->addHour()->timestamp);

        Http::fake([
            'fcm.googleapis.com/*' => fn () => Http::response(...$this->fcmReply),
        ]);

        $this->admin = $this->user('admin', 'admin');
        $this->student = $this->user('student', 'student');

        DeviceToken::create([
            'user_id' => $this->student->id,
            'fcm_token' => 'student-phone-token',
            'device_type' => 'android',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->primeAccessToken(null, null);

        parent::tearDown();
    }

    public function test_dean_decision_on_a_candidacy_is_pushed_to_the_student(): void
    {
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true, 'candidacy_open' => true]);
        $dean = $this->user('dean', 'dean');
        $candidacy = Candidacy::create([
            'user_id' => $this->student->id,
            'department' => 'BSIS',
            'position' => 'SSC President',
            'platform' => 'Transparency in every peso the council spends.',
            'status' => 'pending',
            'school_year' => '2026-2027',
        ]);

        $this->actingAs($dean)->post(route('dean.candidacy.vote', $candidacy))->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('Candidacy approved', $push['notification']['title']);
        $this->assertStudentLink('/m/student/candidacy', $push['data']['url']);
        $this->assertCount(1, $this->student->fresh()->notifications);
    }

    public function test_an_unchanged_candidacy_decision_is_not_pushed_again(): void
    {
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true, 'candidacy_open' => true]);
        $dean = $this->user('dean', 'dean');
        $candidacy = Candidacy::create([
            'user_id' => $this->student->id,
            'department' => 'BSIS',
            'position' => 'SSC President',
            'platform' => 'Transparency in every peso the council spends.',
            'status' => 'approved',
            'school_year' => '2026-2027',
        ]);

        $this->actingAs($dean)->post(route('dean.candidacy.vote', $candidacy))->assertRedirect();

        Http::assertNothingSent();
    }

    public function test_enrollment_payment_confirmation_is_pushed_to_the_student(): void
    {
        $payment = $this->payment(['status' => 'pending']);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollment.payments.mark_paid', $payment))
            ->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('Enrollment payment confirmed', $push['notification']['title']);
        $this->assertStudentLink('/m/student/enrollment', $push['data']['url']);
    }

    public function test_rejected_payment_proof_is_pushed_with_the_reason(): void
    {
        $payment = $this->payment(['status' => 'pending', 'proof_path' => 'proofs/receipt.jpg', 'proof_status' => 'pending']);

        $this->actingAs($this->admin)
            ->post(route('admin.enrollment.payments.proof.reject', $payment), ['proof_notes' => 'Receipt is blurry'])
            ->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('Payment proof rejected', $push['notification']['title']);
        $this->assertStringContainsString('Receipt is blurry', $push['notification']['body']);
        $this->assertCount(1, $this->student->fresh()->notifications);
    }

    public function test_feedback_reply_is_pushed_exactly_once(): void
    {
        $feedback = Feedback::create([
            'student_id' => $this->student->id,
            'message' => 'Please add more benches near the library.',
            'status' => 'Pending',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.feedback.reply', $feedback), ['reply' => 'Five benches arrive next week.'])
            ->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('The SSC replied to your feedback', $push['notification']['title']);
        $this->assertSame('Five benches arrive next week.', $push['notification']['body']);
        $this->assertStudentLink('/m/student/feedback', $push['data']['url']);
    }

    public function test_opening_the_election_rings_each_phone_once_and_opens_the_ballot(): void
    {
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.election.open'))->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('SSC Elections are OPEN!', $push['notification']['title']);
        $this->assertStudentLink('/m/student/voting', $push['data']['url']);
        // The board post still goes up; it just doesn't send a second push.
        $this->assertDatabaseHas('announcements', ['title' => 'Supreme Student Council Elections are OPEN!']);
    }

    public function test_announcements_are_pushed_and_open_the_news_page(): void
    {
        \App\Models\Announcement::create([
            'title' => 'Intramurals schedule',
            'content' => 'Opening parade starts at 7 AM on Monday.',
            'created_by' => $this->admin->id,
        ]);

        $push = $this->onlyPush();
        $this->assertSame('Intramurals schedule', $push['notification']['title']);
        $this->assertSame('/m/student/announcements', parse_url($push['data']['url'], PHP_URL_PATH));
    }

    public function test_opening_candidacy_filing_from_the_admin_portal_links_to_the_student_site(): void
    {
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true, 'candidacy_open' => false]);

        $this->actingAs($this->admin)
            ->post(route('admin.settings.candidacy.toggle'))
            ->assertRedirect();

        $push = $this->onlyPush();
        $this->assertSame('Filing for SSC Officer Candidacy is OPEN!', $push['notification']['title']);
        // Not admin.mccsupremestudentcouncil.com, where the student has no
        // session and would be sent to the admin login.
        $this->assertStudentLink('/m/student/announcements', $push['data']['url']);
    }

    public function test_bell_links_created_on_a_staff_portal_point_to_the_student_site(): void
    {
        SchoolYear::create(['label' => '2026-2027', 'is_active' => true, 'candidacy_open' => true]);
        $dean = $this->user('dean', 'dean');
        $candidacy = Candidacy::create([
            'user_id' => $this->student->id,
            'department' => 'BSIS',
            'position' => 'SSC President',
            'platform' => 'Transparency in every peso the council spends.',
            'status' => 'pending',
            'school_year' => '2026-2027',
        ]);

        $this->actingAs($dean)->post(route('dean.candidacy.vote', $candidacy))->assertRedirect();

        $this->assertStudentLink('/student/candidacy', $this->student->fresh()->notifications->first()->data['url']);
    }

    public function test_bell_repairs_links_saved_on_a_staff_portal_before_the_fix(): void
    {
        $this->student->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => \App\Notifications\ElectionOpenNotification::class,
            'data' => [
                'message' => 'SSC Elections are now OPEN!',
                'url' => 'https://admin.mccsupremestudentcouncil.com/student/voting',
            ],
        ]);

        $this->actingAs($this->student)
            ->getJson('http://mccsupremestudentcouncil.com/student/notifications')
            ->assertOk()
            ->assertJsonPath('0.url', 'http://mccsupremestudentcouncil.com/student/voting');
    }

    public function test_a_token_from_an_uninstalled_app_stops_receiving_pushes(): void
    {
        $this->fcmReply = [[
            'error' => [
                'code' => 404,
                'status' => 'NOT_FOUND',
                'details' => [['@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError', 'errorCode' => 'UNREGISTERED']],
            ],
        ], 404];

        $delivered = PushNotificationService::sendToUsers([$this->student->id], 'Hello', 'World');

        $this->assertFalse($delivered);
        $this->assertFalse(DeviceToken::where('fcm_token', 'student-phone-token')->value('is_active'));
    }

    public function test_a_malformed_registration_token_is_retired_and_the_send_fails(): void
    {
        $this->fcmReply = [[
            'error' => [
                'code' => 400,
                'status' => 'INVALID_ARGUMENT',
                'details' => [
                    [
                        '@type' => 'type.googleapis.com/google.firebase.fcm.v1.FcmError',
                        'errorCode' => 'INVALID_ARGUMENT',
                    ],
                    [
                        '@type' => 'type.googleapis.com/google.rpc.BadRequest',
                        'fieldViolations' => [[
                            'field' => 'message.token',
                            'description' => 'The registration token is not a valid FCM registration token',
                        ]],
                    ],
                ],
            ],
        ], 400];

        $delivered = PushNotificationService::sendToUsers([$this->student->id], 'Hello', 'World');

        $this->assertFalse($delivered);
        $this->assertFalse(DeviceToken::where('fcm_token', 'student-phone-token')->value('is_active'));
    }

    public function test_a_relative_key_path_is_found_from_web_requests_too(): void
    {
        // Web requests run with public/ as the working directory, so a relative
        // path must be taken from the project root or the key is never found.
        $keyPath = new \ReflectionMethod(PushNotificationService::class, 'serviceAccountKeyPath');

        config(['services.firebase.service_account_key_path' => 'storage/firebase-key.json']);
        $this->assertSame(base_path('storage/firebase-key.json'), $keyPath->invoke(null));

        config(['services.firebase.service_account_key_path' => '/etc/secrets/firebase.json']);
        $this->assertSame('/etc/secrets/firebase.json', $keyPath->invoke(null));
    }

    /**
     * Student links must point at the student site, even when the action that
     * sent them happened on a staff portal subdomain (admin., dean., treasurer.),
     * where the student has no session and would land on that portal's login.
     */
    private function assertStudentLink(string $path, string $url): void
    {
        $this->assertSame('mccsupremestudentcouncil.com', parse_url($url, PHP_URL_HOST), "Link points at the wrong site: {$url}");
        $this->assertSame($path, parse_url($url, PHP_URL_PATH));
    }

    /** The single FCM message sent, asserting there was exactly one. */
    private function onlyPush(): array
    {
        $sends = Http::recorded(fn (Request $request) => $request->url() === self::FCM_URL);
        $this->assertCount(1, $sends, 'Expected exactly one push to be sent.');

        $message = $sends->first()[0]->data()['message'];
        $this->assertSame('student-phone-token', $message['token']);
        // Tray notifications are drawn by Android itself while the app is
        // closed, so the channel the server names must be the app's channel.
        $this->assertSame('ssc_alerts', $message['android']['notification']['channel_id']);
        $this->assertSame('high', $message['android']['priority']);

        return $message;
    }

    private function payment(array $attributes): EnrollmentPayment
    {
        return EnrollmentPayment::create($attributes + [
            'user_id' => $this->student->id,
            'amount' => 50,
            'semester' => '2026-2027',
            'method' => 'gcash',
            'reference' => 'REF-1001',
        ]);
    }

    private function user(string $role, string $suffix): User
    {
        return User::create([
            'first_name' => ucfirst($suffix),
            'last_name' => 'Tester',
            'fullname' => ucfirst($suffix) . ' Tester',
            'email' => $suffix . '@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => $role,
            'status' => 'active',
            'student_id' => strtoupper($suffix) . '-001',
            'year_level' => '3rd Year',
            'department' => 'BSIS',
            'age' => 20,
        ]);
    }

    private function primeAccessToken(?string $token, ?int $expiresAt): void
    {
        (new ReflectionProperty(PushNotificationService::class, 'accessToken'))->setValue(null, $token);
        (new ReflectionProperty(PushNotificationService::class, 'tokenExpire'))->setValue(null, $expiresAt);
    }
}
