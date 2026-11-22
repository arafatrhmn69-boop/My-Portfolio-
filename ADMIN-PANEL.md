# THE JOURNEY ARCHIVE — Admin Panel

A secure, dependency-free admin console for the travel portfolio: **PHP 8 (OOP + PDO)**, **SQLite**, and a **Bootstrap 5** dashboard styled to match the public site.

The public HTML site keeps working exactly as before. The admin panel is an optional layer that adds:

- password-protected administration (`password_hash` / `password_verify`)
- a dashboard with live totals
- full CRUD for trips & destinations, including image uploads
- an inquiry inbox fed by the public contact form
- a JSON trip feed and a ready-made public contact endpoint
- a server-rendered demo of the frontend integration

---

## 1. Requirements

| Requirement | Notes |
|---|---|
| PHP **8.0+** (8.1+ recommended) | Uses `str_contains`, `match`, constructor promotion, typed properties |
| `pdo_sqlite` extension | Bundled with most PHP builds; enable in `php.ini` if missing |
| `fileinfo` extension | Used to verify uploaded image MIME types |
| `mbstring` extension | Used for UTF-8 safe length/truncation |
| Apache with `mod_rewrite` / `mod_headers` **or** Nginx | `.htaccess` files protect internals; Nginx rules are in §11 |
| Writable folders | `database/` and `uploads/trips/` must be writable by PHP |

> **GitHub Pages cannot run PHP.** The static site can stay on Pages, but the admin panel needs any PHP host (shared hosting, cPanel/XAMPP, a VPS, or a container). Upload the whole project to the PHP host; the static pages continue to work there too.

No Composer, Node, build step or external PHP library is required.

---

## 2. Folder structure

```text
My-Portfolio-/
│
├── admin/                      # Everything behind the login
│   ├── index.php               # → dashboard (or login)
│   ├── login.php               # sign-in form
│   ├── logout.php              # POST-only sign-out
│   ├── dashboard.php           # summary cards + recent activity
│   ├── account.php             # identity + change password
│   ├── partials/
│   │   ├── header.php          # <head>, sidebar, top bar, flash messages
│   │   └── footer.php          # footer + scripts
│   ├── trips/
│   │   ├── index.php           # list + search + pagination
│   │   ├── create.php          # add (image upload)
│   │   ├── edit.php            # update / replace / remove image
│   │   ├── delete.php          # POST + CSRF delete
│   │   └── _form.php           # shared create/edit form
│   ├── inquiries/
│   │   ├── index.php           # inbox + search + pagination
│   │   ├── view.php            # read one message
│   │   └── delete.php          # POST + CSRF delete
│   └── assets/
│       ├── admin.css           # dashboard design system
│       └── admin.js            # sidebar, confirms, previews, counters
│
├── classes/                    # OOP application layer (autoloaded)
│   ├── Database.php            # single PDO/SQLite connection
│   ├── Schema.php              # CREATE TABLE IF NOT EXISTS
│   ├── Seeder.php              # default admin + starter trips
│   ├── Auth.php                # sessions, login, throttling, password change
│   ├── Csrf.php                # per-session synchronizer tokens
│   ├── Flash.php               # one-time messages
│   ├── Validator.php           # small fluent validator
│   ├── ImageUploader.php       # MIME-checked, randomly named uploads
│   ├── Trip.php                # trips model (CRUD + search + paging)
│   └── Inquiry.php             # inquiries model
│
├── config/
│   ├── config.php              # paths, limits, default admin hash, settings
│   ├── bootstrap.php           # autoloader, sessions, headers, error handler
│   └── .htaccess               # deny all
│
├── database/
│   ├── setup.php               # one-time installer / password reset
│   ├── .htaccess               # blocks the .sqlite file
│   └── portfolio.sqlite        # created automatically (git-ignored)
│
├── api/                        # Public endpoints (no login)
│   ├── trips.php               # GET JSON feed of trips
│   └── contact.php             # GET token / POST contact message
│
├── includes/
│   ├── helpers.php             # e(), url(), image_url(), redirect(), …
│   └── .htaccess               # deny all
│
├── uploads/
│   ├── .htaccess               # blocks PHP execution, enables caching
│   └── trips/                  # uploaded photographs (git-ignored)
│
├── assets/
│   └── trip-placeholder.svg    # fallback cover image
│
├── journeys.php                # public server-rendered demo of the integration
├── ADMIN-PANEL.md              # this file
├── .htaccess                   # blocks internals + directory listings
└── .gitignore                  # ignores the database + uploads
```

