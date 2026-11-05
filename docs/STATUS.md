# Status

_Last updated: 4 Nov 2026 (late night)_

## Done
- Audit, repo, docs, media originals (`media/uploads/`), local dev copy (`scripts/local/`).
- Live cleanup: dead plugins/themes, webroot junk, Updraft pruned to 2 (retain 2), DISALLOW_FILE_EDIT.
- Theme `silq` 0.1.0 (from `fosr`): prefix rename; SILQ palette (navy #001871, green #009E5E/#007A49, off-white #FAF8F4);
  horizontal logo lockup (theme asset, from media 888); header, footer, homepage (featured services, all services,
  S·I·L·Q values, news, founder, offices/contact); services index with group chips; service template; topics.
- Removed from the FOSR build: awards template, ads attribution script, EN/TH pairing (site is English-only).

- Migrations (tested on the local copy, dry run by default):
  - `scripts/migrate/services.php` — 18 Elementor service pages → clean content on the service template, hero from the
    opening widget, interactive tools kept, featured images set; real estate pages merged (12012 keeps `/real-estate/`, 11966 trashed).
  - `scripts/migrate/new-pages.php` — Construction & Property Development (`content/construction-property-development.html`, ID set at run time).
  - `scripts/migrate/cleanup.php` — trash demo pages + themo_portfolio; delete form 15; fix form 5 actions; remove Polylang; tagline.
  - `theme/silq/inc/redirects.php` — 301 map (old `/services/*`, merged real estate, retired pages).

- Pages: team, profile, contact templates; About, International Support, Thank You rewritten (`content/`);
  language pages + terms converted (lorem-ipsum testimonials removed); `scripts/migrate/pages.php` (re-runnable).
- Articles: theme author cards only (PublishPress box hidden; PublishPress stays for assigning authors).
- Cleanup also: SILQ author bio, unused Font Awesome snippet off. Orphan uploads script (moves, never deletes).
- Lighthouse desktop (local php -S, no compression, original-size images): 92–100 perf, 100 a11y, 100 best practices, 92–100 SEO.
- Tested on staging2 by Alex (4 Nov): design approved.
- `docs/LAUNCH.md` written.

- Live 4 Nov. 5 Nov fixes: privacy policy / firm identity (`scripts/fix/firm-identity.php`); topic buttons trimmed to the
  top 7 + new `/topics/` page; old Elementor Theme Builder header/footer/tag-archive templates drafted (they overrode tag pages);
  phone → +66 92 258 4527 (`scripts/fix/round2.php`); editor warning on articles without headings.
- Articles with no headings (10): 12111, 1309, 1435, 1444, 1485, 9045, 10050, 10052, 10293, 11015.

## Next
1. Copy pass on service pages: summaries/meta descriptions, FAQs + FAQPage schema on every page (none exist today).
2. News & Updates page template, article template checks, About/Team/Contact/International Support restyle.
4. Lighthouse; staging2 preview; launch plan.

## Open items for Alex
- Logo: horizontal lockup approved; all-white on navy.
- Penny's photo (12051) is a placeholder until a professional portrait is taken.
- Live homepage shows +66 2 107 1882; Contact page shows +66 89 920 4451. Theme uses the latter.
- About page offers a "Free Consultation" — keep that offer?
- Live Contact Us form: 127.0.0.1 redirect + recipients (fixed by cleanup.php at launch, or by hand now).
- A few article images have "fosr" in their file names (e.g. 2026/06/FOSR-Blog-Post-…). Visible only in image URLs.
