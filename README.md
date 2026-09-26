# AURA CARD — Premium NFC Smart Business Card & Digital Profile Platform

<div align="center">
  <p><strong>Next-Generation Physical NFC Smart Cards & Modular Professional Digital Profiles</strong></p>
  <p>Engineered for Commercial SaaS Operations on Managed cPanel, High-Throughput VPS & Cloud Infrastructure</p>
  <p><em>PHP 8.3+ • Laravel 13 • Inertia 3 • React 19 • TypeScript • Tailwind CSS 4 • Filament 5 • Redis • Docker</em></p>
</div>

---

## 🌟 Executive Overview & Product Concept

**AURA CARD** is a commercial-grade, multi-tenant digital identity and hardware SaaS platform tailored for the North African and EMEA markets. It bridges engraved physical NFC hardware (Matte Black PVC, Brushed Stainless Steel, 24K Gold Plated) with an advanced dynamic digital web profile engine.

Unlike disposable paper business cards, an AURA CARD holder can share their complete professional identity with a single physical NFC tap or dynamic QR scan. The recipient requires **zero apps or downloads**—the profile opens instantaneously in their standard mobile browser with direct contact saving (RFC 6350 vCard 4.0), one-tap WhatsApp/phone connectivity, interactive lead generation, social media hubs, and appointment scheduling.

---

## 📦 Commercial Packages & Quota Architecture

AURACARD enforces strict database-driven commercial packages with isolated profile modification credits and automatic entitlement provisioning upon Super Admin payment approval:

| Package | Price (MAD) | Duration | Physical Cards | Profiles | Modification Credits | Highlights |
| :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **AURACARD Solo** | **150 MAD** | 12 Months | 1 Physical NFC Card | 1 Profile | 3 Credits | Perfect for individual executives, doctors, lawyers, and consultants. |
| **AURACARD Trio** *(Featured)* | **249 MAD** | 12 Months | 3 Physical NFC Cards | 3 Profiles | 5 Credits / Profile | Best value for executive teams, partners, and boutique agencies. |
| **AURACARD Business 5** | **399 MAD** | 12 Months | 5 Physical NFC Cards | 5 Profiles | 10 Credits / Profile | Corporate tier with team dashboard, white-label option, priority support. |
| **AURACARD Profile Only** | **100 MAD** | 12 Months | 0 Cards (Bring Your Own) | 1 Profile | 4 Credits | Digital-only plan for existing NFC tags or digital networking. |

### Credit Allocation & Anti-Fraud Invariants
- **First Publication is Free:** The initial publication of any newly drafted profile is 100% free and consumes zero modification credits.
- **Strict Profile Credit Isolation:** Credits are bound directly to each specific `card_entitlement_id`. There is **zero credit pooling or cross-profile leakage** across multiple cards or profiles.
- **Idempotent Order Approval:** Super Admin payment approval triggers `ActivatePackagePurchaseAction`. If triggered more than once, it detects the completed order status and safely no-ops without creating duplicate cards or duplicate entitlements.

---

## 💳 Payment Rails & Dynamic Configuration

AURACARD supports domestic Moroccan payment gateways and manual offline rails with strict secret isolation:

- **Database-Backed Payment Settings:** Payment methods (`payment_settings` table) are managed dynamically in Filament Super Admin.
- **Zero Committed Banking Secrets:** No real bank account numbers, RIBs, or merchant keys are hardcoded in git. Default seeders use safe, synthetic demo accounts (`000 000 0000000000000000 00`).
- **Supported Payment Rails:**
  - Virement Bancaire (Direct wire transfer with automated reference matching)
  - Versement en Agence (Wafacash, Cash Plus, Al Barid Bank, Bank of Africa, Attijariwafa Bank)
  - International / Online Card processing (pluggable gateway drivers)
- **Proof of Payment Upload:** Customers upload JPEG, PNG, or PDF payment slips. Files are validated using MIME magic-byte inspections, stored in private storage (`storage/app/private/payment-proofs/`), and streamed exclusively to authenticated administrators.

---

