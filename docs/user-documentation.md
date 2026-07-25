# Air Concierge — User Documentation

**Audience:** QA, stakeholders, and anyone trying the **new** application  
**Status:** Migration preview — Nuxt admin UI live; not production cutover  
**Last updated:** 2026-07-25

---

## What this is

This is the **new** Air Concierge application (Laravel 13 API + Nuxt 3 admin SPA). Production continues on the existing XAMPP / legacy app until a formal cutover is approved.

Interactive admin screens live in the Nuxt app (typically `http://localhost:3000` locally). Laravel serves JSON under `/admin/*`, auth, and PDF downloads.

---

## How to access

1. Open the SPA URL provided by your team (local Nuxt: `http://localhost:3000`).
2. Use **Login**.

If you cannot open the site, contact engineering — Sail and/or Nuxt may not be running. See [`docs/deploy/spa-hosting.md`](deploy/spa-hosting.md) for production hosting notes.

---

## Sign in

1. Enter your **email** and **password**.
2. Optionally check **Remember me**.
3. Select **Log in**.

Notes:

- Sign-in uses **email**, not username.
- Repeated failed attempts may be temporarily blocked (rate limiting).

### Local development seed (QA only)

After migrate + seed on a **local** environment:

| Field | Value |
|-------|--------|
| Email | `test@example.com` |
| Password | `password` (User factory default) |
| Role | `superadmin` |

Use this **only** on local/dev.

---

## After you sign in

You land on the **Admin dashboard** with summary stats and a revenue chart.

Available admin areas (nav):

- Bookings, properties, payments, property payments  
- Documents, imports  
- Chronologies, send email  
- Reports, Zoho Sign status, failed jobs  
- **Owner terms** — view agreement; Accept or Decline (decline ends the session)  
- **Owner statements** — property + date-range profit/loss summary and CSV export  

Owner terms and active-property middleware apply on owner terms/statements routes (not the whole admin shell).

---

## What is still follow-up

- Full legacy dashboard commission / headline filter suite  
- Complete legacy P&amp;L line items that are not yet on the L13 bookings schema  
- Staff CMS to edit/reset owner agreement content  

For a technical overview, see [`technical-documentation.md`](technical-documentation.md).
