<?php
/**
 * Convert the August 2026 Elementor service pages (children of /our-services/) to clean content on the theme's
 * service template. Same IDs, same slugs, same Yoast fields.
 *
 * Per page:
 *  - the opening HTML widget (label / h1 / lead / CTA) becomes the hero: `_silq_eyebrow`, `_silq_headline`, `_silq_standfirst`;
 *  - the remaining HTML widgets are converted to plain semantic HTML (inline styles, cards, emoji icons and #anchor CTAs dropped);
 *  - widgets with <script> (interactive tools) are kept verbatim as a Custom HTML block;
 *  - the closing "Get in touch" widget and the form widget are dropped (the template renders the enquiry form once);
 *  - the Elementor JSON is kept as `_silq_legacy_elementor_data`; Elementor edit mode is switched off;
 *  - template → page-practice.php; featured image set from silq_practices() if missing.
 * Parent /our-services/ → page-practice-index.php.
 * Real estate: 12012 (full) is kept and takes the slug `real-estate`; 11966 (thin) is trashed.
 *
 * Run:  wp eval-file scripts/migrate/services.php          (dry run)
 *       wp eval-file scripts/migrate/services.php commit   (writes)
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}

$commit = in_array( 'commit', $args ?? array(), true );
$parent = get_page_by_path( 'our-services' );
if ( ! $parent ) {
	WP_CLI::error( 'Parent page /our-services/ not found.' );
}

const SILQ_MIG_KEEP_RE   = 12012; // Real Estate & Property (full)
const SILQ_MIG_TRASH_RE  = 11966; // Real Estate & Construction (thin)

/** All widgets of an Elementor tree, in order. */
function silq_mig_widgets( $nodes ): array {
	$out = array();
	foreach ( (array) $nodes as $n ) {
		if ( ! is_array( $n ) ) {
			continue;
		}
		if ( ( $n['elType'] ?? '' ) === 'widget' ) {
			$out[] = $n;
		}
		$out = array_merge( $out, silq_mig_widgets( $n['elements'] ?? array() ) );
	}
	return $out;
}

function silq_mig_dom( string $html ): DOMDocument {
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="utf-8"?><div id="silq-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	return $doc;
}

function silq_mig_text( DOMNode $n ): string {
	return trim( preg_replace( '/\s+/u', ' ', $n->textContent ) );
}

/** True if a string is only emoji/pictographs/punctuation. */
function silq_mig_is_icon( string $t ): bool {
	return '' !== $t && ! preg_match( '/[\p{L}\p{N}]/u', $t );
}

/**
 * Element → clean HTML. Blocks kept: h2–h4, p, ul, ol, li, table parts, blockquote, details/summary, strong/em/a/br.
 * Wrapper divs are unwrapped; a div holding only inline content becomes a <p>.
 */
function silq_mig_clean_node( DOMNode $node, int $h_shift = 0 ): string {
	if ( XML_TEXT_NODE === $node->nodeType ) {
		return esc_html( preg_replace( '/\s+/u', ' ', $node->nodeValue ) );
	}
	if ( XML_ELEMENT_NODE !== $node->nodeType ) {
		return '';
	}
	$tag = strtolower( $node->nodeName );
	if ( in_array( $tag, array( 'style', 'script', 'svg', 'img', 'button', 'form', 'input', 'iframe' ), true ) ) {
		return '';
	}
	$inner = '';
	foreach ( $node->childNodes as $c ) {
		$inner .= silq_mig_clean_node( $c, $h_shift );
	}
	$text = silq_mig_text( $node );

	switch ( $tag ) {
		case 'a':
			$href = (string) $node->getAttribute( 'href' );
			if ( '' === $href || str_starts_with( $href, '#' ) ) {
				return ''; // in-page CTA buttons ("Speak to an adviser").
			}
			return '<a href="' . esc_url( $href ) . '">' . trim( $inner ) . '</a>';
		case 'strong':
		case 'b':
			return '' === trim( $inner ) ? '' : '<strong>' . trim( $inner ) . '</strong>';
		case 'em':
		case 'i':
			return '' === trim( $inner ) ? '' : '<em>' . trim( $inner ) . '</em>';
		case 'br':
			return '<br>';
		case 'span':
		case 'small':
		case 'sup':
		case 'sub':
			return $inner;
		case 'h1':
		case 'h2':
		case 'h3':
		case 'h4':
		case 'h5':
		case 'h6':
			$lvl = min( 4, max( 2, (int) substr( $tag, 1 ) + $h_shift ) );
			return '' === $text ? '' : "\n<h{$lvl}>" . trim( $inner ) . "</h{$lvl}>\n";
		case 'p':
			if ( '' === $text || silq_mig_is_icon( $text ) ) {
				return '';
			}
			return "\n<p>" . trim( $inner ) . "</p>\n";
		case 'ul':
		case 'ol':
		case 'table':
		case 'thead':
		case 'tbody':
		case 'tr':
		case 'blockquote':
		case 'details':
			return '' === $text ? '' : "\n<{$tag}>" . $inner . "</{$tag}>\n";
		case 'li':
		case 'summary':
			return '' === $text ? '' : "<{$tag}>" . trim( $inner ) . "</{$tag}>\n";
		case 'th':
		case 'td':
			$span = '';
			foreach ( array( 'colspan', 'rowspan' ) as $a ) {
				if ( $node->getAttribute( $a ) ) {
					$span .= ' ' . $a . '="' . (int) $node->getAttribute( $a ) . '"';
				}
			}
			return "<{$tag}{$span}>" . trim( $inner ) . "</{$tag}>";
		default: // div, section, article, header, etc.
			if ( '' === $text || silq_mig_is_icon( $text ) ) {
				return '';
			}
			$has_block = false;
			foreach ( $node->childNodes as $c ) {
				if ( XML_ELEMENT_NODE === $c->nodeType && in_array( strtolower( $c->nodeName ), array( 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'table', 'section', 'blockquote', 'details', 'article', 'header' ), true ) ) {
					$has_block = true;
					break;
				}
			}
			return $has_block ? $inner : "\n<p>" . trim( $inner ) . "</p>\n";
	}
}

