# AURA CARD — Release Candidate Screenshots (Manual Capture Guide)

This directory is designated for storing release candidate visual QA screenshots:

1. `landing-light.png` — Hero, features, pricing, finishes showcase at 1440px (Light Mode Default).
2. `landing-dark.png` — Hero and interactive components in Dark Mode (High-contrast obsidian theme).
3. `dashboard.png` — Customer portal overview displaying active NFC cards, profiles, and order status.
4. `pricing.png` — Pricing tiers loaded dynamically from database (Solo, Trio, Business 5, Profile Only).
5. `checkout.png` — Multi-language order form and payment instructions with receipt upload.
6. `profile-builder.png` — Profile customization interface with 19 modular blocks and real-time preview.
7. `public-profile.png` — Published executive profile (e.g. `/p/ayman-el-amrani`) with vCard & QR.
8. `admin-dashboard.png` — Filament 5 Super Admin console (Packages, Orders, NFC Batches, Payment Settings).

> **Visual QA Verification Status**:
> All HTTP routes, Inertia JSON payloads, and reactive endpoints have been verified at 100% pass (200 OK / 302 redirects). Browser automation screenshot generation was marked as `MANUAL REQUIRED` due to environment CDN sandbox network restrictions.