## 🔒 Physical NFC Lifecycle & Anti-Fraud Engine

Every physical AURA CARD follows an immutable security lifecycle:

```
[ Factory Batch ] ➔ [ Inventory (Unclaimed) ] ➔ [ Sold ] ➔ [ Claimed / Active ]
                                                                   │
                                                      ┌────────────┴────────────┐
                                                      ▼                         ▼
                                                 [ Suspended ]             [ Replaced ]
                                                      │                         │
                                                      ▼                         ▼
                                                  (Revoked)              (Linked to New Card)
```

1. **Zero Plaintext Token Storage:** Physical cards are encoded with a 64-character URL-safe token. The database stores strictly a SHA-256 hash (`public_token_hash`).
2. **One-Time Claim Codes:** Card packaging contains an 8-character activation code (`AURA-XXXX-XXXX`). The code is Bcrypt-hashed. Upon first customer claim, the claim code hash is permanently deleted (`NULL`), preventing replay attacks.
3. **Hardware-Bound Entitlements:** Profile access and modification credits are anchored to the `CardEntitlement` model.
4. **Instant Revocation & Replacement:** If a card is reported lost or stolen:
   - The compromised card's status immediately transitions to `LOST` or `SUSPENDED` (taps return a secure warning).
   - Linking a replacement card seamlessly re-binds the existing published profile and version history without duplicate credit allocation.

---

## 🎨 Profile Engine & 19 Modular Blocks

The digital profile builder provides a real-time mobile preview and draft/publish isolation:

- **Complete Draft / Published Isolation:** Live visitors only see immutable published versions (`ProfileVersion`). Changes made in the builder remain in draft until the user clicks "Publish".
- **30 Profession Presets & 6 Responsive Templates:** Designed specifically for Moroccan professions (Avocat, Notaire, Médecin, Chirurgien-Dentiste, Architecte, Ingénieur, Artisan, Agent Immobilier).
- **19 Dynamic Content Blocks:**
  1. Header & Bio Card
  2. One-Tap Quick Contact (Call, Email, SMS)
  3. WhatsApp Direct Chat with prefilled greeting
  4. Moroccan & International Social Channels (LinkedIn, Instagram, X, Facebook, YouTube, TikTok)
  5. Services & Pricing Catalog
  6. Portfolio Gallery with lightbox
  7. Interactive Google Maps & Physical Address
  8. Business Hours with open/closed status indicators
  9. Customer Testimonials & Reviews
  10. Lead Capture Form with anti-bot honeypot
  11. Document & Brochure Downloads (PDF, Menus, Catalogs)
  12. Bank Account Details (RIB display with one-tap copy)
  13. Appointment Booking Links (Calendly, Google Meet)
  14. Video Embeds (YouTube, Vimeo)
  15. Custom Link List & Call-to-Action Buttons
  16. FAQ Accordion
  17. Wi-Fi Share Card
  18. Custom Badge & Certifications
  19. Direct vCard / Save Contact Action
- **RFC 6350 vCard 4.0 Engine:** Built-in zero-dependency PHP generator with 75-octet line folding, UTF-8 normalization, embedded base64 avatars, and structured phone/email/address formatting.
- **Dynamic Vector SVG QR Codes:** Generates high-contrast dynamic QR codes without third-party API dependencies.

---

## 🌐 Internationalization (AR / FR / EN) & RTL Support

- **Tri-Lingual Interface:** Full support for French (`fr`), English (`en`), and Moroccan Arabic (`ar`).
- **Native RTL Architecture:** Right-to-left layout direction is automatically applied when switching to Arabic (`dir="rtl"`), including mirror transformations for grids, navigation menus, and form inputs.
- **Light & Dark Mode:**
  - **Light Mode is the DEFAULT** across the entire application.
  - Dark Mode (Obsidian / High Contrast) is toggleable with persistent user preference storage in `localStorage`.

---

## ⚡ Performance Optimization

