#!/usr/bin/env bash
# Rebuild the local silqlaw.com dev copy in a fresh session.
# Needs (not in Git): the DB dump at $DUMP (`wp db export` from SiteGround), the plugins folder at $PLUGINS
# (Elementor Pro is licensed) and the child theme folder at $CHILD.
# Usage: DUMP=/path/silq-db.sql PLUGINS=/path/plugins CHILD=/path/hello-elementor-child bash scripts/local/setup.sh
set -euo pipefail
REPO="$(cd "$(dirname "$0")/../.." && pwd)"
S=/root/silq/site
: "${DUMP:?set DUMP}"; : "${PLUGINS:?set PLUGINS}"; : "${CHILD:?set CHILD}"

command -v mysqld >/dev/null || { apt-get update -qq && apt-get install -y -qq mariadb-server; }
mysqladmin ping >/dev/null 2>&1 || { (setsid mysqld_safe --user=root </dev/null >/tmp/mysqld.log 2>&1 &); for i in $(seq 30); do mysqladmin ping >/dev/null 2>&1 && break; sleep 1; done; }
mysql -e "drop database if exists silq; create database silq; create user if not exists 'wp'@'localhost' identified by 'wp'; grant all on silq.* to 'wp'@'localhost';"
sed -e "s/utf8mb4_0900_ai_ci/utf8mb4_unicode_520_ci/g" "$DUMP" | mysql silq
mysql silq -e "update uam_options set option_value='http://localhost:8080' where option_name in ('home','siteurl');"

# WordPress core 7.1.4 (matches live db_version 61833) + Hello Elementor parent
[ -f "$S/wp-includes/version.php" ] || git clone -q --depth 1 --branch 7.1.4 https://github.com/WordPress/WordPress "$S"
rm -rf "$S/.git" "$S/wp-content/themes/"* "$S/wp-content/plugins" "$S/wp-content/uploads"
git clone -q --depth 1 https://github.com/elementor/hello-theme "$S/wp-content/themes/hello-elementor" && rm -rf "$S/wp-content/themes/hello-elementor/.git"
cp -r "$CHILD" "$S/wp-content/themes/hello-elementor-child"
[ -d "$REPO/theme/silq" ] && ln -sfn "$REPO/theme/silq" "$S/wp-content/themes/silq"
cp -r "$PLUGINS" "$S/wp-content/plugins"
mkdir -p "$S/wp-content/mu-plugins" && cp "$REPO/scripts/local/local-dev.php" "$S/wp-content/mu-plugins/"
if [ -d "$REPO/media/uploads" ]; then cp -r "$REPO/media/uploads" "$S/wp-content/uploads"; else mkdir -p "$S/wp-content/uploads"; fi

cat > "$S/wp-config.php" <<'CFG'
<?php
define('DB_NAME','silq'); define('DB_USER','wp'); define('DB_PASSWORD','wp'); define('DB_HOST','localhost:/run/mysqld/mysqld.sock');
define('DB_CHARSET','utf8mb4'); define('DB_COLLATE',''); $table_prefix='uam_';
define('WP_HOME','http://localhost:8080'); define('WP_SITEURL','http://localhost:8080');
define('WP_DEBUG',true); define('WP_DEBUG_LOG','/root/silq/debug.log'); define('WP_DEBUG_DISPLAY',false);
define('DISABLE_WP_CRON',true); define('WP_ENVIRONMENT_TYPE','local'); define('AUTOMATIC_UPDATER_DISABLED',true);
foreach (['AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY','AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT'] as $k) define($k,'local-'.$k);
if(!defined('ABSPATH')) define('ABSPATH',__DIR__.'/'); require_once ABSPATH.'wp-settings.php';
CFG

pkill -f 'php.*-S 127.0.0.1:8080' || true
(cd "$S" && PHP_CLI_SERVER_WORKERS=4 nohup php -d memory_limit=512M -S 127.0.0.1:8080 -t "$S" "$REPO/scripts/local/router.php" >/root/silq/php.log 2>&1 &)
echo "Local site: http://localhost:8080"
