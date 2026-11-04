# Project brief — silqlaw.com rebuild

_Owner: Alex (webmaster, owns the whole site). Started 4 Nov 2026._

## Goal
Rebuild silqlaw.com with the fosrlaw.com design system: very light, very fast WordPress theme, modern law-firm styling,
strong SEO structure, no lost functionality. Rebranded and re-pointed at SILQ's content.

## Agreed decisions (4 Nov 2026)
- **Stay on WordPress / SiteGround.** Theme `silq`, child of Hello Elementor, from the `fosr` code.
- **Environments:** local full copy for building; staging2.silqlaw.com wiped and re-cloned fresh from live for preview.
- **Palette:** navy (from logo, ~#05176C) primary, red accent, gold secondary. Warm off-white background. Final hexes to confirm against the logo.
- **Tagline:** Real Advice, Real Results.
- **Voice:** warmer than FOSR, SME-focused, helping people; authoritative through knowledge.
- **No awards or rankings** (new firm). No recognition strip.
- **No newsletter.** No Google Ads, so no GCLID attribution work.
- **Polylang removed.** Site is English-only (Russian dropped). Migration clears language assignments before deactivation.
- **Practice areas** move from `themo_portfolio` `/services/…` to pages under `/practice-areas/…`, one direct 301 each.
- **Homepage featured practices:** Company Formation, BOI, Visas & Work Permits, Real Estate, Dispute Resolution.
- **Homepage people:** Penny (Chairwoman) only.
- **Articles and team stay as they are** — SILQ content.
- **Enquiries:** email only for now → alex@silqlaw.com, paul@silqlaw.com. Bigin later.
- **Line** stays as a contact option.
- **Analytics:** GA4 property 479785685; Site Kit installed but GA not connected in it — find where the tag comes from.
- **Cookie consent:** CookieYes account exists, plugin inactive — set up, gate analytics behind consent.
- **Thin pages** (e.g. Treaty of Amity) get real content written.
- Office address and contact details: as on the live site.

## To do before launch
- Privacy policy + cookie policy pages, linked in footer (PDPA, CookieYes).
- Search Console access for sitemap resubmission and 404 monitoring.
- Review SiteGround Speed Optimizer against the theme's asset handling (compare mufasa.co.th settings).

## SiteGround Speed Optimizer (copy mufasa.co.th, 100/100)
Dynamic cache on · file-based cache on · Memcached off · minify CSS on · combine CSS off · minify JS off ·
combine JS off · defer render-blocking JS on (2 exclusions — TBC) · minify HTML on · web fonts optimisation off.
Media tab: TBC.

## Access
SSH key on Alex's Mac (Neo): `~/.ssh/siteground_silq` (use `-o IdentitiesOnly=yes`, port 18765). Username/host TBC.
