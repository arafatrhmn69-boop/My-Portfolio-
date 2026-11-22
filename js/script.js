/* ============================================================
   THE JOURNEY ARCHIVE — script.js
   Purpose: shared data and behaviour for every HTML page.

   BEGINNER EDIT GUIDE
   1. Change Arafat Rahman in SITE.name below.
   2. Change every year: "YEAR" in the journeys object.
   3. Replace descriptions and photo titles/stories in journeys.
   4. Put your own JPG photos at the exact paths listed in journeys.
   5. Dates are "DATE" placeholders inside createPhoto().
   ============================================================ */

(function () {
    "use strict";

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const $ = (selector, scope = document) => scope.querySelector(selector);
    const $$ = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    /* Minimal loading screen controller. The fallback timer guarantees
       that a slow/missing placeholder image can never trap the visitor. */
    window.hideLoader = function () {
        const loader = document.getElementById("loader");
        if (loader) loader.classList.add("is-hidden");
        document.body.classList.add("is-loaded");
    };
    window.addEventListener("load", () => window.setTimeout(window.hideLoader, 350));
    window.setTimeout(window.hideLoader, 1800);

    /* --------------------------------------------------------
       SITE CONFIGURATION — edit your name here.
       -------------------------------------------------------- */
    const SITE = {
        brand: "THE JOURNEY ARCHIVE",
        name: "Arafat Rahman",
        concept: "Every Place Has a Story. Every Journey Becomes a Memory.",
        email: "arafat.rhmn.69@gmail.com"
    };

    /* Optional remote placeholders from Pexels.
       They appear ONLY when a local JPG is missing, so the site looks
       complete before your photographs are added. They are not bundled
       into the project and are not personal photos. */
    const pexels = (id, w = 1600, h = 1000) =>
        `https://images.pexels.com/photos/${id}/pexels-photo-${id}.jpeg?auto=compress&cs=tinysrgb&fit=crop&w=${w}&h=${h}`;

    const REMOTE = {
        profile: pexels(39876323, 900, 1200),
        about: pexels(39876323, 900, 1200),
        hero: pexels(34657697, 2000, 1200),
        philosophy: pexels(40718140, 2000, 1200),
        albums: {
            "cox-bazar": [38183292, 34984159, 35550762, 31521011].map(id => pexels(id)),
            "teknaf": [40719879, 40718140, 40719129, 40719877].map(id => pexels(id)),
            "bandarban": [37818818, 40144339, 28672619, 16710718].map(id => pexels(id)),
            "sylhet": [16246007, 19102359, 18940677, 30390430].map(id => pexels(id)),
            "sitakunda": [35550992, 39164235, 34657697, 17219042].map(id => pexels(id)),
            "akhilpur": [35044472, 35044471, 35550762, 38183292].map(id => pexels(id)),
            "guliakhali": [6033048, 27021799, 38309908, 35044472].map(id => pexels(id))
        }
    };

    const PALETTES = {
        default: ["#101a1d", "#334b4c", "#c8a66c"],
        "cox-bazar": ["#121a22", "#425b68", "#d9a870"],
        teknaf: ["#101c1b", "#31544b", "#d4ae73"],
        bandarban: ["#0d1714", "#31533d", "#b8a877"],
        sylhet: ["#0c1712", "#365b3a", "#d1bb7a"],
        sitakunda: ["#101719", "#3b5654", "#c2a87d"],
        akhilpur: ["#141a22", "#4d5b68", "#d5a36c"],
        guliakhali: ["#0e1a1c", "#3c5d5a", "#cbb17d"]
    };

    /* Helper that keeps every photo object consistent and easy to edit.
       DATE is an intentional placeholder — replace it after adding photos. */
    function createPhoto(slug, number, location, title, story) {
        return {
            image: `images/${slug}/photo${number}.jpg`,
            title: title,
            story: story,
            date: "DATE",
            location: location
        };
    }

    /* --------------------------------------------------------
       TRAVEL ALBUM DATA
       Replace YEAR, DESCRIPTION, DATE, PHOTO TITLE and PHOTO STORY.
       Reorder the album objects below after adding your real years;
       the timeline follows this order.
       -------------------------------------------------------- */
    const journeys = {
        "cox-bazar": {
            title: "Cox's Bazar",
            year: "2025/2026", // EDIT: replace YEAR with your real travel year
            region: "COAST / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] The long coastline, changing light and an evening that felt larger than the frame.",
            thumbnail: "images/cox-bazar/thumbnail.jpg",
            photos: [
                createPhoto("cox-bazar", 1, "Cox's Bazar", "PHOTO TITLE — The Long Shore", "[PLACEHOLDER STORY] The sea kept its own rhythm that evening; write here what you remember from this frame."),
                createPhoto("cox-bazar", 2, "Cox's Bazar", "PHOTO TITLE — Last Light", "[PLACEHOLDER STORY] I stopped walking when the horizon turned quiet. Replace this line with your own moment."),
                createPhoto("cox-bazar", 3, "Cox's Bazar", "PHOTO TITLE — Feet in the Sand", "[PLACEHOLDER STORY] A small, unplanned pause between one stretch of beach and the next."),
                createPhoto("cox-bazar", 4, "Cox's Bazar", "PHOTO TITLE — Before Dark", "[PLACEHOLDER STORY] The colour changed slowly, and this photograph held the last few minutes of it.")
            ]
        },
        "teknaf": {
            title: "Teknaf",
            year: "2026", // EDIT: replace YEAR with your real travel year
            region: "SOUTHERN EDGE / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] Waterways, quiet horizons and the feeling of standing at the edge of the map.",
            thumbnail: "images/teknaf/thumbnail.jpg",
            photos: [
                createPhoto("teknaf", 1, "Teknaf", "PHOTO TITLE — The Water Road", "[PLACEHOLDER STORY] Boats moved without hurry here; describe the journey that led to this photograph."),
                createPhoto("teknaf", 2, "Teknaf", "PHOTO TITLE — River Light", "[PLACEHOLDER STORY] The afternoon felt wide open. Add the detail you still remember."),
                createPhoto("teknaf", 3, "Teknaf", "PHOTO TITLE — Green Channels", "[PLACEHOLDER STORY] A view that made the route feel like part of the destination."),
                createPhoto("teknaf", 4, "Teknaf", "PHOTO TITLE — Edge of Day", "[PLACEHOLDER STORY] I stayed a little longer than planned. Write your own version of this pause.")
            ]
        },
        "bandarban": {
            title: "Bandarban",
            year: "2026", // EDIT: replace YEAR with your real travel year
            region: "HILLS / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] Layered hills, shifting mist and paths that disappear into green silence.",
            thumbnail: "images/bandarban/thumbnail.jpg",
            photos: [
                createPhoto("bandarban", 1, "Bandarban", "PHOTO TITLE — Into the Hills", "[PLACEHOLDER STORY] The path was quieter than the view suggested. Replace this with what you noticed first."),
                createPhoto("bandarban", 2, "Bandarban", "PHOTO TITLE — Morning Mist", "[PLACEHOLDER STORY] Clouds rested between the ridges, and the morning seemed to wait with them."),
                createPhoto("bandarban", 3, "Bandarban", "PHOTO TITLE — A Green Distance", "[PLACEHOLDER STORY] One of those frames where distance felt impossible to measure."),
                createPhoto("bandarban", 4, "Bandarban", "PHOTO TITLE — The Climb", "[PLACEHOLDER STORY] A small moment from the walk, kept because the feeling mattered more than the view.")
            ]
        },
        "sylhet": {
            title: "Sylhet",
            year: "2026", // EDIT: replace YEAR with your real travel year
            region: "GREEN NORTH-EAST / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] Tea gardens, soft rain and slow roads lined with deep green.",
            thumbnail: "images/sylhet/thumbnail.jpg",
            photos: [
                createPhoto("sylhet", 1, "Sylhet", "PHOTO TITLE — After Rain", "[PLACEHOLDER STORY] Water clung to the leaves long after the rain had stopped. Add your own memory here."),
                createPhoto("sylhet", 2, "Sylhet", "PHOTO TITLE — Garden Path", "[PLACEHOLDER STORY] The route curved out of sight, which made walking it feel like a small discovery."),
                createPhoto("sylhet", 3, "Sylhet", "PHOTO TITLE — Tree Tunnel", "[PLACEHOLDER STORY] The light kept changing between the branches; this frame caught one of those changes."),
                createPhoto("sylhet", 4, "Sylhet", "PHOTO TITLE — Open Green", "[PLACEHOLDER STORY] A quiet stop before continuing. Replace this note with the story behind your photograph.")
            ]
        },
        "sitakunda": {
            title: "Sitakunda",
            year: "2025", // EDIT: replace YEAR with your real travel year
            region: "HILL TRAILS / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] Forest trails, uneven ground and the hidden quiet of the hills.",
            thumbnail: "images/sitakunda/thumbnail.jpg",
            photos: [
                createPhoto("sitakunda", 1, "Sitakunda", "PHOTO TITLE — The Trail", "[PLACEHOLDER STORY] The climb began without ceremony. Describe the first part of your walk here."),
                createPhoto("sitakunda", 2, "Sitakunda", "PHOTO TITLE — Under the Canopy", "[PLACEHOLDER STORY] The forest softened every sound; this is the frame I would attach to that feeling."),
                createPhoto("sitakunda", 3, "Sitakunda", "PHOTO TITLE — Hidden Water", "[PLACEHOLDER STORY] We found this only after following the sound. Replace with your own discovery."),
                createPhoto("sitakunda", 4, "Sitakunda", "PHOTO TITLE — The Way Back", "[PLACEHOLDER STORY] Returning felt different from arriving. Add what changed along the way.")
            ]
        },
        "akhilpur": {
            title: "Akhilpur",
            year: "2024", // EDIT: replace YEAR with your real travel year
            region: "COASTAL MEMORY / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] A coastal chapter represented by the light and open shore of Patenga Beach.",
            /* IMPORTANT: Akhilpur's thumbnail MUST be a Patenga Beach photo. */
            thumbnail: "images/akhilpur/patenga-beach.jpg",
            photos: [
                createPhoto("akhilpur", 1, "Akhilpur / Patenga Beach", "PHOTO TITLE — Patenga Shore", "[PLACEHOLDER STORY] The shoreline was busy and calm at the same time. Write the moment you remember here."),
                createPhoto("akhilpur", 2, "Akhilpur / Patenga Beach", "PHOTO TITLE — Nets at Sunset", "[PLACEHOLDER STORY] A working evening by the water, held in one frame. Replace with your own note."),
                createPhoto("akhilpur", 3, "Akhilpur / Patenga Beach", "PHOTO TITLE — Sea Air", "[PLACEHOLDER STORY] The kind of place where the light makes you stop without deciding to."),
                createPhoto("akhilpur", 4, "Akhilpur / Patenga Beach", "PHOTO TITLE — After the Tide", "[PLACEHOLDER STORY] The day left its marks in the sand. Add the personal story behind this photograph.")
            ]
        },
        "guliakhali": {
            title: "Guliakhali",
            year: "2024", // EDIT: replace YEAR with your real travel year
            region: "COAST / BANGLADESH",
            description: "[PLACEHOLDER DESCRIPTION] Open water, shifting grass and a quieter side of the coast.",
            thumbnail: "images/guliakhali/thumbnail.jpg",
            photos: [
                createPhoto("guliakhali", 1, "Guliakhali", "PHOTO TITLE — Green Water", "[PLACEHOLDER STORY] The colour of this place is what stayed with me first. Add your own impression here."),
                createPhoto("guliakhali", 2, "Guliakhali", "PHOTO TITLE — Shoreline Birds", "[PLACEHOLDER STORY] A brief movement at the edge of the water, almost missed."),
                createPhoto("guliakhali", 3, "Guliakhali", "PHOTO TITLE — Walking the Coast", "[PLACEHOLDER STORY] The walk had no fixed destination. Describe where this frame was taken."),
                createPhoto("guliakhali", 4, "Guliakhali", "PHOTO TITLE — Sea and Sky", "[PLACEHOLDER STORY] A final look before leaving. Replace this with the story you want to keep.")
            ]
        }
    };

    /* Expose the editable data for learning/debugging in the browser console. */
    window.JOURNEYS = journeys;
    window.SITE_CONFIG = SITE;

    /* --------------------------------------------------------
       SMALL UTILITIES
       -------------------------------------------------------- */
    const escapeMap = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
    function esc(value) {
        return String(value == null ? "" : value).replace(/[&<>"']/g, character => escapeMap[character]);
    }
    function pad(number) { return String(number).padStart(2, "0"); }
    function setText(selector, value) {
        const element = $(selector);
        if (element) element.textContent = value;
    }
    function remoteFor(slug, index) {
        const list = REMOTE.albums[slug] || [];
        return list.length ? list[index % list.length] : "";
    }
    function totalPhotos() {
        return Object.values(journeys).reduce((sum, album) => sum + album.photos.length, 0);
    }

    /* Offline SVG fallback: a distinct cinematic landscape for each place. */
    function svgPlaceholder(label, paletteKey) {
        const palette = PALETTES[paletteKey] || PALETTES.default;
        const safeLabel = esc(String(label).toUpperCase());
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800">
            <defs>
                <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop stop-color="${palette[0]}"/><stop offset="1" stop-color="${palette[1]}"/></linearGradient>
                <linearGradient id="ground" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#090d0e"/><stop offset="1" stop-color="${palette[1]}"/></linearGradient>
                <radialGradient id="sun"><stop stop-color="${palette[2]}" stop-opacity=".9"/><stop offset="1" stop-color="${palette[2]}" stop-opacity="0"/></radialGradient>
                <filter id="grain"><feTurbulence type="fractalNoise" baseFrequency=".75" numOctaves="2"/></filter>
            </defs>
            <rect width="1200" height="800" fill="url(#sky)"/>
            <circle cx="875" cy="250" r="240" fill="url(#sun)" opacity=".45"/>
            <circle cx="875" cy="250" r="54" fill="${palette[2]}" opacity=".55"/>
            <path d="M0 520 L220 330 L390 490 L580 275 L790 510 L970 370 L1200 530 L1200 800 L0 800Z" fill="#0b1112" opacity=".7"/>
            <path d="M0 610 Q220 540 410 610 T820 600 T1200 590 L1200 800 L0 800Z" fill="url(#ground)" opacity=".92"/>
            <path d="M0 680 Q300 630 600 680 T1200 660" fill="none" stroke="${palette[2]}" stroke-opacity=".22" stroke-width="2"/>
            <rect width="1200" height="800" filter="url(#grain)" opacity=".08"/>
            <rect x="34" y="34" width="1132" height="732" fill="none" stroke="#ffffff" stroke-opacity=".16"/>
            <text x="70" y="110" fill="#e6d6b8" font-family="Georgia,serif" font-size="24" letter-spacing="8">THE JOURNEY ARCHIVE</text>
            <text x="70" y="690" fill="#f2eee6" font-family="Georgia,serif" font-size="62" letter-spacing="3">${safeLabel}</text>
            <text x="72" y="730" fill="#b9b7ad" font-family="Arial,sans-serif" font-size="16" letter-spacing="5">LOCAL PHOTO PLACEHOLDER</text>
        </svg>`;
        return "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg);
    }

    const imageCache = new Map();
    function loadImage(url) {
        return new Promise((resolve, reject) => {
            const image = new Image();
            image.onload = () => resolve(url);
            image.onerror = () => reject(new Error("Image unavailable"));
            image.src = url;
        });
    }
    function resolveImage(localSrc, remoteSrc, label, paletteKey) {
        const key = `${localSrc}|${remoteSrc}|${paletteKey}`;
        if (imageCache.has(key)) return imageCache.get(key);

        const safeLocal = localSrc || "";
        const fallbackSvg = svgPlaceholder(label || SITE.brand, paletteKey);
        const promise = loadImage(safeLocal)
            .catch(() => remoteSrc ? loadImage(remoteSrc) : Promise.resolve(fallbackSvg))
            .catch(() => fallbackSvg);
        imageCache.set(key, promise);
        return promise;
    }
    function hydrateImages(scope = document) {
        $$("img[data-local]", scope).forEach(image => {
            if (image.dataset.hydrated) return;
            image.dataset.hydrated = "true";
            image.classList.add("photo-loading");
            const local = image.dataset.local || image.getAttribute("src") || "";
            const remote = image.dataset.remote || "";
            const label = image.dataset.label || image.alt || SITE.brand;
            const palette = image.dataset.palette || "default";
            resolveImage(local, remote, label, palette).then(src => {
                image.src = src;
                image.classList.add("is-loaded");
            });
        });
    }

    /* --------------------------------------------------------
       SHARED NAVBAR + FOOTER (injected on every page)
       -------------------------------------------------------- */
    function navigationMarkup() {
        const links = [
            ["home", "Home", "index.html"],
            ["about", "About", "about.html"],
            ["journeys", "Journeys", "albums.html"],
            ["gallery", "Gallery", "gallery.php"],
            ["skills", "Skills", "index.html#skills"],
            ["contact", "Contact", "index.html#contact"]
        ];
        const active = document.body.dataset.page === "album" ? "journeys" : document.body.dataset.page;
        return `
        <nav class="navbar navbar-expand-lg site-nav fixed-top" aria-label="Main navigation">
            <div class="container-xxl">
                <a class="navbar-brand brand" href="index.html" aria-label="${esc(SITE.brand)} — home">
                    <span class="brand-mark" aria-hidden="true"></span><span>${esc(SITE.brand)}</span>
                </a>
                <button class="navbar-toggler" type="button" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="toggler-line"></span><span class="toggler-line"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        ${links.map(([id, label, href]) => `
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom${active === id ? " active" : ""}" href="${href}"${active === id ? ' aria-current="page"' : ""}>${label}</a>
                        </li>`).join("")}
                        <li class="nav-item ms-lg-3">
                            <button id="theme-toggle" type="button" class="theme-toggle">☀️ Light</button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>`;
    }

    function footerMarkup() {
        return `
        <footer class="site-footer">
            <div class="container-xxl">
                <div class="footer-grid">
                    <div>
                        <div class="footer-brand">${esc(SITE.brand)}</div>
                        <p class="footer-desc">A personal archive of places, moments and memories.</p>
                        <div class="social-row" aria-label="Social links">
                            <!-- PLACEHOLDER SOCIAL LINKS: replace href values when ready. -->
                            <a class="social-link" href="#" data-placeholder-link data-placeholder-message="Replace this Facebook URL in js/script.js.">Facebook</a>
                            <a class="social-link" href="#" data-placeholder-link data-placeholder-message="Replace this Instagram URL in js/script.js.">Instagram</a>
                            <a class="social-link" href="#" data-placeholder-link data-placeholder-message="Replace this GitHub URL in js/script.js.">GitHub</a>
                        </div>
                    </div>
                    <div>
                        <p class="footer-heading">Explore</p>
                        <ul class="footer-links">
                            <li><a href="about.html">About</a></li>
                            <li><a href="albums.html">Journeys</a></li>
                            <li><a href="gallery.php">Gallery</a></li>
                            <li><a href="index.html#contact">Contact</a></li>
                        </ul>
                    </div>
                    <div>
                        <p class="footer-heading">Field Notes</p>
                        <p class="footer-desc">${esc(SITE.concept)}</p>
                        <p class="footer-desc">Built while learning HTML, CSS, Bootstrap and JavaScript.</p>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span>&copy; <span data-current-year>${new Date().getFullYear()}</span> <span data-site-name>${esc(SITE.name)}</span>. All Rights Reserved.</span>
                    <span>Student &middot; Aspiring Developer &middot; Traveller</span>
                </div>
            </div>
        </footer>`;
    }

    function injectChrome() {
        const navMount = $("#site-nav");
        const footerMount = $("#site-footer");
        if (navMount) navMount.innerHTML = navigationMarkup();
        if (footerMount) footerMount.innerHTML = footerMarkup();
        $$("[data-site-name]").forEach(element => { element.textContent = SITE.name; });
        $$("[data-site-email]").forEach(element => { element.textContent = SITE.email; });
        $$("[data-current-year]").forEach(element => { element.textContent = new Date().getFullYear(); });
        $$("[data-total-journeys]").forEach(element => { element.textContent = pad(Object.keys(journeys).length); });
        $$("[data-total-photos]").forEach(element => { element.textContent = pad(totalPhotos()); });
    }

    /* --------------------------------------------------------
       DATA-DRIVEN RENDERING
       -------------------------------------------------------- */
    function journeyCardMarkup(slug, index) {
        const album = journeys[slug];
        const remote = remoteFor(slug, 0);
        return `
        <article class="journey-card-wrap" data-reveal style="--reveal-delay:${(index % 3) * 0.08}s">
            <a class="journey-card" href="album.php?place=${esc(slug)}" aria-label="Explore ${esc(album.title)} journey">
                <img src="${esc(album.thumbnail)}" data-local="${esc(album.thumbnail)}" data-remote="${esc(remote)}"
                     data-label="${esc(album.title)}" data-palette="${esc(slug)}"
                     alt="Travel photograph representing ${esc(album.title)}" loading="${index < 2 ? "eager" : "lazy"}" decoding="async">
                <div class="card-top"><span class="card-number">JOURNEY ${pad(index + 1)}</span><span class="travel-year">${esc(album.year)}</span></div>
                <div class="card-bottom">
                    <span class="card-region">${esc(album.region)}</span>
                    <h3>${esc(album.title)}</h3>
                    <p class="card-description">${esc(album.description)}</p>
                    <div class="card-meta-row">
                        <span>${pad(album.photos.length)} Memories</span>
                        <span class="card-cta">Explore Journey <span class="arrow" aria-hidden="true">&#8599;</span></span>
                    </div>
                </div>
            </a>
        </article>`;
    }

    function renderJourneyGrid(selector) {
        const container = $(selector);
        if (!container) return;
        container.innerHTML = Object.keys(journeys).map(journeyCardMarkup).join("");
        hydrateImages(container);
    }

    function renderTimeline() {
        const container = $("#timeline-list");
        if (!container) return;
        container.innerHTML = Object.entries(journeys).map(([slug, album], index) => `
            <article class="timeline-item" data-reveal style="--reveal-delay:${(index % 2) * 0.1}s">
                <div class="timeline-content">
                    <span class="timeline-year travel-year">${esc(album.year)}</span>
                    <span class="timeline-edit">Editable year &middot; ${pad(index + 1)}</span>
                    <h3>${esc(album.title)}</h3>
                    <p>${esc(album.region)} &middot; ${pad(album.photos.length)} frames</p>
                </div>
            </article>`).join("");
    }

    function renderFeatured() {
        const container = $("#featured-grid");
        if (!container) return;
        const entries = Object.entries(journeys).slice(0, 6).map(([slug, album], index) => ({
            slug: slug, album: album, photo: album.photos[index % album.photos.length], index: index
        }));
        container.innerHTML = entries.map((entry, index) => `
            <a class="featured-item" href="gallery.php#gallery-collection" aria-label="View ${esc(entry.album.title)} in the gallery" data-reveal style="--reveal-delay:${(index % 3) * 0.08}s">
                <img src="${esc(entry.photo.image)}" data-local="${esc(entry.photo.image)}" data-remote="${esc(remoteFor(entry.slug, index))}"
                     data-label="${esc(entry.album.title)}" data-palette="${esc(entry.slug)}"
                     alt="${esc(entry.photo.title)} — ${esc(entry.album.title)}" loading="lazy" decoding="async">
                <span class="featured-caption">${esc(entry.album.title)}<strong>${esc(entry.photo.title.replace("PHOTO TITLE — ", ""))}</strong></span>
            </a>`).join("");
        hydrateImages(container);
    }

    function miniJourneyMarkup(slug, index) {
        const album = journeys[slug];
        return `
        <a class="mini-journey" href="album.php?place=${esc(slug)}" data-reveal style="--reveal-delay:${index * 0.08}s" aria-label="Continue to ${esc(album.title)}">
            <img src="${esc(album.thumbnail)}" data-local="${esc(album.thumbnail)}" data-remote="${esc(remoteFor(slug, 0))}"
                 data-label="${esc(album.title)}" data-palette="${esc(slug)}"
                 alt="Thumbnail for ${esc(album.title)}" loading="lazy" decoding="async">
            <div><span>${esc(album.year)} &middot; ${pad(album.photos.length)} memories</span><h3>${esc(album.title)}</h3></div>
        </a>`;
    }

    function renderMiniJourneys(currentSlug) {
        const container = $("#other-journeys");
        if (!container) return;
        const keys = Object.keys(journeys).filter(key => key !== currentSlug).slice(0, 3);
        container.innerHTML = keys.map(miniJourneyMarkup).join("");
        hydrateImages(container);
    }

    /* --------------------------------------------------------
       NAVIGATION, LOADER, SCROLL REVEALS, PARALLAX, CURSOR
       -------------------------------------------------------- */
    function initNavbar() {
        const toggler = $(".navbar-toggler");
        const collapse = $("#mainNavbar");
        if (!toggler || !collapse) return;
        toggler.addEventListener("click", () => {
            const isOpen = collapse.classList.toggle("show");
            toggler.setAttribute("aria-expanded", String(isOpen));
        });
        $$("a", collapse).forEach(link => link.addEventListener("click", () => {
            collapse.classList.remove("show");
            toggler.setAttribute("aria-expanded", "false");
        }));
        document.addEventListener("keydown", event => {
            if (event.key === "Escape" && collapse.classList.contains("show")) {
                collapse.classList.remove("show");
                toggler.setAttribute("aria-expanded", "false");
                toggler.focus();
            }
        });
    }

    function initTheme() {
        const toggle = document.getElementById("theme-toggle");
        const root = document.documentElement;
        const themeColor = document.querySelector('meta[name="theme-color"]');

        function updateThemeUI() {
            const light = root.getAttribute("data-theme") === "light";
            if (toggle) {
                toggle.textContent = light ? "🌙 Dark" : "☀️ Light";
                toggle.setAttribute("aria-label", light ? "Switch to dark theme" : "Switch to light theme");
            }
            if (themeColor) themeColor.setAttribute("content", light ? "#f8f7f4" : "#07090a");
        }

        updateThemeUI();
        toggle?.addEventListener("click", () => {
            const theme = root.getAttribute("data-theme") === "light" ? "dark" : "light";
            root.setAttribute("data-theme", theme);
            try { localStorage.setItem("theme", theme); } catch (error) { /* Theme still works for this page. */ }
            updateThemeUI();
        });
    }

    function initLoader() {
        if (typeof window.hideLoader === "function") {
            window.setTimeout(window.hideLoader, 1000);
        }
    }

    let revealObserver = null;
    function initReveal() {
        if (reducedMotion || !("IntersectionObserver" in window)) {
            $$("[data-reveal]").forEach(element => element.classList.add("is-visible"));
            return;
        }
        revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: "0px 0px -45px 0px" });
        refreshReveal();
    }
    function refreshReveal(scope = document) {
        const items = $$("[data-reveal]", scope);
        if (!revealObserver) {
            items.forEach(element => element.classList.add("is-visible"));
            return;
        }
        items.forEach(element => {
            if (!element.dataset.revealBound) {
                element.dataset.revealBound = "true";
                revealObserver.observe(element);
            }
        });
    }

    function initScrollEffects() {
        const nav = $(".site-nav");
        const parallaxItems = $$("[data-parallax]");
        let ticking = false;
        function update() {
            if (nav) nav.classList.toggle("is-scrolled", window.scrollY > 35);
            if (!reducedMotion) {
                const middle = window.innerHeight / 2;
                parallaxItems.forEach(element => {
                    const rect = element.getBoundingClientRect();
                    if (rect.bottom > 0 && rect.top < window.innerHeight) {
                        const speed = parseFloat(element.dataset.parallax) || 0.1;
                        const offset = (rect.top + rect.height / 2 - middle) * speed;
                        element.style.setProperty("--parallax-y", `${offset.toFixed(1)}px`);
                    }
                });
            }
            ticking = false;
        }
        window.addEventListener("scroll", () => {
            if (!ticking) { window.requestAnimationFrame(update); ticking = true; }
        }, { passive: true });
        window.addEventListener("resize", update);
        update();
    }

    function initProfileTilt() {
        const stage = $(".profile-stage");
        const frame = $(".profile-image-wrap");
        if (!stage || !frame || !window.matchMedia("(pointer: fine)").matches || reducedMotion) return;
        stage.addEventListener("mousemove", event => {
            const rect = stage.getBoundingClientRect();
            const x = (event.clientX - rect.left) / rect.width - 0.5;
            const y = (event.clientY - rect.top) / rect.height - 0.5;
            frame.style.setProperty("--tilt-x", `${(-y * 5).toFixed(2)}deg`);
            frame.style.setProperty("--tilt-y", `${(x * 5).toFixed(2)}deg`);
        });
        stage.addEventListener("mouseleave", () => {
            frame.style.setProperty("--tilt-x", "0deg");
            frame.style.setProperty("--tilt-y", "0deg");
        });
    }

    function initCursor() {
        if (reducedMotion || !window.matchMedia("(pointer: fine)").matches) return;
        const dot = document.createElement("div");
        const ring = document.createElement("div");
        dot.className = "cursor-dot";
        ring.className = "cursor-ring";
        document.body.append(dot, ring);
        document.body.classList.add("has-custom-cursor");

        let mouseX = -100, mouseY = -100, ringX = -100, ringY = -100;
        document.addEventListener("mousemove", event => {
            mouseX = event.clientX; mouseY = event.clientY;
            dot.style.transform = `translate3d(${mouseX}px, ${mouseY}px, 0)`;
            dot.classList.add("is-visible");
            ring.classList.add("is-visible");
        });
        document.addEventListener("mouseover", event => {
            const interactive = event.target.closest("a, button, input, textarea, [role='button']");
            ring.classList.toggle("is-hover", Boolean(interactive));
        });
        document.addEventListener("mouseleave", () => {
            dot.classList.remove("is-visible"); ring.classList.remove("is-visible");
        });
        document.addEventListener("mouseenter", () => {
            dot.classList.add("is-visible"); ring.classList.add("is-visible");
        });
        function animateCursor() {
            ringX += (mouseX - ringX) * 0.16;
            ringY += (mouseY - ringY) * 0.16;
            ring.style.transform = `translate3d(${ringX}px, ${ringY}px, 0)`;
            window.requestAnimationFrame(animateCursor);
        }
        animateCursor();
    }

    function showToast(message) {
        const existing = $(".toast-note");
        if (existing) existing.remove();
        const toast = document.createElement("div");
        toast.className = "toast-note";
        toast.setAttribute("role", "status");
        toast.textContent = message;
        document.body.appendChild(toast);
        window.setTimeout(() => toast.remove(), 3200);
    }

    function initPlaceholderLinks() {
        document.addEventListener("click", event => {
            const link = event.target.closest("[data-placeholder-link]");
            if (!link) return;
            event.preventDefault();
            showToast(link.dataset.placeholderMessage || "Placeholder link — replace this URL when you are ready.");
        });
    }

    function initPageTransitions() {
        document.addEventListener("click", event => {
            const link = event.target.closest("a[href]");
            if (!link || event.defaultPrevented || link.dataset.placeholderLink !== undefined) return;
            if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            if (link.target && link.target.toLowerCase() !== "_self") return;
            let url;
            try { url = new URL(link.getAttribute("href"), window.location.href); }
            catch (error) { return; }
            if (url.origin !== window.location.origin) return;
            if (url.hash && url.pathname === window.location.pathname && url.search === window.location.search) return;
            event.preventDefault();
            document.body.classList.add("is-leaving");
            window.setTimeout(() => window.location.assign(url.href), reducedMotion ? 0 : 260);
        });
    }

    function initHashScroll() {
        if (!window.location.hash) return;
        window.setTimeout(() => {
            let id = "";
            try { id = decodeURIComponent(window.location.hash.slice(1)); }
            catch (error) { return; }
            const target = document.getElementById(id);
            if (target) target.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
        }, 700);
    }

    /* --------------------------------------------------------
       DATABASE-BACKED ALBUM DATA
       album.php prints this block from gallery_photos. Adding it to the
       journeys object lets the existing cinematic slideshow render the
       live archive without duplicating the slideshow logic.
       -------------------------------------------------------- */
    function hydrateDatabaseAlbum() {
        const data = document.getElementById("album-db-data");
        if (!data) return;

        const slug = data.dataset.albumSlug;
        const photos = $$("[data-photo]", data).map(node => ({
            title: node.dataset.title || "Untitled frame",
            story: node.dataset.story || "",
            date: node.dataset.date || "",
            image: node.dataset.image || "",
            location: node.dataset.location || data.dataset.albumTitle || ""
        }));

        if (!slug || !photos.length) return;

        journeys[slug] = {
            title: data.dataset.albumTitle || "Travel Album",
            year: data.dataset.albumYear || "",
            region: data.dataset.albumRegion || "Personal archive",
            description: data.dataset.albumDescription || "",
            thumbnail: photos[0].image,
            photos: photos
        };
    }

    /* --------------------------------------------------------
       CINEMATIC ALBUM SLIDESHOW
       -------------------------------------------------------- */
    function initSlideshow() {
        const stage = $("#album-stage");
        if (!stage) return;

        const params = new URLSearchParams(window.location.search);
        const slug = params.get("place") || stage.dataset.albumSlug || "cox-bazar";
        const album = journeys[slug];
        const notFound = $("#album-not-found");
        const experience = $("#album-experience");
        if (!album) {
            if (experience) experience.hidden = true;
            if (notFound) notFound.hidden = false;
            return;
        }

        const photos = album.photos;
        const DURATION = 5500;
        const layers = [$("#slide-layer-a"), $("#slide-layer-b")].filter(Boolean);
        const thumbs = $("#slide-thumbs");
        const storyList = $("#story-list");
        const storyPanel = $("#album-story-panel");
        const progress = $("#slide-progress");
        const toggleButton = $('[data-slide="toggle"]');
        const fullscreenButton = $('[data-slide="fullscreen"]');

        let current = 0;
        let activeLayer = 0;
        let playing = !reducedMotion;
        let timer = null;
        let metaTimer = null;
        let renderToken = 0;

        document.title = `${album.title} — ${SITE.brand}`;
        setText("#album-page-title", album.title);
        setText("#album-title-small", album.title);
        setText("#album-description", album.description);
        setText("#album-year", album.year);
        setText("#album-region", album.region);
        setText("#album-count", `${pad(photos.length)} frames`);
        setText("#album-slide-total", pad(photos.length));
        setText("#slide-total", pad(photos.length));
        $$("[data-album-title]").forEach(element => { element.textContent = album.title; });

        if (thumbs) {
            thumbs.innerHTML = photos.map((photo, index) => `
                <button class="slide-thumb${index === 0 ? " active" : ""}" type="button" data-slide-index="${index}" aria-label="Show photograph ${index + 1}: ${esc(photo.title)}">
                    <img src="${esc(photo.image)}" data-local="${esc(photo.image)}" data-remote="${esc(remoteFor(slug, index))}"
                         data-label="${esc(album.title)}" data-palette="${esc(slug)}" alt="" loading="lazy" decoding="async">
                </button>`).join("");
            hydrateImages(thumbs);
        }

        if (storyList) {
            storyList.innerHTML = photos.map((photo, index) => `
                <a class="story-entry${index === 0 ? " active" : ""}" href="#album-stage" data-slide-index="${index}">
                    <span>FRAME ${pad(index + 1)} &middot; ${esc(photo.date)}</span>
                    <h3>${esc(photo.title)}</h3>
                    <p>${esc(photo.story)}</p>
                </a>`).join("");
        }

        if (!document.getElementById("album-db-data")) renderMiniJourneys(slug);

        function sourceFor(index) {
            const photo = photos[index];
            return resolveImage(photo.image, remoteFor(slug, index), `${album.title} — ${photo.title}`, slug);
        }
        function prefetch(index) { sourceFor((index + photos.length) % photos.length); }
        function updatePlayButton() {
            if (!toggleButton) return;
            const symbol = $(".symbol", toggleButton);
            const label = $(".label", toggleButton);
            if (symbol) symbol.textContent = playing ? "Ⅱ" : "▶";
            if (label) label.textContent = playing ? "Pause" : "Play";
            toggleButton.setAttribute("aria-pressed", String(playing));
            toggleButton.setAttribute("aria-label", playing ? "Pause slideshow" : "Play slideshow");
        }
        function restartAutoplay() {
            window.clearInterval(timer);
            timer = null;
            if (progress) {
                progress.classList.remove("is-running");
                void progress.offsetWidth;
                if (playing && !document.hidden) progress.classList.add("is-running");
            }
            if (playing && !document.hidden) {
                timer = window.setInterval(() => renderSlide(current + 1), DURATION);
            }
            updatePlayButton();
        }
        function updateMeta(index) {
            const photo = photos[index];
            window.clearTimeout(metaTimer);
            const apply = () => {
                setText("#slide-location", photo.location || album.title);
                setText("#slide-title", photo.title);
                setText("#slide-story", photo.story);
                setText("#slide-date", `DATE: ${photo.date} · YEAR: ${album.year}`);
                setText("#slide-number", pad(index + 1));
                $$(".slide-thumb").forEach((button, i) => button.classList.toggle("active", i === index));
                $$(".story-entry").forEach((button, i) => button.classList.toggle("active", i === index));
                if (storyPanel) storyPanel.classList.remove("is-changing");
            };
            if (storyPanel && !reducedMotion) {
                storyPanel.classList.add("is-changing");
                metaTimer = window.setTimeout(apply, 260);
            } else {
                apply();
            }
        }
        function renderSlide(index, initial = false) {
            const nextIndex = (index + photos.length) % photos.length;
            current = nextIndex;
            const token = ++renderToken;
            const nextLayerIndex = 1 - activeLayer;
            const nextLayer = layers[nextLayerIndex];
            const oldLayer = layers[activeLayer];
            const photo = photos[nextIndex];

            sourceFor(nextIndex).then(src => {
                if (token !== renderToken) return;
                if (nextLayer) {
                    nextLayer.src = src;
                    nextLayer.alt = `${photo.title} — ${album.title}`;
                    nextLayer.classList.remove("is-active");
                    void nextLayer.offsetWidth;
                    nextLayer.classList.add("is-active");
                }
                if (oldLayer && oldLayer !== nextLayer) oldLayer.classList.remove("is-active");
                if (layers.length) activeLayer = nextLayerIndex;
                updateMeta(nextIndex);
                restartAutoplay();
                prefetch(nextIndex + 1);
                if (initial) refreshReveal(stage);
            });
        }
        function togglePlay(force) {
            playing = typeof force === "boolean" ? force : !playing;
            restartAutoplay();
        }
        async function toggleFullscreen() {
            try {
                if (!document.fullscreenElement && stage.requestFullscreen) {
                    await stage.requestFullscreen();
                } else if (document.fullscreenElement && document.exitFullscreen) {
                    await document.exitFullscreen();
                } else {
                    showToast("Fullscreen is not available in this browser.");
                }
            } catch (error) {
                showToast("Fullscreen could not be opened. Try again from a button click.");
            }
        }

        $('[data-slide="prev"]')?.addEventListener("click", () => renderSlide(current - 1));
        $('[data-slide="next"]')?.addEventListener("click", () => renderSlide(current + 1));
        toggleButton?.addEventListener("click", () => togglePlay());
        fullscreenButton?.addEventListener("click", toggleFullscreen);
        thumbs?.addEventListener("click", event => {
            const button = event.target.closest("[data-slide-index]");
            if (button) renderSlide(Number(button.dataset.slideIndex));
        });
        storyList?.addEventListener("click", event => {
            const button = event.target.closest("[data-slide-index]");
            if (button) {
                renderSlide(Number(button.dataset.slideIndex));
                $("#album-stage")?.scrollIntoView({ behavior: reducedMotion ? "auto" : "smooth", block: "start" });
            }
        });
        document.addEventListener("fullscreenchange", () => {
            fullscreenButton?.setAttribute("aria-pressed", String(Boolean(document.fullscreenElement)));
        });
        document.addEventListener("visibilitychange", restartAutoplay);
        document.addEventListener("keydown", event => {
            if ($(".lightbox.is-open")) return;
            const tag = event.target.tagName;
            if (tag === "INPUT" || tag === "TEXTAREA" || event.target.isContentEditable) return;
            if (event.key === "ArrowRight") { event.preventDefault(); renderSlide(current + 1); }
            else if (event.key === "ArrowLeft") { event.preventDefault(); renderSlide(current - 1); }
            else if (event.key === " ") { event.preventDefault(); togglePlay(); }
            else if (event.key === "f" || event.key === "F") { toggleFullscreen(); }
        });

        renderSlide(0, true);
        prefetch(1);
    }

    /* --------------------------------------------------------
       FILTERABLE GALLERY + LIGHTBOX
       -------------------------------------------------------- */
    function getGalleryEntries() {
        const sizes = ["tall", "wide", "square", "wide", "tall", "square"];
        const entries = [];
        Object.entries(journeys).forEach(([slug, album]) => {
            album.photos.forEach((photo, photoIndex) => {
                entries.push({
                    globalIndex: entries.length,
                    slug: slug,
                    album: album,
                    photo: photo,
                    photoIndex: photoIndex,
                    remote: remoteFor(slug, photoIndex),
                    size: sizes[entries.length % sizes.length]
                });
            });
        });
        return entries;
    }

    function initDatabaseGallery(grid, filters, count, lightbox) {
        const items = $$(".gallery-item[data-gallery-entry]", grid);
        if (!items.length) return;

        const entries = items.map((element, index) => ({
            index: index,
            element: element,
            album: element.dataset.album || "",
            title: element.dataset.title || "Untitled frame",
            story: element.dataset.story || "",
            date: element.dataset.date || "",
            image: element.dataset.image || (element.querySelector("img")?.getAttribute("src") || "")
        }));

        let visibleEntries = entries.slice();
        let lightboxPosition = 0;
        let imageToken = 0;
        let lastFocused = null;

        function updateCount() {
            if (count) {
                count.textContent = `${pad(visibleEntries.length)} photograph${visibleEntries.length === 1 ? "" : "s"}`;
            }
        }

        function applyFilter(filter) {
            visibleEntries = filter === "all" ? entries.slice() : entries.filter(entry => entry.album === filter);

            $$(".filter-btn", filters || document).forEach(button => {
                const isActive = button.dataset.filter === filter;
                button.classList.toggle("active", isActive);
                button.setAttribute("aria-pressed", String(isActive));
            });

            entries.forEach(entry => {
                const show = filter === "all" || entry.album === filter;
                entry.element.hidden = !show;

                if (show) {
                    entry.element.classList.remove("gallery-enter");
                    void entry.element.offsetWidth;
                    entry.element.classList.add("gallery-enter");
                }
            });

            updateCount();
        }

        filters?.addEventListener("click", event => {
            const button = event.target.closest("[data-filter]");
            if (button) applyFilter(button.dataset.filter);
        });

        function setLightboxImage(src, alt) {
            const image = $("#lightbox-image");
            if (!image) return;
            const token = ++imageToken;
            resolveImage(src, "", alt, "default").then(resolved => {
                if (token !== imageToken || !lightbox || !lightbox.classList.contains("is-open")) return;
                image.src = resolved;
                image.alt = alt;
            });
        }

        function showLightbox(position) {
            if (!visibleEntries.length || !lightbox) return;
            lightboxPosition = (position + visibleEntries.length) % visibleEntries.length;
            const entry = visibleEntries[lightboxPosition];

            setLightboxImage(entry.image, `${entry.title} — ${entry.album}`);
            setText("#lightbox-place", entry.album);
            setText("#lightbox-title", entry.title);
            setText("#lightbox-story", entry.story);
            setText("#lightbox-date", entry.date);
            setText("#lightbox-number", pad(lightboxPosition + 1));
            setText("#lightbox-total", pad(visibleEntries.length));
        }

        function openLightbox(entryIndex) {
            if (!lightbox) return;
            lastFocused = document.activeElement;
            lightbox.inert = false;
            lightbox.classList.add("is-open");
            lightbox.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            const position = visibleEntries.findIndex(entry => entry.index === entryIndex);
            showLightbox(position >= 0 ? position : 0);
            $("#lightbox-close")?.focus();
        }

        function closeLightbox() {
            if (!lightbox || !lightbox.classList.contains("is-open")) return;
            lightbox.classList.remove("is-open");
            lightbox.setAttribute("aria-hidden", "true");
            lightbox.inert = true;
            document.body.style.overflow = "";
            if (lastFocused && typeof lastFocused.focus === "function") lastFocused.focus();
        }

        grid.addEventListener("click", event => {
            const item = event.target.closest("[data-gallery-entry]");
            if (item) openLightbox(Number(item.dataset.index));
        });
        $('[data-lightbox="prev"]')?.addEventListener("click", () => showLightbox(lightboxPosition - 1));
        $('[data-lightbox="next"]')?.addEventListener("click", () => showLightbox(lightboxPosition + 1));
        $("[data-lightbox-close]")?.addEventListener("click", closeLightbox);
        lightbox?.addEventListener("click", event => { if (event.target === lightbox) closeLightbox(); });
        document.addEventListener("keydown", event => {
            if (!lightbox || !lightbox.classList.contains("is-open")) return;
            if (event.key === "Escape") { event.preventDefault(); closeLightbox(); }
            else if (event.key === "ArrowRight") { event.preventDefault(); showLightbox(lightboxPosition + 1); }
            else if (event.key === "ArrowLeft") { event.preventDefault(); showLightbox(lightboxPosition - 1); }
        });

        updateCount();
    }

    function initGallery() {
        const grid = $("#gallery-grid");
        if (!grid) return;
        const filters = $("#gallery-filters");
        const count = $("#gallery-count");
        const lightbox = $("#lightbox");

        if (grid.dataset.gallerySource === "database") {
            initDatabaseGallery(grid, filters, count, lightbox);
            return;
        }

        const entries = getGalleryEntries();
        let visibleEntries = entries.slice();
        let lightboxPosition = 0;
        let lightboxToken = 0;
        let lastFocused = null;

        if (filters) {
            filters.innerHTML = `<button class="filter-btn active" type="button" data-filter="all" aria-pressed="true">All</button>` +
                Object.entries(journeys).map(([slug, album]) =>
                    `<button class="filter-btn" type="button" data-filter="${esc(slug)}" aria-pressed="false">${esc(album.title)}</button>`
                ).join("");
        }

        grid.innerHTML = entries.map((entry, index) => `
            <button class="gallery-item gallery-enter" type="button" data-place="${esc(entry.slug)}" data-lightbox-index="${entry.globalIndex}"
                    data-size="${esc(entry.size)}" style="animation-delay:${(index % 8) * 0.04}s"
                    aria-label="Open ${esc(entry.photo.title)} from ${esc(entry.album.title)} in fullscreen">
                <img src="${esc(entry.photo.image)}" data-local="${esc(entry.photo.image)}" data-remote="${esc(entry.remote)}"
                     data-label="${esc(entry.album.title)}" data-palette="${esc(entry.slug)}"
                     alt="${esc(entry.photo.title)} — ${esc(entry.album.title)}" loading="lazy" decoding="async">
                <span class="gallery-overlay">
                    <span class="gallery-place">${esc(entry.album.title)} &middot; ${esc(entry.album.year)}</span>
                    <strong>${esc(entry.photo.title.replace("PHOTO TITLE — ", ""))}</strong>
                    <small>Click to read the story</small>
                </span>
            </button>`).join("");
        hydrateImages(grid);

        function updateCount() {
            if (count) count.textContent = `${pad(visibleEntries.length)} photographs`;
        }
        function applyFilter(filter) {
            visibleEntries = filter === "all" ? entries.slice() : entries.filter(entry => entry.slug === filter);
            $$(".filter-btn", filters || document).forEach(button => {
                const isActive = button.dataset.filter === filter;
                button.classList.toggle("active", isActive);
                button.setAttribute("aria-pressed", String(isActive));
            });
            $$(".gallery-item", grid).forEach(item => {
                const show = filter === "all" || item.dataset.place === filter;
                item.hidden = !show;
                if (show) {
                    item.classList.remove("gallery-enter");
                    void item.offsetWidth;
                    item.classList.add("gallery-enter");
                }
            });
            updateCount();
        }
        filters?.addEventListener("click", event => {
            const button = event.target.closest("[data-filter]");
            if (button) applyFilter(button.dataset.filter);
        });

        function showLightbox(position) {
            if (!visibleEntries.length || !lightbox) return;
            lightboxPosition = (position + visibleEntries.length) % visibleEntries.length;
            const entry = visibleEntries[lightboxPosition];
            const token = ++lightboxToken;
            const image = $("#lightbox-image");
            resolveImage(entry.photo.image, entry.remote, `${entry.album.title} — ${entry.photo.title}`, entry.slug).then(src => {
                if (token !== lightboxToken || !lightbox.classList.contains("is-open")) return;
                if (image) {
                    image.src = src;
                    image.alt = `${entry.photo.title} — ${entry.album.title}`;
                }
            });
            setText("#lightbox-place", entry.album.title);
            setText("#lightbox-title", entry.photo.title);
            setText("#lightbox-story", entry.photo.story);
            setText("#lightbox-date", `${entry.photo.date} · ${entry.album.year}`);
            setText("#lightbox-number", pad(lightboxPosition + 1));
            setText("#lightbox-total", pad(visibleEntries.length));
        }
        function openLightbox(globalIndex) {
            if (!lightbox) return;
            lastFocused = document.activeElement;
            lightbox.inert = false;
            lightbox.classList.add("is-open");
            lightbox.setAttribute("aria-hidden", "false");
            document.body.style.overflow = "hidden";
            const position = visibleEntries.findIndex(entry => entry.globalIndex === globalIndex);
            showLightbox(position >= 0 ? position : 0);
            $("#lightbox-close")?.focus();
        }
        function closeLightbox() {
            if (!lightbox || !lightbox.classList.contains("is-open")) return;
            lightbox.classList.remove("is-open");
            lightbox.setAttribute("aria-hidden", "true");
            lightbox.inert = true;
            document.body.style.overflow = "";
            if (lastFocused && typeof lastFocused.focus === "function") lastFocused.focus();
        }

        grid.addEventListener("click", event => {
            const item = event.target.closest("[data-lightbox-index]");
            if (item) openLightbox(Number(item.dataset.lightboxIndex));
        });
        $('[data-lightbox="prev"]')?.addEventListener("click", () => showLightbox(lightboxPosition - 1));
        $('[data-lightbox="next"]')?.addEventListener("click", () => showLightbox(lightboxPosition + 1));
        $("[data-lightbox-close]")?.addEventListener("click", closeLightbox);
        lightbox?.addEventListener("click", event => { if (event.target === lightbox) closeLightbox(); });
        document.addEventListener("keydown", event => {
            if (!lightbox || !lightbox.classList.contains("is-open")) return;
            if (event.key === "Escape") { event.preventDefault(); closeLightbox(); }
            else if (event.key === "ArrowRight") { event.preventDefault(); showLightbox(lightboxPosition + 1); }
            else if (event.key === "ArrowLeft") { event.preventDefault(); showLightbox(lightboxPosition - 1); }
        });

        updateCount();
    }

    /* --------------------------------------------------------
       CONTACT FORM — frontend demonstration only.
       CONNECT A BACKEND / EMAIL SERVICE inside this handler later.
       -------------------------------------------------------- */
    function initContactForm() {
        const form = $("#contact-form");
        if (!form) return;
        form.addEventListener("submit", event => {
            event.preventDefault();
            const status = $("#form-status");
            if (status) {
                status.textContent = "DEMO ONLY — this frontend does not send messages yet. Connect a backend or email service in js/script.js.";
            }
            form.reset();
        });
    }

    /* --------------------------------------------------------
       START EVERYTHING
       -------------------------------------------------------- */
    function init() {
        injectChrome();
        renderJourneyGrid("#home-journeys");
        renderJourneyGrid("#albums-grid");
        renderTimeline();
        renderFeatured();
        hydrateImages();
        initLoader();
        initNavbar();
        initTheme();
        initReveal();
        initScrollEffects();
        initProfileTilt();
        initCursor();
        hydrateDatabaseAlbum();
        initSlideshow();
        initGallery();
        initContactForm();
        initPlaceholderLinks();
        initPageTransitions();
        initHashScroll();
        refreshReveal();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();

