# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

Landing page + admin panel for **Піцерія "Travel"**, Khmelnytsky, Ukraine.
Pure PHP, no framework, no database. Flat-file JSON storage.

## Business info

- **Address:** вул. Шевченка 89 (біля залізничного вокзалу)
- **Hours:** 9:00–24:00
- **Phone:** 096 890 40 55
- **Services:** dine-in, takeaway, delivery, events (корпоративи, дні народження), music/DJ daily 9–24
- **Promos:** 2 mega pizzas 60cm → 3rd 30cm free; order 2 mega pizzas → 3 beers free

## Dev environment

DDEV (Docker). PHP 8.4, Nginx, MariaDB 11.8.

```bash
ddev start       # start containers
ddev stop        # stop containers
ddev restart     # restart
ddev ssh         # shell into web container
ddev exec <cmd>  # run command inside web container
```

Site runs at `https://pizza.ddev.site`

## Files (to be created)

- `index.php` — landing page (reads data.json)
- `admin.php` — admin panel (tabs: Бронювання / Контент / Налаштування)
- `submit.php` — booking/order form POST handler
- `data.json` — all settings + bookings array
- `pizza-photo/` — restaurant photos

## Tech stack

- Tailwind CSS via CDN (config inline via `tailwind.config = {...}`)
- Google Fonts: **Play 400/700** (body), **Playfair Display 400** (headings)
- Font Awesome 6 CDN
- IMask.js (phone masking)
- Vanilla JS only — no jQuery, no Alpine, no React

## Design rules

- Minimum font size: **16px** (no `text-xs`, `text-sm`, no `text-[Xpx]` below 16px)
- Colors: `gray-950/900` backgrounds, warm accents (amber/orange suit a pizzeria), `amber-400/500` CTAs
- Headings use `font-playfair` class (Playfair Display)
- Body uses `font-play` class (Play)
- Hero section is always **100% static** — no scroll animations, no JS effects on it
- All other sections use `.anim .anim-up/.anim-left/.anim-right` + IntersectionObserver for fade-in
- Layout is editorial/asymmetric — avoid symmetric grid cards

## data.json settings keys

`business_name`, `phone`, `address`, `business_desc`, `promo_text`,
`form_title`, `footer_text`, `meta_title`, `meta_desc`, `og_image`,
`telegram_token`, `telegram_chat_id`, `analytics_id`, `admin_password`

## Booking record shape

```json
{ "id": 1710000000, "name": "...", "phone": "...", "persons": 4,
  "booking_date": "2026-03-20", "date": "15.03.2026 14:32:00",
  "status": "new", "note": "" }
```

## Admin

- Session-based auth, password stored in `data.json` → `settings.admin_password`
- Default password: `pizza`
- CSV export includes: date, name, phone, booking_date, persons, status, note

## Patterns to follow

- Always use `e()` helper for output escaping: `function e($text) { return htmlspecialchars($text ?? ''); }`
- Settings read with fallback: `e($settings['key']) ?: 'default'`
- New booking fields → update in 3 places: `submit.php` (save), `admin.php` (display + CSV), Telegram message
- New content fields → update in 2 places: `admin.php` (save_content fields array + form UI), `index.php` (read + render)
