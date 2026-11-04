<?php
/**
 * Insights (posts): topic tiles, pagination, reading time, table of contents, author boxes.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

/** Topic tiles shown on Insights and archive pages (tag slug => label). Order as agreed with Alex. */
function silq_topics(): array {
	return array(
		'foreign-investment'    => 'Foreign Investment',
		'dbd'                   => 'Company Registration',
		'business-law'          => 'Business Law',
		'real-estate-en'        => 'Real Estate',
		'property-rental-laws'  => 'Leases & Rentals',
		'foreign-ownership'     => 'Foreign Ownership',
		'nominee'               => 'Nominee',
		'boi'                   => 'BOI',
		'visa'                  => 'Visas',
		'tourism'               => 'Tourism & Hotels',
		'cannabis-en'           => 'Cannabis',
		'bankruptcy'            => 'Bankruptcy',
	);
}

function silq_topic_chips(): void {
	$current = is_tag() ? get_queried_object()->slug : '';
	echo '<nav class="filters" aria-label="Topics">';
	printf( '<a class="chip" href="%s"%s>All</a>', esc_url( home_url( '/news-and-updates/' ) ), is_page() ? ' aria-current="page"' : '' );
	foreach ( silq_topics() as $slug => $label ) {
		printf(
			'<a class="chip" href="%s"%s%s>%s</a>',
			esc_url( home_url( '/legal/' . $slug . '/' ) ),
			$slug === $current ? ' aria-current="page"' : '',
			'',
			esc_html( $label )
		);
	}
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
			$img = method_exists( $a, 'get_avatar_url' ) ? $a->get_avatar_url( 160 ) : '';
			if ( is_array( $img ) ) {
				$img = $img['url'] ?? '';
			}
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
