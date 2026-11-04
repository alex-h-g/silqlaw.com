<?php
/**
 * Template helpers and site data.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

/** Attachment IDs used by theme templates (existing media library items). */
const SILQ_LOGO_ID = 888;   // 2025/09/SILQ-Yellow-Blue-Final.png (navy + green on white)
const SILQ_HERO_ID = 195;   // 2025/02/bkk-skyline-sunrise.webp

/** Firm contact details, used in header, footer, service pages and schema. */
function silq_contact(): array {
	return array(
		'phone'      => '+66 89 920 4451',
		'phone_href' => 'tel:+66899204451',
		'whatsapp'   => 'https://wa.me/66899204451',
		'email'      => 'info@silqlaw.com',
		'line'       => '@700bjyao',
		'line_href'  => 'https://line.me/R/ti/p/@700bjyao',
		'offices'    => array(
			array(
				'name'    => 'Eastern Economic Corridor',
				'address' => '2nd Floor, The Atrium, Soi 5, Pratamnak Hill, Bang Lamung District, Chonburi 20150',
				'locality' => 'Bang Lamung',
				'region'  => 'Chonburi',
				'postcode' => '20150',
				'street'  => '2nd Floor, The Atrium, Soi 5, Pratamnak Hill',
			),
			array(
				'name'    => 'Phuket',
				'address' => '147 Moo 6, Cherngtalay Sub-District, Thalang District, Phuket 83110',
				'locality' => 'Thalang',
				'region'  => 'Phuket',
				'postcode' => '83110',
				'street'  => '147 Moo 6, Cherngtalay Sub-District',
			),
		),
	);
}

/**
 * Logo lockup (theme asset, cropped from media item 888). $variant: 'light' (on paper) or 'dark' (on navy).
 */
function silq_logo( string $variant = 'light', array $attr = array() ): string {
	$file = 'dark' === $variant ? 'logo-h-dark.webp' : 'logo-h.webp';
	$attr = array_merge( array( 'alt' => 'SILQ Law', 'width' => 353, 'height' => 200, 'decoding' => 'async' ), $attr );
	$out  = '<img src="' . esc_url( SILQ_URI . '/assets/img/' . $file ) . '"';
	foreach ( $attr as $k => $v ) {
		$out .= ' ' . esc_attr( $k ) . '="' . esc_attr( (string) $v ) . '"';
	}
	return $out . '>';
}

/**
 * Responsive <img> for an attachment, with sensible defaults.
 */
function silq_img( int $id, string $size = 'large', array $attr = array() ): string {
	$attr = array_merge( array( 'loading' => 'lazy', 'decoding' => 'async' ), $attr );
	return (string) wp_get_attachment_image( $id, $size, false, $attr );
}

/**
 * Services, grouped for the homepage list, footer and "related" links.
 * Slugs are children of /our-services/. 'img' = existing attachment ID.
 */
function silq_practices(): array {
	return array(
		'Starting & investing' => array(
			array( 'slug' => 'company-formation', 'title' => 'Company Formation', 'img' => 1509 ),
			array( 'slug' => 'foreign-investment-market-entry', 'title' => 'Foreign Investment & BOI', 'img' => 1110 ),
			array( 'slug' => 'best-foreign-owned-structure', 'title' => 'Foreign-Owned Structures', 'img' => 1503 ),
			array( 'slug' => 'nominee-compliance', 'title' => 'Nominee Structures & Compliance', 'img' => 10798 ),
			array( 'slug' => 'ma-corporate-law', 'title' => 'M&A & Corporate', 'img' => 10791 ),
		),
		'Property & building' => array(
			array( 'slug' => 'real-estate', 'title' => 'Real Estate & Property', 'img' => 930 ),
			array( 'slug' => 'construction-property-development', 'title' => 'Construction & Property Development', 'img' => 10808 ),
		),
		'People & licences' => array(
			array( 'slug' => 'visas-work-permits', 'title' => 'Visas & Work Permits', 'img' => 1474 ),
			array( 'slug' => 'regulatory-compliance', 'title' => 'Regulatory & Compliance', 'img' => 1386 ),
			array( 'slug' => 'cannabis-kratom-health', 'title' => 'Cannabis, Kratom & Health Products', 'img' => 1375 ),
		),
		'When things go wrong' => array(
			array( 'slug' => 'dispute-resolution', 'title' => 'Dispute Resolution', 'img' => 849 ),
			array( 'slug' => 'bankruptcy-debt-insolvency', 'title' => 'Bankruptcy, Debt & Insolvency', 'img' => 1533 ),
		),
		'Industries' => array(
			array( 'slug' => 'hospitality-tourism', 'title' => 'Hospitality & Tourism', 'img' => 11951 ),
			array( 'slug' => 'food-beverage', 'title' => 'Food & Beverage', 'img' => 11944 ),
			array( 'slug' => 'healthcare-wellness', 'title' => 'Healthcare & Wellness', 'img' => 11937 ),
			array( 'slug' => 'manufacturing-trade', 'title' => 'Manufacturing & Trade', 'img' => 11942 ),
			array( 'slug' => 'tech-digital', 'title' => 'Tech & Digital', 'img' => 11953 ),
			array( 'slug' => 'financial-services-fintech', 'title' => 'Financial Services & Fintech', 'img' => 11938 ),
		),
	);
}

