<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_google_configuration_returns_to_login_instead_of_503(): void
    {
        config(['services.google.client_id' => null, 'services.google.client_secret' => null]);

        $this->get(route('google.redirect'))->assertRedirect(route('login'))->assertSessionHas('auth_error');
        $this->get(route('google.redirect', ['from' => 'register']))->assertRedirect(route('register'))->assertSessionHas('auth_error');
    }

    public function test_placeholder_google_credentials_do_not_redirect_to_google(): void
    {
        config(['services.google.client_id' => 'client_id_dari_google', 'services.google.client_secret' => 'client_secret_dari_google']);

        $this->get(route('google.redirect'))->assertRedirect(route('login'))->assertSessionHas('auth_error');
    }

    public function test_google_redirect_contains_state_and_configured_callback(): void
    {
        config(['services.google.client_id' => '123.apps.googleusercontent.com', 'services.google.client_secret' => 'valid-secret-value', 'services.google.redirect_uri' => 'http://localhost/auth/google/callback']);

        $response = $this->get(route('google.redirect'));

        $response->assertRedirectContains('https://accounts.google.com/o/oauth2/v2/auth');
        $response->assertSessionHas('google_oauth_state');
        $this->assertStringContainsString(urlencode('http://localhost/auth/google/callback'), $response->headers->get('Location'));
    }

    public function test_google_callback_creates_and_logs_in_user(): void
    {
        config(['services.google.client_id' => '123.apps.googleusercontent.com', 'services.google.client_secret' => 'valid-secret-value', 'services.google.redirect_uri' => 'http://localhost/auth/google/callback']);
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'token']),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response(['sub' => 'google-123', 'email' => 'google@example.com', 'name' => 'Google User', 'picture' => 'https://example.com/avatar.jpg']),
        ]);

        $this->withSession(['google_oauth_state' => 'valid-state'])->get(route('google.callback', ['code' => 'code', 'state' => 'valid-state']))
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'google@example.com', 'google_id' => 'google-123']);
    }
}
