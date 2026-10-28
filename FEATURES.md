# crqr: Features Roadmap

A QR code generator, URL shortener and link-in-bio tool, built one feature at a time.

**Stack:** Laravel 12 · PHP 8.2+ · MySQL 8 / MariaDB · Blade + Tailwind + Alpine.js · chillerlan/php-qrcode
**Hosting:** CloudPanel (Nginx + PHP-FPM), local dev on XAMPP
**Domain:** `crqr.in`

| URL | Purpose |
|---|---|
| `crqr.in` | Landing page |
| `crqr.in/{code}` | Short link redirect (e.g. `crqr.in/aB3x9K`) |
| `crqr.in/@username` | Link-in-bio page |
| `crqr.in/qr` | Public QR generator |
| `crqr.in/dashboard` | Logged-in app |

**Core idea:** a short link, a QR code and a bio button are all the same *link* record. Every QR code encodes a short link, which makes it *dynamic* (you can change where it points and count scans). Every bio button is tracked the same way.

Legend: `[ ]` todo · `[~]` in progress · `[x]` done

---

## 0. Project Setup
- [x] Install Composer locally
- [x] Create the Laravel 12 project in `crqr/`
- [x] Configure `.env` (app name, URL, MySQL connection)
- [x] Create the `crqr` database in local MySQL
- [x] Install Tailwind + Alpine.js (Vite)
- [x] Base layout: header, nav, flash messages, footer
- [x] Landing page (hero + 3 feature cards)
- [ ] Git: `.gitignore`, first commit

## 1. Authentication & Accounts
- [x] Auth built in-house (Breeze would clash with Tailwind 4): register, login, logout
- [x] Sign up (name, email, password)
- [x] Unique `username` at sign-up (used for `/@username`)
- [x] Log in / log out
- [ ] Forgot / reset password (mail configured)
- [ ] Email verification (optional for MVP)
- [ ] Account settings: change name, email, password
- [ ] Delete account (cascades links, clicks, bio)
- [x] Reserved words list, shared by usernames and short-link aliases so they never clash with app routes (`admin`, `login`, `register`, `logout`, `api`, `dashboard`, `qr`, `bio`, `settings`, `assets`, `build`, `storage`, etc.)

## 2. URL Shortener
### 2.1 Create
- [x] Form: destination URL, optional title, optional custom alias
- [x] Auto-add `https://` when no scheme is given
- [x] Allow only `http` / `https` (block `javascript:`, `data:`, etc.)
- [x] Random code generator (6 chars, lowercase without look-alike characters, collision-safe; codes are case-insensitive)
- [x] Custom alias validation (3–32 chars, `a-z 0-9 - _`, stored lowercase, not reserved, unique)
- [x] Block shortening our own domain (no redirect loops)
- [ ] Auto-fetch the page title when none is given (optional)

### 2.2 Redirect
- [x] Route `/{code}` → 302 redirect to the destination
- [x] 404 page for unknown or disabled codes
- [x] Keep the redirect path fast (one indexed lookup, no heavy work)

### 2.3 Manage
- [x] Dashboard list: title, short URL, destination, clicks, created date
- [x] Copy short URL button
- [x] Edit destination URL and title (this is what makes QR codes dynamic)
- [x] Enable / disable a link
- [x] Delete link (with confirm)
- [x] Search and filter links
- [x] Pagination

## 3. QR Code Generator
### 3.1 Generator
- [x] Public QR page (no login needed for static QR codes)
- [x] Live preview while typing
- [ ] Content types:
  - [x] URL / plain text
  - [x] Wi-Fi (SSID, password, encryption)
  - [x] Email (to, subject, body)
  - [x] Phone / SMS
  - [ ] vCard contact (later)
- [x] Options: size, foreground colour, background colour, margin, error-correction level
- [x] Download as PNG
- [x] Download as SVG

### 3.2 Dynamic QR (linked to short links)
- [x] "QR" button on each short link in the dashboard
- [x] QR encodes the short URL + `?s=qr` so scans are tracked separately
- [ ] Save QR style per link (colours, size)
- [x] Changing a link's destination updates the QR without reprinting

### 3.3 Extras (post-MVP)
- [ ] Logo in the centre of the QR
- [ ] Dot / corner styles
- [ ] Frames with "Scan me" text
- [ ] Download as PDF / high-res print

