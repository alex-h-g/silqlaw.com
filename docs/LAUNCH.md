# Launch — silqlaw.com

Launch pushes the **theme and the page/taxonomy migrations only**. Posts, media items, users, forms entries and
settings outside the migrations are not touched, so articles published during the build stay as they are.
Every script is a dry run unless given `commit`, and is safe to re-run.

SSH alias on Alex's Mac: `silq` (see `~/.ssh/config`). Paths below are on the server.

## 0. Before
- Site Tools → Security → Backups → **Create** (`pre-silq-theme`). Wait for it to finish.
- Optional dress rehearsal: Site Tools → WordPress → Staging → re-create staging2 from live, then run steps 1–2 with
  `staging2.silqlaw.com` in place of `silqlaw.com`.

## 1. Upload (on the Mac)
```
cd ~/Downloads/silqlaw-repo && git pull -q && rsync -az --delete theme/silq/ silq:www/silqlaw.com/public_html/wp-content/themes/silq/ && rsync -az --delete scripts content silq:silq-build/ && echo uploaded
```

## 2. Run (on the server)
```
ssh silq 'bash -s' <<'EOF'
set -e
cd ~/www/silqlaw.com/public_html
wp db export ~/silqlaw-before-theme.sql --quiet
wp theme activate silq
B=~/silq-build/scripts/migrate
wp eval-file $B/services.php commit
wp eval-file $B/new-pages.php commit
wp eval-file $B/pages.php commit
wp eval-file $B/rename-media.php commit
wp eval-file $B/cleanup.php commit
wp rewrite flush
wp elementor flush-css 2>/dev/null || true
wp sg purge 2>/dev/null || true
for u in / /our-services/ /our-services/company-formation/ /our-services/real-estate/ /our-services/construction-property-development/ /about-us/ /our-team/ /contact-us/ /news-and-updates/ /international-support/ /services/visas/ /careers/; do printf '%s %s\n' "$(curl -s -o /dev/null -w '%{http_code}' https://silqlaw.com$u)" "$u"; done
EOF
```
Expected: 200 for pages, 301 for `/services/visas/` and `/careers/`.

## 3. In wp-admin
- **CookieYes**: activate `cookie-law-info`, connect the account, banner on; analytics only after consent.
- **Site Kit**: connect Analytics to GA4 property **479785685** (it is installed but not connected).
- **Speed Optimizer** (match mufasa.co.th): dynamic + file-based cache on; Memcached off; minify CSS on, combine off;
  minify JS off, combine off; defer render-blocking JS on; minify HTML on; web fonts optimisation off.
- **Formidable → Contact Us**: send one test enquiry; confirm it lands on `/thank-you/` and reaches alex@ and paul@.
- **Search Console**: resubmit `https://silqlaw.com/sitemap_index.xml`; watch Coverage for 404s over the next week.

## 4. After (optional, within a week)
- Unused uploads: `ssh silq 'cd ~/www/silqlaw.com/public_html && wp eval-file ~/silq-build/scripts/migrate/orphan-uploads.php'`
  (dry run lists them; add `commit` to move them to `~/silq-orphans/`, outside the website — nothing is deleted).
- Deactivate and delete: polylang, connect-polylang-elementor, menu-icons (already deactivated by cleanup), embedpress.
- Delete the `hello-elementor-child` theme after 30 days.

## Rollback
```
ssh silq 'cd ~/www/silqlaw.com/public_html && wp db import ~/silqlaw-before-theme.sql && wp theme activate hello-elementor-child && wp sg purge'
```
Renamed media files (8) keep their new names after a DB rollback; restore the Site Tools backup for a full file rollback.
