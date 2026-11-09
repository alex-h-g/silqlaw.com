<?php
/**
 * Insights (posts): topic tiles, pagination, reading time, table of contents, author boxes.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

/** Topic buttons on News & Updates (tag slug => friendly label): the most-used topics only. Everything else is on /topics/. */
function silq_topics(): array {
	return array(
		'real-estate-en'     => 'Real Estate',
		'foreign-investment' => 'Foreign Investment',
		'dbd'                => 'DBD',
		'tourism'            => 'Tourism',
		'visa'               => 'Visa',
		'nominee'            => 'Nominee',
		'boi'                => 'BOI',
		'bankruptcy'         => 'Bankruptcy',
		'cannabis-en'        => 'Cannabis',
	);
}

/** Tags too general to be useful as a topic. */
function silq_generic_tags(): array {
	return array( 'thai-law', 'thailand', 'law', 'business', 'asia' );
}

/** Friendly label for any tag: the curated label, else the tag name in title case. */
function silq_topic_label( WP_Term $t ): string {
	$labels = silq_topics() + array(
		'property'               => 'Property',
		'property-rental-laws'   => 'Leases & Rentals',
		'rental'                 => 'Leases & Rentals',
		'lease-en'               => 'Leases & Rentals',
		'cannabis-en'            => 'Cannabis',
		'fba'                    => 'Foreign Business Act',
		'visas-and-work-permits' => 'Visas & Work Permits',
		'hotel'                  => 'Hotels',
		'sme'                    => 'SMEs',
		'sec-thailand'           => 'SEC Thailand',
		'dtv-visa'               => 'DTV Visa',
		'amlo'                   => 'Anti-Money Laundering (AMLO)',
		'nacc'                   => 'Anti-Corruption (NACC)',
		'boi'                    => 'BOI',
		'dbd'                    => 'Company Registration',
		'rehabilitation'         => 'Business Rehabilitation',
	);
	if ( isset( $labels[ $t->slug ] ) ) {
		return $labels[ $t->slug ];
	}
	$name = html_entity_decode( $t->name );
	return preg_match( '/[A-Z]{2,}/', $name ) ? $name : ucwords( strtolower( $name ) );
}

/** All topics worth listing (2+ articles, not generic), most used first, with duplicate labels merged. */
function silq_all_topics( int $min = 2 ): array {
	$terms = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
	$out   = array();
	foreach ( is_wp_error( $terms ) ? array() : $terms as $t ) {
		if ( $t->count < $min || in_array( $t->slug, silq_generic_tags(), true ) ) {
			continue;
		}
		$label = silq_topic_label( $t );
		if ( ! isset( $out[ $label ] ) ) {
			$out[ $label ] = $t; // keep the most-used tag for each label
		}
	}
	return $out;
}

function silq_topic_chips(): void {
	$current = is_tag() ? get_queried_object()->slug : '';
	echo '<nav class="filters" aria-label="Topics">';
	printf( '<a class="chip" href="%s"%s>All</a>', esc_url( home_url( '/news-and-updates/' ) ), is_page() ? ' aria-current="page"' : '' );
	foreach ( silq_topics() as $slug => $label ) {
		$term = get_term_by( 'slug', $slug, 'post_tag' );
		if ( ! $term || ! $term->count ) {
			continue; // tag deleted or empty: no chip, no dead link
		}
		printf(
			'<a class="chip" href="%s"%s%s>%s</a>',
			esc_url( get_term_link( $term ) ),
			$slug === $current ? ' aria-current="page"' : '',
			'',
			esc_html( $label )
		);
	}
	printf( '<a class="chip chip--more" href="%s">All topics %s</a>', esc_url( home_url( '/topics/' ) ), silq_icon( 'arrow' ) ); // phpcs:ignore
	echo '</nav>';
}

function silq_search_form(): void {
	?>
	<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<label class="screen-reader-text" for="silq-s">Search news and updates</label>
		<input id="silq-s" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Search the archive">
		<button type="submit" class="button button--small">Search</button>
	</form>
	<?php
}

function silq_pagination( WP_Query $q = null ): void {
	global $wp_query;
	$q     = $q ?: $wp_query;
	$links = paginate_links(
		array(
			'total'     => (int) $q->max_num_pages,
			'current'   => max( 1, (int) get_query_var( 'paged' ) ),
			'prev_text' => '← Newer',
			'next_text' => 'Older →',
			'type'      => 'list',
			'mid_size'  => 1,
		)
	);
	if ( $links ) {
		echo '<nav class="pagination" aria-label="Pagination">' . $links . '</nav>'; // phpcs:ignore
	}
}