## 4. Link-in-Bio Page
### 4.1 Public Page
- [ ] Route `/@username`
- [ ] Shows avatar, display name, bio text, and a list of link buttons
- [ ] Mobile-first layout
- [ ] Bio buttons go through short links with `?s=bio` (tracked)
- [ ] SEO / social meta tags (title, description, OG image)
- [ ] "Made with crqr" footer (toggle later for paid plans)

### 4.2 Bio Editor
- [ ] Edit display name, bio text
- [ ] Upload avatar (resize, store in `storage/`)
- [ ] Add a link to the bio (new or existing short link)
- [ ] Reorder links (drag and drop, or up/down)
- [ ] Show / hide individual links
- [ ] Remove a link from the bio (keep the short link)
- [ ] Live preview beside the editor
- [ ] Copy bio URL and download the bio page QR

### 4.3 Appearance
- [ ] Theme presets (light, dark, plus 2–3 coloured themes)
- [ ] Custom background colour / button style (post-MVP)
- [ ] Social icons row (Instagram, X, YouTube, etc.) (post-MVP)

## 5. Analytics
### 5.1 Tracking
- [ ] `clicks` table: link, time, source, referrer host, device, country (later)
- [ ] Source detection: `qr`, `bio`, referrer domain, or `direct`
- [ ] Device detection: mobile / tablet / desktop
- [x] Skip bots and link-preview crawlers (in click counter; reuse for `clicks` table)
- [ ] Log clicks through the queue so redirects stay fast
- [x] Denormalised `clicks` counter on each link for fast dashboard totals

### 5.2 Reports
- [ ] Per-link stats page: total clicks, clicks per day (last 30 days)
- [ ] Breakdown by source (QR vs bio vs direct vs referrer)
- [ ] Breakdown by device
- [ ] Bio page: total views + clicks per button
- [ ] Dashboard summary: total links, total clicks, top links
- [ ] Date-range filter (7 / 30 / 90 days)
- [ ] Country breakdown via GeoIP (post-MVP)
- [ ] CSV export (post-MVP)

## 6. Security & Abuse Protection
- [ ] CSRF on all forms (Laravel default)
- [x] Rate limit link creation and login attempts
- [x] Require login to create short links
- [ ] Domain blocklist (manual list in DB/config)
- [ ] Google Safe Browsing check on create (post-MVP)
- [ ] "Report this link" page
- [ ] Admin can disable any link

## 7. Admin Panel (minimal)
- [ ] Admin role flag on users
- [ ] List users (search, ban / unban)
- [ ] List all links (search, disable, delete)
- [ ] Review reported links
- [ ] Basic site stats (users, links, clicks today)

## 8. Deployment (CloudPanel)
- [ ] Create a PHP 8.2+ site with the Laravel vhost template
- [ ] Create the MySQL database and user
- [ ] Deploy code (Git pull or CI), `composer install --no-dev`, `npm run build`
- [ ] `.env` production values, `php artisan key:generate`, `migrate --force`
- [ ] `php artisan storage:link`, config/route/view cache
- [ ] Cron: `* * * * * php artisan schedule:run`
- [ ] Queue worker via cron (`queue:work --stop-when-empty`) or Supervisor
- [ ] Let's Encrypt SSL
- [ ] Point `crqr.in` DNS (A record) to the CloudPanel server; redirect `www.crqr.in` → `crqr.in`
- [ ] Optional: Cloudflare in front, cache public bio pages
- [ ] Backups (DB dump schedule)

---

## Later / Nice-to-have
- [ ] Custom domains per user
- [ ] Link expiry date / click limit
- [ ] Password-protected links
- [ ] UTM builder
- [ ] Bulk shorten (CSV upload)
- [ ] Public API with tokens
- [ ] Teams / workspaces
- [ ] Paid plans (Stripe / Razorpay) with limits per plan
- [ ] Browser extension

## Open Decisions
- [ ] Only for you, or public signups?
- [x] Domain: `crqr.in` serves everything (it's already short, so no separate short domain is needed)
- [ ] Livewire, or Inertia (Vue/React), for the dashboard?
- [ ] CloudPanel VPS size (affects how analytics are stored)
