# AURA CARD — Security Architecture & Hardening Guide

## 1. Threat Model & Security Posture

AURA CARD bridges physical NFC hardware with digital SaaS profiles and commercial payment handling. This surface requires defense-in-depth across 6 security domains.

---

## 2. Authentication, Rate Limiting & Account Security

### 2.1 Password Hashing & Credentials
- All passwords hashed via PHP's native `password_hash()` with `PASSWORD_BCRYPT` (cost 12).
- Email addresses normalized to lowercase before validation and storage.
- Active user middleware (`active.user`) prevents suspended accounts from issuing requests or executing sessions.

### 2.2 Rate Limiting Protections
| Route Endpoint | Rate Limit | Purpose |
| :--- | :--- | :--- |
| `GET /c/{token}` | 60 req/min | Prevents high-speed NFC token enumeration / brute-force scans. |
| `POST /cards/claim` | 10 req/min | Blocks claim code guessing and card hijacking. |
| `POST /login` | 5 req/min | Defends against credential stuffing and brute-force logins. |
| `POST /profile/{id}/publish` | 10 req/min | Prevents rapid ledger depletion or resource flooding. |
| `POST /p/{slug}/contact` | 10 req/min | Protects owners from spam and mail flooding. |
| `POST /checkout/{plan}` | 10 req/min | Throttles receipt uploads and payment request submissions. |

---

## 3. Physical NFC Security & Anti-Counterfeiting

### 3.1 Cryptographic Token Hashing
- **Zero Plaintext Tokens:** Raw NFC public tokens are never stored in the database.
- Tapping a card submits raw token `t`. The server performs:
  $$\text{hash} = \text{hash}('sha256', t)$$
- Only the SHA-256 digest is stored and queried (`public_token_hash`).
- Claim codes are Bcrypt hashed with high salt entropy (`claim_code_hash`).

### 3.2 Token Lifecycle & Replacement
- Reporting a card lost immediately disables the card and marks it `LOST`.
- Linking a replacement card retains profile version history while revoking the lost card's token permanently.

---

## 4. Media Upload Security & Payment Proof Isolation

### 4.1 Strict MIME & Magic-Bytes Verification
File uploads (`SubmitPaymentRequestAction`, `ProfileMediaController`) perform binary header inspection:
```php
$header = file_get_contents($file->getRealPath(), false, null, 0, 16);
// PDF Magic Bytes: %PDF-
// JPEG: \xFF\xD8\xFF
// PNG: \x89PNG\r\n\x1a\n
// WebP: RIFF....WEBP
```
- **SVGs are strictly rejected** to eliminate stored SVG XSS vectors.
- **Executables (`.exe`, `.sh`, `.php`, `.phtml`, `.phar`) are rejected** regardless of fake HTTP headers.
- **Magic bytes mismatch results in immediate 422 validation failure.**

### 4.2 Private Payment Receipt Storage
- Proof files are stored in `storage/app/private/payment_proofs/` on the private `local` disk.
- **Never linked to `public/storage`.**
- File names are randomized using `Str::random(40)` to prevent guessing.
- Served solely via `GET /payment-requests/{paymentRequest}/proof` guarded by:
  - Ownership check: `$paymentRequest->user_id === $request->user()->id`
  - Staff check: `$user->hasPermission('payments.review')` or Super Admin role.

---

## 5. Authorization, Multi-Tenancy & Draft Isolation

### 5.1 Server-Side Ownership Enforcement
All profile mutations verify ownership at the service layer:
```php
if ($lockedProfile->user_id !== $user->id) {
    throw ValidationException::withMessages(['profile' => 'Unauthorized']);
}
```
Client-side tampering with IDs is thwarted by server-side row-level locks and user ID verification.

### 5.2 Draft vs. Published State Isolation
- Public requests to `/p/{slug}` **only read the immutable `content_snapshot`** of the latest published `ProfileVersion`.
- Working drafts stored in `profiles` (name, bio, draft tokens, block order) are completely invisible to the public until explicitly published through the credit/subscription gate.

---

## 6. Privacy & Visitor Anonymity

- **No Raw IP Storage:** Analytics events compute a daily HMAC SHA-256 hash:
  $$\text{visitor\_hash} = \text{hash\_hmac}('sha256', \text{IP} \parallel \text{UserAgent}, \text{APP\_KEY} \parallel \text{date('Y-m-d')})$$
- Cross-day tracking is mathematically impossible due to daily salt rotation.
- Profile contact forms use anti-bot honeypots (`website_trap`) and never reveal cardholder email addresses to the public DOM.
