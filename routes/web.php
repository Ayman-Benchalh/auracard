<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\NfcCardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PublicCardTapController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Marketing, Locale, NFC Tap & Digital Profile Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');
Route::get('/c/{token}', [PublicCardTapController::class, 'show'])->middleware('throttle:60,1')->name('cards.public.tap');
Route::get('/p/{slug}', [\App\Http\Controllers\PublicProfileController::class, 'show'])->middleware('throttle:120,1')->name('profile.public.show');
Route::get('/p/{slug}/vcard', [\App\Http\Controllers\PublicProfileController::class, 'downloadVCard'])->middleware('throttle:60,1')->name('profile.public.vcard');
Route::get('/p/{slug}/qr', [\App\Http\Controllers\PublicProfileController::class, 'showQr'])->middleware('throttle:60,1')->name('profile.public.qr');
Route::post('/p/{slug}/contact', [\App\Http\Controllers\PublicProfileLeadController::class, 'store'])->middleware('throttle:10,1')->name('profile.public.contact');
Route::post('/p/{slug}/events', [\App\Http\Controllers\PublicProfileAnalyticsController::class, 'track'])->middleware('throttle:60,1')->name('profile.public.events');
Route::get('/pricing', [\App\Http\Controllers\Customer\PricingController::class, 'index'])->name('pricing');

/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| Social OAuth Authentication Routes (Google, Facebook, Instagram)
|--------------------------------------------------------------------------
*/
Route::get('auth/{provider}/redirect', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirect'])
    ->name('auth.social.redirect');
Route::get('auth/{provider}/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'callback'])
    ->name('auth.social.callback');

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes (Protected with active.user & auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])->group(function () {
    // Email Verification
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Customer App Shell
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // NFC Cards Management (Phase 2 - requires verified email)
    Route::middleware('verified')->group(function () {
        Route::get('/cards', [NfcCardController::class, 'index'])->name('cards.index');
        Route::get('/cards/claim', [NfcCardController::class, 'claimView'])->name('cards.claim');
        Route::post('/cards/claim', [NfcCardController::class, 'claim'])->middleware('throttle:10,1')->name('cards.claim.store');
        Route::get('/cards/claim-success', [NfcCardController::class, 'claimSuccess'])->name('cards.claim-success');
        Route::patch('/cards/{card}/toggle-status', [NfcCardController::class, 'toggleStatus'])->name('cards.toggle-status');
        Route::post('/cards/{card}/lost', [NfcCardController::class, 'reportLost'])->name('cards.report-lost');

        // Digital Profile Engine (Phase 3)
        Route::get('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'index'])->name('profile.index');
        Route::get('/profile/create', [\App\Http\Controllers\Customer\ProfileController::class, 'onboarding'])->name('profile.create');
        Route::get('/profile/onboarding', [\App\Http\Controllers\Customer\ProfileController::class, 'onboarding'])->name('profile.onboarding');
        Route::post('/profile', [\App\Http\Controllers\Customer\ProfileController::class, 'store'])->name('profile.store');
        Route::get('/profile/{profile}/builder', [\App\Http\Controllers\Customer\ProfileController::class, 'builder'])->name('profile.builder');
        Route::get('/profile/{profile}/preview', [\App\Http\Controllers\Customer\ProfileController::class, 'preview'])->name('profile.preview');
        Route::put('/profile/{profile}/draft', [\App\Http\Controllers\Customer\ProfileController::class, 'updateDraft'])->name('profile.draft.update');
        Route::post('/profile/{profile}/publish', [\App\Http\Controllers\Customer\ProfileController::class, 'publish'])->middleware('throttle:10,1')->name('profile.publish');

        // Profile Modular Blocks & Media
        Route::post('/profile/{profile}/blocks', [\App\Http\Controllers\Customer\ProfileBlockController::class, 'store'])->name('profile.blocks.store');
        Route::post('/profile/{profile}/blocks/reorder', [\App\Http\Controllers\Customer\ProfileBlockController::class, 'reorder'])->name('profile.blocks.reorder');
        Route::delete('/profile/{profile}/blocks/{block}', [\App\Http\Controllers\Customer\ProfileBlockController::class, 'destroy'])->name('profile.blocks.destroy');
        Route::post('/profile/{profile}/media', [\App\Http\Controllers\Customer\ProfileMediaController::class, 'store'])->middleware('throttle:20,1')->name('profile.media.store');
        Route::delete('/profile/{profile}/media/{media}', [\App\Http\Controllers\Customer\ProfileMediaController::class, 'destroy'])->name('profile.media.destroy');
    });

    // Commercial Subscriptions & Orders
    Route::get('/subscription', [\App\Http\Controllers\Customer\PricingController::class, 'subscription'])->name('subscription.index');
    Route::get('/checkout/{plan}', [\App\Http\Controllers\Customer\PricingController::class, 'checkout'])->name('checkout.show');
    Route::post('/checkout/{plan}', [\App\Http\Controllers\Customer\PricingController::class, 'submitPayment'])->middleware('throttle:10,1')->name('checkout.submit');
    Route::get('/payment-requests/{paymentRequest}/proof', [\App\Http\Controllers\Customer\PricingController::class, 'downloadProof'])->name('payment-requests.proof');

    // Commercial Package Orders & Hardware Selection
    Route::get('/checkout/package/{package}', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'checkout'])->name('commercial.checkout');
    Route::post('/checkout/package/{package}', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'submit'])->middleware('throttle:10,1')->name('commercial.checkout.submit');
    Route::get('/orders', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/proof', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'uploadProof'])->middleware('throttle:10,1')->name('orders.upload-proof');
    Route::get('/orders/{order}/proofs/{paymentRequest}/download', [\App\Http\Controllers\Customer\CommercialOrderController::class, 'downloadProof'])->name('orders.download-proof');

    // Account & Security Settings
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
