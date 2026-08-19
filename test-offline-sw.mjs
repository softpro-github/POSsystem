import { chromium } from 'playwright';

const BASE = 'http://127.0.0.1:8000';
const log = (...args) => console.log('[test]', ...args);

const browser = await chromium.launch({ channel: 'msedge', headless: false });
const context = await browser.newContext();
const page = await context.newPage();

page.on('console', (msg) => {
    if (msg.type() === 'error') log('CONSOLE ERROR:', msg.text());
});
page.on('pageerror', (err) => log('PAGE ERROR:', err.message));

try {
    // 1. Log in — click, then just poll page.url() rather than trusting
    // Playwright's navigation-wait heuristics (unreliable in this environment).
    await page.goto(`${BASE}/login`);
    await page.fill('input[name=email]', 'admin@gadgetstore.test');
    await page.fill('input[name=password]', 'password');
    await page.click('button[type=submit]', { noWaitAfter: true });

    let loggedIn = false;
    for (let i = 0; i < 20; i++) {
        await page.waitForTimeout(1000);
        if (page.url().includes('/dashboard')) { loggedIn = true; break; }
    }
    log('Logged in:', loggedIn, '(url:', page.url(), ')');

    // 2. Load /pos online, let the SW register
    await page.goto(`${BASE}/pos`, { waitUntil: 'load' });
    await page.waitForTimeout(1000);
    log('POS page loaded, visible:', await page.isVisible('text=Point of Sale'));

    const fetchDiag = await page.evaluate(async () => {
        try {
            const res = await fetch('/service-worker.js');
            const text = await res.text();
            return { status: res.status, contentType: res.headers.get('content-type'), length: text.length };
        } catch (err) {
            return { error: String(err) };
        }
    });
    log('Fetch /service-worker.js from page context:', JSON.stringify(fetchDiag));

    const manualRegisterResult = await page.evaluate(async () => {
        if (!('serviceWorker' in navigator)) return { supported: false };
        try {
            const reg = await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
            await new Promise((resolve) => setTimeout(resolve, 2000));
            return {
                supported: true, ok: true, scope: reg.scope,
                installing: reg.installing?.state ?? null,
                waiting: reg.waiting?.state ?? null,
                active: reg.active?.state ?? null,
            };
        } catch (err) {
            return { supported: true, ok: false, error: String(err) };
        }
    });
    log('Manual register attempt:', JSON.stringify(manualRegisterResult));

    await page.waitForTimeout(3000);
    const swDiag = await page.evaluate(async () => {
        const regs = await navigator.serviceWorker.getRegistrations();
        return {
            registrationCount: regs.length,
            states: regs.map((r) => ({
                scope: r.scope,
                installing: r.installing?.state ?? null,
                waiting: r.waiting?.state ?? null,
                active: r.active?.state ?? null,
            })),
            hasController: navigator.serviceWorker.controller !== null,
        };
    });
    log('SW diagnostic:', JSON.stringify(swDiag));

    const cacheState = await page.evaluate(async () => {
        const keys = await caches.keys();
        return { cacheNames: keys };
    });
    log('Cache Storage keys:', JSON.stringify(cacheState.cacheNames));

    log('TEST COMPLETE (diagnostic phase)');
} catch (err) {
    log('TEST FAILED:', err.message);
    await page.screenshot({ path: 'test-failure.png' }).catch(() => {});
    process.exitCode = 1;
} finally {
    await browser.close();
}