---

## 3. Install (5 minutes)

### 3.1 Put the files on a PHP server

Copy/clone the repository into your web root (or a sub-folder — base URLs are auto-detected). For local development either use XAMPP/MAMP or PHP's built-in server:

```bash
cd My-Portfolio-
php -S localhost:8000
```

Then open <http://localhost:8000/>.

### 3.2 Make two folders writable

```bash
# Linux / macOS
chmod -R 775 database uploads/trips

# If your host runs PHP as a different user:
# chown -R www-data:www-data database uploads/trips
```

On shared hosting, set `database/` and `uploads/trips/` to `755` (or `775` where supported) in the file manager.

### 3.3 Run the installer once

```bash
php database/setup.php
```

or visit `https://your-domain.com/database/setup.php` **once**.

This will:

1. create `database/portfolio.sqlite`;
2. create the `admins`, `trips`, `inquiries` and `login_attempts` tables + indexes;
3. add the seeded administrator (username `arafat_admin`);
4. add seven starter trips based on the existing albums (skip with `--no-demo`).

The installer refuses to run again once an administrator exists. Delete `database/setup.php` after installing.

### 3.4 Sign in and change the password

Open:

```text
https://your-domain.com/admin/login.php
```

Use the username and password supplied with this build (also stated at the end of the build conversation — **it is not stored in the repository**). Then go to **Account & Security → Change password** immediately.

### 3.5 Verify

- `/admin/dashboard.php` shows the trip and inquiry totals.
- `/journeys.php` renders the seven seeded trips from SQLite.
- `/api/trips.php` returns JSON.

---

## 4. Database

### `admins`

| Column | Type | Notes |
|---|---|---|
| `id` | INTEGER PK | autoincrement |
| `username` | TEXT | unique, case-insensitive login |
| `password_hash` | TEXT | bcrypt digest from `password_hash()` |
| `created_at` | TEXT | UTC timestamp |

### `trips`

| Column | Type | Notes |
|---|---|---|
| `id` | INTEGER PK | |
| `destination_name` | TEXT | required, ≤ 120 chars |
| `country` | TEXT | required, ≤ 80 chars |
| `description` | TEXT | required, ≤ 5000 chars |
| `price_or_budget` | TEXT | optional free text (e.g. `BDT 8,500 / person`) |
| `travel_date` | TEXT | `YYYY-MM-DD`, required |
| `image_path` | TEXT NULL | `uploads/trips/<random>.jpg` or a seeded `images/...` path |
| `created_at` | TEXT | UTC timestamp |

### `inquiries`

| Column | Type | Notes |
|---|---|---|
| `id` | INTEGER PK | |
| `sender_name` | TEXT | required, ≤ 120 chars |
| `email` | TEXT | required, validated address, ≤ 190 chars |
| `message` | TEXT | required, 10–5000 chars |
| `created_at` | TEXT | UTC timestamp |

### `login_attempts` (extra, security)

| Column | Type | Notes |
|---|---|---|
| `id` | INTEGER PK | |
| `username` | TEXT | attempted username |
| `ip` | TEXT | request IP |
| `attempted_at` | TEXT | UTC timestamp |

Used for brute-force lockout only; rows older than 24 hours are pruned automatically. To regenerate everything from scratch, delete `database/portfolio.sqlite` and run the installer again.

