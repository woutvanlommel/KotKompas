// Diagnostic: does the cross-document view transition ACTUALLY fire on each navigation?
//
// A `pageswap` listener on the (loaded, stable) source page fires just before the page is
// swapped out, with `event.viewTransition` set when a cross-document view transition is
// engaged. We stash that in sessionStorage (survives the same-origin navigation) and read
// it on the destination. CRITICAL: a fresh browser context per pair — the browser skips a
// view transition if another just ran, so reusing one context produces false negatives.
//
// Usage: node scripts/scan-transitions.ts <from> <to...>
//   raw "1" = transition fired · "0" = navigated, no transition · null/"error" = inconclusive
import { chromium } from "playwright";

const BASE: string = process.env.BASE_URL ?? "https://kotkompas.test";
const from: string | undefined = process.argv[2];
const targets: string[] = process.argv.slice(3);

if (!from || targets.length === 0) {
  console.error("Usage: node scripts/scan-transitions.ts <from> <to...>");
  process.exit(2);
}

type Result = {
  to: string;
  fired: boolean;
  raw: string | null;
  landedOn?: string;
  error?: string;
};

const browser = await chromium.launch({
  args: ["--enable-webgl", "--use-gl=swiftshader", "--ignore-gpu-blocklist"],
});

async function testPair(to: string): Promise<Result> {
  const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, ignoreHTTPSErrors: true });
  const page = await ctx.newPage();
  try {
    await page.goto(BASE + from, { waitUntil: "load", timeout: 30000 });
    await page.waitForTimeout(400); // let GSAP/Lenis settle so the click hits a stable page
    await page.evaluate((url: string) => {
      try { sessionStorage.removeItem("kk_swap"); } catch {}
      window.addEventListener("pageswap", (e: Event) => {
        const vt = (e as Event & { viewTransition?: unknown }).viewTransition;
        try { sessionStorage.setItem("kk_swap", vt ? "1" : "0"); } catch {}
      });
      const a = document.createElement("a");
      a.id = "kk-vt-link";
      a.href = url;
      a.textContent = "go";
      a.style.cssText = "position:fixed;left:0;top:0;width:24px;height:24px;z-index:99999";
      document.body.appendChild(a);
    }, BASE + to);
    await page.click("#kk-vt-link"); // Playwright manages the navigation wait
    await page.waitForLoadState("load", { timeout: 30000 });
    await page.waitForTimeout(300);
    const raw = await page.evaluate(() => {
      try { return sessionStorage.getItem("kk_swap"); } catch { return null; }
    });
    return { to, fired: raw === "1", raw, landedOn: page.url().replace(BASE, "") };
  } catch (e) {
    return { to, fired: false, raw: "error", error: String(e).slice(0, 140) };
  } finally {
    await ctx.close();
  }
}

const results: Result[] = [];
for (const to of targets) {
  if (to === from) continue;
  results.push(await testPair(to));
}

await browser.close();
console.log(JSON.stringify({ from, results }, null, 2));
