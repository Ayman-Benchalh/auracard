<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    protected function mockSocialite(string $driver, array $userData): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn($userData['id']);
        $socialiteUser->shouldReceive('getEmail')->andReturn($userData['email']);
        $socialiteUser->shouldReceive('getName')->andReturn($userData['name']);
        $socialiteUser->shouldReceive('getAvatar')->andReturn($userData['avatar'] ?? null);
        $socialiteUser->shouldReceive('getNickname')->andReturn($userData['nickname'] ?? null);

        $providerMock = Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
        $providerMock->shouldReceive('user')->andReturn($socialiteUser);
        $providerMock->shouldReceive('scopes')->andReturnSelf();
        $providerMock->shouldReceive('redirect')->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        Socialite::shouldReceive('driver')->with($driver)->andReturn($providerMock);
    }

    public function test_google_redirect_succeeds_when_configured(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'mock-google-client-id',
            'services.google.client_secret' => 'mock-google-secret',
        ]);

        $this->mockSocialite('google', ['id' => '1', 'email' => 't@t.com', 'name' => 'T']);

        $response = $this->get(route('auth.social.redirect', ['provider' => 'google']));
        $response->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_disabled_provider_returns_404(): void
    {
        config(['services.instagram.enabled' => false]);

        $response = $this->get(route('auth.social.redirect', ['provider' => 'instagram']));
        $response->assertNotFound();
    }

    public function test_unsupported_provider_returns_404(): void
    {
        $response = $this->get(route('auth.social.redirect', ['provider' => 'tiktok']));
        $response->assertNotFound();
    }

    public function test_missing_credentials_redirects_to_login_with_flash_error(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get(route('auth.social.redirect', ['provider' => 'google']));
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_google_callback_registers_new_user_and_creates_social_account(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $this->mockSocialite('google', [
            'id' => 'google-uid-1001',
            'email' => 'samir.alami@example.com',
            'name' => 'Samir Alami',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'samir.alami@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('Samir Alami', $user->name);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasRole('customer'));

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-uid-1001',
            'email' => 'samir.alami@example.com',
        ]);
    }

    public function test_google_callback_authenticates_existing_linked_social_account(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $user = User::factory()->create([
            'email' => 'existing.client@example.com',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-existing-uid',
            'email' => 'existing.client@example.com',
        ]);

        $this->mockSocialite('google', [
            'id' => 'google-existing-uid',
            'email' => 'existing.client@example.com',
            'name' => $user->name,
        ]);

        $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_links_to_existing_user_with_same_verified_email(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $user = User::factory()->create([
            'email' => 'same.email@example.com',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);

        $this->mockSocialite('google', [
            'id' => 'google-new-uid-same-email',
            'email' => 'same.email@example.com',
            'name' => 'Same Email Name',
        ]);

        $response = $this->get(route('auth.social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-new-uid-same-email',
        ]);

        // Verify no duplicate user was created
        $this->assertSame(1, User::where('email', 'same.email@example.com')->count());
    }

    public function test_facebook_callback_registers_new_user(): void
    {
        config([
            'services.facebook.enabled' => true,
            'services.facebook.client_id' => 'fb-id',
            'services.facebook.client_secret' => 'fb-secret',
        ]);

        $this->mockSocialite('facebook', [
            'id' => 'fb-uid-9988',
            'email' => 'kenza.bennani@example.com',
            'name' => 'Kenza Bennani',
            'avatar' => 'https://graph.facebook.com/avatar.jpg',
        ]);

        $response = $this->get(route('auth.social.callback', ['provider' => 'facebook']));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'facebook',
            'provider_user_id' => 'fb-uid-9988',
            'email' => 'kenza.bennani@example.com',
        ]);
    }

    public function test_oauth_user_denial_redirects_to_login_gracefully(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $response = $this->get(route('auth.social.callback', [
            'provider' => 'google',
            'error' => 'access_denied',
        ]));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('info');
        $this->assertGuest();
    }

    public function test_already_authenticated_user_can_link_social_account(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $user = User::factory()->create();

        $this->mockSocialite('google', [
            'id' => 'google-linked-id-555',
            'email' => 'linked@example.com',
            'name' => 'Linked User',
        ]);

        $response = $this->actingAs($user)->get(route('auth.social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('account.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_user_id' => 'google-linked-id-555',
        ]);
    }

    public function test_social_account_cannot_be_linked_to_different_user(): void
    {
        config([
            'services.google.enabled' => true,
            'services.google.client_id' => 'id',
            'services.google.client_secret' => 'secret',
        ]);

        $owner = User::factory()->create();
        $attacker = User::factory()->create();

        SocialAccount::create([
            'user_id' => $owner->id,
            'provider' => 'google',
            'provider_user_id' => 'stolen-uid-777',
        ]);

        $this->mockSocialite('google', [
            'id' => 'stolen-uid-777',
            'email' => 'victim@example.com',
            'name' => 'Victim',
        ]);

        $response = $this->actingAs($attacker)->get(route('auth.social.callback', ['provider' => 'google']));

        $response->assertRedirect(route('account.edit'));
        $response->assertSessionHas('error');

        // Confirm ownership unchanged
        $account = SocialAccount::where('provider_user_id', 'stolen-uid-777')->first();
        $this->assertSame($owner->id, $account->user_id);
    }
}