---

## 5. Using the console

### Dashboard

Four summary cards (trips, inquiries, countries, contact-form status), the four newest journeys and the five newest messages. A warning appears while the seeded password is still active.

### Trips & Destinations

- **Add trip** — destination, country, travel date, optional budget, story, optional photograph.
- **Search** — matches destination, country and description.
- **Edit** — replace the photo, tick *Remove the current photograph*, or update any field.
- **Delete** — POST + CSRF, with a JavaScript confirmation; the uploaded image is removed from disk too.

Image rules: JPG/PNG/WebP, max 5 MB, verified with `finfo` + `getimagesize`, stored with a random 32-character filename under `/uploads/trips`. Seeded photographs in `/images` are never deleted by the panel.

### Inquiries

Search by name, email or message; read the full message; reply via `mailto:`; delete permanently. New submissions appear immediately.

### Account & Security

Change the password (12+ characters, at least one letter and one number, must match confirmation). The session ID and CSRF token rotate afterwards.

---

## 6. Public site integration

There are two ways to display trips. **Option A** keeps your existing `index.html` untouched; **Option B** renders cards on the server from `index.php`.

### Option A — keep `index.html`, read the JSON feed (recommended)

`api/trips.php` returns:

```json
{
  "ok": true,
  "count": 7,
  "data": [
    {
      "id": 1,
      "destination_name": "Cox's Bazar",
      "country": "Bangladesh",
      "description": "The long coastline, changing light…",
      "price_or_budget": "BDT 8,500 / person",
      "travel_date": "2026-02-14",
      "image_path": "images/cox-bazar/thumbnail.jpg",
      "image_url": "/images/cox-bazar/thumbnail.jpg",
      "created_at": "2026-11-22 10:12:04"
    }
  ]
}
```

Add a container where the JS-generated cards currently appear, for example in `index.html`:

```html
<div class="journeys-grid" id="db-journeys" aria-live="polite"></div>
```

Then add this script at the end of `js/script.js` (or in a new `js/trips.js` loaded with `defer`):

```js
/* ============================================================
   Load trips from the PHP/SQLite admin panel.
   ============================================================ */
(function () {
  "use strict";

  const grid = document.getElementById("db-journeys");
  if (!grid) return;

  const esc = value => String(value ?? "").replace(/[&<>"']/g, ch => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  }[ch]));

  const pad = n => String(n).padStart(2, "0");

  fetch("api/trips.php?limit=6", { headers: { Accept: "application/json" } })
    .then(response => {
      if (!response.ok) throw new Error("Feed unavailable");
      return response.json();
    })
    .then(payload => {
      if (!Array.isArray(payload.data) || !payload.data.length) {
        grid.innerHTML = '<p class="text-muted">No journeys published yet.</p>';
        return;
      }

      grid.innerHTML = payload.data.map((trip, index) => `
        <article class="journey-card-wrap">
          <a class="journey-card" href="albums.html" aria-label="Explore ${esc(trip.destination_name)}">
            <img src="${esc(trip.image_url)}" alt="Travel photograph representing ${esc(trip.destination_name)}"
                 loading="${index < 2 ? "eager" : "lazy"}" decoding="async" class="is-loaded">
            <div class="card-top">
              <span class="card-number">JOURNEY ${pad(index + 1)}</span>
              <span class="travel-year">${esc((trip.travel_date || "").slice(0, 4))}</span>
            </div>
            <div class="card-bottom">
              <span class="card-region">${esc(trip.country)}</span>
              <h3>${esc(trip.destination_name)}</h3>
              <p class="card-description">${esc(trip.description)}</p>
              <div class="card-meta-row">
                <span>${esc(trip.travel_date)}</span>
                ${trip.price_or_budget ? `<span>${esc(trip.price_or_budget)}</span>` : ""}
                <span class="card-cta">From the archive <span class="arrow" aria-hidden="true">↗</span></span>
              </div>
            </div>
          </a>
        </article>`).join("");
    })
    .catch(() => {
      grid.innerHTML = '<p class="text-muted">Journeys are temporarily unavailable.</p>';
    });
})();
```

