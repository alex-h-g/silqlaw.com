<?php
/**
 * Permanent redirects for retired URLs. One hop each, straight to the final URL (no chains).
 * Runs before WordPress resolves a 404, so it costs nothing on normal page views.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

function silq_redirects(): array {
	return array(
		// Old EntrepreneurX portfolio entries (themo_portfolio, /services/…).
		'/services/setup-a-company/'                           => '/our-services/company-formation/',
		'/services/thailand-company-registration-rules-2026/' => '/our-services/company-formation/',
		'/services/visas/'                                     => '/our-services/visas-work-permits/',
		'/services/regulated-products/'                        => '/our-services/regulatory-compliance/',
		'/services/real-estate/'                               => '/our-services/real-estate/',
		'/services/litigation-and-dispute-resolution/'         => '/our-services/dispute-resolution/',
		'/services/treaty-of-amity/'                           => '/our-services/best-foreign-owned-structure/',
		'/services/foreign-owned/'                             => '/our-services/best-foreign-owned-structure/',
		'/services/best-foreign-setup-boi-rep-tiso-thailand/'  => '/our-services/foreign-investment-market-entry/',
		'/services/boi-promoted-company/'                      => '/our-services/foreign-investment-market-entry/',
		'/services/eec-promoted-company/'                      => '/our-services/foreign-investment-market-entry/',
		'/services/cannabis/'                                  => '/our-services/cannabis-kratom-health/',
		'/services/idea-collaboration/'                        => '/our-services/',
		'/services/intellectual-capital-2/'                    => '/our-services/',
		'/services/'                                           => '/our-services/',
		// Merged real estate pages.
		'/our-services/real-estate-property/'                  => '/our-services/real-estate/',
		// Retired pages.
		'/contact-2/'                                          => '/contact-us/',
		'/careers/'                                            => '/contact-us/',
		'/shop/'                                               => '/',
		'/shop-2/'                                             => '/',
		'/shop-sidebar/'                                       => '/',
		'/my-account/'                                         => '/',
	);
}

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$path = (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH );
	$path = '/' . trim( $path, '/' ) . '/';
	$map  = silq_redirects();
	if ( isset( $map[ $path ] ) ) {
		wp_safe_redirect( home_url( $map[ $path ] ), 301, 'SILQ' );
		exit;
	}
}, 1 );
