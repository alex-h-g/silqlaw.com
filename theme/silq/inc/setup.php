<?php
/**
 * Theme supports, menus, image sizes.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary navigation', 'silq' ),
			'footer'  => __( 'Footer navigation', 'silq' ),
			'legal'   => __( 'Legal links', 'silq' ),
		)
	);

	// Card and hero sizes, cropped from existing uploads (regenerate thumbnails once on launch).
	add_image_size( 'silq-card', 720, 450, true );
	add_image_size( 'silq-hero', 1920, 1080, true );
	add_image_size( 'silq-portrait', 600, 750, true );
} );

// Comments are not used anywhere on the site.
add_action( 'init', function () {
	foreach ( get_post_types() as $type ) {
		if ( post_type_supports( $type, 'comments' ) ) {
			remove_post_type_support( $type, 'comments' );
			remove_post_type_support( $type, 'trackbacks' );
		}
	}
} );
add_filter( 'comments_open', '__return_false', 20 );
add_filter( 'pings_open', '__return_false', 20 );
add_filter( 'comments_array', '__return_empty_array', 10 );
