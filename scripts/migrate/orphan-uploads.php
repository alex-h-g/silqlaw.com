<?php
/**
 * Move files in uploads/YYYY/MM/ that no media library item uses to a folder outside the website.
 * Nothing is deleted: files go to ~/silq-orphans/<same path> and can be moved back.
 * Only year/month folders are scanned (plugin folders such as elementor/ and sucuri/ are left alone).
 *
 *   wp eval-file scripts/migrate/orphan-uploads.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
$commit = in_array( 'commit', $args ?? array(), true );
$base   = wp_get_upload_dir()['basedir'];
$dest   = rtrim( getenv( 'HOME' ) ?: dirname( ABSPATH ), '/' ) . '/silq-orphans';

$used = array();
foreach ( $wpdb->get_results( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ('_wp_attached_file','_wp_attachment_metadata','_wp_attachment_backup_sizes')" ) as $r ) {
	if ( '_wp_attached_file' === $r->meta_key ) {
		$used[ $r->meta_value ] = true;
		continue;
	}
	$m   = maybe_unserialize( $r->meta_value );
	$rel = (string) get_post_meta( $r->post_id, '_wp_attached_file', true );
	$dir = dirname( $rel );
	if ( is_array( $m ) ) {
		foreach ( (array) ( $m['sizes'] ?? $m ) as $s ) {
			if ( is_array( $s ) && ! empty( $s['file'] ) ) {
				$used[ $dir . '/' . $s['file'] ] = true;
			}
		}
		if ( ! empty( $m['original_image'] ) ) {
			$used[ $dir . '/' . $m['original_image'] ] = true;
		}
	}
}

$moved = 0;
$bytes = 0;
foreach ( glob( $base . '/[12][0-9][0-9][0-9]/[01][0-9]/*' ) as $path ) {
	if ( ! is_file( $path ) ) {
		continue;
	}
	$rel = substr( $path, strlen( $base ) + 1 );
	if ( isset( $used[ $rel ] ) || str_ends_with( $rel, '.json' ) ) {
		continue;
	}
	// Safety: keep anything whose file name appears in content, Elementor data or options (linked directly, not via the media library).
	$like = '%' . $wpdb->esc_like( basename( $rel ) ) . '%';
	if ( $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$wpdb->posts} WHERE post_content LIKE %s AND post_type NOT IN ('revision') LIMIT 1", $like ) )
		|| $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$wpdb->postmeta} WHERE meta_value LIKE %s LIMIT 1", $like ) )
		|| $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$wpdb->options} WHERE option_value LIKE %s LIMIT 1", $like ) ) ) {
		continue;
	}
	$moved++;
	$bytes += filesize( $path );
	if ( $commit ) {
		wp_mkdir_p( dirname( $dest . '/' . $rel ) );
		rename( $path, $dest . '/' . $rel );
	} elseif ( $moved <= 15 ) {
		WP_CLI::log( '  ' . $rel );
	}
}
WP_CLI::log( sprintf( '%d unused files, %.1f MB %s %s', $moved, $bytes / 1048576, $commit ? 'moved to' : 'would move to', $dest ) );
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
