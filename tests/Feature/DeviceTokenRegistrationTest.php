<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_and_refresh_a_device_token(): void
    {
        $student = $this->student('one');

        $this->actingAs($student)
            ->postJson('/student/api/device-token', [
                'fcm_token' => 'test-fcm-token',
                'device_type' => 'android',
                'device_name' => 'Test phone',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $student->id,
            'fcm_token' => 'test-fcm-token',
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->postJson('/student/api/device-token', [
                'fcm_token' => 'test-fcm-token',
                'device_type' => 'android',
                'device_name' => 'Updated phone',
            ])
            ->assertOk();
    }

    public function test_token_is_reassigned_when_a_different_student_uses_the_device(): void
    {
        $firstStudent = $this->student('first');
        $secondStudent = $this->student('second');

        $payload = [
            'fcm_token' => 'shared-device-token',
            'device_type' => 'android',
        ];

        $this->actingAs($firstStudent)
            ->postJson('/student/api/device-token', $payload)
            ->assertCreated();

        $this->actingAs($secondStudent)
            ->postJson('/student/api/device-token', $payload)
            ->assertOk();

        $this->assertDatabaseCount('device_tokens', 1);
        $this->assertDatabaseHas('device_tokens', [
            'user_id' => $secondStudent->id,
            'fcm_token' => 'shared-device-token',
            'is_active' => true,
        ]);
    }

    private function student(string $suffix): User
    {
        return User::create([
            'first_name' => 'Push',
            'last_name' => ucfirst($suffix),
            'fullname' => 'Push ' . ucfirst($suffix),
            'email' => 'push-' . $suffix . '@mcclawis.edu.ph',
            'password' => bcrypt('password'),
            'role' => 'student',
            'status' => 'active',
            'student_id' => 'PUSH-' . strtoupper($suffix),
            'year_level' => '1st Year',
            'department' => 'BSIS',
            'age' => 18,
        ]);
    }
}