function silq_mig_tidy( string $html ): string {
	$html = preg_replace( '#<p>\s*(<br>\s*)*</p>#', '', $html );
	$html = preg_replace( '#(<br>\s*){2,}#', '<br>', $html );
	$html = preg_replace( '#[ \t]+#', ' ', $html );
	// Card badges ("Non-B") that sat beside a card title: fold into the heading.
	$html = preg_replace( '#<p>([^<.:]{1,24})</p>\s*<h3>(.*?)</h3>#u', '<h3>$2 ($1)</h3>', $html );
	// Short labels introducing a list ("Financial Requirements"): make them small headings.
	$html = preg_replace( '#<p>([^<.:!?]{3,48})</p>(\s*<ul>)#u', '<h4>$1</h4>$2', $html );
	$html = preg_replace( "#\n{3,}#", "\n\n", $html );
	return trim( $html );
}

/** Opening widget → eyebrow / headline / standfirst. */
function silq_mig_hero( string $html ): array {
	$doc  = silq_mig_dom( preg_replace( '#<br\s*/?>#i', ' ', $html ) );
	$h1   = $doc->getElementsByTagName( 'h1' )->item( 0 );
	$hero = array( 'eyebrow' => '', 'headline' => '', 'standfirst' => '' );
	if ( ! $h1 ) {
		return $hero;
	}
	$hero['headline'] = silq_mig_text( $h1 );
	// Order-based: first <p> before the h1 = eyebrow, first <p> after = standfirst.
	$seen_h1 = false;
	$walk    = function ( DOMNode $n ) use ( &$walk, &$seen_h1, &$hero, $h1 ) {
		foreach ( $n->childNodes as $c ) {
			if ( $c->isSameNode( $h1 ) ) {
				$seen_h1 = true;
				continue;
			}
			if ( XML_ELEMENT_NODE === $c->nodeType && 'p' === strtolower( $c->nodeName ) ) {
				$t = silq_mig_text( $c );
				if ( '' !== $t ) {
					if ( ! $seen_h1 && '' === $hero['eyebrow'] ) {
						$hero['eyebrow'] = $t;
					} elseif ( $seen_h1 && '' === $hero['standfirst'] ) {
						$hero['standfirst'] = $t;
					}
				}
			} elseif ( XML_ELEMENT_NODE === $c->nodeType ) {
				$walk( $c );
			}
		}
	};
	$walk( $doc->documentElement );
	return $hero;
}

function silq_mig_is_closing_cta( string $html ): bool {
	$t = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $html ) ) );
	return (bool) preg_match( '/^(Get in Touch|Talk to Our|Speak (with|to) Our|Ready to discuss)/i', $t ) && str_word_count( $t ) < 80;
}

$imgs = array();
foreach ( silq_practices() as $items ) {
	foreach ( $items as $p ) {
		$imgs[ $p['slug'] ] = $p['img'];
	}
}

