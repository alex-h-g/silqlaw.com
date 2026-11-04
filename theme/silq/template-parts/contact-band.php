<?php
/**
 * "Talk to us" band with offices and contact links.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;
$contact = silq_contact();
?>
<section class="section section--mist">
	<div class="wrap contact-band">
		<div>
			<h2>Talk to us</h2>
			<p>Not sure where to start? Tell us what you are planning or what has happened. We will explain your options in plain language and reply within one business day.</p>
			<div class="offices">
				<?php foreach ( $contact['offices'] as $o ) : ?>
					<div>
						<h3><?php echo esc_html( $o['name'] ); ?></h3>
						<p><?php echo esc_html( $o['address'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div>
			<?php silq_contact_list( 'contact-band__list' ); ?>
			<p><a class="button" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact us <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a></p>
		</div>
	</div>
</section>
