## 1. Executive Summary & Moroccan Market Context

AURA CARD monetizes digital professional identities in Morocco through a hybrid model:
1. **Commercial Hardware Packages (12-Month Entitlements):**
   - **AURACARD Solo:** 150 MAD (1 physical card, 1 profile, 3 edits, 12 months)
   - **AURACARD Trio (Featured):** 249 MAD (3 physical cards with finish catalog choice, 3 profiles, 5 edits/profile, 12 months)
   - **AURACARD Business 5:** 399 MAD (5 physical cards, 5 profiles, 10 edits/profile, 12 months)
   - **AURACARD Profile Only:** 100 MAD (0 inventory cards, customer brings compatible NFC tag, 1 profile, 4 edits, 12 months)
2. **Edit Modification Credits Ledger:** An immutable double-entry ledger managing digital profile versioning costs. First publication of any profile is **100% Free**; credits are granted after first publication and isolated per profile.
3. **Database-Backed Moroccan Payment Rails:** Direct support for National Bank Wire Transfers and Cash Branch Agency deposits. All banking details and instructions are dynamically configured in the database via Super Admin (`payment_settings`) with zero hardcoded banking secrets.

---

## 2. Commercial Package Matrix

| Package | Price (MAD/12 Months) | Physical Cards | Profiles | Credits / Profile | Hardware Origin |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **AURACARD Solo** | 150 MAD | 1 Card | 1 Profile | 3 Credits | AuraCard Inventory |
| **AURACARD Trio** *(Featured)* | 249 MAD | 3 Cards | 3 Profiles | 5 Credits | AuraCard Inventory |
| **AURACARD Business 5** | 399 MAD | 5 Cards | 5 Profiles | 10 Credits | AuraCard Inventory |
| **AURACARD Profile Only** | 100 MAD | 0 Cards | 1 Profile | 4 Credits | Customer Owned |

---

## 3. Edit Credit Invariants & Ledger Rules

The edit credit system prevents database bloat and creates monetization incentives:

```
                                  [First Publish]
                                         │
                                         ▼
                      Is entitlement credits already granted?
                                   /          \
                                 No            Yes
                                 /              \
                   Grant +2 Credits             Proceed to Cost Check
                   Mark granted = true                    │
                   Cost = 0 Credits                       │
                                                          ▼
                                            Is content hash identical?
                                                    /         \
                                                  Yes          No
                                                  /             \
                                     Cost = 0 Credits     Has unlimited subscription?
                                     (Unchanged Publish)          /         \
                                                                Yes          No
                                                                /             \
                                                     Cost = 0 Credits    Cost = 1 Credit
                                                     (Pro / Business)     (Deduct from Ledger)
                                                                               │
                                                                          Balance >= 1 ?
                                                                           /         \
                                                                         Yes          No
                                                                         /             \
                                                                 Publish OK       422 Insufficient Credits
```

### Invariants:
1. **First Publication Invariant:** The first successful publication of a profile bound to an active physical card entitlement awards exactly **+2 edit credits** once. Replacement cards preserving history do not re-grant credits.
2. **Zero-Cost Unchanged Publish:** Publishing without modifying draft attributes or blocks costs **0 credits** and maintains the existing version number.
3. **Changed Publish Cost:** A publication containing modified attributes or block configurations costs **1 credit** for users on the free tier.
4. **Unlimited Subscription Override:** When a user holds an active subscription containing the `profile.unlimited_edits` feature flag, all changed publications cost **0 credits**, preserving their existing credit balance.
5. **Zero-Balance Protection:** If balance is 0 and user lacks an unlimited subscription, publication is blocked with a validation exception directing them to `/pricing`.

---

## 4. Moroccan Payment Rails & Proof Verification

### 4.1 Payment Options
- **Virement Bancaire:**
  - Bank: Attijariwafa Bank Maroc / BMCE / CIH
  - 24-digit RIB display with 1-click clipboard copy
  - Mandatory transfer reason: `AURA-XXXXXX` (client reference hint)
- **Versement Espèces en Agence:**
  - Partner networks: Wafacash, Cash Plus, Barid Cash
  - Paper receipt photo upload

### 4.2 Security & Private Storage
- Receipts are uploaded via `POST /checkout/{plan}`.
- Strict MIME and magic-bytes inspection (`%PDF-`, `\xFF\xD8\xFF`, `\x89PNG`, `RIFF....WEBP`).
- Executables, PHP scripts, SVGs, and shell files are strictly rejected.
- Files are saved to `storage/app/private/payment_proofs/{random_hash}.ext` on the `local` disk (never in `public/storage`).
- Access is strictly gated via `GET /payment-requests/{paymentRequest}/proof`:
  - Requester must be the payment request owner or have the `payments.review` permission / Super Admin role.
  - Unauthorized requests return `403 Forbidden`.

### 4.3 Admin Approval Workflow
In Filament Admin (`Commercial & Finance > Payment Requests`):
1. **Pending Queue:** Lists submissions with customer name, email, plan, amount, and reference number.
2. **Approve Action:**
   - Locks row atomically (`lockForUpdate`).
   - Checks idempotency: if already approved/finalized, throws `ValidationException`.
   - Transitions status to `APPROVED`, records `processed_by` and `processed_at`.
   - Deactivates previous subscriptions.
   - Creates active `Subscription` with immutable `plan_snapshot` capturing features at time of purchase.
   - Writes immutable `AuditLog` entry (`action: payment.approved`).
   - Sends `PaymentApprovedNotification` to customer.
3. **Reject Action:**
   - Requires mandatory rejection reason.
   - Transitions status to `REJECTED`, stores reason, records `processed_by`.
   - Writes immutable `AuditLog` entry (`action: payment.rejected`).
   - Sends `PaymentRejectedNotification` with explanation to customer.
