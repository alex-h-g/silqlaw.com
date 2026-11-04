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

// Language pages (Italian, Ukrainian, Russian, Thai) declare their language for browsers, screen readers and search engines.
add_filter( 'language_attributes', function ( $output ) {
	if ( is_singular() ) {
		$lang = get_post_meta( get_queried_object_id(), '_silq_lang', true );
		if ( $lang && preg_match( '/^[a-z]{2}$/', $lang ) ) {
			$output = preg_replace( '/lang="[^"]*"/', 'lang="' . $lang . '"', $output );
		}
	}
	return $output;
} );

// Meta description fallback: when a page has no Yoast description, use its standfirst/lead, else the first words of the content.
add_filter( 'wpseo_metadesc', function ( $desc ) {
	if ( $desc || ! is_singular() ) {
		return $desc;
	}
	$id    = get_queried_object_id();
	$fixed = array(
		'page-contact.php'        => 'Contact SILQ Law by phone, WhatsApp, Line or email, or send a message. Offices on the Eastern Seaboard (Pratamnak Hill) and in Phuket. We reply within one business day.',
		'page-team.php'           => 'Meet the lawyers of SILQ Law: Thai and international lawyers working in English and Thai, with offices on the Eastern Seaboard and in Phuket.',
		'page-insights.php'       => 'Plain-English news and updates on Thai law for business owners, investors and people living in Thailand, from the lawyers of SILQ Law.',
		'page-practice-index.php' => 'Legal help in Thailand for business owners, investors and families: company formation, BOI, property, construction, visas, licences and disputes.',
	);
	if ( isset( $fixed[ get_page_template_slug( $id ) ] ) ) {
		return $fixed[ get_page_template_slug( $id ) ];
	}
	$text = get_post_meta( $id, '_silq_standfirst', true ) ?: get_post_meta( $id, '_silq_lead', true ) ?: ( has_excerpt( $id ) ? get_the_excerpt( $id ) : '' );
	if ( ! $text ) {
		$text = wp_trim_words( wp_strip_all_tags( strip_shortcodes( (string) get_post_field( 'post_content', $id ) ) ), 28, '…' );
	}
	return $text ? wp_html_excerpt( $text, 158, '…' ) : $desc;
} );
