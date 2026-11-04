<?php
/**
 * Styles, scripts and font preloads.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'silq', SILQ_URI . '/assets/css/main.css', array(), SILQ_VERSION );
	wp_enqueue_script( 'silq', SILQ_URI . '/assets/js/main.js', array(), SILQ_VERSION, array( 'strategy' => 'defer', 'in_footer' => true ) );
}, 20 );

// Preload the two fonts used above the fold.
add_action( 'wp_head', function () {
	$fonts = array( 'serif-600.woff2', 'inter-400.woff2' );
	foreach ( $fonts as $font ) {
		if ( ! file_exists( SILQ_DIR . '/assets/fonts/' . $font ) ) {
			continue;
		}
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( SILQ_URI . '/assets/fonts/' . $font )
		);
	}
}, 1 );