function silq_reading_time( int $id = 0 ): int {
	$text = wp_strip_all_tags( (string) get_post_field( 'post_content', $id ?: get_the_ID() ) );
	$thai = preg_match_all( '/[\x{0E00}-\x{0E7F}]/u', $text );
	$words = $thai > 200 ? (int) ( $thai / 5 ) : str_word_count( $text );
	return max( 1, (int) round( $words / 230 ) );
}

/** Authors for a post (PublishPress Authors aware). Returns array of [name, url, bio, avatar id/url]. */
function silq_post_authors( int $id = 0 ): array {
	$id  = $id ?: get_the_ID();
	$out = array();
	if ( function_exists( 'get_post_authors' ) ) {
		foreach ( get_post_authors( $id ) as $a ) {
			// Only a photo uploaded in PublishPress Authors; otherwise the card shows the author's initials.
			$avatar = ! empty( $a->term_id ) ? (int) get_term_meta( $a->term_id, 'avatar', true ) : 0;
			$img    = $avatar ? (string) wp_get_attachment_image_url( $avatar, 'thumbnail' ) : '';
			$out[] = array(
				'name' => (string) $a->display_name,
				'url'  => (string) $a->link,
				'bio'  => (string) $a->description,
				'img'  => (string) $img,
			);
		}
	}
	if ( ! $out ) {
		$uid   = (int) get_post_field( 'post_author', $id );
		$out[] = array(
			'name' => get_the_author_meta( 'display_name', $uid ),
			'url'  => get_author_posts_url( $uid ),
			'bio'  => get_the_author_meta( 'description', $uid ),
			'img'  => '',
		);
	}
	return $out;
}

/**
 * Articles carry a PublishPress author-box shortcode in their content; the theme renders its own author section,
 * so the shortcode is skipped on the front end (content in the database is untouched).
 */
add_filter( 'the_content', function ( $content ) {
	if ( is_admin() || ! is_singular( 'post' ) ) {
		return $content;
	}
	return preg_replace( '/\[publishpress_authors_[a-z_]+[^\]]*\]/', '', $content );
}, 1 );

/**
 * Table of contents: give every <h2> in an article an id; collect them for the TOC.
 */
add_filter( 'the_content', function ( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$GLOBALS['silq_toc'] = array();
	$used                = array();
	return preg_replace_callback(
		'#<h2([^>]*)>(.*?)</h2>#is',
		function ( $m ) use ( &$used ) {
			$text = trim( wp_strip_all_tags( $m[2] ) );
			if ( preg_match( '/\sid="([^"]+)"/', $m[1], $idm ) ) {
				$id = $idm[1];
			} else {
				$id = sanitize_title( $text ) ?: 'section';
				$base = $id;
				$n    = 2;
				while ( isset( $used[ $id ] ) ) {
					$id = $base . '-' . $n++;
				}
				$m[1] .= ' id="' . esc_attr( $id ) . '"';
			}
			$used[ $id ]           = true;
			$GLOBALS['silq_toc'][] = array( $id, $text );
			return '<h2' . $m[1] . '>' . $m[2] . '</h2>';
		},
		$content
	);
}, 20 );

// The theme renders its own author cards on articles; stop PublishPress Authors appending its box to the content.
add_filter( 'pp_multiple_authors_filter_should_display_author_box', function ( $display ) {
	return is_admin() ? $display : false;
} );

// Editor reminder: articles read better (and get a table of contents) when they use headings.
add_action( 'admin_notices', function () {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || 'post' !== $screen->base || 'post' !== $screen->post_type || empty( $_GET['post'] ) ) {
		return;
	}
	$content = (string) get_post_field( 'post_content', absint( $_GET['post'] ) );
	if ( str_word_count( wp_strip_all_tags( $content ) ) > 300 && ! preg_match( '/<h[23][\s>]/i', $content ) ) {
		echo '<div class="notice notice-warning"><p><strong>This article has no headings.</strong> Break it into sections with Heading 2 blocks (and Heading 3 for sub-points). Readers can scan it, Google understands it better, and the page gets a table of contents.</p></div>';
	}
} );
