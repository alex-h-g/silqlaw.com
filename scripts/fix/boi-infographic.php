<?php
/**
 * Restore the "BOI Application Process" infographic in "Securing BOI Privileges in Thailand".
 * Imports content/media/boi-application-process-infographic.webp as a new media item (once) and points the article at it.
 * Dry run by default.  wp eval-file scripts/fix/boi-infographic.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$commit = in_array( 'commit', $args ?? array(), true );
$src    = dirname( __DIR__, 2 ) . '/content/media/boi-application-process-infographic.webp';
$alt    = 'The BOI application process in eight steps, from eligibility and activity classification to the Promotion Certificate';
$post   = get_page_by_path( 'securing-boi-privileges-in-thailand', OBJECT, 'post' );
if ( ! $post || ! file_exists( $src ) ) {
	WP_CLI::error( 'Article or image file not found.' );
}

global $wpdb;
$id = (int) $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE '%/boi-application-process-infographic.webp' LIMIT 1" );
WP_CLI::log( $id ? "media item exists: {$id}" : 'import media item boi-application-process-infographic.webp' );

if ( ! $commit ) {
	WP_CLI::success( 'Dry run. Add "commit" to write.' );
	return;
}
if ( ! $id ) {
	$tmp = wp_tempnam( 'boi-application-process-infographic.webp' );
	copy( $src, $tmp );
	$id = media_handle_sideload( array( 'name' => 'boi-application-process-infographic.webp', 'tmp_name' => $tmp ), $post->ID, 'The BOI Application Process' );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
}
$url = wp_get_attachment_image_url( $id, 'large' ) ?: wp_get_attachment_url( $id );

$c = $post->post_content;
$c = preg_replace( '#<!-- wp:image \{"id":9189,#', '<!-- wp:image {"id":' . (int) $id . ',', $c );
$c = preg_replace( '#<img src="[^"]*BOI-Infographic[^"]*" alt="[^"]*" class="wp-image-9189"\s*/?>#', '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/>', $c );
if ( $c === $post->post_content ) {
	WP_CLI::warning( 'Article image markup not found (already fixed?).' );
} else {
	wp_update_post( array( 'ID' => $post->ID, 'post_content' => $c ) );
	WP_CLI::log( "article {$post->ID} → {$url}" );
}
WP_CLI::success( "Committed. Media item {$id}." );
