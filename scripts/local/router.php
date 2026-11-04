<?php
// Local dev router for `php -S`. Serves static files; if a resized upload (name-WxH.ext / -WxH@2x.ext)
// is missing, serves the original instead, so thumbnails never need regenerating locally.
$p    = rawurldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
$root = $_SERVER['DOCUMENT_ROOT'];
$f    = $root . $p;

if ( $p !== '/' && is_file( $f ) ) {
	return false;
}
if ( str_starts_with( $p, '/wp-content/uploads/' ) ) {
	$orig = preg_replace( '/-(\d+x\d+|scaled)(@2x)?(\.\w+)$/', '$3', $f );
	if ( $orig !== $f && is_file( $orig ) ) {
		$types = array( 'webp' => 'image/webp', 'png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'avif' => 'image/avif' );
		header( 'Content-Type: ' . ( $types[ strtolower( pathinfo( $orig, PATHINFO_EXTENSION ) ) ] ?? 'application/octet-stream' ) );
		readfile( $orig );
		return true;
	}
	http_response_code( 404 );
	return true;
}
if ( is_dir( $f ) && is_file( $f . '/index.php' ) ) {
	require $f . '/index.php';
	return;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
