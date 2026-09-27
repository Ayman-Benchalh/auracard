<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Redirect user to OAuth provider.
     */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        $this->ensureProviderSupportedAndEnabled($provider);

        // Verify provider credentials exist
        if (! $this->hasProviderCredentials($provider)) {
            return redirect()->route('login')
                ->with('error', __('Authentication with :provider is currently not configured.', ['provider' => ucfirst($provider)]));
        }

        try {
            $driver = Socialite::driver($provider);

            if ($provider === 'google') {
                $driver->scopes(['openid', 'profile', 'email']);
            } elseif ($provider === 'facebook') {
                $driver->scopes(['email', 'public_profile']);
            }

            return $driver->redirect();
        } catch (Throwable $e) {
            Log::warning("OAuth redirect failed for provider {$provider}", ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', __('Unable to initiate authentication with :provider.', ['provider' => ucfirst($provider)]));
        }
    }

    /**
     * Handle OAuth provider callback.
     */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        $this->ensureProviderSupportedAndEnabled($provider);

        // Check if user denied permission or error returned in request
        if ($request->has('error') || $request->has('error_reason')) {
            Log::info("User denied OAuth access for {$provider}", ['query' => $request->query()]);

            return redirect()->route('login')
                ->with('info', __('Authentication cancelled.'));
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $e) {
            Log::warning("OAuth callback failure for provider {$provider}", ['error' => $e->getMessage()]);

            return redirect()->route('login')
                ->with('error', __('Unable to authenticate with :provider. Please try again or use your password.', ['provider' => ucfirst($provider)]));
        }

        $providerUserId = (string) $socialUser->getId();
        $email = $socialUser->getEmail();
        $avatar = $socialUser->getAvatar();
        $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'Member';

        if (empty($providerUserId)) {
            return redirect()->route('login')
                ->with('error', __('Invalid identity payload received from provider.'));
        }

        // Scenario 1: User is already logged in (Linking account in settings)
        if (Auth::check()) {
            $currentUser = Auth::user();

            $existingLink = SocialAccount::where('provider', $provider)
                ->where('provider_user_id', $providerUserId)
                ->first();

            if ($existingLink && $existingLink->user_id !== $currentUser->id) {
                return redirect()->route('account.edit')
                    ->with('error', __('This :provider account is already linked to another user profile.', ['provider' => ucfirst($provider)]));
            }

            SocialAccount::updateOrCreate(
                [
                    'provider' => $provider,
                    'provider_user_id' => $providerUserId,
                ],
                [
                    'user_id' => $currentUser->id,
                    'email' => $email,
                    'avatar_url' => $avatar,
                ]
            );

            return redirect()->route('account.edit')
                ->with('success', __('Your :provider account has been successfully linked.', ['provider' => ucfirst($provider)]));
        }

        // Scenario 2: Existing Social Account record
        $socialAccount = SocialAccount::with('user')->where('provider', $provider)
            ->where('provider_user_id', $providerUserId)
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;

            if (! $user || ! $user->isActive()) {
                return redirect()->route('login')
                    ->with('error', __('Your account is suspended or inactive. Please contact support.'));
            }

            // Sync latest avatar/email
            $socialAccount->update([
                'email' => $email,
                'avatar_url' => $avatar,
            ]);

            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        // Scenario 3: Provider must supply a valid email for initial sign-in / registration
        if (empty($email)) {
            return redirect()->route('register')
                ->with('error', __('Your :provider account does not share a verified email address. Please register directly using your email.', ['provider' => ucfirst($provider)]));
        }

        // Scenario 4: User exists with the same verified email -> Safe account linking
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            if (! $existingUser->isActive()) {
                return redirect()->route('login')
                    ->with('error', __('Your account is suspended or inactive. Please contact support.'));
            }

            // Link this provider to existing user
            SocialAccount::create([
                'user_id' => $existingUser->id,
                'provider' => $provider,
                'provider_user_id' => $providerUserId,
                'email' => $email,
                'avatar_url' => $avatar,
            ]);

            Auth::login($existingUser, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('info', __('Your :provider account was successfully linked to your existing AURA CARD profile.', ['provider' => ucfirst($provider)]));
        }

        // Scenario 5: Brand new user registration via OAuth
        $newUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'account_status' => AccountStatus::ACTIVE,
            'locale' => session('locale', config('app.locale', 'fr')),
            'email_verified_at' => now(), // Email verified by OAuth identity provider
        ]);

        // Assign default customer role
        $customerRole = Role::where('name', RoleName::CUSTOMER->value)->first();
        if ($customerRole) {
            $newUser->roles()->syncWithoutDetaching([$customerRole->id]);
        }

        // Create linked social account
        SocialAccount::create([
            'user_id' => $newUser->id,
            'provider' => $provider,
            'provider_user_id' => $providerUserId,
            'email' => $email,
            'avatar_url' => $avatar,
        ]);

        event(new Registered($newUser));

        Auth::login($newUser, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'))
            ->with('success', __('Welcome to AURA CARD! Your account has been created successfully.'));
    }

    /**
     * Ensure the requested provider is supported and enabled by configuration.
     */
    protected function ensureProviderSupportedAndEnabled(string $provider): void
    {
        if (! in_array($provider, SocialAccount::SUPPORTED_PROVIDERS, true)) {
            abort(404, __('Unsupported authentication provider.'));
        }

        $enabled = (bool) config("services.{$provider}.enabled", false);
        if (! $enabled) {
            abort(404, __('Provider authentication is currently disabled.'));
        }
    }

    /**
     * Check if provider credentials are set.
     */
    protected function hasProviderCredentials(string $provider): bool
    {
        $clientId = config("services.{$provider}.client_id");
        $clientSecret = config("services.{$provider}.client_secret");

        return ! empty($clientId) && ! empty($clientSecret);
    }
}
