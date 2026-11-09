<?php
/**
 * SILQ child theme bootstrap.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

define( 'SILQ_VERSION', '0.1.5' );
define( 'SILQ_DIR', get_stylesheet_directory() );
define( 'SILQ_URI', get_stylesheet_directory_uri() );

require_once SILQ_DIR . '/inc/setup.php';
require_once SILQ_DIR . '/inc/performance.php';
require_once SILQ_DIR . '/inc/assets.php';
require_once SILQ_DIR . '/inc/template-tags.php';
require_once SILQ_DIR . '/inc/practice.php';
require_once SILQ_DIR . '/inc/insights.php';
require_once SILQ_DIR . '/inc/redirects.php';
