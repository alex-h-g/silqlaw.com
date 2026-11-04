const { chromium } = require('playwright');
(async () => {
  const [,, url, out, w] = process.argv;
  const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: +(w||1440), height: 900 } });
  await p.goto(url, { waitUntil: 'networkidle', timeout: 60000 });
  if (process.env.FULL === '1') {
    await p.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 120)); } window.scrollTo(0, 0); });
    await p.waitForLoadState('networkidle'); await p.waitForTimeout(500);
  }
  await p.screenshot({ path: out, fullPage: process.env.FULL === '1' }); await b.close();
})();
