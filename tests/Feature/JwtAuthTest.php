<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Announcement;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class JwtAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_jwt_protected_route_fails_without_token(): void
    {
        $response = $this->getJson('/api/v1/announcements');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'TOKEN_ABSENT',
                 ]);
    }

    public function test_jwt_auth_me_fails_without_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'error_code' => 'TOKEN_ABSENT',
                 ]);
    }

    public function test_login_validation_fails_with_missing_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Validation error.',
                 ])
                 ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid email or password.',
                 ]);
    }

    public function test_student_login_and_access_protected_routes(): void
    {
        $user = User::create([
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'fullname'   => 'John Doe',
            'email'      => 'student@example.com',
            'password'   => Hash::make('password123'),
            'role'       => 'student',
            'department' => 'BSIT',
            'year_level' => '3rd Year',
            'student_id' => '2023-0001',
            'status'     => 'active',
        ]);

        // 1. Successful login
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email'    => 'student@example.com',
            'password' => 'password123',
            'portal'   => 'student',
        ]);

        $loginRes->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'access_token',
                     'token_type',
                     'expires_in',
                     'user' => ['id', 'email', 'fullname', 'role'],
                 ]);

        $token = $loginRes->json('access_token');

        // 2. Access /api/v1/auth/me with Bearer token
        $meRes = $this->withHeader('Authorization', "Bearer {$token}")
                      ->getJson('/api/v1/auth/me');

        $meRes->assertStatus(200)
              ->assertJson([
                  'success' => true,
                  'data'    => [
                      'email'    => 'student@example.com',
                      'fullname' => 'John Doe',
                      'role'     => 'student',
                  ],
              ]);

        // 3. Access student profile
        $profileRes = $this->withHeader('Authorization', "Bearer {$token}")
                           ->getJson('/api/v1/student/profile');

        $profileRes->assertStatus(200)
                   ->assertJson([
                       'success' => true,
                       'data'    => [
                           'student_id' => '2023-0001',
                           'department' => 'BSIT',
                       ],
                   ]);

        // 4. Test Announcements access with JWT
        Announcement::create([
            'title'      => 'Welcome to the New Academic Year',
            'content'    => 'We are excited to kick off classes.',
            'category'   => 'general',
            'created_by' => $user->id,
            'created_at' => now(),
        ]);

        $announcementRes = $this->withHeader('Authorization', "Bearer {$token}")
                                ->getJson('/api/v1/announcements');

        $announcementRes->assertStatus(200)
                        ->assertJson([
                            'success' => true,
                        ])
                        ->assertJsonFragment([
                            'title' => 'Welcome to the New Academic Year',
                        ]);

        // 5. Test Budgets access with JWT
        Budget::create([
            'title'             => 'BSIT Department Budget',
            'department'        => 'BSIT',
            'allocated_amount'  => 50000.00,
            'remaining_balance' => 42000.00,
            'school_year'       => '2026-2027 1st Semester',
            'status'            => 'Approved',
        ]);

        $budgetRes = $this->withHeader('Authorization', "Bearer {$token}")
                          ->getJson('/api/v1/budgets?school_year=2026-2027+1st+Semester');

        $budgetRes->assertStatus(200)
                  ->assertJson([
                      'success' => true,
                  ])
                  ->assertJsonFragment([
                      'title' => 'BSIT Department Budget',
                  ]);

        // 6. Test Token Refresh
        $refreshRes = $this->withHeader('Authorization', "Bearer {$token}")
                           ->postJson('/api/v1/auth/refresh');

        $refreshRes->assertStatus(200)
                   ->assertJsonStructure([
                       'success',
                       'access_token',
                   ]);

        $newToken = $refreshRes->json('access_token');

        // 7. Test Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer {$newToken}")
                          ->postJson('/api/v1/auth/logout');

        $logoutRes->assertStatus(200)
                  ->assertJson([
                      'success' => true,
                      'message' => 'Successfully logged out.',
                  ]);
    }
}
