# AURA CARD — Operations & Administration Manual

## 1. Lead Generation & Contact Form Engine

The digital profile includes an integrated contact and inquiry capture block (`lead_form` / `contact_form`).

### 1.1 Invariant Rules & Security
- **Zero Email Disclosure:** The cardholder's email address is never exposed in client HTML or API payloads.
- **Anti-Spam Honeypot:** The form includes a hidden trap field (`website_trap`) and timestamp guard (`_hp_time`). Automated spambots populating these fields receive a synthetic `200 OK` response while the submission is silently discarded.
- **Rate Limiting:** Restricted to 10 submissions per minute per IP via Laravel's rate limiter (`throttle:10,1`).
- **Notification Dispatch:** Legitimate leads trigger an instant `ProfileLeadReceivedNotification` to the profile owner containing the prospect's name, email, phone number, and message.
- **Data Retention:** Inquiries are stored in `profile_leads` indexed by `profile_id`.

---

## 2. Privacy-Preserving Analytics & Visitor Telemetry

AURA CARD implements an ethical, GDPR-compliant analytics engine that tracks interactions without persistent visitor tracking:

### 2.1 Daily Rotating HMAC Hashing
```
Raw IP + User-Agent + [APP_KEY . Current Date]  ──►  HMAC-SHA256  ──►  visitor_hash
```
- **No Raw IP Storage:** The visitor's raw IP address is never stored in the database.
- **Cross-Day Isolation:** Because the salt rotates daily with `date('Y-m-d')`, a visitor's interactions on Monday cannot be cross-referenced with their visits on Tuesday.
- **Tracked Event Types:**
  - `view`: Profile visit (server-side recorded).
  - `nfc_tap`: Physical card tap on NFC device.
  - `qr_scan`: Profile QR code scan or download.
  - `vcard_download`: RFC 6350 contact card save.
  - `link_click`: Outbound social or website links.

### 2.2 Metrics Aggregation
The `ProfileAnalyticsService::getProfileMetrics(Profile $profile, int $days = 30)` method aggregates counts:
- Total page views
- Physical NFC card taps
- QR code scans
- vCard downloads
- External action clicks
- Daily interaction timeline

---

## 3. Administrative Workflows in Filament

Staff and super-admins manage the platform through the Filament 5 admin panel at `/admin`.

### 3.1 Payment Verification Workflow (`Commercial & Finance > Payment Requests`)
1. Review pending transfer receipts.
2. Verify bank credit via corporate banking portal (Attijari Net / CIH Enterprise) using the reference number.
3. Click **Approve** to activate the subscription immediately.
4. If invalid or unreadable, click **Reject** and supply a clear customer-facing reason.

### 3.2 NFC Card Batch Creation (`NFC Lifecycle > Card Batches`)
1. Create new batch specifying quantity, card type (`matte_black`, `gold_foil`, `bamboo_wood`, etc.).
2. The system generates high-entropy random public tokens (SHA-256 hashed in DB) and 8-character activation claim codes (Bcrypt hashed).
3. Export CSV with serial numbers and raw tap URLs (`https://auracard.ma/c/{raw_token}`) for factory engraving.

### 3.3 Security & Moderation
- **Profile Suspension:** Admins can suspend violating profiles (`profiles.suspend` permission). Suspended profiles return 404/suspended notice and cannot be tapped or published.
- **Audit Logs (`Settings & Audit > Audit Logs`):** Immutable log entries record every privileged action with actor ID, IP address, before/after payloads, and timestamps.
