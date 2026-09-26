# AURA CARD — Phase 3 Profile Engine & Modular Blocks Specification

## 1. Overview & Architecture

The AURA CARD Profile Engine delivers a high-performance, mobile-first, immutable digital card platform tailored for the Moroccan and MENA markets. It operates in strict compliance with the project's zero-dependency production constraints (cPanel, PHP 8.3+, MySQL 8+, React 19, Inertia 3, Tailwind 4, Filament 5).

```
   ┌──────────────────────────────────────────────────────────────┐
   │                   Physical NFC Tap / QR Scan                 │
   │                        GET /c/{rawToken}                     │
   └───────────────────────────────┬──────────────────────────────┘
                                   │
                           SHA-256 Hash Lookup
                                   │
                   ┌───────────────▼───────────────┐
                   │ Active Card + Published Profile│
                   └───────────────┬───────────────┘
                                   │ 302 Redirect
                                   ▼
   ┌──────────────────────────────────────────────────────────────┐
   │                  Canonical Public Profile View               │
   │                          GET /p/{slug}                       │
   │                                                              │
   │  - Zero Draft Leakage: Serves only canonical snapshot        │
   │  - RFC 6350 vCard 4.0 generation & cached download (/vcard) │
   │  - Inline SVG QR Code Generator (/qr)                        │
   │  - Trilingual Support (FR, AR, EN) with full RTL layouts     │
   └──────────────────────────────────────────────────────────────┘
```

---

## 2. Core Architectural Invariants

### 2.1 One Eligible Physical Card = One Primary Profile
- Each profile is strictly bound to a `card_entitlement_id`.
- The database enforces a `UNIQUE` constraint on `profiles.card_entitlement_id`.
- Attempts to bind a second profile to an entitlement fail with `ValidationException`.
- Users cannot bind another user's entitlement (`403` / validation error).

### 2.2 Immutable Draft vs. Published Separation
- Editing a profile updates `draft_design_tokens` and active `profile_blocks` rows in real time.
- The public route `GET /p/{slug}` queries **only** the canonical snapshot stored on the `ProfileVersion` record linked by `profiles.current_version`.
- Draft edits **never** leak to the public profile until an explicit publication action succeeds.
- When unpublished or suspended, `GET /p/{slug}` responds with `404 Not Found`.

### 2.3 Publication & Modification Credits Ledger
The modification credits engine is an **append-only, auditable ledger** (`edit_credit_transactions`):

| Action | Cost / Award | Ledger Reason | Condition |
| :--- | :--- | :--- | :--- |
| **First Publication** | **0 credits** | `INITIAL_GRANT` (+2) | `is_initial_credits_granted = false` |
| **Double Publish** | **0 credits** | None (`status: unchanged`) | Content hash identical to active version |
| **Subsequent Edit Publish** | **-1 credit** | `PROFILE_PUBLISH` (-1) | Content hash differs & balance >= 1 |
| **Subsequent Publish (Zero Balance)** | **Blocked** | None (`ValidationException`) | Balance = 0; requires credit top-up |

- **Double-Publish Idempotency**: If a user double-clicks publish without making changes, the canonical snapshot hash matches the existing active version. No credits are deducted, no version is incremented, and no duplicate bonus credits can ever be granted.
- **Balance Calculation**: Derived deterministically as `SUM(amount)` from `edit_credit_transactions`.

---

## 3. Modular Block Engine (19 Types)

The profile builder supports 19 modular block types, validated via `BlockValidationService` and rendered via `BlockDispatcher.tsx`:

| Category | Block Types | Security / Validation Rules |
| :--- | :--- | :--- |
| **Contact** | `contact_details`, `vcard_download`, `map_location` | Phone format validation, email validation, address string sanitization |
| **Social & Links** | `social_links`, `custom_links` | Protocol allowlist: `https://`, `http://`, `mailto:`, `tel:`, `sms:`. Rejects `javascript:`, `data:`, `vbscript:`. |
| **Media** | `photo_gallery`, `video_embed`, `audio_player`, `document_attachment` | Embed domains restricted (YouTube, Vimeo). MIME types verified. Max file sizes enforced. |
| **Business** | `about_bio`, `business_hours`, `services_list`, `product_catalog` | Opening hours validation, currency formatting (MAD, EUR, USD). |
| **Interactive** | `contact_form`, `appointment_booking`, `faq_accordion`, `customer_reviews`, `qr_code_share` | Input sanitization, booking URL validation, star rating 1-5 range. |

---

## 4. RFC 6350 vCard 4.0 Specification

Generated via `VCard4Generator` (`app/Services/VCard/VCard4Generator.php`):
- **Standards Compliance**: RFC 6350 specification.
- **Encoding**: UTF-8 with standard CRLF (`\r\n`) line endings.
- **Line Folding**: Automatically wraps long lines at 75 octets with leading whitespace on continuation lines.
- **Escaping**: Special characters (`,`, `;`, `\`) properly backslash-escaped in text properties.
- **Multilingual**: Full Arabic, French, and English Unicode support.
- **Fields**: FN, N, ORG, TITLE, NOTE, URL, TEL, EMAIL, ADR, REV, PHOTO.
- **Caching**: Pre-computed at publish time and stored on `published_design_tokens['vcard_cache']` for zero-latency public downloads.

---

## 5. Inline SVG QR Code Generator

Generated via `ProfileQrCodeService` (`app/Services/QrCode/ProfileQrCodeService.php`):
- Utilizes `bacon/bacon-qr-code` to produce vector SVG streams directly without external cloud APIs.
- Encodes the canonical profile URL: `https://{domain}/p/{slug}`.
- Embedded dynamically in the public profile modal and accessible via `GET /p/{slug}/qr`.

---

## 6. Filament 5 Admin Management

Admins manage the profile ecosystem via four dedicated Filament resources:
1. `ProfessionTypeResource`: Create and configure the 30 profession presets (multilingual names, icons, default modular blocks).
2. `TemplateResource`: Configure layout templates (Classic, Modern, Minimal, Corporate, Creative, Executive).
3. `ThemeResource`: Configure theme palettes and typography defaults.
4. `ProfileResource`: View all customer profiles, inspect canonical versions, view ledger balance, and issue administrative suspensions or restorations.

---

## 7. Automated Testing Suite

All Phase 3 features are verified by 24 dedicated test cases across 6 feature test suites:
- `Tests\Feature\Profile\ProfileCreationTest`: Onboarding wizard, entitlement binding, reserved slug collisions.
- `Tests\Feature\Profile\ProfileDraftPublishTest`: Draft/publish separation, first-publish +2 credits, zero-cost unchanged idempotency, balance deduction, zero-balance blocking.
- `Tests\Feature\Profile\PublicProfileTest`: 404 on draft/suspended, 200 on published, vCard stream, SVG QR endpoint.
- `Tests\Feature\Profile\NfcProfileIntegrationTest`: Physical card tap (`/c/{token}`) 302 redirect to canonical `/p/{slug}`.
- `Tests\Feature\Profile\ProfileBlocksTest`: Block addition, schema validation, XSS/URL protocol rejection, block reordering and deletion.
- `Tests\Feature\Profile\VCardGeneratorTest`: RFC 6350 compliance, CRLF line folding, multilingual character preservation.

**Combined Suite Status**: 65 tests passed, 311 assertions, 0 failures, 0 regressions across Phase 1, Phase 2, and Phase 3.
