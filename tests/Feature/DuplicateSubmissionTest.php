<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DuplicateSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = '0123456789abcdef0123456789abcdef';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('User-Agent', 'Mozilla/5.0');
        $this->actingAs(User::create([
            'fullname' => 'Budget Administrator',
            'email' => 'budget-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ]));
    }

    private function addFunds(?string $token, string $title = 'Council General Fund')
    {
        return $this->from(route('admin.budgets'))->post(route('admin.budgets.store'), array_filter([
            'title' => $title,
            'department' => 'All Departments',
            'allocated_amount' => '20000',
            'school_year' => '2026-2027',
            '_submit_token' => $token,
        ]));
    }

    public function test_a_form_that_arrives_twice_is_saved_once(): void
    {
        $this->addFunds(self::TOKEN)
            ->assertRedirect(route('admin.budgets'))
            ->assertSessionHas('success', 'Funds added successfully.');

        $this->addFunds(self::TOKEN)
            ->assertRedirect(route('admin.budgets'))
            ->assertSessionHas('warning', 'This form was already submitted, so the repeat submission was ignored.');

        $this->assertDatabaseCount('budgets', 1);
    }

    public function test_separate_submissions_of_a_form_are_each_saved(): void
    {
        $this->addFunds(self::TOKEN, 'Sports Fund')->assertSessionHas('success');
        $this->addFunds(strrev(self::TOKEN), 'Library Fund')->assertSessionHas('success');

        $this->assertDatabaseCount('budgets', 2);
    }

    public function test_a_form_sent_without_a_token_is_handled_as_before(): void
    {
        $this->addFunds(null, 'Sports Fund')->assertSessionHas('success');
        $this->addFunds(null, 'Library Fund')->assertSessionHas('success');

        $this->assertDatabaseCount('budgets', 2);
    }

    public function test_the_token_never_reaches_the_controller_and_a_json_repeat_is_refused(): void
    {
        Route::post('/_test/echo-input', fn (Request $request) => response()->json($request->all()))
            ->middleware('web');

        $this->postJson('/_test/echo-input', ['title' => 'Fund', '_submit_token' => self::TOKEN])
            ->assertOk()
            ->assertExactJson(['title' => 'Fund']);

        $this->postJson('/_test/echo-input', ['title' => 'Fund', '_submit_token' => self::TOKEN])
            ->assertStatus(409)
            ->assertJson(['success' => false]);
    }
}