- **Zero N+1 Queries:** Database relationships (`user`, `entitlement`, `profile`, `batch`, `theme`) are preloaded with Eloquent eager loading (`with()`).
- **Lightweight SVG Vector Assets:** System logos and brand icons are 100% vector SVG (under 3 KB), eliminating layout shifts and massive image downloads.
- **Dynamic Asset Chunking:** Vite splits code into lazy-loaded chunks for optimal initial load time.
- **Optimized NFC Redirects:** Public NFC card tap lookups (`/c/{token}`) resolve via indexed SHA-256 lookups in under 15ms.

---

## 🛡️ Security Posture & Quality Gate

- **Zero Known Vulnerabilities:** `composer audit` (0 advisories) and `npm audit` (0 vulnerabilities).
- **IDOR Protection:** Every card, profile, entitlement, and order mutation strictly verifies `user_id` ownership and authorization policies.
- **CSRF & Request Forgery Prevention:** Laravel CSRF tokens validated on all mutating HTTP requests.
- **Rate Limiting:**
  - NFC token lookups: 60 requests/minute per IP.
  - Public lead submissions: 5 requests/minute with honeypot validation.
  - Authentication attempts: 5 requests/minute with exponential backoff.
- **Private Payment Receipts:** Proof files are inaccessible via direct URL and only streamed through authenticated admin endpoints.

---

## 💻 Local Development Setup (Native & Laragon)

### Prerequisites
- PHP 8.3+ (with extensions: `pdo_mysql`, `mbstring`, `gd`, `zip`, `bcmath`, `fileinfo`)
- MySQL 8.0+ or MariaDB 10.6+
- Node.js 20+ and NPM 10+
- Composer 2.7+

### Laragon Setup Guide
1. **PHP Configuration:**
   - Ensure PHP 8.3 is selected in Laragon (`Laragon > PHP > Quick Settings > 8.3.x`).
   - Binary path: `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe`.
2. **MySQL Configuration:**
   - Start MySQL 8.4 via Laragon or terminal:
     ```powershell
     & "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini" --console
     ```
3. **Application Setup:**
   ```bash
   # Clone the repository
   cd "d:\Ayman code\All code\Laravel_V2\AuraCard"

   # Install Composer dependencies
   composer install

   # Install NPM dependencies
   npm install

   # Setup environment
   cp .env.example .env
   php artisan key:generate

   # Run database migrations and seeders
   php artisan migrate:fresh --seed

   # Build frontend assets
   npm run build
   ```
4. **Run Local Servers:**
   ```bash
   # Terminal 1: Laravel backend server
   php artisan serve --port=8000

   # Terminal 2: Vite dev server (for hot module replacement)
   npm run dev

   # Terminal 3: Queue worker
   php artisan queue:work --tries=3
   ```
5. **Access Application:**
   - Public Landing: `http://127.0.0.1:8000`
   - Customer Portal: `http://127.0.0.1:8000/login` (`customer@auracard.ma` / `password`)
   - Super Admin (Filament): `http://127.0.0.1:8000/admin` (`superadmin@auracard.ma` / `password`)

---

## 🐳 Docker Architecture & VPS Setup

AURACARD includes a complete, production-ready multi-container Docker compose configuration:

### Container Topology
- **`app`**: PHP 8.3-FPM Alpine container with PECL Redis and GD.
- **`web`**: Nginx Alpine reverse proxy on port `8080` (or `80`/`443`).
- **`mysql`**: MySQL 8.0 database engine on internal port `3306` (host port `33060`).
- **`redis`**: Redis Alpine high-throughput cache, queue, and session store on internal port `6379`.
- **`queue-worker`**: Dedicated background supervisor running `php artisan queue:work --tries=3 --timeout=120`.

### Docker Commands
```bash
# Validate compose configuration
docker compose config

# Build containers
docker compose build

# Start the cluster in detached mode
docker compose up -d

# Check running services
docker compose ps

# Run database migrations and seeders inside app container
docker compose exec app php artisan migrate:fresh --seed

# Monitor queue worker logs
docker compose logs -f queue-worker
```

---

## ⚡ Redis & Queue Runtime Configuration

