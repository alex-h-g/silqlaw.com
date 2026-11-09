<?php
/**
 * Crawl the site from the homepage (plus every published page and post) and report internal links that do not work.
 * Read-only. Follows internal links only; checks images/PDFs under wp-content too.
 *
 *   wp eval-file scripts/check/links.php            (report)
 *   wp eval-file scripts/check/links.php external   (also check links to other sites — slower)
 *
 * @package silq
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run with wp eval-file\n" );
}
$external = in_array( 'external', $args ?? array(), true );
remove_all_filters( 'pre_http_request' ); // a local-dev guard blocks HTTP; the crawler needs loopback requests
$home     = untrailingslashit( home_url() );
$host     = wp_parse_url( $home, PHP_URL_HOST );
$hosts    = array( $host, 'www.' . preg_replace( '/^www\./', '', $host ), preg_replace( '/^www\./', '', $host ) );

$norm = function ( string $url, string $base ) use ( $home ): ?string {
	$url = html_entity_decode( trim( $url ) );
	if ( '' === $url || preg_match( '#^(mailto:|tel:|javascript:|data:|\#|sms:|whatsapp:)#i', $url ) ) {
		return null;
	}
	if ( str_starts_with( $url, '//' ) ) {
		$url = 'https:' . $url;
	} elseif ( str_starts_with( $url, '/' ) ) {
		$url = $home . $url;
	} elseif ( ! preg_match( '#^https?://#i', $url ) ) {
		$url = trailingslashit( dirname( $base . 'x' ) ) . $url;
	}
	$url = preg_replace( '/#.*$/', '', $url );
	// Absolute links to the live domain are checked against this install (so the check also works on a copy).
	return preg_replace( '#^https?://(www\.)?silqlaw\.com(?=/|$)#i', $home, $url );
};
$is_internal = fn( string $u ) => in_array( wp_parse_url( $u, PHP_URL_HOST ), $hosts, true );
// Links that can never work for visitors: dev/staging addresses left in content.
$wrong_host = fn( string $u ) => (bool) preg_match( '#^https?://(127\.0\.0\.1|localhost(?!:8080)|[a-z0-9-]+\.silqlaw\.com|staging[0-9]*\.)#i', $u ) && ! $is_internal( $u );

$queue = array( $home . '/' );
foreach ( get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
	$queue[] = get_permalink( $id );
}
$queue  = array_values( array_unique( $queue ) );
$seen   = array_fill_keys( $queue, true );
$status = array();   // url => code
$from   = array();   // url => [pages linking to it]
$pages  = 0;

$check = function ( string $u, bool $get = false ) use ( &$status ) {
	if ( isset( $status[ $u ] ) && ! $get ) {
		return array( $status[ $u ], '' );
	}
	$r = $get
		? wp_remote_get( $u, array( 'timeout' => 20, 'redirection' => 0, 'sslverify' => false, 'user-agent' => 'SILQ link check' ) )
		: wp_remote_head( $u, array( 'timeout' => 15, 'redirection' => 0, 'sslverify' => false, 'user-agent' => 'SILQ link check' ) );
	$code = is_wp_error( $r ) ? 0 : (int) wp_remote_retrieve_response_code( $r );
	if ( in_array( $code, array( 301, 302, 307, 308 ), true ) ) {
		$loc  = (string) wp_remote_retrieve_header( $r, 'location' );
		$code = $code . '→' . $loc;
	}
	$status[ $u ] = $code;
	return array( $code, $get ? (string) wp_remote_retrieve_body( $r ) : '' );
};

while ( $queue && $pages < 1500 ) {
	$url = array_shift( $queue );
	list( $code, $body ) = $check( $url, true );
	$pages++;
	if ( 200 !== $code || ! $body ) {
		continue;
	}
	if ( ! preg_match_all( '#<(?:a|link)\b[^>]*\bhref=["\']([^"\']+)["\']|<(?:img|source|script|iframe)\b[^>]*\bsrc=["\']([^"\']+)["\']#i', $body, $m, PREG_SET_ORDER ) ) {
		continue;
	}
	foreach ( $m as $hit ) {
		$raw  = $hit[1] ?: ( $hit[2] ?? '' );
		$link = $norm( $raw, $url );
		if ( ! $link || preg_match( '#/(cdn-cgi|wp-json|xmlrpc\.php|wp-admin|wp-login\.php|feed|comments/feed)\b|\?(s|replytocom|p)=|/wp-includes/#', $link ) ) {
			continue;
		}
		if ( $wrong_host( $link ) ) {
			$status[ $link ]       = 'wrong address (staging/dev)';
			$from[ $link ][ $url ] = true;
			continue;
		}
		$internal = $is_internal( $link );
		if ( ! $internal && ! $external ) {
			continue;
		}
		$from[ $link ][ $url ] = true;
		if ( isset( $seen[ $link ] ) ) {
			continue;
		}
		$seen[ $link ] = true;
		$is_page = $internal && ! preg_match( '#/wp-content/|\.(jpe?g|png|webp|gif|svg|pdf|css|js|ico|xml|woff2?|mp4)(\?|$)#i', $link );
		if ( $is_page && wp_parse_url( $link, PHP_URL_HOST ) === $host ) {
			$queue[] = $link; // crawl it
		} else {
			$check( $link );
		}
	}
}

// Report.
$bad = array();
foreach ( $status as $u => $code ) {
	$c = (string) $code;
	if ( '200' === $c ) {
		continue;
	}
	if ( preg_match( '/^30[1278]→(.*)$/', $c, $mm ) ) {
		$target = $norm( $mm[1], $u );
		if ( $target && $is_internal( $target ) && rtrim( $target, '/' ) === rtrim( $u, '/' ) ) {
			continue; // trailing slash only
		}
		if ( $target && $is_internal( $target ) ) {
			list( $tc ) = $check( $target );
			$c          = '200' === (string) $tc ? 'redirect' : "redirect to broken ({$tc})";
		} else {
			$c = 'redirect';
		}
		if ( 'redirect' === $c && ! isset( $from[ $u ] ) ) {
			continue;
		}
	}
	if ( 'redirect' === $c && wp_parse_url( $u, PHP_URL_HOST ) !== $host && ! $is_internal( $u ) ) {
		continue; // external redirects are normal
	}
	$bad[ $c ][ $u ] = array_keys( $from[ $u ] ?? array() );
}
ksort( $bad );
WP_CLI::log( sprintf( 'Crawled %d pages, checked %d links.', $pages, count( $status ) ) );
foreach ( $bad as $code => $list ) {
	WP_CLI::log( "\n== {$code} (" . count( $list ) . ')' );
	foreach ( $list as $u => $pagesfrom ) {
		WP_CLI::log( '  ' . $u );
		foreach ( array_slice( $pagesfrom, 0, 4 ) as $p ) {
			WP_CLI::log( '      on ' . $p );
		}
		if ( count( $pagesfrom ) > 4 ) {
			WP_CLI::log( '      … and ' . ( count( $pagesfrom ) - 4 ) . ' more pages' );
		}
	}
}
WP_CLI::success( $bad ? 'Done — see the list above.' : 'No broken internal links.' );
