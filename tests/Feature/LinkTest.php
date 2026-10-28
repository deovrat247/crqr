<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->user = User::factory()->create();
    }

    public function test_dashboard_lists_only_my_links(): void
    {
        Link::factory()->for($this->user)->create(['title' => 'Mine']);
        Link::factory()->create(['title' => 'Someone else']);

        $this->actingAs($this->user)->get('/dashboard')
            ->assertOk()
            ->assertSee('Mine')
            ->assertDontSee('Someone else');
    }

    public function test_dashboard_search(): void
    {
        Link::factory()->for($this->user)->create(['title' => 'Spring sale']);
        Link::factory()->for($this->user)->create(['title' => 'Newsletter']);

        $this->actingAs($this->user)->get('/dashboard?q=spring')
            ->assertSee('Spring sale')
            ->assertDontSee('Newsletter');
    }

    public function test_shorten_with_random_code(): void
    {
        $response = $this->actingAs($this->user)->post('/links', ['url' => 'example.com/some/long/path']);

        $link = Link::sole();
        $response->assertRedirect('/dashboard')->assertSessionHas('created_link', $link->id);
        $this->assertSame('https://example.com/some/long/path', $link->url);
        $this->assertMatchesRegularExpression('/^[abcdefghjkmnpqrstuvwxyz23456789]{6}$/', $link->code);
        $this->assertTrue($link->user->is($this->user));

        $this->actingAs($this->user)->get('/dashboard')->assertSee('Your short link is ready')->assertSee($link->shortUrl());
    }

    public function test_shorten_with_custom_alias_is_lowercased(): void
    {
        $this->actingAs($this->user)->post('/links', ['url' => 'https://example.com', 'alias' => 'Spring-Sale', 'title' => 'Sale']);

        $this->assertDatabaseHas('links', ['code' => 'spring-sale', 'title' => 'Sale']);
    }

    public function test_invalid_aliases_are_rejected(): void
    {
        Link::factory()->create(['code' => 'taken']);

        foreach (['taken', 'TAKEN', 'qr', 'login', 'ab', '-start', 'has space', 'emoji😀'] as $alias) {
            $this->actingAs($this->user)
                ->post('/links', ['url' => 'https://example.com', 'alias' => $alias])
                ->assertSessionHasErrors('alias');
        }

        $this->assertSame(1, Link::count());
    }

    public function test_invalid_destinations_are_rejected(): void
    {
        foreach (['', 'javascript:alert(1)', 'ftp://example.com', 'not a url', 'https://crqr.in/abc', 'http://localhost/abc'] as $url) {
            $this->actingAs($this->user)
                ->post('/links', ['url' => $url])
                ->assertSessionHasErrors('url');
        }

        $this->assertSame(0, Link::count());
    }

    public function test_redirect_counts_the_click(): void
    {
        $link = Link::factory()->create(['code' => 'abc123', 'url' => 'https://example.com/target']);

        $this->get('/abc123', ['User-Agent' => 'Mozilla/5.0 (iPhone)'])
            ->assertStatus(302)
            ->assertRedirect('https://example.com/target')
            ->assertHeader('X-Robots-Tag', 'noindex')
            ->assertCookieMissing(config('session.cookie'));

        $this->assertSame(1, $link->fresh()->clicks);
    }

    public function test_redirect_is_case_insensitive_and_ignores_query_string(): void
    {
        $link = Link::factory()->create(['code' => 'abc123', 'url' => 'https://example.com']);

        $this->get('/ABC123?s=qr')->assertRedirect('https://example.com');
        $this->assertSame(1, $link->fresh()->clicks);
    }

    public function test_bots_are_redirected_but_not_counted(): void
    {
        $link = Link::factory()->create(['code' => 'abc123']);

        $this->get('/abc123', ['User-Agent' => 'WhatsApp/2.23'])->assertStatus(302);
        $this->get('/abc123', ['User-Agent' => 'Googlebot/2.1'])->assertStatus(302);

        $this->assertSame(0, $link->fresh()->clicks);
    }

    public function test_unknown_and_disabled_links_are_404(): void
    {
        Link::factory()->inactive()->create(['code' => 'off123']);

        $this->get('/nope99')->assertNotFound()->assertSee("This link doesn't exist", false);
        $this->get('/off123')->assertNotFound();
    }

    public function test_owner_can_edit_destination_and_disable(): void
    {
        $link = Link::factory()->for($this->user)->create(['code' => 'abc123']);

        $this->actingAs($this->user)->get("/links/{$link->id}/edit")->assertOk();

        $this->actingAs($this->user)
            ->put("/links/{$link->id}", ['url' => 'new.example.com', 'title' => 'New'])
            ->assertRedirect("/links/{$link->id}/edit");

        $link->refresh();
        $this->assertSame('https://new.example.com', $link->url);
        $this->assertFalse($link->is_active); // unchecked checkbox
        $this->assertSame('abc123', $link->code);
    }

    public function test_owner_can_delete(): void
    {
        $link = Link::factory()->for($this->user)->create();

        $this->actingAs($this->user)->delete("/links/{$link->id}")->assertRedirect('/dashboard');

        $this->assertModelMissing($link);
    }

    public function test_other_users_cannot_touch_my_links(): void
    {
        $link = Link::factory()->for($this->user)->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get("/links/{$link->id}/edit")->assertForbidden();
        $this->actingAs($intruder)->put("/links/{$link->id}", ['url' => 'https://evil.example'])->assertForbidden();
        $this->actingAs($intruder)->delete("/links/{$link->id}")->assertForbidden();

        $this->assertModelExists($link);
    }

    public function test_guests_cannot_create_links(): void
    {
        $this->post('/links', ['url' => 'https://example.com'])->assertRedirect('/login');
        $this->assertSame(0, Link::count());
    }

    public function test_generated_codes_avoid_existing_and_reserved_codes(): void
    {
        config(['crqr.code_alphabet' => 'ab', 'crqr.code_length' => 1]);
        Link::factory()->create(['code' => 'a']);
        Link::factory()->create(['code' => 'b']);

        $this->assertSame(2, strlen(Link::generateCode()));
    }
}
