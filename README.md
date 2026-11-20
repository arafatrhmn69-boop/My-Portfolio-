# THE JOURNEY ARCHIVE

A premium, cinematic personal travel portfolio for a **student + aspiring web developer + traveller + explorer**.

> Every Place Has a Story. Every Journey Becomes a Memory.

Built with **HTML5, CSS3, Bootstrap 5 and Vanilla JavaScript only**. No React, Next.js, Vue, Angular, Tailwind, TypeScript or build tools. No backend is required.

---

## 1. Run it in VS Code

1. Install the **Live Server** extension in VS Code.
2. Open this `travel-portfolio/` folder in VS Code.
3. Right-click `index.html` → **Open with Live Server**.
4. Use the navigation to visit About, Journeys, an Album and the Gallery.

All internal links use relative paths. The Bootstrap CSS and optional remote placeholder photos need an internet connection; the custom CSS layouts and generated SVG photo fallbacks still work offline.

---

## 2. Project structure

```text
travel-portfolio/
├── index.html          # Homepage: hero, journeys, quote, timeline, about, skills, featured gallery, contact
├── about.html          # Longer personal story
├── albums.html         # All seven editorial album cards
├── album.html          # ONE reusable slideshow page: album.html?place=cox-bazar
├── gallery.html        # Filterable masonry gallery and fullscreen lightbox
│
├── css/
│   └── style.css       # All shared cinematic styling and responsive rules
├── js/
│   └── script.js       # Album data, shared nav/footer, cards, slideshow, gallery, animations
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

A card links to a URL such as:

```text
album.html?place=cox-bazar
```

`album.html` reads the `place` query parameter and loads that destination from the `journeys` object. Adding or editing an album does not require another HTML file.

Each photo object has this shape:

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

`gallery.html` builds an editorial CSS-columns masonry from all album photos. Use the destination filters, then click any image to open the fullscreen lightbox. The lightbox shows the place, title, story, date and counter, and supports `←`, `→` and `Escape`. The lightbox navigates only through the currently filtered photographs.

---

## 7. Contact form

The contact form is a **frontend-only demonstration**. It validates fields in the browser and then clearly says that no message was sent.

To make it real, connect a backend or an email service inside `initContactForm()` in `js/script.js`. There is no fake success message.

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
- [ ] Each journey card opens its matching `album.html?place=...`
- [ ] Slideshow changes photo, title, story, date, counter and active thumbnail
- [ ] Play/pause, arrows, fullscreen and keyboard shortcuts work
- [ ] Gallery filters change the visible photographs and counter
- [ ] Lightbox opens, navigates and closes with `Escape`
- [ ] Contact form shows the frontend-only notice
- [ ] No horizontal scrolling at desktop, tablet or mobile widths

---

## 10. Placeholder photo credit

Optional temporary travel/portrait placeholders are loaded from [Pexels](https://www.pexels.com/) under the Pexels License. They are not bundled into this project and are replaced automatically by your local JPG files. The offline SVG fallbacks are generated by this project's JavaScript.
