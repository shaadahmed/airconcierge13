# Air Concierge — User Documentation (Phase 0–1)

**Audience:** QA, stakeholders, and anyone trying the **new** application shell  
**Status:** Early preview only — not production  
**Last updated:** 2026-07-16

---

## What this is

This is the **new** Air Concierge application (Laravel 13), still under development after foundation (Phase 0) and login/routing scaffold (Phase 1).

It is **not** the live production system. Production continues on the existing XAMPP / legacy app until a formal cutover is approved.

Most business features (bookings, payments, reports, property admin, and so on) are **not available** in this build. You will mainly see login, a simple dashboard, and a few placeholder pages.

---

## How to access

1. Open the application URL provided by your team (locally this is usually the Sail / `APP_URL` address, for example `http://localhost`).
2. Use **Login** (or go to `/login`).

If you cannot open the site, contact engineering — the stack may not be running.

---

## Sign in

1. Enter your **email** and **password**.
2. Optionally check **Remember me**.
3. Select **Log in**.

Notes for this preview:

- Sign-in uses **email**, not username.
- Repeated failed attempts may be temporarily blocked (rate limiting).
- Password-expiry and other legacy login rules are **not** in this build yet.

### Local development seed (QA only)

After a fresh migrate + seed on a **local** environment, a sample account may exist:

| Field | Value |
|-------|--------|
| Email | `test@example.com` |
| Password | `password` (User factory default) |
| Role | `superadmin` |

Use this **only** on local/dev. Never treat it as a production credential.

---

## After you sign in

- You land on the **Admin dashboard** shell (`/admin/dashboard`).
- Use **Log out** to end the session.

Placeholder pages that may appear in the URL structure (content is not finished):

- **Owner statements** (`/admin/owner-statements`)
- **Terms** (`/admin/terms`)

These pages are stubs for future owner workflows. In a later release, owners without required terms or active property access may be redirected here; with the current stubs, that enforcement is not active for real business data.

---

## What you cannot do yet

Do not expect these in the Phase 0–1 preview:

- Creating or managing bookings and payments  
- Reports and the full dashboard  
- Hostaway admin tools (webhook endpoint requires configured Basic Auth credentials; sync processing still pending)  
- Cron jobs via public web URLs  
- Full role-based menus and admin modules from the old app  

For product questions or missing features, ask the migration / engineering owner. For a technical overview, see [`technical-documentation.md`](technical-documentation.md).

---

## Who to contact

| Issue | Contact |
|-------|---------|
| Cannot log in / wrong environment | Engineering team managing `airconcierge13` |
| Access to the live production app | Keep using the legacy Air Concierge system |
| Feature requests for unfinished modules | Raise via the project migration process |

---

## What’s next

User documentation will grow as modules ship (for example Hostaway, email/chronology, bookings & payments). Until then, treat this preview as a sign-in and shell check only.
