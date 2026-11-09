<?php
/**
 * Template Name: Contact
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
$c = silq_contact();
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">Contact us</h1>
		<p class="page-hero__lead">Tell us what you are planning or what has happened. A lawyer will read your message and reply within one business day, in English or Thai. Everything you send is kept confidential.</p>
	</div>
</header>

<section class="section" id="enquiry">
	<div class="wrap enquiry">
		<div class="enquiry__intro">
			<h2>Call, message or email</h2>
			<?php silq_contact_list( 'contact-band__list' ); ?>
			<h2 class="h3">Offices</h2>
			<div class="offices offices--stack">
				<?php foreach ( $c['offices'] as $o ) : ?>
					<div>
						<h3><?php echo esc_html( $o['name'] ); ?></h3>
						<p><?php echo esc_html( $o['address'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
			<p class="small">Meetings by appointment.</p>
		</div>
		<div class="enquiry__form" id="enquiry-form">
			<h2>Send us a message</h2>
			<?php echo do_shortcode( '[formidable id=5]' ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php
get_footer();
