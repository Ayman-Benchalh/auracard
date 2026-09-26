<?php

declare(strict_types=1);

namespace Tests\Feature\Profile;

use App\Actions\Profile\CreateProfileForEntitlementAction;
use App\Enums\CardEntitlementStatus;
use App\Enums\CardStatus;
use App\Models\CardEntitlement;
use App\Models\NfcCard;
use App\Models\Profile;
use App\Models\ProfileMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;
    protected Profile $profileA;
    protected Profile $profileB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        $this->userA = User::factory()->create(['email_verified_at' => now()]);
        $this->userB = User::factory()->create(['email_verified_at' => now()]);

        $cardA = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->userA->id,
            'serial_number' => 'AURA-SEC-0001',
            'public_token_hash' => NfcCard::hashPublicToken('auramediasecuritytokenusera00012345'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $entitlementA = CardEntitlement::create([
            'source_nfc_card_id' => $cardA->id,
            'current_nfc_card_id' => $cardA->id,
            'user_id' => $this->userA->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $cardB = NfcCard::create([
            'batch_id' => null,
            'user_id' => $this->userB->id,
            'serial_number' => 'AURA-SEC-0002',
            'public_token_hash' => NfcCard::hashPublicToken('auramediasecuritytokenuserb00056789'),
            'claim_code_hash' => null,
            'card_type' => 'matte_black',
            'status' => CardStatus::ACTIVE,
            'activated_at' => now(),
        ]);

        $entitlementB = CardEntitlement::create([
            'source_nfc_card_id' => $cardB->id,
            'current_nfc_card_id' => $cardB->id,
            'user_id' => $this->userB->id,
            'initial_credits_amount' => 2,
            'is_initial_credits_granted' => false,
            'status' => CardEntitlementStatus::ACTIVE,
        ]);

        $createAction = app(CreateProfileForEntitlementAction::class);
        $this->profileA = $createAction->execute($this->userA, $entitlementA, ['display_name' => 'User A Profile']);
        $this->profileB = $createAction->execute($this->userB, $entitlementB, ['display_name' => 'User B Profile']);
    }

    public function test_valid_jpeg_png_webp_accepted(): void
    {
        // 1. JPEG
        $jpeg = UploadedFile::fake()->image('avatar.jpg', 400, 400)->size(500);
        $resJpeg = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $jpeg,
            'collection' => 'avatar',
        ]);
        $resJpeg->assertStatus(200);
        $resJpeg->assertJsonPath('success', true);

        // 2. PNG
        $png = UploadedFile::fake()->image('cover.png', 800, 400)->size(800);
        $resPng = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $png,
            'collection' => 'cover',
        ]);
        $resPng->assertStatus(200);

        // 3. WebP
        $webp = UploadedFile::fake()->image('gallery.webp', 600, 600)->size(300);
        $resWebp = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $webp,
            'collection' => 'gallery',
        ]);
        $resWebp->assertStatus(200);
    }

    public function test_invalid_mime_rejected(): void
    {
        $txt = UploadedFile::fake()->create('notes.txt', 50, 'text/plain');
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $txt,
            'collection' => 'media',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_raw_svg_rejected(): void
    {
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><circle cx="50" cy="50" r="40"/></svg>';
        $svg = UploadedFile::fake()->createWithContent('malicious.svg', $svgContent);

        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $svg,
            'collection' => 'avatar',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_oversized_image_rejected(): void
    {
        // 6MB > 5MB limit
        $oversized = UploadedFile::fake()->image('huge.jpg', 2000, 2000)->size(6144);
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $oversized,
            'collection' => 'cover',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_oversized_pdf_rejected(): void
    {
        // 12MB > 10MB limit for document collection
        $oversizedPdf = UploadedFile::fake()->create('huge_doc.pdf', 12288, 'application/pdf');
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $oversizedPdf,
            'collection' => 'document',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_executable_upload_rejected(): void
    {
        $exe = UploadedFile::fake()->create('backdoor.exe', 100, 'application/x-msdownload');
        $resExe = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $exe,
            'collection' => 'media',
        ]);
        $resExe->assertStatus(422);
        $resExe->assertJsonValidationErrors('file');

        $php = UploadedFile::fake()->create('shell.php', 10, 'text/x-php');
        $resPhp = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $php,
            'collection' => 'media',
        ]);
        $resPhp->assertStatus(422);
        $resPhp->assertJsonValidationErrors('file');
    }

    public function test_spoofed_mime_rejected(): void
    {
        // File named evil.jpg containing PHP script (not a valid image format)
        $spoofed = UploadedFile::fake()->createWithContent('evil.jpg', '<?php echo "evil"; ?>');
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $spoofed,
            'collection' => 'avatar',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_randomized_safe_filename_used(): void
    {
        $image = UploadedFile::fake()->image('../../../etc/passwd_evil.png', 200, 200)->size(100);
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileA->id}/media", [
            'file' => $image,
            'collection' => 'avatar',
        ]);
        $response->assertStatus(200);

        $media = ProfileMedia::where('profile_id', $this->profileA->id)->first();
        $this->assertNotNull($media);
        $this->assertStringNotContainsString('passwd', $media->file_path);
        $this->assertStringNotContainsString('..', $media->file_path);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}\.(png|jpg|jpeg|webp|bin)$/', basename($media->file_path));
    }

    public function test_user_a_cannot_modify_delete_attach_media_owned_by_user_b(): void
    {
        // User A tries to upload to User B's profile -> 403 Forbidden
        $image = UploadedFile::fake()->image('intruder.png', 100, 100);
        $response = $this->actingAs($this->userA)->postJson("/profile/{$this->profileB->id}/media", [
            'file' => $image,
            'collection' => 'avatar',
        ]);
        $response->assertStatus(403);

        // User B uploads legitimate media
        $legit = UploadedFile::fake()->image('user_b_pic.png', 100, 100);
        $uploadRes = $this->actingAs($this->userB)->postJson("/profile/{$this->profileB->id}/media", [
            'file' => $legit,
            'collection' => 'avatar',
        ]);
        $uploadRes->assertStatus(200);
        $mediaId = $uploadRes->json('media.id');

        // User A attempts to DELETE User B's media -> 403 Forbidden
        $delRes = $this->actingAs($this->userA)->deleteJson("/profile/{$this->profileB->id}/media/{$mediaId}");
        $delRes->assertStatus(403);

        // Media still exists in database
        $this->assertDatabaseHas('profile_media', ['id' => $mediaId]);
    }
}
