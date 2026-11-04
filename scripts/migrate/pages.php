<?php
/**
 * Move the remaining Elementor pages onto theme templates / clean content. Same IDs and slugs; Yoast fields untouched.
 * The old Elementor JSON is kept as `_silq_legacy_elementor_data`. Dry run by default.
 *
 *   wp eval-file scripts/migrate/pages.php [commit] [include-paul]
 *
 * `include-paul`: keep the paragraphs that introduce Dr Paul Crosio on the language pages (off by default).
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
$commit       = in_array( 'commit', $args ?? array(), true );
$include_paul = in_array( 'include-paul', $args ?? array(), true );
$dir          = dirname( __DIR__, 2 ) . '/content';

$allowed = array(
	'p' => array(), 'br' => array(), 'strong' => array(), 'b' => array(), 'em' => array(), 'i' => array(),
	'ul' => array(), 'ol' => array(), 'li' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(),
	'a' => array( 'href' => array() ), 'blockquote' => array(),
	'table' => array(), 'thead' => array(), 'tbody' => array(), 'tr' => array(), 'th' => array( 'colspan' => array() ), 'td' => array( 'colspan' => array() ),
);
$clean = function ( string $html ) use ( $allowed ): string {
	$html = preg_replace( '#</?div[^>]*>#i', "\n\n", $html );
	$html = wpautop( wp_kses( $html, $allowed ) );
	// Paragraph breaks lost in the source ("…Thailandia.Il nostro…"): a sentence end glued to the next sentence.
	$html = preg_replace( '/(\p{Ll}[.!?])(\p{Lu})/u', "$1</p>\n<p>$2", $html );
	$html = preg_replace( '#<p>(\s|&nbsp;|<br\s*/?>)*</p>#i', '', $html );
	return trim( preg_replace( "#\n{3,}#", "\n\n", $html ) );
};

/** Elementor widgets → content (headings + text). Lorem-ipsum testimonials, buttons and forms dropped. */
$convert = function ( int $id, bool $skip_first_heading, callable $keep ) use ( $clean ): array {
	$raw   = get_post_meta( $id, '_elementor_data', true );
	$data  = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	$out   = '';
	$first = array();
	$walk  = function ( $nodes ) use ( &$walk, &$out, &$first, $clean, $skip_first_heading, $keep ) {
		foreach ( (array) $nodes as $n ) {
			if ( ! is_array( $n ) ) {
				continue;
			}
			if ( ( $n['elType'] ?? '' ) !== 'widget' ) {
				$walk( $n['elements'] ?? array() );
				continue;
			}
			$s = is_array( $n['settings'] ?? null ) ? $n['settings'] : array();
			switch ( $n['widgetType'] ) {
				case 'heading':
					$t = trim( wp_strip_all_tags( (string) ( $s['title'] ?? '' ) ) );
					$t = trim( preg_replace( '/\s*-\s*SILQ( Law)?$/u', '', $t ) );
					if ( '' === $t ) {
						break;
					}
					if ( $skip_first_heading && count( $first ) < 2 ) {
						$first[] = $t;
						break;
					}
					if ( $keep( $t ) ) {
						$out .= "\n<h2>" . esc_html( $t ) . "</h2>\n";
					}
					break;
				case 'text-editor':
					$h = (string) ( $s['editor'] ?? '' );
					if ( preg_match( '/Donec facilisis|In vel dolor ut neque|Lorem ipsum/i', $h ) ) {
						break;
					}
					if ( $skip_first_heading && 1 === count( $first ) ) {
						$first[] = trim( wp_strip_all_tags( $h ) );
						break;
					}
					if ( $keep( $h ) ) {
						$out .= "\n" . $clean( $h ) . "\n";
					}
					break;
			}
		}
	};
	$walk( $data );
	$out = preg_replace( '#\s*<h2>[^<]*</h2>\s*$#u', '', trim( $out ) ); // closing CTA heading whose button was dropped
	return array( $out, $first, $raw );
};

