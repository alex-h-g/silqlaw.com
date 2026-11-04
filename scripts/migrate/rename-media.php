<?php
/**
 * Rename media files in place (same attachment IDs). Renames the original and every resized copy on disk,
 * updates attachment meta and title, and replaces the old URLs in post content and Elementor data.
 * Dry run by default.  wp eval-file scripts/migrate/rename-media.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
$commit = in_array( 'commit', $args ?? array(), true );

/** Attachment ID => new base name (no extension). */
$map = array(
	10762 => 'blog-post-image-format-4',
	10763 => 'blog-post-image-format',
	10800 => 'parents-leave-update',
	10801 => 'parents-leave-update-2',
	10814 => 'thai-visa-article',
	10815 => 'thai-visa-article-2',
	11753 => 'parents-leave-update-3',
	11755 => 'thai-visa-article-3',
);

$uploads = wp_get_upload_dir();
foreach ( $map as $id => $new_base ) {
	$rel  = (string) get_post_meta( $id, '_wp_attached_file', true );
	$meta = wp_get_attachment_metadata( $id );
	if ( ! $rel ) {
		WP_CLI::warning( "{$id}: no file" );
		continue;
	}
	$dir      = dirname( $rel );
	$ext      = pathinfo( $rel, PATHINFO_EXTENSION );
	$old_base = pathinfo( $rel, PATHINFO_FILENAME );
	$pairs    = array( basename( $rel ) => $new_base . '.' . $ext );
	foreach ( (array) ( $meta['sizes'] ?? array() ) as $s ) {
		$pairs[ $s['file'] ] = $new_base . substr( $s['file'], strlen( $old_base ) );
	}
	if ( ! empty( $meta['original_image'] ) ) {
		$pairs[ $meta['original_image'] ] = $new_base . substr( $meta['original_image'], strlen( $old_base ) );
	}
	WP_CLI::log( sprintf( '%-6d %s → %s/%s (%d files)', $id, $rel, $dir, $pairs[ basename( $rel ) ], count( $pairs ) ) );
	if ( ! $commit ) {
		continue;
	}
	foreach ( $pairs as $from => $to ) {
		$a = $uploads['basedir'] . '/' . $dir . '/' . $from;
		$b = $uploads['basedir'] . '/' . $dir . '/' . $to;
		if ( file_exists( $a ) && ! file_exists( $b ) ) {
			rename( $a, $b );
		}
		$old_url = $uploads['baseurl'] . '/' . $dir . '/' . $from;
		$new_url = $uploads['baseurl'] . '/' . $dir . '/' . $to;
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, %s, %s) WHERE post_content LIKE %s", $old_url, $new_url, '%' . $wpdb->esc_like( $old_url ) . '%' ) );
		// Elementor stores URLs JSON-escaped (\/).
		$eo = str_replace( '/', '\\/', $old_url );
		$en = str_replace( '/', '\\/', $new_url );
		$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, %s, %s) WHERE meta_key IN ('_elementor_data','_silq_legacy_elementor_data') AND meta_value LIKE %s", $eo, $en, '%' . $wpdb->esc_like( $eo ) . '%' ) );
	}
	update_post_meta( $id, '_wp_attached_file', $dir . '/' . $pairs[ basename( $rel ) ] );
	$meta['file'] = $dir . '/' . $pairs[ basename( $rel ) ];
	foreach ( (array) ( $meta['sizes'] ?? array() ) as $k => $s ) {
		$meta['sizes'][ $k ]['file'] = $pairs[ $s['file'] ];
	}
	if ( ! empty( $meta['original_image'] ) ) {
		$meta['original_image'] = $pairs[ $meta['original_image'] ];
	}
	wp_update_attachment_metadata( $id, $meta );
	$wpdb->update( $wpdb->posts, array( 'post_title' => ucfirst( str_replace( '-', ' ', $new_base ) ), 'guid' => $uploads['baseurl'] . '/' . $meta['file'] ), array( 'ID' => $id ) );
	clean_post_cache( $id );
}
if ( $commit && class_exists( '\Elementor\Plugin' ) ) {
	\Elementor\Plugin::instance()->files_manager->clear_cache();
}
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
