<?php
/**
 * Front-end performance: strip everything the site does not need.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

// Emoji scripts and styles.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );

// Head clutter.
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );

add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) {
		return;
	}

	// Block-editor CSS is not used: posts are styled by the theme.
	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'classic-theme-styles', 'global-styles' ) as $handle ) {
		wp_dequeue_style( $handle );
	}

	// Hello Elementor parent styles are replaced entirely by assets/css/main.css.
	foreach ( array( 'hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer' ) as $handle ) {
		wp_dequeue_style( $handle );
	}

	// No jQuery on templates the theme renders itself (Elementor pages still get it if they need it).
	if ( silq_is_theme_rendered() ) {
		wp_dequeue_script( 'jquery' );
		wp_deregister_script( 'jquery' );
		// Elementor front-end assets are only needed on Elementor-built pages.
		foreach ( array( 'swiper', 'elementor-frontend', 'elementor-pro-frontend', 'elementor-icons', 'e-animations', 'e-swiper', 'elementor-post-6', 'elementor-global', 'font-awesome' ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_dequeue_script( $handle );
		}
	}

	// Formidable only where a form is present.
	if ( ! silq_page_has_form() ) {
		foreach ( array( 'formidable', 'frm_fonts', 'formidable-js' ) as $handle ) {
			wp_dequeue_style( $handle );
			wp_dequeue_script( $handle );
		}
	}
}, 100 );

/**
 * Allow-list on theme-rendered pages: only these handles may print. Everything else that plugins enqueue
 * (Elementor, widget packs, embed plugins, Formidable without a form…) is dropped.
 */
function silq_allowed_handles(): array {
	return array(
		'styles'  => array( 'silq', 'admin-bar', 'dashicons' ),
		'scripts' => array( 'silq', 'google_gtagjs', 'admin-bar', 'hoverintent-js', 'cookie-law-info', 'cookieyes' ),
	);
}

function silq_filter_queue( string $type ): void {
	if ( is_admin() || ! silq_is_theme_rendered() ) {
		return;
	}
	$deps = 'styles' === $type ? wp_styles() : wp_scripts();
	$keep = silq_allowed_handles()[ $type ];
	if ( silq_page_has_form() ) {
		$keep = array_merge( $keep, array( 'formidable', 'frm_fonts', 'formidable-js' ) );
	}
	$deps->queue = array_values( array_intersect( $deps->queue, $keep ) );
}
add_action( 'wp_print_styles', fn() => silq_filter_queue( 'styles' ), 1 );
add_action( 'wp_print_scripts', fn() => silq_filter_queue( 'scripts' ), 1 );
add_action( 'wp_print_footer_scripts', function () { silq_filter_queue( 'scripts' ); silq_filter_queue( 'styles' ); }, 1 );

/**
 * True for templates rendered by this theme in PHP (no Elementor content on the page).
 */
function silq_is_theme_rendered(): bool {
	if ( is_front_page() || is_home() || is_archive() || is_search() || is_404() ) {
		return true;
	}
	if ( is_singular() ) {
		$id = get_queried_object_id();
		return 'builder' !== get_post_meta( $id, '_elementor_edit_mode', true );
	}
	return false;
}

/**
 * True when the current page renders a Formidable form.
 */
function silq_page_has_form(): bool {
	if ( is_front_page() || ! is_singular() ) {
		return false;
	}
	$id      = get_queried_object_id();
	if ( in_array( get_page_template_slug( $id ), array( 'page-practice.php', 'page-contact.php' ), true ) ) {
		return true;
	}
	$content = (string) get_post_field( 'post_content', $id );
	$data    = get_post_meta( $id, '_elementor_data', true );
	$data    = is_string( $data ) ? $data : (string) wp_json_encode( $data );
	return str_contains( $content, '[formidable' ) || str_contains( $data, 'formidable' );
}
