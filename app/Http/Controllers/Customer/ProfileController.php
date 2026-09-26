<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Actions\Profile\PublishProfileVersionAction;
use App\Actions\Profile\UpdateProfileDraftAction;
use App\Enums\CardEntitlementStatus;
use App\Http\Controllers\Controller;
use App\Models\CardEntitlement;
use App\Models\ProfessionType;
use App\Models\Profile;
use App\Models\Template;
use App\Models\Theme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        protected CreateProfileForEntitlementAction $createProfileAction,
        protected UpdateProfileDraftAction $updateDraftAction,
        protected PublishProfileVersionAction $publishAction
    ) {}

    /**
     * Entrypoint for customer profiles.
     */
    public function index(Request $request): RedirectResponse|Response
    {
        $user = $request->user();
        $primaryProfile = $user->primaryProfile()->with(['professionType', 'template', 'theme'])->first()
            ?? $user->profiles()->first();

        if ($primaryProfile) {
            return redirect()->route('profile.builder', ['profile' => $primaryProfile->id]);
        }

        // Check if user has an active entitlement to configure
        $activeEntitlement = $user->cardEntitlements()
            ->where('status', CardEntitlementStatus::ACTIVE)
            ->whereNull('profile_id')
            ->first();

        if ($activeEntitlement) {
            return redirect()->route('profile.onboarding', ['entitlement' => $activeEntitlement->id]);
        }

        // No active card entitlements yet - redirect to cards
        return redirect()->route('cards.index')
            ->with('info', __('Please activate a card first to build your digital profile.'));
    }

    /**
     * Show the 8-step onboarding wizard.
     */
    public function onboarding(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Get eligible active entitlements without a profile
        $entitlements = $user->cardEntitlements()
            ->where('status', CardEntitlementStatus::ACTIVE)
            ->whereNull('profile_id')
            ->with(['currentCard'])
            ->get();

        if ($entitlements->isEmpty()) {
            $existing = $user->profiles()->first();
            if ($existing) {
                return redirect()->route('profile.builder', ['profile' => $existing->id]);
            }

            return redirect()->route('cards.index')
                ->with('warning', __('No active physical cards available for profile setup. Please claim your card first.'));
        }

        $professions = ProfessionType::where('is_active', true)->orderBy('name->en')->get();
        $templates = Template::where('is_active', true)->get();
        $themes = Theme::where('is_active', true)->get();

        return Inertia::render('Customer/Profile/Onboarding', [
            'entitlements' => $entitlements->map(fn ($e) => [
                'id' => $e->id,
                'card_serial' => $e->currentCard?->serial_number,
                'card_type' => $e->currentCard?->card_type,
            ]),
            'professions' => $professions->map(fn ($p) => [
                'id' => $p->id,
                'slug' => $p->slug,
                'name' => $p->name,
                'category' => $p->category,
                'icon' => $p->icon,
                'default_blocks' => $p->default_blocks,
            ]),
            'templates' => $templates->map(fn ($t) => [
                'id' => $t->id,
                'slug' => $t->slug,
                'name' => $t->name,
                'description' => $t->description,
                'preview_url' => $t->preview_url,
            ]),
            'themes' => $themes->map(fn ($th) => [
                'id' => $th->id,
                'slug' => $th->slug,
                'name' => $th->name,
                'tokens' => $th->tokens,
            ]),
            'defaultName' => $user->name,
            'defaultEmail' => $user->email,
        ]);
    }

    /**
     * Create initial draft profile from onboarding wizard.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'card_entitlement_id' => ['required', 'string', 'exists:card_entitlements,id'],
            'display_name' => ['required', 'string', 'max:150'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:200'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'profession_type_id' => ['nullable', 'string', 'exists:profession_types,id'],
            'template_id' => ['nullable', 'string', 'exists:templates,id'],
            'theme_id' => ['nullable', 'string', 'exists:themes,id'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'font_family' => ['nullable', 'string', 'max:50'],
            'publish_now' => ['sometimes', 'boolean'],
        ]);

        $entitlement = CardEntitlement::where('id', $validated['card_entitlement_id'])
            ->where('user_id', $user->id)
            ->first();

        if (! $entitlement) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'entitlement' => [__('You do not own this card entitlement.')],
            ]);
        }

        $profile = $this->createProfileAction->execute($user, $entitlement, $validated);

        if ($request->boolean('publish_now')) {
            $this->publishAction->execute($user, $profile);

            return redirect()->route('profile.builder', ['profile' => $profile->id])
                ->with('success', __('Your digital profile has been published! You received 2 free edit credits.'));
        }

        return redirect()->route('profile.builder', ['profile' => $profile->id])
            ->with('success', __('Your profile draft has been created! Customize your modular blocks below.'));
    }

    /**
     * Profile builder: split-pane interface (desktop: form on left, interactive preview on right; mobile: tabbed).
     */
    public function builder(Request $request, Profile $profile): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $profile);

        $user = $request->user();

        $profile->load([
            'professionType',
            'template',
            'theme',
            'blocks' => fn ($query) => $query->orderBy('sort_order', 'asc'),
            'media',
            'cardEntitlement.currentCard',
        ]);

        $professions = ProfessionType::where('is_active', true)->orderBy('name->en')->get();
        $templates = Template::where('is_active', true)->get();
        $themes = Theme::where('is_active', true)->get();

        return Inertia::render('Customer/Profile/Builder', [
            'profile' => [
                'id' => $profile->id,
                'slug' => $profile->slug,
                'status' => $profile->status->value,
                'is_primary' => $profile->is_primary,
                'version_counter' => $profile->version_counter,
                'published_at' => $profile->published_at?->toISOString(),
                'display_name' => $profile->display_name,
                'headline' => $profile->headline,
                'bio' => $profile->bio,
                'company' => $profile->company,
                'job_title' => $profile->job_title,
                'location' => $profile->location,
                'avatar_url' => $profile->avatar_url,
                'cover_url' => $profile->cover_url,
                'profession_type_id' => $profile->profession_type_id,
                'template_id' => $profile->template_id,
                'theme_id' => $profile->theme_id,
                'primary_color' => $profile->primary_color,
                'secondary_color' => $profile->secondary_color,
                'font_family' => $profile->font_family,
                'custom_css' => $profile->custom_css,
                'blocks' => $profile->blocks->map(fn ($b) => [
                    'id' => $b->id,
                    'type' => $b->type->value,
                    'title' => $b->title,
                    'order_index' => $b->order_index,
                    'is_visible' => $b->is_visible,
                    'config' => $b->config ?? [],
                    'content' => $b->content ?? [],
                ]),
                'media' => $profile->media,
            ],
            'card' => $profile->cardEntitlement?->currentCard ? [
                'serial_number' => $profile->cardEntitlement->currentCard->serial_number,
                'card_type' => $profile->cardEntitlement->currentCard->card_type,
            ] : null,
            'editCreditBalance' => $user->edit_credit_balance,
            'isFirstPublish' => ($profile->published_version_id === null),
            'professions' => $professions,
            'templates' => $templates,
            'themes' => $themes,
            'publicUrl' => url("/p/{$profile->slug}"),
        ]);
    }

    /**
     * Authenticated preview of the draft.
     */
    public function preview(Request $request, Profile $profile): Response
    {
        \Illuminate\Support\Facades\Gate::authorize('preview', $profile);

        $profile->load(['professionType', 'template', 'theme', 'blocks' => fn ($q) => $q->where('is_enabled', true)->orderBy('sort_order')]);

        return Inertia::render('Customer/Profile/Preview', [
            'profile' => [
                'id' => $profile->id,
                'slug' => $profile->slug,
                'status' => $profile->status->value,
                'display_name' => $profile->display_name,
                'headline' => $profile->headline,
                'bio' => $profile->bio,
                'company' => $profile->company,
                'job_title' => $profile->job_title,
                'location' => $profile->location,
                'avatar_url' => $profile->avatar_url,
                'cover_url' => $profile->cover_url,
                'primary_color' => $profile->primary_color,
                'secondary_color' => $profile->secondary_color,
                'font_family' => $profile->font_family,
                'custom_css' => $profile->custom_css,
                'profession' => $profile->professionType,
                'template' => $profile->template,
                'theme' => $profile->theme,
                'blocks' => $profile->blocks,
            ],
            'isDraftPreview' => true,
        ]);
    }

    /**
     * Update draft attributes.
     */
    public function updateDraft(Request $request, Profile $profile): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('update', $profile);

        $user = $request->user();

        $validated = $request->validate([
            'slug' => ['sometimes', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'display_name' => ['required', 'string', 'max:150'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'location' => ['nullable', 'string', 'max:200'],
            'avatar_url' => ['nullable', 'string', 'max:500'],
            'cover_url' => ['nullable', 'string', 'max:500'],
            'profession_type_id' => ['nullable', 'string', 'exists:profession_types,id'],
            'template_id' => ['nullable', 'string', 'exists:templates,id'],
            'theme_id' => ['nullable', 'string', 'exists:themes,id'],
            'primary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'font_family' => ['nullable', 'string', 'max:50'],
            'custom_css' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->updateDraftAction->execute($user, $profile, $validated);

        return back()->with('success', __('Draft saved successfully.'));
    }

    /**
     * Publish the draft to a new immutable version.
     */
    public function publish(Request $request, Profile $profile): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('publish', $profile);

        $user = $request->user();

        $validated = $request->validate([
            'change_summary' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $this->publishAction->execute($user, $profile, $validated['change_summary'] ?? null);

        if ($result['status'] === 'unchanged') {
            return back()->with('info', __('Profile is already up to date. No new version published.'));
        }

        $message = $result['credits_awarded'] > 0
            ? __('Profile published successfully! You received :credits free edit credits.', ['credits' => $result['credits_awarded']])
            : __('Profile version :version published successfully.', ['version' => $result['version']->version_number]);

        return back()->with('success', $message);
    }
}
