# AURA CARD — Architectural Blueprint & Specification

## 1. Domain Overview
AURA CARD implements a modern monolithic architecture using Laravel 13, Inertia 3, React 19, and Filament 5, explicitly architected to operate on managed cPanel/LiteSpeed environments without Redis, Supervisor, or persistent Node.js runtimes.

## 2. Implemented Modules (Phase 1 & Phase 2)
### Phase 1: Foundation & RBAC
- **RBAC Engine**: Strict `roles`, `permissions`, `role_permissions`, `user_roles` relational structure with 4 default roles (`super_admin`, `admin`, `support`, `customer`) and permission groupings.
- **Unified Authentication**: Session-based authentication with Argon2id password hashing, email verification, session regeneration, and IP-level login rate limiting.
- **Account Lifecycle**: Explicit `account_status` (`active`, `suspended`, `disabled`) with automated session invalidation for suspended accounts.
- **Multilingual Support**: Dynamic AR (RTL), FR (LTR), and EN (LTR) support with session/user-persisted locales and Tailwind 4 logical property styles.
- **Design System**: Tailored AURA CARD tokens (Primary `#6C5CE7`, Secondary `#14B8A6`, Dark `#0B1020`, Light `#F7F8FC`) and glassmorphic surface cards.
- **Filament Administration Suite**: Secured `/admin` panel requiring Super Admin, Admin, or Support role.

### Phase 2: NFC Lifecycle & Anti-Fraud Card Claiming
- **Hardware Relational Schema**:
  - `nfc_cards`: Core physical tag inventory with unique hardware chip `card_uid`, cryptographic `claim_token`, lifecycle status (`inactive`, `active`, `suspended`, `replaced`, `retired`), and `batch_number`.
  - `card_entitlements`: Anti-fraud constraint strictly 1-to-1 tied to physical card hardware. Tracks initial free benefits (profile creation + 2 free publication credits) preventing duplicate claims across distinct email accounts.
  - `card_claims`: Full audit trail of card activations with IP address, user agent, and timestamp.
  - `card_transfers`: Legitimate ownership reassignment system between users with expiring transfer tokens and revocation capability.
- **Business Services**:
  - `CardClaimService`: Atomic anti-fraud claim redemption tying benefits to hardware unit.
  - `CardBatchService`: Batch minting utility for inventory generation with unique cryptographically secure tokens.
  - `CardTransferService`: Secure ownership transfer workflow.
- **Public NFC Routing**: `/c/{card_uid}` route handling physical smartphone taps, routing active cards to profile, displaying onboarding guidance on unassigned cards, or showing paused status on suspended cards.
- **Customer Portal**: Dedicated `/cards` index with physical card simulation preview (standard, matte black, metal gold, bamboo wood), pause/resume toggle, and `/cards/claim` activation wizard.
- **Filament Management**: `NfcCardResource` with batch minting modal, token resets, card replacement, and inventory filtering.

### Phase 3: Profile Engine, Professions, Themes & Modular Blocks
- **Profile Relational Schema**:
  - `profession_types`: 30 curated profession presets (Medical, Legal, Tech, Architecture, etc.) with multilingual labels (AR, FR, EN), icons, and default block blueprints.
  - `templates`: 6 responsive layouts (Classic, Modern, Minimal, Corporate, Creative, Executive).
  - `themes`: 6 curated palettes with contrast tokens (Emerald, Sapphire, Amber, Rose, Violet, Slate).
  - `profiles`: 1-to-1 bound to `card_entitlements`, draft design tokens, published design tokens, current version tracker.
  - `profile_blocks`: 19 modular block types across 5 categories (Contact, Social, Media, Business, Interactive) with strict schema validation and XSS prevention.
  - `profile_media`: Secure asset handling for avatars, covers, gallery items, and attachments.
  - `profile_versions`: Immutable, canonical snapshots stored as JSON with deterministic SHA-256 content hashes.
  - `edit_credit_transactions`: Append-only credit ledger tracking initial grant (+2 credits on first publish), deductions (-1 credit on publish with changes), and administrative adjustments.
- **Invariants & Security**:
  - Strict Draft vs. Published immutability (public route `/p/{slug}` serves only versioned canonical snapshot).
  - First publication consumes 0 credits and awards +2 free modification credits.
  - Double-publish idempotency (unchanged content consumes 0 credits and awards no extra credits).
  - Dangerous URL protocols (`javascript:`, `data:`, `vbscript:`) strictly rejected.
  - RFC 6350 vCard 4.0 generation with UTF-8, CRLF, and 75-octet line folding.
  - Inline vector SVG QR code generation via `bacon/bacon-qr-code`.
  - Active NFC card tap (`GET /c/{token}`) 302 redirects to canonical profile (`/p/{slug}`).
- **Filament Administration Suite**:
  - `ProfessionTypeResource`: Multilingual management of 30 profession archetypes.
  - `TemplateResource`: Presentation layout configuration.
  - `ThemeResource`: Color and typography tokens.
  - `ProfileResource`: Global inspection, suspension, and moderation.

## 3. Planned Modules (Phases 4 - 12)
- **Phase 4 (Next)**: Commercial Engine & Subscriptions (`features`, `plans`, `plan_features`, `subscriptions`, credit purchases).
- **Phase 5**: Orders & Manual Payment Verification (`payment_methods`, `orders`, `payments`).
- **Phase 6**: Customer Profile Builder enhanced live mobile preview.
- **Phase 7**: Privacy-Preserving Analytics (Salted HMAC daily rollup) & Lead Capture.
- **Phase 8**: Administrative Inventory & System Health Diagnostics.
- **Phase 9**: High-Converting Marketing Website & Solutions Showcase.
- **Phase 10**: Hardening, CSP, & RTL Visual Polish.
- **Phase 11**: End-to-End Test Suite.
- **Phase 12**: Final cPanel Production Packaging.