$results = array();
$apply   = function ( int $id, array $changes, $raw = null ) use ( $commit ) {
	$p = get_post( $id );
	if ( ! $p ) {
		WP_CLI::warning( "{$id}: not found" );
		return;
	}
	WP_CLI::log( sprintf( '%-6d %-40s tpl:%-22s words:%s', $id, $p->post_name, $changes['template'] ?? '(default)', isset( $changes['content'] ) ? str_word_count( wp_strip_all_tags( $changes['content'] ) ) : '-' ) );
	if ( ! $commit ) {
		return;
	}
	$raw = $raw ?? get_post_meta( $id, '_elementor_data', true );
	if ( $raw && ! get_post_meta( $id, '_silq_legacy_elementor_data', true ) ) {
		update_post_meta( $id, '_silq_legacy_elementor_data', wp_slash( is_array( $raw ) ? wp_json_encode( $raw ) : (string) $raw ) );
	}
	delete_post_meta( $id, '_elementor_edit_mode' );
	delete_post_meta( $id, '_elementor_data' );
	delete_post_meta( $id, '_elementor_css' );
	update_post_meta( $id, '_wp_page_template', $changes['template'] ?? 'default' );
	foreach ( (array) ( $changes['meta'] ?? array() ) as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	if ( isset( $changes['content'] ) ) {
		wp_update_post( array( 'ID' => $id, 'post_content' => $changes['content'] ) );
	}
	if ( ! empty( $changes['thumb'] ) ) {
		set_post_thumbnail( $id, (int) $changes['thumb'] );
	}
};
$file = fn( string $f ) => (string) file_get_contents( $dir . '/' . $f );

// Templates rendered entirely by the theme.
$apply( 50, array( 'template' => 'page-insights.php', 'content' => '' ) );
$apply( 243, array( 'template' => 'page-team.php', 'content' => '' ) );
$apply( 62, array( 'template' => 'page-contact.php', 'content' => '' ) );

// Authored content.
$apply( 221, array( 'content' => $file( 'about-us.html' ), 'meta' => array( '_silq_lead' => 'Real advice, real results: Thai law explained plainly, and handled properly.' ) ) );
$apply( 1210, array( 'content' => $file( 'international-support.html' ), 'meta' => array( '_silq_lead' => 'Legal help in your language: Italian, German, Thai and Eastern European languages, as well as English.' ) ) );
$apply( 166, array( 'content' => $file( 'thank-you.html' ), 'meta' => array( '_silq_lead' => 'Thank you for getting in touch.' ) ) );

// Team profiles: bio text from Elementor, photo as featured image.
foreach ( silq_team() as $m ) {
	$p = get_page_by_path( 'our-team/' . $m['slug'] );
	if ( ! $p ) {
		WP_CLI::warning( "team page {$m['slug']} not found" );
		continue;
	}
	list( $content, , $raw ) = $convert( $p->ID, true, fn() => true );
	$content = preg_replace( '#^\s*(<p>|<h[2-4]>)\s*(<strong>)?\s*Profile\s*(</strong>)?\s*(</p>|</h[2-4]>)\s*#i', '', $content );
	$apply( $p->ID, array( 'template' => 'page-person.php', 'content' => $content, 'thumb' => $m['img'] ), $raw );
}

// Language pages under International Support.
$langs = array( 979 => 'it', 1185 => 'uk', 1216 => 'th', 1258 => 'ru' );
$keep  = fn( string $t ) => $include_paul || ! preg_match( '/Crosio|Крозіо|Кросио|ครอสซิโอ|Managing Partner|керуючого партнера|управляющего партн|หุ้นส่วนผู้จัดการ|Managing Partner con/u', $t );
foreach ( $langs as $id => $lang ) {
	list( $content, $first, $raw ) = $convert( $id, true, $keep );
	$apply(
		$id,
		array(
			'content' => $content,
			'meta'    => array( '_silq_lang' => $lang, '_silq_headline' => $first[0] ?? '', '_silq_lead' => $first[1] ?? '' ),
		),
		$raw
	);
}

// Terms of engagement: text + link to the PDF (replaces the PDF embed plugin).
$terms = get_page_by_path( 'about-us/terms-and-conditions-engagement' );
if ( $terms ) {
	list( $content, $first, $raw ) = $convert( $terms->ID, false, fn() => true );
	$content  = preg_replace( '#^<h2>Terms and Conditions of Engagement</h2>\s*#', '', $content );
	$pdf      = wp_get_attachment_url( 12074 );
	$content .= $pdf ? "\n<p><a href=\"" . esc_url( $pdf ) . "\">Download the terms and conditions (PDF)</a></p>" : '';
	$apply( $terms->ID, array( 'content' => $content, 'meta' => array( '_silq_headline' => 'Terms and Conditions of Engagement' ) ), $raw );
}

WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