AURACARD supports dual-mode infrastructure:

### Mode 1: High-Performance VPS / Docker (Redis Engine)
In `.env`:
```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```
- Safe queued jobs (`GenerateMarketingAssetJob`, `SafeRuntimeAuditJob`) run asynchronously.
- Session concurrency handled in RAM for sub-millisecond response times.

### Mode 2: cPanel / Shared Hosting Fallback (Zero Redis Requirement)
In `.env`:
```env
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```
- Runs 100% on standard MySQL tables (`cache`, `jobs`, `sessions`).
- Zero memory daemons required. Queue processed via standard cPanel cron:
  ```bash
  * * * * * cd /home/username/auracard && php artisan queue:work --stop-when-empty --max-time=50 >> /dev/null 2>&1
  ```

---

## 🧪 Comprehensive Verification Suite

Run the full verification test suite to validate all 120+ tests and 630+ assertions:

```bash
# Clear all caches
php artisan optimize:clear

# Check migration status
php artisan migrate:status

# List registered routes
php artisan route:list

# Run PHP automated tests (Pest / PHPUnit)
php artisan test

# Frontend TypeScript verification
npm run typecheck

# Production asset compilation
npm run build

# Security audit
composer audit
npm audit --audit-level=high
```

---

## 📁 Repository Structure

```
AuraCard/
├── app/
│   ├── Actions/            # Business actions (Cards, Profile, Orders)
│   ├── Enums/              # Typed Enums (CardStatus, ProfileStatus, CardType)
│   ├── Filament/           # Super Admin Resources (Orders, Packages, Payments)
│   ├── Http/Controllers/  # HTTP & Inertia Controllers
│   ├── Jobs/               # Asynchronous Background Queue Jobs
│   ├── Models/             # Eloquent Models & Relations
│   └── Services/           # Domain Services (vCard, QR, Analytics, Marketing)
├── config/                 # Laravel & Package Configurations
├── database/
│   ├── migrations/         # Database Schema Migrations
│   └── seeders/            # Baseline Seeders (Packages, Payments, Demo Cards)
├── docker/                 # Nginx & Docker deployment configs
├── docs/                   # Architectural & System Documentation
│   └── screenshots/        # Visual QA release candidate screenshots
├── public/                 # Public web root & SVG assets
├── resources/
│   ├── css/                # Tailwind CSS styling & animations
│   ├── js/                 # Inertia + React 19 Frontend Components
│   └── views/              # Blade root layouts
├── routes/                 # Web, Admin & API routes
└── tests/                  # Pest & PHPUnit Feature / Unit Tests
```

---

## 📸 Visual QA & Screenshots

Screenshots of the verified Release Candidate interface are located in [docs/screenshots/](docs/screenshots/README.md):
- `landing-light.png` — Landing page with 3D NFC card preview (Default Light Mode)
- `landing-dark.png` — Dark obsidian high-contrast theme
- `pricing.png` — Dynamic 4-tier commercial pricing table
- `checkout.png` — Moroccan payment instructions and receipt upload
- `profile-builder.png` — Real-time interactive profile builder
- `public-profile.png` — Verified executive mobile profile
- `admin-dashboard.png` — Filament 5 Super Admin console

---

## 🗺️ Roadmap & Known Limitations

### Roadmap
- [ ] NFC Multi-Card Batch Pre-Encoding Tool for hardware fulfillment factories.
- [ ] WhatsApp Business Cloud API automated payment confirmation notifications.
- [ ] Native CMI (Centre Monétique Interbancaire) direct merchant credit card integration.
- [ ] Apple Wallet (.pkpass) & Google Wallet dynamic pass generator.

### Known Limitations
- Background queue workers on cPanel shared hosting rely on scheduled cron executions rather than persistent supervisor daemons.
- Playwright browser automated headless capture requires host display libraries on Windows sandboxes (fallback: HTTP/API verification with manual capture).

---

## 📄 License & Proprietary Rights

The AURA CARD SaaS platform and hardware firmware integration specifications are proprietary commercial software. All rights reserved.
