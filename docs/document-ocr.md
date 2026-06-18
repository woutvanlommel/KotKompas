# Document OCR & AI descriptions

When a user uploads a document (PDF or image) on the **Mijn documenten** page, the app:

1. Stores the file (Spatie Media Library).
2. Queues `App\Jobs\ProcessDocumentOcr`, which:
   - extracts text with the **OCR.Space** API (`ocr_text`),
   - asks **Google Gemini** to condense that text into a short Dutch description (`description`),
   - tracks progress in `ocr_status` (`pending` → `processing` → `done` / `failed`).
3. Shows the description (foldable) and an analysis status badge on each document card, and
   renders **page 1 of a PDF** as the card thumbnail.

For this to work end-to-end on a developer machine, you need three things: **API keys**, a
**running queue worker**, and (for PDF thumbnails) **Imagick + Ghostscript**.

---

## 1. API keys

Add these to your `.env` (placeholders are in `.env.example`):

```dotenv
OCR_SPACE_API_KEY=...        # https://ocr.space/ocrapi  (free tier is fine)
GEMINI_API_KEY=...           # https://aistudio.google.com/apikey
GEMINI_MODEL=gemini-2.5-flash
```

Notes:

- **Gemini key:** create it in Google AI Studio. Keys created under a brand‑new API project work
  on the free tier. If a model returns **HTTP 429 "exceeded your current quota"**, that model has
  no free quota on your project — keep `GEMINI_MODEL=gemini-2.5-flash` (verified to work on the
  free tier) or enable billing on the project. `gemini-2.0-flash` has **no** free quota.
- **Config cache:** if you change `.env` and the value doesn't take effect, run
  `php artisan config:clear`.
- **OS environment variables override `.env`.** Laravel will *not* let `.env` overwrite a variable
  that already exists in your real shell/OS environment. If `config('services.gemini.key')` shows a
  different key than your `.env`, you have a stale OS variable:
  - **Windows (PowerShell):** `[Environment]::SetEnvironmentVariable('GEMINI_API_KEY', $null, 'User')`
    then open a new terminal.
  - **macOS/Linux:** remove any `export GEMINI_API_KEY=...` from `~/.zshrc` / `~/.bashrc`, then
    `unset GEMINI_API_KEY` and restart the terminal.

---

## 2. Queue worker

OCR runs on the queue (`QUEUE_CONNECTION=database`). Nothing happens until a worker processes the
job.

- **Recommended:** `composer run dev` already starts a worker (the `queue` process runs
  `php artisan queue:listen`), alongside the Vite server, Reverb, Mailpit and Stripe listener.
- **Standalone:** `php artisan queue:work` (or `queue:listen`, which also picks up code changes).

Without a worker, an uploaded document stays on the **"Wordt geanalyseerd…"** badge forever.

---

## 3. PDF page‑1 thumbnails (Imagick + Ghostscript)

Image uploads (PNG/JPG/WebP) get a thumbnail out of the box. **PDF** thumbnails require the PHP
**Imagick** extension plus **Ghostscript** (Imagick uses Ghostscript to rasterise PDFs). The
`spatie/pdf-to-image` Composer package is already a project dependency; you only need the system
pieces. Without them the card falls back to a plain "PDF" placeholder (the feature degrades
gracefully — nothing breaks).

### macOS (Homebrew)

```bash
brew install ghostscript imagemagick
pecl install imagick          # or: brew install php-imagick (depending on your PHP install)
php -m | grep imagick         # confirm the extension is loaded
```

If you use Herd/Valet or a Homebrew PHP, make sure the `imagick` extension is enabled for that PHP
binary (`php --ini` shows which `php.ini` is in use).

### Windows

1. **Ghostscript:** download and run the 64‑bit installer from
   https://ghostscript.com/releases/gsdnld.html. Confirm it is on `PATH`:
   `gswin64c --version` (restart the terminal after install).
2. **ImageMagick + PHP Imagick:**
   - Find your PHP build: `php -i | findstr /C:"Architecture" /C:"Thread Safety" /C:"PHP Version"`.
   - Download the matching `php_imagick` DLL (e.g. from the mlocati/PECL Windows builds) and the
     corresponding ImageMagick DLLs.
   - Put `php_imagick.dll` in PHP's `ext\` directory and the ImageMagick `*.dll` files next to
     `php.exe` (or on `PATH`).
   - Add `extension=imagick` to your `php.ini`.
   - Confirm: `php -m | findstr imagick`.

### Verify the generator is ready (both platforms)

```bash
php artisan tinker --execute='echo (new \Spatie\MediaLibrary\Conversions\ImageGenerators\Pdf())->requirementsAreInstalled() ? "READY" : "NOT READY";'
```

Expected: `READY`. If it prints `NOT READY`, either Imagick isn't loaded or `spatie/pdf-to-image`
isn't installed (`composer install`).

### Backfill existing documents

New uploads generate the thumbnail automatically (via the queue). To generate thumbnails for
documents uploaded **before** Imagick was installed:

```bash
php artisan media-library:regenerate
```

---

## Composer note for the team

`composer.json` requires `spatie/pdf-to-image`, which declares `ext-imagick`. On a machine where
Imagick is not yet installed, `composer install` will fail the platform check. Either install
Imagick first (recommended — and **required on the production server** for thumbnails to generate),
or install once with:

```bash
composer install --ignore-platform-req=ext-imagick
```

---

## Quick end‑to‑end check

With keys set, a worker running, and (optionally) Imagick installed:

```bash
php artisan tinker --execute='$d = App\Models\Document::latest()->whereNotNull("ocr_text")->first() ?? App\Models\Document::find(11); (new App\Jobs\ProcessDocumentOcr($d))->handle(); $d->refresh(); echo $d->ocr_status." | ".$d->description;'
```

Expected output: `done | <a 2–3 sentence Dutch summary of the document>`.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| Badge stuck on "Wordt geanalyseerd…" | No queue worker running | `composer run dev` or `php artisan queue:work` |
| `ocr_status = done` but `description` empty | Gemini key/quota/model issue | Check key, keep `GEMINI_MODEL=gemini-2.5-flash`, see §1 |
| Gemini "API key expired/invalid" with a fresh key | Stale OS env var overriding `.env` | Remove the OS variable (see §1) |
| Gemini HTTP 429 quota | Model has no free quota on the project | Use `gemini-2.5-flash` or enable billing |
| Card shows "PDF" instead of page 1 | Imagick/Ghostscript missing | Install them (§3), then `media-library:regenerate` |
| Multi‑page PDF, only partial text | OCR.Space free tier caps PDFs at 3 pages | Expected; the job keeps the first 3 pages' text on purpose |
