<?php
/**
 * Services (children of /our-services/): filter groups, breadcrumbs, FAQ schema and related-article lookup.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;


/** Filter chips for the services index: one per group in silq_practices(). */
function silq_practice_filter_terms(): array {
	$out = array();
	foreach ( array_keys( silq_practices() ) as $group ) {
		$out[] = (object) array( 'slug' => sanitize_title( $group ), 'name' => $group );
	}
	return $out;
}

/** Group slugs a service page belongs to (for the index filter). */
function silq_practice_groups( string $slug ): array {
	$out = array();
	foreach ( silq_practices() as $group => $items ) {
		if ( in_array( $slug, wp_list_pluck( $items, 'slug' ), true ) ) {
			$out[] = sanitize_title( $group );
		}
	}
	return $out;
}

/** Service pages (children of /our-services/), alphabetical. */
function silq_practice_pages(): array {
	$parent = get_page_by_path( 'our-services' );
	if ( ! $parent ) {
		return array();
	}
	return get_pages(
		array(
			'parent'      => $parent->ID,
			'sort_column' => 'post_title',
			'post_status' => 'publish',
		)
	);
}

/** Short display title (page title without SEO suffixes like "| Telecom, Media…"). */
function silq_practice_title( WP_Post $page ): string {
	foreach ( silq_practices() as $items ) {
		foreach ( $items as $p ) {
			if ( $p['slug'] === $page->post_name ) {
				return $p['title'];
			}
		}
	}
	return trim( preg_replace( '/\s*[|:].*$/', '', $page->post_title ) );
}

/** Group (from silq_practices) a practice slug belongs to, and its siblings. */
function silq_practice_siblings( string $slug ): array {
	foreach ( silq_practices() as $group => $items ) {
		if ( in_array( $slug, wp_list_pluck( $items, 'slug' ), true ) ) {
			return array( $group, array_values( array_filter( $items, fn( $p ) => $p['slug'] !== $slug ) ) );
		}
	}
	return array( '', array() );
}

/** Tag slugs used to pull related insights onto each practice page. */
function silq_practice_tags( string $slug ): array {
	$map = array(
		'company-formation'               => array( 'dbd', 'business-law', 'corporate-governance', 'sme', 'startup' ),
		'foreign-investment-market-entry' => array( 'foreign-investment', 'boi', 'foreign-business-act', 'fba' ),
		'best-foreign-owned-structure'    => array( 'foreign-ownership', 'foreign-business-act', 'fba', 'foreign-investment' ),
		'nominee-compliance'              => array( 'nominee', 'foreign-ownership' ),
		'ma-corporate-law'                => array( 'corporate-governance', 'business-law', 'sec-thailand' ),
		'real-estate'                     => array( 'real-estate-en', 'property', 'property-law', 'land-department', 'lease-en', 'rental', 'property-rental-laws' ),
		'construction-property-development' => array( 'real-estate-en', 'property', 'land-department', 'property-law' ),
		'visas-work-permits'              => array( 'visa', 'visas-and-work-permits', 'dtv-visa', 'ltr-visa', 'work-permit' ),
		'regulatory-compliance'           => array( 'alcohol', 'excise', 'amlo', 'nacc', 'labour-law' ),
		'cannabis-kratom-health'          => array( 'cannabis-en', 'kratom' ),
		'dispute-resolution'              => array( 'business-law' ),
		'bankruptcy-debt-insolvency'      => array( 'bankruptcy', 'rehabilitation' ),
		'hospitality-tourism'             => array( 'tourism', 'hotel' ),
		'food-beverage'                   => array( 'alcohol', 'excise' ),
		'healthcare-wellness'             => array( 'cannabis-en', 'kratom' ),
		'manufacturing-trade'             => array( 'boi', 'foreign-investment' ),
		'tech-digital'                    => array( 'startup' ),
		'financial-services-fintech'      => array( 'sec-thailand', 'amlo' ),
	);
	return $map[ $slug ] ?? array();
}

function silq_related_insights( string $slug, int $count = 3 ): WP_Query {
	$tags = silq_practice_tags( $slug );
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'tag_slug__in'        => $tags ? $tags : array( '__none__' ),
		)
	);
}

/**
 * Breadcrumb trail for the current view. Yoast already outputs BreadcrumbList schema; this renders the visible trail.
 */
function silq_breadcrumbs(): void {
	if ( is_front_page() ) {
		return;
	}
	$crumbs = array( array( 'Home', home_url( '/' ) ) );

	if ( is_page() ) {
		$id     = get_queried_object_id();
		$labels = array( 'our-services' => 'Services', 'news-and-updates' => 'News & Updates', 'about-us' => 'About', 'our-team' => 'Our Team', 'contact-us' => 'Contact', 'awards' => 'Awards' );
		$label  = fn( $pid ) => $labels[ get_post_field( 'post_name', $pid ) ] ?? wp_strip_all_tags( get_the_title( $pid ) );
		foreach ( array_reverse( get_post_ancestors( $id ) ) as $anc ) {
			$crumbs[] = array( $label( $anc ), get_permalink( $anc ) );
		}
		$page     = get_post( $id );
		$crumbs[] = array( $page->post_parent && 'our-services' === get_post_field( 'post_name', $page->post_parent ) ? silq_practice_title( $page ) : $label( $id ), '' );
	} elseif ( is_singular( 'post' ) ) {
		$crumbs[] = array( 'News & Updates', home_url( '/news-and-updates/' ) );
		$crumbs[] = array( get_the_title(), '' );
	} elseif ( is_tag() || is_category() ) {
		$crumbs[] = array( 'News & Updates', home_url( '/news-and-updates/' ) );
		$crumbs[] = array( single_term_title( '', false ), '' );
	} elseif ( is_author() ) {
		$crumbs[] = array( 'News & Updates', home_url( '/news-and-updates/' ) );
		$crumbs[] = array( get_the_author_meta( 'display_name', get_queried_object_id() ), '' );
	} elseif ( is_search() ) {
		$crumbs[] = array( 'Search', '' );
	} elseif ( is_404() ) {
		$crumbs[] = array( 'Page not found', '' );
	}

	echo '<nav class="breadcrumbs" aria-label="Breadcrumb"><ol>';
	foreach ( $crumbs as $i => list( $label, $url ) ) {
		$last = $i === count( $crumbs ) - 1;
		if ( $last || ! $url ) {
			printf( '<li><span aria-current="page">%s</span></li>', esc_html( wp_strip_all_tags( $label ) ) );
		} else {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( wp_strip_all_tags( $label ) ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * FAQPage schema from <details><summary> pairs in the page content.
 */
add_action( 'wp_footer', function () {
	if ( ! is_singular() ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	if ( ! str_contains( $content, '<details' ) ) {
		return;
	}
	preg_match_all( '#<details[^>]*>\s*<summary[^>]*>(.*?)</summary>(.*?)</details>#is', $content, $m, PREG_SET_ORDER );
	$items = array();
	foreach ( $m as $pair ) {
		$q = trim( wp_strip_all_tags( $pair[1] ) );
		$a = trim( wp_kses( $pair[2], array( 'p' => array(), 'br' => array(), 'ul' => array(), 'ol' => array(), 'li' => array(), 'a' => array( 'href' => array() ), 'strong' => array(), 'em' => array() ) ) );
		if ( $q && $a ) {
			$items[] = array(
				'@type'          => 'Question',
				'name'           => $q,
				'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $a ),
			);
		}
	}
	if ( $items ) {
		echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $items ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
	}
} );