$pages = get_pages( array( 'parent' => $parent->ID, 'post_status' => 'publish' ) );
foreach ( $pages as $page ) {
	$raw  = get_post_meta( $page->ID, '_elementor_data', true );
	$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	if ( ! is_array( $data ) ) {
		WP_CLI::warning( "{$page->ID} {$page->post_name}: no Elementor data, skipped" );
		continue;
	}
	if ( SILQ_MIG_TRASH_RE === $page->ID ) {
		WP_CLI::log( sprintf( '%-6d %-36s → trash (merged into %d)', $page->ID, $page->post_name, SILQ_MIG_KEEP_RE ) );
		if ( $commit ) {
			wp_trash_post( $page->ID );
		}
		continue;
	}

	$widgets = array_values( array_filter( silq_mig_widgets( $data ), fn( $w ) => 'html' === ( $w['widgetType'] ?? '' ) ) );
	$hero    = array( 'eyebrow' => '', 'headline' => '', 'standfirst' => '' );
	$content = '';
	$tools   = 0;
	foreach ( $widgets as $i => $w ) {
		$html = (string) ( $w['settings']['html'] ?? '' );
		if ( 0 === $i && str_contains( $html, '<h1' ) ) {
			$hero = silq_mig_hero( $html );
			continue;
		}
		if ( $i === count( $widgets ) - 1 && silq_mig_is_closing_cta( $html ) ) {
			continue;
		}
		if ( str_contains( $html, '<script' ) ) {
			$content .= "\n<!-- wp:html -->\n" . trim( $html ) . "\n<!-- /wp:html -->\n";
			$tools++;
			continue;
		}
		$doc  = silq_mig_dom( $html );
		$root = $doc->getElementById( 'silq-root' ) ?: $doc->documentElement;
		$content .= "\n" . silq_mig_clean_node( $root ) . "\n";
	}
	$content = silq_mig_tidy( $content );
	$slug    = SILQ_MIG_KEEP_RE === $page->ID ? 'real-estate' : $page->post_name;
	$thumb   = (int) get_post_thumbnail_id( $page ) ?: (int) ( $imgs[ $slug ] ?? 0 );

	WP_CLI::log(
		sprintf(
			'%-6d %-36s words:%-5d h2:%-3d tables:%-2d tools:%d  img:%-6d  headline: %s',
			$page->ID,
			$slug,
			str_word_count( wp_strip_all_tags( $content ) ),
			substr_count( $content, '<h2' ),
			substr_count( $content, '<table' ),
			$tools,
			$thumb,
			$hero['headline']
		)
	);

	if ( ! $commit ) {
		continue;
	}
	if ( ! get_post_meta( $page->ID, '_silq_legacy_elementor_data', true ) ) {
		update_post_meta( $page->ID, '_silq_legacy_elementor_data', wp_slash( is_array( $raw ) ? wp_json_encode( $raw ) : (string) $raw ) );
	}
	wp_update_post(
		array(
			'ID'           => $page->ID,
			'post_content' => $content,
			'post_name'    => $slug,
		)
	);
	delete_post_meta( $page->ID, '_elementor_edit_mode' );
	delete_post_meta( $page->ID, '_elementor_data' );
	delete_post_meta( $page->ID, '_elementor_css' );
	update_post_meta( $page->ID, '_wp_page_template', 'page-practice.php' );
	foreach ( $hero as $k => $v ) {
		$v ? update_post_meta( $page->ID, '_silq_' . $k, $v ) : delete_post_meta( $page->ID, '_silq_' . $k );
	}
	if ( $thumb && ! has_post_thumbnail( $page ) ) {
		set_post_thumbnail( $page->ID, $thumb );
	}
}

// Parent: services index.
WP_CLI::log( sprintf( '%-6d %-36s → page-practice-index.php', $parent->ID, 'our-services' ) );
if ( $commit ) {
	if ( ! get_post_meta( $parent->ID, '_silq_legacy_elementor_data', true ) ) {
		update_post_meta( $parent->ID, '_silq_legacy_elementor_data', wp_slash( (string) get_post_meta( $parent->ID, '_elementor_data', true ) ) );
	}
	delete_post_meta( $parent->ID, '_elementor_edit_mode' );
	delete_post_meta( $parent->ID, '_elementor_data' );
	update_post_meta( $parent->ID, '_wp_page_template', 'page-practice-index.php' );
	wp_update_post( array( 'ID' => $parent->ID, 'post_title' => 'Our Services' ) );
}

WP_CLI::success( $commit ? 'Committed.' : 'Dry run. Add "commit" to write.' );