The script above is self-contained, escapes every value and uses the same `.journey-card` styling already in `css/style.css`. Remove the old JS data call for that container so two renderers don't fight (in `js/script.js`, the `renderJourneyGrid("#home-journeys")` call — point it at a different container or delete that call if you replace the section).

### Option B — rename `index.html` to `index.php` and render on the server

```bash
git mv index.html index.php
# then replace links to index.html in all pages and js/script.js with index.php
```

Add **at the very top of `index.php`**, before the doctype:

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/config/bootstrap.php';

$tripModel = new Trip(Database::connection());
$homeTrips = $tripModel->all(6);
?>
```

Then replace the home journeys grid with:

```php
<section class="journeys-section section-pad" id="journeys" aria-labelledby="home-journeys-title">
    <div class="container-xxl">
        <div class="section-heading" data-reveal>
            <div>
                <span class="section-index">02 / Selected journeys</span>
                <h2 id="home-journeys-title">Places that stayed</h2>
            </div>
            <p>Every card is published from the admin console and stored in SQLite.</p>
        </div>

        <?php if ($homeTrips === []): ?>
            <p class="text-muted">No journeys published yet.</p>
        <?php else: ?>
            <div class="journeys-grid">
                <?php foreach ($homeTrips as $index => $trip): ?>
                    <article class="journey-card-wrap"
                        data-reveal
                        style="--reveal-delay:<?= e((string) (($index % 3) * 0.08)) ?>s">
                        <div class="journey-card">
                            <img src="<?= e(image_url($trip['image_path'])) ?>"
                                alt="<?= e('Travel photograph representing ' . $trip['destination_name']) ?>"
                                loading="<?= $index < 2 ? 'eager' : 'lazy' ?>" decoding="async" class="is-loaded">

                            <div class="card-top">
                                <span class="card-number">JOURNEY <?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                <span class="travel-year"><?= e(substr((string) $trip['travel_date'], 0, 4)) ?></span>
                            </div>

                            <div class="card-bottom">
                                <span class="card-region"><?= e($trip['country']) ?></span>
                                <h3><?= e($trip['destination_name']) ?></h3>
                                <p class="card-description"><?= e(excerpt((string) $trip['description'], 180)) ?></p>
                                <div class="card-meta-row">
                                    <span><?= e(format_date((string) $trip['travel_date'], 'j M Y')) ?></span>
                                    <?php if (trim((string) $trip['price_or_budget']) !== ''): ?>
                                        <span><?= e($trip['price_or_budget']) ?></span>
                                    <?php endif; ?>
                                    <span class="card-cta">From the archive <span class="arrow" aria-hidden="true">↗</span></span>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
```

`journeys.php` in the repository is a complete, working example of this approach, including a working contact form at the bottom (driven by `assets/contact-demo.js` and `api/contact.php`). Every value is escaped with `e()`, which is `htmlspecialchars()` with `ENT_QUOTES | ENT_SUBSTITUTE` and UTF-8.

### Contact form → SQLite

`api/contact.php` accepts JSON (preferred) or a normal form POST. Add a hidden anti-bot field and a status element to the form:

```html
<form id="contact-form" novalidate>
    <!-- … name / email / message fields … -->

    <!-- Honeypot: humans never see this; bots fill it in. -->
    <input type="text" name="website" tabindex="-1" autocomplete="off"
           aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px">

    <p class="form-status" id="form-status" role="status" aria-live="polite"></p>
    <button class="btn-cinematic" type="submit">Send Message</button>