function silq_practice_url( string $slug ): string {
	return home_url( '/our-services/' . $slug . '/' );
}

/** People shown on the homepage. */
function silq_partners(): array {
	return array(
		array(
			'name' => 'Penny Namno',
			'role' => 'Founder & Managing Partner',
			'url'  => '/our-team/penny-namno-founder-managing-partner/',
			'img'  => 12051,
		),
	);
}

/** Team, in display order. Profile pages are children of /our-team/. */
function silq_team(): array {
	return array(
		array( 'name' => 'Penny Namno', 'role' => 'Founder & Managing Partner', 'office' => '', 'slug' => 'penny-namno-founder-managing-partner', 'img' => 12051 ),
		array( 'name' => 'Dr. Andrew Paziuk', 'role' => 'Managing Partner', 'office' => 'Phuket', 'slug' => 'andrew-paziuk-managing-partner-phuket', 'img' => 1291 ),
		array( 'name' => 'Kanta Kanvichit', 'role' => 'Associate', 'office' => 'Phuket', 'slug' => 'kanta-associate', 'img' => 1296 ),
		array( 'name' => 'Nawatchapon (Butter) Ngarmtaranakit', 'role' => 'Associate', 'office' => 'Phuket', 'slug' => 'butter-associate', 'img' => 1298 ),
	);
}

/** Primary navigation. */
function silq_nav_items(): array {
	return array(
		array( 'About', '/about-us/' ),
		array( 'Services', '/our-services/' ),
		array( 'Our Team', '/our-team/' ),
		array( 'News & Updates', '/news-and-updates/' ),
		array( 'International', '/international-support/' ),
	);
}

function silq_icon( string $name ): string {
	$paths = array(
		'arrow' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
		'mail'  => '<path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/>',
		'chat'  => '<path d="M4 5h16v11H9l-5 4z"/>',
		'pin'   => '<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
		'menu'  => '<path d="M4 7h16M4 12h16M4 17h16"/>',
	);
	return '<svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}

/** Phone, email and Line links as a list. */
function silq_contact_list( string $class = 'contact-list' ): void {
	$c = silq_contact();
	printf( '<ul class="%s">', esc_attr( $class ) );
	printf( '<li><a href="%s">%s %s</a> <span class="contact-list__note">· <a href="%s" rel="noopener">WhatsApp</a></span></li>', esc_url( $c['phone_href'] ), silq_icon( 'phone' ), esc_html( $c['phone'] ), esc_url( $c['whatsapp'] ) ); // phpcs:ignore
	printf( '<li><a href="mailto:%1$s">%2$s %1$s</a></li>', esc_attr( $c['email'] ), silq_icon( 'mail' ) ); // phpcs:ignore
	printf( '<li><a href="%s" rel="noopener">%s Line %s</a></li>', esc_url( $c['line_href'] ), silq_icon( 'chat' ), esc_html( $c['line'] ) ); // phpcs:ignore
	echo '</ul>';
}

/**
 * Title/subtitle from an old banner widget on an Elementor page, or null.
 * EntrepreneurX pages used `themo-header`; August 2026 pages open with an HTML widget whose first heading is the title.
 */
function silq_legacy_banner( int $id ): ?array {
	static $cache = array();
	if ( array_key_exists( $id, $cache ) ) {
		return $cache[ $id ];
	}
	$raw  = get_post_meta( $id, '_elementor_data', true );
	$data = is_array( $raw ) ? $raw : json_decode( (string) $raw, true );
	$find = function ( $nodes ) use ( &$find ) {
		foreach ( (array) $nodes as $n ) {
			if ( ! is_array( $n ) ) {
				continue;
			}
			if ( ( $n['widgetType'] ?? '' ) === 'themo-header' ) {
				return is_array( $n['settings'] ?? null ) ? $n['settings'] : array();
			}
			$hit = $find( $n['elements'] ?? array() );
			if ( null !== $hit ) {
				return $hit;
			}
		}
		return null;
	};
	$s = is_array( $data ) ? $find( $data ) : null;
	$cache[ $id ] = null === $s ? null : array(
		'title' => trim( wp_strip_all_tags( (string) ( $s['title_text'] ?? '' ) ) ) ?: get_the_title( $id ),
		'lead'  => trim( wp_strip_all_tags( (string) ( $s['description_text'] ?? '' ) ) ),
	);
	return $cache[ $id ];
}
