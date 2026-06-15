import { chromium } from "playwright";

// KotKompas is a Laravel + Blade app served by Laravel Herd at
// https://kotkompas.test (local, self-signed cert). Override for artisan
// serve / CI: BASE_URL=http://localhost:8000 npm run scan
const BASE: string = process.env.BASE_URL ?? "https://kotkompas.test";

// Public routes only. The dashboard/auth pages load the Reverb websocket
// (echo.ts), whose connection state is environment-dependent — scan those
// explicitly (e.g. `npm run scan /dashboard/login`) with Reverb running.
// Room detail needs a real id: `npm run scan /koten/1`.
const DEFAULT_ROUTES: string[] = [
  "/",
  "/koten",
  "/contact",
  "/faq",
  "/privacy",
  "/cookies",
  "/algemene-voorwaarden",
  "/gegevens-verwijderen",
];

const routes: string[] = process.argv.slice(2).length
  ? process.argv.slice(2)
  : DEFAULT_ROUTES;

const sameOrigin = (url: string): boolean => {
  try {
    return new URL(url).origin === new URL(BASE).origin;
  } catch {
    return false;
  }
};

const browser = await chromium.launch({
  args: ["--enable-webgl", "--use-gl=swiftshader", "--ignore-gpu-blocklist"],
});
const ctx = await browser.newContext({
  viewport: { width: 1440, height: 900 },
  ignoreHTTPSErrors: true, // Herd's .test certificate is locally signed
});
const page = await ctx.newPage();

const log: string[] = [];
// Console errors and uncaught exceptions are app bugs wherever they originate.
page.on("console", (m) => {
  if (m.type() === "error") log.push(`[error] ${m.text()}`);
});
page.on("pageerror", (e) => log.push(`PAGEERROR: ${e.message}\n${e.stack ?? ""}`));
// Network failures are only flagged for OUR origin — a stale/missing built
// asset 404s here, but third-party Leaflet tiles / Typekit fonts are ignored.
page.on("requestfailed", (r) => {
  if (sameOrigin(r.url())) log.push(`REQFAIL: ${r.failure()?.errorText} ${r.url()}`);
});
page.on("response", (r) => {
  if (r.status() >= 400 && sameOrigin(r.url())) log.push(`HTTP ${r.status()} ${r.url()}`);
});

let failed = 0;
for (const route of routes) {
  log.length = 0;
  try {
    await page.goto(BASE + route, { waitUntil: "load", timeout: 30000 });
  } catch (e) {
    failed++;
    console.log(`\n=== ${route} (navigation failed) ===\n${(e as Error).message}`);
    continue;
  }
  await page.waitForTimeout(2000);
  // Scroll pass: GSAP/Lenis scroll-driven effects only error mid-scroll.
  await page.evaluate(async () => {
    for (let i = 1; i <= 6; i++) {
      window.scrollTo(0, (document.body.scrollHeight * i) / 6);
      await new Promise((r) => setTimeout(r, 250));
    }
    window.scrollTo(0, 0);
  });
  await page.waitForTimeout(800);
  const errs = log.filter((l) => /^(\[error\]|PAGEERROR|REQFAIL|HTTP )/.test(l));
  if (errs.length) {
    failed++;
    console.log(`\n=== ${route} ===\n${errs.join("\n")}`);
  } else {
    console.log(`✓ ${route}`);
  }
}
await browser.close();
process.exit(failed ? 1 : 0);