</form>
```

Replace the demo `initContactForm()` in `js/script.js` with this working version:

```js
function initContactForm() {
    const form = $("#contact-form");
    if (!form) return;

    const status = $("#form-status");
    const submit = form.querySelector('[type="submit"]');
    const endpoint = "api/contact.php";

    let token = null;
    let startedAt = Math.floor(Date.now() / 1000);
    let sending = false;

    const setStatus = (message, isError) => {
        if (!status) return;
        status.textContent = message;
        status.dataset.state = isError ? "error" : "success";
    };

    async function refreshToken() {
        const response = await fetch(endpoint, { headers: { Accept: "application/json" } });
        if (!response.ok) throw new Error("The contact service is unavailable.");
        const data = await response.json();
        token = data.csrf_token;
        startedAt = data.server_time;
    }

    refreshToken().catch(() => {
        setStatus("The contact form is temporarily unavailable. Please try again later.", true);
    });

    form.addEventListener("submit", async event => {
        event.preventDefault();
        if (sending) return;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        sending = true;
        if (submit) submit.disabled = true;
        setStatus("Sending…", false);

        try {
            if (!token) await refreshToken();

            const payload = {
                _csrf: token,
                sender_name: form.elements.namedItem("name").value.trim(),
                email: form.elements.namedItem("email").value.trim(),
                message: form.elements.namedItem("message").value.trim(),
                website: form.elements.namedItem("website").value,
                started_at: startedAt
            };

            const response = await fetch(endpoint, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": token,
                    Accept: "application/json"
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.ok) {
                setStatus(result.message || "Thank you — your message has been received.", false);
                form.reset();
                token = null;
            } else {
                setStatus(result.message || "Please check the form and try again.", true);
            }
        } catch (error) {
            setStatus("Something went wrong. Please try again in a moment.", true);
        } finally {
            sending = false;
            if (submit) submit.disabled = false;
        }
    });
}
```

Also remove the “Demo form only” note from the HTML. The endpoint enforces: CSRF token, honeypot, minimum 2 seconds between loading the form and submitting it, 5 messages per hour per session, name ≤ 120, email ≤ 190 and validated, message 10–5000 characters, then inserts with a prepared statement.

### API summary

| Endpoint | Method | Purpose |
|---|---|---|
| `api/trips.php` | GET | JSON list of trips (`?limit=1…200`) |
| `api/contact.php` | GET | JSON `{csrf_token, server_time}` |
| `api/contact.php` | POST | Store a contact message (JSON or form data) |

Contact responses use `201` on success, `419` for an expired CSRF token, `422` for validation errors and `429` for rate limiting. The body is always JSON:

```json
{ "ok": false, "message": "Please check…", "errors": { "email": "Enter a valid email address." } }
```

---

## 7. Security implemented

| Threat | Mitigation |
|---|---|
| SQL injection | **Every** query uses PDO prepared statements with bound values. `ATTR_EMULATE_PREPARES = false`. No user input is interpolated into SQL. |
| XSS | All output passes through `e()` (`htmlspecialchars` with `ENT_QUOTES \| ENT_SUBSTITUTE`, UTF-8). A strict CSP forbids inline scripts and third-party scripts. |
| CSRF | 32-byte random tokens per session, verified with `hash_equals()`, required on every admin POST (create, update, delete, logout, password change) and the public contact endpoint. |
| Password theft from DB | bcrypt via `password_hash()`; plain text is never stored. `password_needs_rehash()` upgrades older hashes on login. |
| Brute force | 5 failures per username+IP in 15 minutes → temporary lockout; failures are recorded in SQLite; response timing is equalised for unknown usernames. |
| Session hijacking / fixation | Session ID regenerated on login and logout; HttpOnly, SameSite=Lax cookie, `Secure` on HTTPS; idle (30 min) and absolute (2 h) timeouts; user-agent fingerprint check. |
| Malicious uploads | Upload error checks, `is_uploaded_file()`, 5 MB cap, `finfo` MIME allow-list, `getimagesize()` validation, 60-megapixel cap, random filename, extension from the MIME map, PHP execution denied in `/uploads`. |
| Path traversal on delete | `ImageUploader::delete()` only removes files that resolve directly inside `uploads/trips`. |
| Enumeration / information leaks | Generic login errors, `noindex` headers, no stack traces unless `APP_DEBUG` is true, session pages send `Cache-Control: no-store`. |
| Clickjacking / MIME sniffing | `X-Frame-Options`, CSP `frame-ancestors`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`. |
| Direct access to internals | `.htaccess` denies `config/`, `classes/`, `includes/`, all `*.sqlite` files, dotfiles and directory listings. `require`/`include` files also refuse direct access. |
| Setup abuse | The web installer only runs while **no** admin exists; password reset is CLI-only. |

