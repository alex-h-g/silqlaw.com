<?php
/**
 * Make the site 100% SILQ Royal Co., Ltd. / silqlaw.com. Dry run by default.
 *
 * Privacy Policy (works on the page as it is now, including the cookie policy added on 5 Nov):
 *  - "SILQ Legal (Asia) Co., Ltd." → "SILQ Royal Co., Ltd."
 *  - removes the EU-US Data Privacy Framework section (it named another firm, and the framework only applies to US organisations);
 *  - names Thailand's PDPA alongside the Personal Data Protection Committee;
 *  - replaces the old Bangkok postal address with the SILQ office address below;
 *  - fixes http://127.0.0.1 links → https://silqlaw.com;
 *  - removes a leftover chatbot line ("This version includes proper line breaks…").
 * Site-wide:
 *  - Yoast organisation legal name → SILQ Royal Co., Ltd. (used in search-engine schema);
 *  - deletes leftover options from the old theme and UpdraftPlus backup history (file names only; backups themselves untouched);
 *  - deletes post revisions (old drafts history; 1,000+ rows, some carry old text). Pages and posts themselves are untouched.
 *  - reports anything still mentioning another firm anywhere in the database.
 *
 *   wp eval-file scripts/fix/firm-identity.php [commit]
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
global $wpdb;
$commit = in_array( 'commit', $args ?? array(), true );

const SILQ_LEGAL_NAME = 'SILQ Royal Co., Ltd.';
const SILQ_POSTAL     = "SILQ Royal Co., Ltd.<br>Data Protection Officer<br>2nd Floor, The Atrium, Soi 5, Pratamnak Hill,<br>Bang Lamung District, Chonburi 20150<br>Thailand";
$needles = array( 'Data Privacy Framework', 'Formichella', 'Srithawat', 'Sritawat', 'fosrlaw', 'FOSR', 'Interchange Building', 'SILQ Legal (Asia)' );

// ---------------------------------------------------------------- Privacy Policy
$page = get_page_by_path( 'privacy-policy' );
if ( ! $page ) {
	WP_CLI::error( 'Privacy Policy page not found.' );
}
$html   = $page->post_content;
$before = $html;

// Split into blocks at Gutenberg block comments so whole paragraphs can be removed.
$blocks = preg_split( '/(?=<!-- wp:(?:paragraph|list|list-item|separator|heading|group|html)\b)/', $html );
$out    = array();
$skip   = false;
foreach ( $blocks as $b ) {
	if ( str_contains( $b, 'EU-US Data Privacy Framework' ) && preg_match( '/Formichella|Srithawat|Sritawat/i', $b ) ) {
		$skip = true; // start of the DPF section
	}
	if ( $skip && str_contains( $b, 'Personal Data Protection Committee' ) ) {
		$skip = false; // first paragraph after the DPF section
	}
	if ( $skip ) {
		continue;
	}
	if ( str_contains( $b, 'This version includes proper line breaks' ) ) {
		continue;
	}
	// "Please review … relevant to our participation in the Data Privacy Framework" and the list that follows it.
	if ( str_contains( $b, 'participation in the Data Privacy Framework' ) ) {
		$skip_list = true;
		continue;
	}
	if ( ! empty( $skip_list ) ) {
		if ( preg_match( '/^<!-- wp:(list|list-item|separator)\b/', $b ) || '' === trim( wp_strip_all_tags( preg_replace( '/<!--.*?-->/s', '', $b ) ) ) ) {
			if ( str_starts_with( $b, '<!-- wp:separator' ) ) {
				$skip_list = false;
				$out[]     = $b;
			}
			continue;
		}
		$skip_list = false;
	}
	$out[] = $b;
}
$html = implode( '', $out );
// Any other stray mention of another firm inside a single paragraph: drop that paragraph.
$html = preg_replace( '#<!-- wp:paragraph -->\s*<p>(?:(?!</p>).)*(?:Formichella|Srithawat|Sritawat)(?:(?!</p>).)*</p>\s*<!-- /wp:paragraph -->\s*#is', '', $html );

$html = str_replace( 'SILQ Legal (Asia) Co., Ltd.', SILQ_LEGAL_NAME, $html );
$html = str_replace(
	'We are subject to the investigatory and enforcement powers of the Thailand Personal Data Protection Committee.',
	"We comply with Thailand's Personal Data Protection Act B.E. 2562 (2019) (PDPA) and are subject to the investigatory and enforcement powers of Thailand's Personal Data Protection Committee.",
	$html
);
$html = preg_replace( '#<p>\s*' . preg_quote( SILQ_LEGAL_NAME, '#' ) . '\s*<br>\s*Data Protection Officer<br>.*?Interchange.*?</p>#is', '<p>' . SILQ_POSTAL . '</p>', $html );
$html = str_replace( array( 'http://127.0.0.1/', 'http://127.0.0.1' ), array( 'https://silqlaw.com/', 'https://silqlaw.com' ), $html );

$removed = str_word_count( wp_strip_all_tags( $before ) ) - str_word_count( wp_strip_all_tags( $html ) );
WP_CLI::log( sprintf( 'Privacy Policy (ID %d): %s; %d words removed', $page->ID, $html === $before ? 'no change' : 'updated', $removed ) );
foreach ( $needles as $n ) {
	if ( stripos( $html, $n ) !== false ) {
		WP_CLI::warning( "Privacy Policy still contains \"{$n}\" after the fix — check by hand." );
	}
}
if ( stripos( $html, '127.0.0.1' ) !== false ) {
	WP_CLI::warning( 'Privacy Policy still contains 127.0.0.1.' );
}
if ( $commit && $html !== $before ) {
	update_post_meta( $page->ID, '_silq_privacy_before_fix', wp_slash( $before ) );
	wp_update_post( array( 'ID' => $page->ID, 'post_content' => $html ) );
}

// ---------------------------------------------------------------- Rest of the site: 127.0.0.1 links
$loop = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type <> 'revision' AND post_content LIKE '%127.0.0.1%'" );
foreach ( $loop as $p ) {
	WP_CLI::log( "127.0.0.1 link → silqlaw.com in {$p->ID} {$p->post_title}" );
	if ( $commit ) {
		$c = (string) get_post_field( 'post_content', $p->ID );
		wp_update_post( array( 'ID' => $p->ID, 'post_content' => str_replace( array( 'http://127.0.0.1/', 'http://127.0.0.1' ), array( 'https://silqlaw.com/', 'https://silqlaw.com' ), $c ) ) );
	}
}

// ---------------------------------------------------------------- Yoast, leftovers, revisions
$titles = get_option( 'wpseo_titles' );
if ( is_array( $titles ) && ( $titles['org-legal-name'] ?? '' ) !== SILQ_LEGAL_NAME ) {
	WP_CLI::log( 'Yoast organisation legal name → ' . SILQ_LEGAL_NAME );
	if ( $commit ) {
		$titles['org-legal-name'] = SILQ_LEGAL_NAME;
		update_option( 'wpseo_titles', $titles );
	}
}
foreach ( array( 'theme_mods_entrepreneurx-child', 'theme_mods_entrepreneurx', 'updraft_backup_history' ) as $opt ) {
	if ( false !== get_option( $opt, false ) ) {
		WP_CLI::log( "delete option {$opt}" );
		if ( $commit ) {
			delete_option( $opt );
		}
	}
}
$revs = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'revision'" );
WP_CLI::log( sprintf( 'delete %d post revisions', count( $revs ) ) );
if ( $commit ) {
	foreach ( $revs as $rid ) {
		wp_delete_post_revision( (int) $rid );
	}
}

// ---------------------------------------------------------------- Report anything left
$where = implode( ' OR ', array_map( fn( $n ) => $wpdb->prepare( 'X LIKE %s', '%' . $wpdb->esc_like( $n ) . '%' ), $needles ) );
$q     = array(
	'posts'    => "SELECT CONCAT(ID,' ',post_type,' ',post_status,' ',post_title) FROM {$wpdb->posts} WHERE post_type <> 'revision' AND (" . str_replace( 'X LIKE', 'CONCAT(post_title,post_content,post_excerpt) LIKE', $where ) . ')',
	'postmeta' => "SELECT CONCAT(post_id,' ',meta_key) FROM {$wpdb->postmeta} WHERE meta_key NOT IN ('_silq_privacy_before_fix','_silq_legacy_elementor_data') AND post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type <> 'revision') AND (" . str_replace( 'X LIKE', 'meta_value LIKE', $where ) . ')',
	'options'  => "SELECT option_name FROM {$wpdb->options} WHERE (" . str_replace( 'X LIKE', 'option_value LIKE', $where ) . ')',
	'termmeta' => "SELECT CONCAT(term_id,' ',meta_key) FROM {$wpdb->termmeta} WHERE (" . str_replace( 'X LIKE', 'meta_value LIKE', $where ) . ')',
	'terms'    => "SELECT CONCAT(term_id,' ',name) FROM {$wpdb->terms} WHERE (" . str_replace( 'X LIKE', 'CONCAT(name,slug) LIKE', $where ) . ')',
	'users'    => "SELECT CONCAT(ID,' ',user_login,' ',user_email) FROM {$wpdb->users} WHERE (" . str_replace( 'X LIKE', 'CONCAT(user_login,user_email,user_url,display_name) LIKE', $where ) . ')',
	'usermeta' => "SELECT CONCAT(user_id,' ',meta_key) FROM {$wpdb->usermeta} WHERE (" . str_replace( 'X LIKE', 'meta_value LIKE', $where ) . ')',
);
$left = 0;
foreach ( $q as $table => $sql ) {
	foreach ( $wpdb->get_col( $sql ) as $hit ) {
		if ( $commit || 'options' !== $table || ! in_array( $hit, array( 'theme_mods_entrepreneurx-child', 'theme_mods_entrepreneurx', 'updraft_backup_history' ), true ) ) {
			WP_CLI::log( "  still mentions another firm → {$table}: {$hit}" );
			$left++;
		}
	}
}
WP_CLI::log( $left ? "{$left} item(s) above still need a look." : 'Nothing else in the database mentions another firm.' );
WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
