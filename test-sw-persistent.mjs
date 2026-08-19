import { chromium } from 'playwright';
import fs from 'fs';

const BASE = 'http://127.0.0.1:8000';
const log = (...args) => console.log('[test]', ...args);
const profileDir = 'C:\\xampp\\htdocs\\GadgetStorePOSsystem\\.edge-test-profile';
fs.mkdirSync(profileDir, { recursive: true });

const context = await chromium.launchPersistentContext(profileDir, {
    channel: 'msedge',
    headless: false,
});
const page = context.pages()[0] ?? (await context.newPage());
page.on('pageerror', (err) => log('PAGE ERROR:', err.message));

try {
    await page.goto(`${BASE}/login`);
    await page.fill('input[name=email]', 'admin@gadgetstore.test');
    await page.fill('input[name=password]', 'password');
    await page.click('button[type=submit]', { noWaitAfter: true });
    for (let i = 0; i < 20; i++) {
        await page.waitForTimeout(1000);
        if (page.url().includes('/dashboard')) break;
    }
    log('At:', page.url());

    await page.goto(`${BASE}/pos`, { waitUntil: 'load' });
    await page.waitForTimeout(1000);

    const manualRegisterResult = await page.evaluate(async () => {
        try {
            const reg = await navigator.serviceWorker.register('/service-worker.js', { scope: '/' });
            await new Promise((r) => setTimeout(r, 3000));
            return {
                ok: true, scope: reg.scope,
                installing: reg.installing?.state ?? null,
                waiting: reg.waiting?.state ?? null,
                active: reg.active?.state ?? null,
            };
        } catch (err) {
            return { ok: false, error: String(err) };
        }
    });
    log('Register result:', JSON.stringify(manualRegisterResult));

    const swDiag = await page.evaluate(async () => {
        const regs = await navigator.serviceWorker.getRegistrations();
        return { registrationCount: regs.length, hasController: navigator.serviceWorker.controller !== null };
    });
    log('SW diag:', JSON.stringify(swDiag));
} catch (err) {
    log('FAILED:', err.message);
} finally {
    await context.close();
}
