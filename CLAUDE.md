# Instructions for working in this repo

- This is the silqlaw.com rebuild. Read `docs/PROJECT-BRIEF.md` before changing anything.
- Reference build: `alex-h-g/fosrlaw.com` (read-only). Its theme `theme/fosr/` is the starting code; this repo's theme is `theme/silq/` (prefix `silq`).
- WordPress on SiteGround. Live silqlaw.com is the source of truth; staging2.silqlaw.com is the preview environment.
- Theme: `theme/silq/` is a child of Hello Elementor. Strict performance budget: no jQuery on the front end, one CSS file,
  one small vanilla JS file, self-hosted fonts, asset allow-list, no render-blocking third-party scripts. Target 100/100/100/100 desktop.
- Homepage, header, footer, practice pages + index, Insights, archives, articles, search, 404 are PHP. Other page bodies stay editable in Elementor.
- Formidable Forms loads only on pages that contain a form. Never on the homepage.
- Never re-upload or re-slot images. Reuse existing attachment IDs. Do not regenerate thumbnails.
- Keep every existing URL working. Any URL change needs a 301 in `scripts/redirects/`. No redirect chains.
- SILQ voice: warm, helpful, plain-spoken, SME-focused; authoritative through knowledge. Tagline "Real Advice, Real Results".
  No sales pressure, scare tactics, promises or outcome claims. "Past results do not guarantee a similar outcome." once, small, in the footer.
- All site content (articles, team, authors) is SILQ content. Never reference any other firm on the site.
- Do not lose existing functionality: Formidable forms + entries, enquiry emails, Line contact, GA4 (Site Kit), CookieYes, PublishPress Authors, redirects.
- Launch pushes theme + page/taxonomy migrations only. Never restore a DB over live.
- Migrations: same IDs, slugs, Yoast fields; old Elementor JSON kept as backup meta; dry run by default, `commit` to write.
- Never commit DB dumps, wp-config, credentials or plugin zips. Original images live in `media/uploads/` (no resized copies).
- If something is unclear or risky, stop and ask Alex. Alex signs off all pages; bulk rewrites OK.
- Alex prefers exact copy-paste terminal commands and drop-in replacement files.
