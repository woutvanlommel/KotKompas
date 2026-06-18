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

### macOS (Homebrew) — recommended for most of the team

This is the smooth path. It takes ~5 minutes.

```bash
# 1. System libraries (ImageMagick is the engine, Ghostscript reads PDFs)
brew install imagemagick ghostscript pkg-config

# 2. The PHP imagick extension (built against the ImageMagick you just installed)
pecl install imagick
```

During `pecl install imagick` you may be asked:
*"Please provide the prefix of ImageMagick installation"* — press **Enter** to accept
`autodetect`. If autodetect fails, answer with your Homebrew prefix:
- Apple Silicon (M1/M2/M3/M4): `/opt/homebrew`
- Intel Macs: `/usr/local`

**Enable the extension** (pecl usually adds the line automatically; verify it did):

```bash
php --ini                       # shows the "Loaded Configuration File" — your active php.ini
php -m | grep imagick           # should print: imagick
```

If `imagick` is **not** listed, add this line to that php.ini and re-check:

```ini
extension=imagick
```

**Restart your PHP process** after enabling it: `php artisan serve` / `composer run dev` must be
restarted, and if you use **Valet** run `valet restart`.

#### Laravel Herd users

Herd ships its own PHP, so the Homebrew `pecl` extension won't apply to it.
- **Herd Pro** can toggle extensions in the app UI (PHP → Extensions → enable *imagick*) — use that.
- You still need Ghostscript on PATH: `brew install ghostscript`.
- If your Herd version doesn't offer imagick, run the verification commands below against Herd's
  `php` (`which php` should point inside `~/Library/Application Support/Herd`); if it can't be
  enabled there, run the OCR queue worker with a Homebrew PHP that has imagick, or use the
  graceful fallback (PDFs just show the "PDF" placeholder — nothing breaks).

#### Verify on macOS

```bash
gs --version                    # Ghostscript prints e.g. 10.05.1
php -m | grep imagick           # prints: imagick
```

### Windows — detailed walkthrough (with the traps)

There is no winget/PECL one-liner, so this is manual but reliable. The steps below match a PHP
installed at `C:\tools\php85` — substitute your own PHP path (find it with
`(Get-Command php).Source`).

First, get your **exact** PHP build — you must match all three (version / thread-safety /
architecture):

```powershell
php -i | Select-String '^PHP Version','^Architecture','^Thread Safety','Loaded Configuration File','^extension_dir'
```

Example: `PHP 8.5 · x64 · Thread Safety disabled (= NTS)` → you need the **8.5 / NTS / x64** Imagick.

#### 1. PHP Imagick extension

1. Download the matching build from PECL (https://pecl.php.net/package/imagick → latest →
   *Windows*) — pick the row under your PHP version, e.g. **"8.5 Non Thread Safe (NTS) x64"**.
   (mlocati's builds at https://github.com/mlocati/imagick-windows-builds/releases are an
   alternative and often more current.)
2. The zip is large (hundreds of files) — it bundles the **whole ImageMagick runtime**. Place it
   like this:
   - **`php_imagick.dll`** → your PHP `ext\` folder (e.g. `C:\tools\php85\ext\`)
   - **All the other files** (the `CORE_RL_*.dll`, `IM_MOD_RL_*.dll`, `FILTER_*.dll`, `.xml`
     configs, …) → keep them **together** in one new folder, e.g. `C:\tools\php85\imagick\`.
     Don't split them up — the ImageMagick modules find each other by sitting side-by-side.
3. Put that folder on PATH so the extension can find its libraries:
   ```powershell
   [Environment]::SetEnvironmentVariable('Path', [Environment]::GetEnvironmentVariable('Path','User') + ';C:\tools\php85\imagick', 'User')
   ```
4. Enable the extension — add to your `php.ini`:
   ```ini
   extension=imagick
   ```
5. The Imagick build needs the **Microsoft Visual C++ 2015–2022 Redistributable (x64)**. It's
   usually already present; if Imagick fails to load, install it from
   https://aka.ms/vs/17/release/vc_redist.x64.exe.

#### 2. Ghostscript (Imagick uses it to read PDFs)

1. Download the **Ghostscript** (the "Postscript and PDF interpreter/renderer", *not* GhostPCL /
   GhostXPS / GhostPDL) Windows **64-bit** installer from
   https://ghostscript.com/releases/gsdnld.html — a file like `gs10071w64.exe`.
   ⚠️ **Do NOT use `winget install ... ghostscript`** — winget's only match is **"Ghost Trap"**, a
   sandboxed wrapper that does **not** provide `gswin64c.exe`. Use the official installer.
2. Run it (defaults are fine) → installs to `C:\Program Files\gs\gs10.07.x\`. The installer adds
   its `bin` to the **Machine** PATH automatically; if `gswin64c` isn't found later, add it:
   ```powershell
   [Environment]::SetEnvironmentVariable('Path', [Environment]::GetEnvironmentVariable('Path','User') + ';C:\Program Files\gs\gs10.07.1\bin', 'User')
   ```

#### 3. ⚠️ Windows gotchas (these cost real debugging time)

- **Open a brand-new terminal after any PATH change.** A new tab inside an already-running editor
  (e.g. VS Code's integrated terminal) still inherits the **old** PATH — you must fully **quit and
  reopen the editor**, not just open a new tab. Symptom: `php -m` warns *"Unable to load dynamic
  library 'imagick' … The specified module could not be found"* even though the DLL is in `ext\`.
  That error means a **dependency** (the ImageMagick core DLLs) isn't on PATH yet — fix the PATH /
  restart, don't re-copy `php_imagick.dll`.
- **winget gives you GhostTrap, not Ghostscript** (see above) — use the official `.exe`.
- **Match NTS vs TS and x64 vs x86 exactly**, or the DLL silently refuses to load.

#### Verify on Windows

```powershell
php -m | Select-String imagick      # prints: imagick (no warning above it)
gswin64c --version                  # prints e.g. 10.07.1
```

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
| Thumbnail renders but is blank/black | Ghostscript not found by Imagick | Ensure `gs`/`gswin64c --version` works on PATH, then regenerate |
| (Windows) `Unable to load dynamic library 'imagick' … module could not be found` | ImageMagick core DLLs not on PATH, or terminal not restarted | Add the imagick folder to PATH and **fully restart the editor** (§3 gotchas) |
| (Windows) `gswin64c` not recognised after winget install | winget installed **GhostTrap**, not Ghostscript | Uninstall it, install the official Ghostscript `.exe` (§3) |
| `requirementsAreInstalled()` = NOT READY | Imagick not loaded **or** `spatie/pdf-to-image` not installed | `php -m` for imagick; `composer install` |
| Multi‑page PDF, only partial text | OCR.Space free tier caps PDFs at 3 pages | Expected; the job keeps the first 3 pages' text on purpose |