### Production checklist

- [ ] Serve the site over **HTTPS** (the session cookie becomes `Secure` automatically).
- [ ] Change the seeded password on first login.
- [ ] Delete `database/setup.php` (or keep it blocked) after installing.
- [ ] Confirm `APP_DEBUG` is `false` in `config/config.php`.
- [ ] Keep `database/` and `uploads/trips/` writable but **not** world-readable where possible (`755`/`775`, files `644`).
- [ ] Never commit `database/portfolio.sqlite` or uploads (`.gitignore` already covers both).
- [ ] Back up the database regularly (§9).
- [ ] Rotate the password if it was ever shared in chat, email or a screenshot.

---

## 8. Adding or resetting administrators

### Reset the seeded password (recommended recovery path)

```bash
php database/setup.php --new-password
```

Generates a new random 18-character password for `arafat_admin`, prints it **once**, updates the hash and clears old lockouts.

```bash
php database/setup.php --new-password --user=another_admin
```

### Add a second administrator via CLI

```bash
php -r '
require "config/bootstrap.php";
$username = "editor";
$password = "Change-This-Password-2026!";
$stmt = Database::connection()->prepare(
    "INSERT INTO admins (username, password_hash) VALUES (:u, :h)"
);
$stmt->execute([":u" => $username, ":h" => password_hash($password, PASSWORD_DEFAULT)]);
echo "Created {$username}" . PHP_EOL;
'
```

There is intentionally no public “register” page.

---

## 9. Backups

The database is a single file, but SQLite runs in WAL mode, so copy it safely while the site is idle:

```bash
# Preferred: consistent backup using SQLite's own backup command
sqlite3 database/portfolio.sqlite ".backup 'database/backup-$(date +%F).sqlite'"

# Acceptable when the site is idle
cp database/portfolio.sqlite /path/to/backups/
```

Uploaded photos live in `uploads/trips/`. Back up both the database and that folder. A `database/backup-*.sqlite` file is already blocked from HTTP access by `.htaccess`.

---

## 10. Troubleshooting

| Symptom | Fix |
|---|---|
| Blank page or HTTP 500 | Temporarily set `APP_DEBUG` to `true` in `config/config.php`, retry, then set it back. Check the PHP error log. |
| “No administrator account found” on login | Run `php database/setup.php` (or open it once in the browser). Check that `database/` is writable. |
| `database is locked` | Make sure the web-server user owns/can write `database/`; WAL creates `-wal` and `-shm` files there. |
| Changes disappear / “read-only database” | The `database/` folder is not writable by PHP. |
| Login succeeds but immediately redirects to login | Cookies not persisting: check `session.save_path` is writable and that the site is not mixing `http`/`https`. If you are on plain HTTP, the `Secure` flag is not applied — no action needed. |
| Images 404 | Auto-detected base URL is wrong. Uncomment `define('APP_BASE_URL', '/your-subfolder');` in `config/config.php`. |
| Uploads fail with 500 | Some hosts disallow `php_flag` in `.htaccess`; remove the `php_flag engine off` lines from `uploads/.htaccess` (the `<FilesMatch>` deny stays). |
| `fileinfo` missing | Enable `extension=fileinfo` in `php.ini` (or ask the host). |
| Setup shows 403 “Already installed” | An administrator exists, by design. Sign in, or use `--new-password` from the CLI. |
| Contact form returns 419 | Reload the page: the session/CSRF token expired. |
| Contact form returns 429 | The per-session rate limit (5/hour) was reached. |
| CSS looks unstyled | The site needs internet access for the Bootstrap CDN; admin CSS is local. |

