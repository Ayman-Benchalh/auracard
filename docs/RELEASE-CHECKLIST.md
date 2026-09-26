# AURA CARD — Release Candidate & Pre-Flight Verification Checklist

This checklist must be executed prior to any deployment or release candidate tag.

---

## 1. Database & Migrations Gate
- [x] All migrations run cleanly with zero errors on a fresh database:
  ```bash
  php artisan migrate:fresh --seed
  ```
- [x] Migration status confirms all migrations are tracked:
  ```bash
  php artisan migrate:status
  ```
- [x] Database seeder populates roles, permissions, themes, templates, professions, commercial plans, and payment settings without missing foreign keys or pivot issues.

---

## 2. Route & Authorization Gate
- [x] No private, draft, or administrative routes are publicly accessible without authentication.
- [x] Public endpoints:
  - `GET /` (Marketing homepage)
  - `GET /c/{token}` (NFC Tap resolver, throttled 60/min)
  - `GET /p/{slug}` (Published Profile view, throttled 120/min)
  - `GET /p/{slug}/vcard` (RFC 6350 vCard 4.0 download, throttled 60/min)
  - `GET /p/{slug}/qr` (SVG QR Code, throttled 60/min)
  - `POST /p/{slug}/contact` (Lead capture with honeypot, throttled 10/min)
  - `POST /p/{slug}/events` (Visitor telemetry, throttled 60/min)
  - `GET /pricing` (Commercial matrix)
- [x] Customer routes (`/dashboard`, `/cards/*`, `/profile/*`, `/checkout/*`, `/subscription`) require `auth` and `active.user`.
- [x] Admin routes (`/admin/*`) require authentication and specific Filament panel permissions.

---

## 3. Automated Test Suite Gate
- [x] Entire test suite passes with **zero failing tests**:
  ```bash
  php artisan test
  ```
- [x] Tests cover:
  - RBAC & Role inheritance
  - Auth, registration, email verification, suspended account blocking
  - Physical NFC card token generation, claiming, locking, reporting lost, and replacement
  - Profile onboarding, draft/published version isolation, and modular blocks
  - vCard 4.0 generator and dynamic QR code generation
  - First publish +2 credit bonus invariant
  - Subsequent unchanged publish = 0 credits, changed publish = 1 credit
  - Active subscription unlimited publish = 0 credits
  - Payment proof upload, strict MIME/magic-bytes validation, and private storage streaming
  - Admin approval/rejection idempotency, immutable plan snapshot, and audit logs
  - Profile lead capture, honeypot defanging, and privacy analytics

---

## 4. Frontend & Asset Compilation Gate
- [x] TypeScript typecheck passes with zero errors:
  ```bash
  npm run typecheck
  ```
- [x] Production asset build compiles cleanly:
  ```bash
  npm run build
  ```
- [x] Manifest file generated in `public/build/manifest.json`.

---

## 5. Security & Vulnerability Audits Gate
- [x] Composer dependencies audit:
  ```bash
  composer audit
  ```
- [x] NPM dependencies audit:
  ```bash
  npm audit --audit-level=high
  ```
- [x] Secret scanning:
  - Zero `.env` credentials in git tracking.
  - Zero private keys, RIBs, or sensitive customer tokens in source code.

---

## 6. cPanel Production Target Readiness Gate
- [x] Zero Node.js runtime requirement on the production server.
- [x] Zero Redis / Supervisor / root requirement.
- [x] Directory isolation structured so non-public files live outside `public_html`.
- [x] Database-backed queue, cache, and session drivers configured.
- [x] Crons configured for scheduler and self-terminating queue worker.
