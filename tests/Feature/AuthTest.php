<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function registration(array $overrides = []): array
    {
        return [
            'name' => 'Asha Rao',
            'username' => 'Asha_Rao',
            'email' => 'Asha@Example.com',
            'password' => 'correct-horse',
            'password_confirmation' => 'correct-horse',
            ...$overrides,
        ];
    }

    public function test_user_can_register_and_is_logged_in(): void
    {
        $this->get('/register')->assertOk();

        $this->post('/register', $this->registration())->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'asha_rao', 'email' => 'asha@example.com']);
    }

    public function test_reserved_and_taken_usernames_are_rejected(): void
    {
        User::factory()->create(['username' => 'taken']);

        $this->post('/register', $this->registration(['username' => 'dashboard']))->assertSessionHasErrors('username');
        $this->post('/register', $this->registration(['username' => 'Taken']))->assertSessionHasErrors('username');
        $this->post('/register', $this->registration(['username' => 'has space']))->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_user_can_log_in_and_out(): void
    {
        $user = User::factory()->create(['email' => 'me@example.com']);

        $this->post('/login', ['email' => 'ME@example.com', 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected_and_throttled(): void
    {
        User::factory()->create(['email' => 'me@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'me@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'me@example.com', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertStringStartsWith('Too many login attempts.', session('errors')->first('email'));
        $this->assertGuest();
    }

    public function test_guests_are_sent_to_login_and_users_away_from_it(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect('/dashboard');
    }
}
