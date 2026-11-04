# Audit — live silqlaw.com (4 Nov 2026)

Access: SSH alias `silq` on Alex's Mac (SiteGround, key `mb-neo-silq`). Paths `~/www/silqlaw.com/public_html` (12 GB)
and `~/www/staging2.silqlaw.com/public_html` (6.9 GB). WP-CLI available, PHP 8.2.
DB prefix `uam_`, 57 MB. Permalinks `/%year%/%postname%/` (keep).
Theme: `hello-elementor-child` 1.0.0 on `hello-elementor` 3.5.1. EntrepreneurX parent + child still installed (inactive).

## Content
- post 79 publish · page 41 publish / 15 draft · attachment 251 · revision 1,058
- themo_portfolio 12 publish / 2 draft (CPT no longer registered — orphaned since the theme switch)
- team_builder / team_layout / team_member: leftovers from a removed team plugin
- frm_form_actions 6, elementor_library 9, ppma_boxes 6, ppmacf_field 5, custom_css 5, wp_navigation 1

## Disk (12 GB)
| Path | Size | Note |
|---|---|---|
| wp-content/updraft | 7.5 GB | UpdraftPlus backups stored on the server |
| wp-content/uploads | 3.5 GB | 2026 1.8 GB + 2025 1.0 GB for only 251 attachments — check for orphans |
| uploads/piwp-update-guard-backups | 381 MB | plugin-update backups |
| uploads/siteground-optimizer-assets | 236 MB | SG minify cache |
| uploads/images-import | 49 MB | import leftovers |
| wp-content/plugins | 408 MB | |
| `*-old` dirs, object-cache.php-old | ~60 MB | leftovers |

Webroot junk: `Default.html`, `old-site/`, `shit-black/`, `set_featured_images.py`, `php_errorlog`, `favicon-16-tmp.png`.

## Plugins
Active: sg-ai-studio, akismet, better-search-replace, code-snippets, connect-polylang-elementor, disable-comments,
duplicate-page, elementor 4.3.6, elementor-pro 4.3.3, embedpress, enable-media-replace, formidable 7.0, hello (Dolly),
menu-icons, phpinfo-wp, polylang 3.9.1, publishpress-authors, simple-page-ordering, google-site-kit, sg-cachepress,
sucuri-scanner, updraftplus, wp-mail-smtp, wordpress-seo.

Inactive: automatic-translations-for-polylang, booked, cookie-law-info, envato-market, imagemagick-engine,
image-optimization, kirki, mystickyelements, option-tree, th-widget-pack, password-protected, sg-security,
siteground-migrator, widget-logic, wordpress-importer.

Drop-ins: advanced-cache.php, maintenance.php, object-cache.php.

## Flags
- **phpinfo-wp active**: exposes server configuration. Remove now.
- Polylang, connect-polylang-elementor and menu-icons (flags) go with the Polylang removal.
- cookie-law-info inactive → configure CookieYes.
- Site Kit active but GA not connected → find the tag source.
