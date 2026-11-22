# THE JOURNEY ARCHIVE

A premium, cinematic personal travel portfolio for a **student + aspiring web developer + traveller + explorer**.

> Every Place Has a Story. Every Journey Becomes a Memory.

Built with **HTML5, CSS3, Bootstrap 5 and Vanilla JavaScript only**. No React, Next.js, Vue, Angular, Tailwind, TypeScript or build tools. The homepage, About and Journeys pages are static; the **Gallery, Album and admin pages** are plain PHP 8 + SQLite (no framework).

An optional **PHP 8 + SQLite admin panel** is included for managing journeys, gallery albums and contact messages. It lives in `admin/`, `classes/`, `config/`, `database/` and `api/`, and requires PHP hosting. The public **Gallery** and **Album** pages (`gallery.php`, `album.php`) are rendered from the same SQLite database, so every upload or deletion appears on the site immediately. See [`ADMIN-PANEL.md`](ADMIN-PANEL.md) for installation, architecture, the public integration snippets and the security model.

---

## 1. Run it in VS Code

1. Install PHP 8+ with `pdo_sqlite` (XAMPP/MAMP, or any PHP host).
2. Run `php database/setup.php` once, then `php -S localhost:8000`.
3. Open **http://localhost:8000/index.html**.

The static HTML pages still work by double-clicking or with Live Server, but the **Gallery** and **Album** pages are now PHP: they read `gallery_photos` from SQLite. For those two pages (and the admin console), use the PHP server or a real PHP host. The legacy `gallery.html` and `album.html` URLs redirect to their PHP equivalents.

All internal links use relative paths. Bootstrap CSS and the optional remote placeholder photos need an internet connection; the custom CSS layouts and generated SVG photo fallbacks still work offline.

---

## 2. Project structure

```text
travel-portfolio/
├── index.html          # Homepage: hero, journeys, quote, timeline, about, skills, featured gallery, contact
├── about.html          # Longer personal story
├── albums.html         # All seven editorial album cards
├── album.php           # Cinematic album slideshow, rendered from SQLite gallery_photos
├── gallery.php         # Filterable masonry gallery + fullscreen lightbox, rendered from SQLite
├── album.html          # Legacy URL → redirects to album.php
├── gallery.html        # Legacy URL → redirects to gallery.php
│
├── admin/              # PHP 8 admin console (trips, gallery, inquiries, account)
├── classes/            # Database, models, auth, CSRF, validation, uploads
├── config/             # Configuration and bootstrap
├── database/           # Installer + auto-created SQLite database (git-ignored)
├── uploads/gallery/    # Admin-uploaded gallery photographs (git-ignored)
│
├── css/
│   └── style.css       # All shared cinematic styling and responsive rules
├── js/
│   ├── script.js       # Nav/footer, cards, slideshow, gallery lightbox, animations
│   └── theme-init.js   # External pre-paint theme + loader boot (CSP-safe)
├── images/
│   ├── profile/
│   ├── cox-bazar/
│   ├── teknaf/
│   ├── bandarban/
│   ├── sylhet/
│   ├── sitakunda/
│   ├── akhilpur/
│   ├── guliakhali/
│   └── README.md       # Exact image filenames and replacement notes
└── README.md
```

The navbar and footer are inserted by `js/script.js` into `#site-nav` and `#site-footer` so you do not have to edit them on five separate pages.

---

## 3. Edit these first

Open **`js/script.js`**. The top of the file is the beginner edit guide.

| What to change | Where |
|---|---|
| Your name | `SITE.name = "[YOUR NAME]"` |
| Your contact email placeholder | `SITE.email = "[YOUR EMAIL]"` |
| Real travel years | Every `year: "YEAR"` inside `journeys` |
| Album descriptions | `description: "[PLACEHOLDER DESCRIPTION] ..."` |
| Photo titles, stories and dates | Each album's `photos` array / `createPhoto()` |
| Timeline order | Reorder album objects inside `journeys` |
| Facebook / Instagram / GitHub URLs | `footerMarkup()` — currently clearly marked placeholder links |
| Send-message behaviour | `initContactForm()` — connect a backend or email service here |

Every year is intentionally `"YEAR"`, and every photo date is intentionally `"DATE"`. **No travel years or dates were invented.** After adding real years, reorder the `journeys` object so the timeline is chronological.

Photo captions beginning with `[PLACEHOLDER STORY]` are writing prompts, not claims about your real memories. Replace them with your own personal, specific notes.

---

## 4. Image placeholders

Each album has a **different** local thumbnail path and four local photo paths:

```text
images/<place>/thumbnail.jpg
images/<place>/photo1.jpg
images/<place>/photo2.jpg
images/<place>/photo3.jpg
images/<place>/photo4.jpg
```

Profile images:

```text
images/profile/profile.jpg       # Hero portrait
images/profile/about.jpg         # About portrait
images/profile/hero-bg.jpg       # Optional hero background
images/profile/philosophy-bg.jpg # Optional quote background
```

### Important Akhilpur requirement

The Akhilpur album thumbnail is deliberately:

```text
images/akhilpur/patenga-beach.jpg
```

This path is set in `js/script.js` and documented with the exact comment `<!-- Replace this with the Patenga Beach thumbnail image -->` in `index.html` and `albums.html`. It is not the same thumbnail as any other album.

### What you will see before adding your JPGs

1. The browser first tries the exact local JPG path.
2. If it is missing, a distinct, freely usable Pexels travel photo is shown as a temporary placeholder.
3. If there is no internet, JavaScript generates a dark cinematic SVG labelled **LOCAL PHOTO PLACEHOLDER** with the destination name.

No personal photographs are bundled. Replace the local files whenever you are ready; the paths are the source of truth.