---

## 11. Nginx equivalent of the `.htaccess` rules

Apache reads the included `.htaccess` files. On Nginx, add the equivalent rules to your server block:

```nginx
# Internals are never public
location ~ ^/(config|classes|includes)/ {
    deny all;
    return 404;
}

# SQLite database and journals
location ~* ^/database/.*\.(sqlite|sqlite3|db)(-wal|-shm|-journal)?$ {
    deny all;
    return 404;
}

# Never execute anything in uploads
location ~* ^/uploads/.*\.(php|phtml|phar|pht)$ {
    deny all;
    return 404;
}
```

The PHP location block must still pass `.php` files to PHP-FPM. Base URL auto-detection works behind Nginx when `DOCUMENT_ROOT` points at the project root.

---

## 12. File reference

| File | Responsibility |
|---|---|
| `config/config.php` | Paths, timeouts, limits, seeded admin hash, debug flag |
| `config/bootstrap.php` | Autoloader, hardened session, security headers, error handler |
| `includes/helpers.php` | `e()`, `url()`, `admin_url()`, `image_url()`, `redirect()`, dates, pagination |
| `classes/Database.php` | One PDO connection, pragmas, lazy schema creation |
| `classes/Schema.php` | Table + index definitions |
| `classes/Seeder.php` | Admin seed, seven demo trips, random password generator |
| `classes/Auth.php` | Login, throttling, session validation, password change |
| `classes/Csrf.php` | Token generation, form field, constant-time validation |
| `classes/Flash.php` | One-shot session messages |
| `classes/Validator.php` | Required / length / email / date rules |
| `classes/ImageUploader.php` | Secure store, inspect and scoped delete |
| `classes/Trip.php` | Trips CRUD, prepared search, pagination |
| `classes/Inquiry.php` | Inquiries CRUD, search, pagination |
| `admin/partials/header.php` | Shell, sidebar, top bar, flash, page heading |
| `admin/partials/footer.php` | Footer and script tags |
| `admin/login.php` | Sign-in screen |
| `admin/dashboard.php` | Overview |
| `admin/trips/*` | Trip management |
| `admin/inquiries/*` | Inbox |
| `admin/account.php` | Password change + security summary |
| `api/trips.php` | Public JSON feed |
| `api/contact.php` | Public contact endpoint |
| `journeys.php` | Public integration demo (trips + working contact form) |
| `assets/contact-demo.js` | Vanilla-JS contact form demo used by `journeys.php` |
| `database/setup.php` | Installer / password reset |
| `admin/assets/admin.css` | Dashboard design system |
| `admin/assets/admin.js` | Sidebar, confirms, upload preview, counters |

---

## 13. Design notes

The console deliberately uses the same visual language as the public portfolio: `#07090a` ink surfaces, `#c8a66c` gold, Cormorant Garamond headings and Manrope body text. It is fully responsive (off-canvas navigation below 992 px), keyboard accessible, respects `prefers-reduced-motion`, and works without JavaScript for every core action (JS only adds the sidebar toggle, confirmations, previews and counters).

All third-party assets are loaded from CDNs already used by the public site (Bootstrap 5.3.3, Bootstrap Icons, Google Fonts). The admin panel has no build step and no npm dependencies.

---

## 14. Default credentials

The username is:

```text
arafat_admin
```

The plain-text password is **not** in this repository. It was delivered in the build conversation and stored only as a bcrypt digest in `config/config.php`. If it is ever lost:

```bash
php database/setup.php --new-password
```

Then change it from **Account & Security**.
