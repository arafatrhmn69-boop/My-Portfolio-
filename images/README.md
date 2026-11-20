# Image folders

Put your own compressed photographs here using the exact filenames below. Until then, the site shows temporary Pexels placeholders or a generated, labelled SVG.

```text
images/
├── profile/
│   ├── profile.jpg           # Hero portrait
│   ├── about.jpg             # Different portrait/layout for About
│   ├── hero-bg.jpg           # Optional wide hero background
│   └── philosophy-bg.jpg     # Optional wide quote background
│
├── cox-bazar/
│   ├── thumbnail.jpg
│   └── photo1.jpg ... photo4.jpg
├── teknaf/
│   ├── thumbnail.jpg
│   └── photo1.jpg ... photo4.jpg
├── bandarban/
│   ├── thumbnail.jpg
│   └── photo1.jpg ... photo4.jpg
├── sylhet/
│   ├── thumbnail.jpg
│   └── photo1.jpg ... photo4.jpg
├── sitakunda/
│   ├── thumbnail.jpg
│   └── photo1.jpg ... photo4.jpg
├── akhilpur/
│   ├── patenga-beach.jpg     # REQUIRED Akhilpur thumbnail (Patenga Beach photo)
│   └── photo1.jpg ... photo4.jpg
└── guliakhali/
    ├── thumbnail.jpg
    └── photo1.jpg ... photo4.jpg
```

## Important

- **Akhilpur's thumbnail must be a Patenga Beach photograph** at `images/akhilpur/patenga-beach.jpg`.
- Do not reuse one thumbnail for every album; each destination has its own path.
- Add more photos by duplicating a `createPhoto(...)` line in `js/script.js` and increasing the photo number.
- Photos are lazy-loaded except the first/hero images. Keep file sizes reasonable.
- Every photo story, date and title is editable in `js/script.js`.