Recommended sizes: portraits around `900 × 1200px`, thumbnails around `1200 × 900px`, and slideshow photos around `1600 × 1000px` or larger. Use compressed JPG/WebP files for performance.

---

## 5. How the album system works

A journey card links to a URL such as:

```text
album.php?place=cox-bazar
```

`album.php` maps the legacy `place` slug to an album name and renders that album's photographs from the SQLite `gallery_photos` table. The admin studio links directly with `album.php?album=Cox%27s+Bazar`. The shared `js/script.js` still supplies the journey cards, thumbnails and slideshow chrome; it hydrates the cinematic slideshow with the live database photographs.

Photographs are added, grouped into albums and deleted from **Admin → Gallery / Albums**. No HTML edits or extra album files are required.

The static `journeys` objects in `js/script.js` still describe the seven editorial cards and their thumbnail artwork. Each photo object has this shape:

```javascript
{
    image: "images/cox-bazar/photo1.jpg",
    title: "PHOTO TITLE",
    story: "PHOTO STORY",
    date: "DATE",
    location: "Cox's Bazar"
}
```

The `createPhoto()` helper creates these objects consistently. Edit the `journeys` object to change thumbnails, years, descriptions, photo paths or stories.

### Slideshow controls

- Automatic play with smooth crossfades and slow Ken Burns movement
- Previous / Next buttons
- Play / Pause
- `01 / 04` frame counter
- Fullscreen button
- Clickable thumbnail strip
- Clickable story list
- Keyboard: `←` / `→` navigate, `Space` play/pause, `F` fullscreen
- Autoplay pauses while the browser tab is hidden

---

## 6. Gallery

`gallery.php` builds an editorial CSS-columns masonry from every row in `gallery_photos`. Each upload gets an album name (free text), one optional caption for the batch and a randomly named file in `/uploads/gallery`. Use the album filters, then click any image to open the fullscreen lightbox. The lightbox shows the album, caption, date and counter, and supports `←`, `→` and `Escape`; it navigates only through the currently filtered photographs.

Uploading or deleting a photograph in **Admin → Gallery / Albums** changes `gallery.php` and `album.php` immediately. Seeded starter photographs point at the existing `/images` folders and are never deleted from disk by the panel.

---

## 7. Contact form

The contact form is a **frontend-only demonstration** in the static site. It validates fields in the browser and then clearly says that no message was sent.

A real, working backend ships with this repository: `api/contact.php` stores messages in SQLite and the admin panel reads them. The drop-in replacement for `initContactForm()` in `js/script.js`, including CSRF handling and the honeypot field, is in [`ADMIN-PANEL.md`](ADMIN-PANEL.md#contact-form--sqlite).

---

## 8. Features included

- Minimal cinematic loading screen with a guaranteed timeout fallback
- Sticky/shrinking responsive navbar and manual Vanilla JS mobile toggle
- Page-load text reveals, scroll reveals, image reveals, card hover motion
- Subtle parallax, floating profile image, pointer tilt, light sweep and particles
- Seven distinct editorial journey cards
- Full-screen destination slideshow with personal story text
- Editable placeholder travel timeline
- Dark parallax travel-philosophy quote
- Learner-framed skill cards with **no exaggerated percentages**
- Filterable masonry gallery and accessible lightbox
- Optional desktop-only custom cursor; automatically disabled on touch/reduced motion
- Lazy loading, image resolution cache, reusable CSS and data-driven rendering
- Semantic landmarks, alt text, visible focus states, keyboard controls and `prefers-reduced-motion` support

---

## 9. Quick check list

- [ ] Home → About → Journeys → Gallery → Skills → Contact all open
- [ ] Mobile navbar opens, closes and follows a link
- [ ] Each journey card opens its matching `album.php?place=...`
- [ ] Slideshow changes photo, title, story, date, counter and active thumbnail
- [ ] Play/pause, arrows, fullscreen and keyboard shortcuts work
- [ ] Gallery filters change the visible photographs and counter
- [ ] Lightbox opens, navigates and closes with `Escape`
- [ ] Admin uploads appear in `gallery.php` / `album.php`; deleting removes the photo and file
- [ ] Contact form shows the frontend-only notice
- [ ] No horizontal scrolling at desktop, tablet or mobile widths

---

## 10. Placeholder photo credit

Optional temporary travel/portrait placeholders are loaded from [Pexels](https://www.pexels.com/) under the Pexels License. They are not bundled into this project and are replaced automatically by your local JPG files. The offline SVG fallbacks are generated by this project's JavaScript.

---

## 11. Admin panel (optional, PHP 8 + SQLite)

The repository also contains a complete, secure admin console for the archive:

- Session login with `password_hash()` / `password_verify()`, CSRF tokens and brute-force lockout
- Secure username and password changes with current-password confirmation
- Dashboard totals for trips, inquiries and gallery photographs
- Full CRUD for trips, including validated image uploads
- A Gallery / Albums manager with multi-file uploads, album filters, search and file-deleting deletes
- An inquiry inbox fed by the public contact form
- A public JSON feed (`api/trips.php`), a contact endpoint (`api/contact.php`) and a server-rendered demo (`journeys.php`)
- SQLite database created automatically by `database/setup.php`

**Quick start**

```bash
php database/setup.php     # create database/portfolio.sqlite + tables + seed data
php -S localhost:8000      # then open http://localhost:8000/admin/login.php
```

Requirements, folder structure, the exact `index.php` / JavaScript integration snippets, deployment notes (Apache + Nginx), backups, troubleshooting and the full security checklist are documented in **[`ADMIN-PANEL.md`](ADMIN-PANEL.md)**.

> The plain-text admin password is never committed. Use the credentials provided with the build, then change the password under **Account & Security**. Run `php database/setup.php --new-password` if you ever lose it.
