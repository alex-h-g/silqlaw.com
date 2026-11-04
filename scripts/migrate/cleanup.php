<?php
/**
 * Site cleanup for the SILQ launch. Dry run by default.
 *
 *  - Trash demo / retired pages (Shop, Shop + Sidebar, My Account, Contact 2, Careers) — their URLs 301 in inc/redirects.php.
 *  - Trash the orphaned EntrepreneurX `themo_portfolio` entries (their /services/ URLs 301 to /our-services/).
 *  - Delete unused Formidable forms (none has entries); keep form 5 "Contact Us".
 *  - Form 5: one confirmation (redirect to /thank-you/ on this site), enquiry emails to alex@ and paul@silqlaw.com.
 *  - Remove Polylang: language terms/relationships and options; deactivate polylang, connect-polylang-elementor, menu-icons.
 *  - Tagline → "Real Advice, Real Results".
 *
 * Run:  wp eval-file scripts/migrate/cleanup.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$commit = in_array( 'commit', $args ?? array(), true );
$do     = function ( string $what, callable $fn ) use ( $commit ) {
	WP_CLI::log( ( $commit ? '✓ ' : '· ' ) . $what );
	if ( $commit ) {
		$fn();
	}
};

// 1. Retired pages.
foreach ( array( 'shop-2', 'shop-sidebar', 'my-account', 'contact-2', 'careers' ) as $slug ) {
	$p = get_page_by_path( $slug );
	if ( $p && 'trash' !== $p->post_status ) {
		$do( "trash page {$p->ID} /{$slug}/", fn() => wp_trash_post( $p->ID ) );
	}
}

// 2. Orphaned portfolio entries (CPT no longer registered, so query the table directly).
$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'themo_portfolio' AND post_status <> 'trash'" );
foreach ( $ids as $id ) {
	$do( "trash themo_portfolio {$id}", function () use ( $wpdb, $id ) {
		update_post_meta( $id, '_wp_trash_meta_status', get_post_field( 'post_status', $id ) );
		update_post_meta( $id, '_wp_trash_meta_time', time() );
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'trash' ), array( 'ID' => $id ) );
	} );
}

// 3–4. Formidable.
if ( class_exists( 'FrmForm' ) ) {
	foreach ( $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}frm_forms WHERE is_template = 0 AND status <> 'trash'" ) as $form ) {
		if ( 5 === (int) $form->id ) {
			continue;
		}
		$entries = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}frm_items WHERE form_id = %d", $form->id ) );
		if ( $entries ) {
			WP_CLI::warning( "form {$form->id} '{$form->name}' has {$entries} entries — kept" );
			continue;
		}
		$do( "delete form {$form->id} '{$form->name}'", fn() => FrmForm::destroy( $form->id ) );
	}
	foreach ( get_posts( array( 'post_type' => 'frm_form_actions', 'menu_order' => 5, 'posts_per_page' => -1, 'post_status' => 'any' ) ) as $a ) {
		$c = json_decode( $a->post_content, true ) ?: array();
		if ( 'email' === $a->post_excerpt ) {
			$do( "form 5 email action {$a->ID} → alex@silqlaw.com, paul@silqlaw.com", function () use ( $a, $c ) {
				$c['email_to'] = 'alex@silqlaw.com, paul@silqlaw.com';
				wp_update_post( array( 'ID' => $a->ID, 'post_content' => wp_slash( wp_json_encode( $c ) ) ) );
			} );
		} elseif ( 'on_submit' === $a->post_excerpt ) {
			if ( 'redirect' === ( $c['success_action'] ?? '' ) ) {
				$do( "form 5 confirmation {$a->ID} → redirect to /thank-you/", function () use ( $a, $c ) {
					$c['success_url'] = home_url( '/thank-you/' );
					wp_update_post( array( 'ID' => $a->ID, 'post_content' => wp_slash( wp_json_encode( $c ) ) ) );
				} );
			} else {
				$do( "form 5 duplicate confirmation {$a->ID} (message) → delete", fn() => wp_delete_post( $a->ID, true ) );
			}
		}
	}
}

// 5. Polylang.
$taxes = array( 'language', 'term_language', 'post_translations', 'term_translations' );
$terms = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ('" . implode( "','", $taxes ) . "')" );
$do( "remove Polylang data ({$terms} language/translation terms + relationships + options)", function () use ( $wpdb, $taxes ) {
	$in  = "'" . implode( "','", $taxes ) . "'";
	$tts = $wpdb->get_col( "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($in)" );
	$tid = $wpdb->get_col( "SELECT term_id FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($in)" );
	if ( $tts ) {
		$wpdb->query( "DELETE FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN (" . implode( ',', array_map( 'intval', $tts ) ) . ')' );
		$wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id IN (" . implode( ',', array_map( 'intval', $tts ) ) . ')' );
	}
	if ( $tid ) {
		$ids = implode( ',', array_map( 'intval', $tid ) );
		$wpdb->query( "DELETE FROM {$wpdb->terms} WHERE term_id IN ($ids) AND term_id NOT IN (SELECT term_id FROM {$wpdb->term_taxonomy})" );
		$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE term_id IN ($ids) AND term_id NOT IN (SELECT term_id FROM {$wpdb->term_taxonomy})" );
	}
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ('polylang','polylang_wpml_strings','widget_polylang','pll_dismissed_notices','polylang_licenses') OR option_name LIKE '\\_transient\\_pll\\_%' OR option_name LIKE 'pll\\_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\\_pll\\_%'" );
} );
foreach ( array( 'polylang/polylang.php', 'connect-polylang-elementor/connect-polylang-elementor.php', 'menu-icons/menu-icons.php' ) as $plugin ) {
	if ( is_plugin_active( $plugin ) ) {
		$do( "deactivate {$plugin}", fn() => deactivate_plugins( $plugin, true ) );
	}
}

// 5b. SILQ author bio (PublishPress Authors): replace the pasted Elementor markup with a short plain bio.
$silq_author = get_term_by( 'slug', 'silq', 'author' );
if ( $silq_author ) {
	$do( 'author SILQ → plain bio', fn() => update_term_meta( $silq_author->term_id, 'description', 'Articles by the lawyers of SILQ Law on changes in Thai law and what they mean for businesses, investors and families in Thailand.' ) );
}

// 6. Tagline.
if ( 'Real Advice, Real Results' !== get_option( 'blogdescription' ) ) {
	$do( 'tagline → Real Advice, Real Results', fn() => update_option( 'blogdescription', 'Real Advice, Real Results' ) );
}

if ( $commit ) {
	flush_rewrite_rules( false );
}
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
