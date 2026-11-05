<?php
/**
 * Site footer.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;
$groups  = silq_practices();
$contact = silq_contact();
?>
</main>

<footer class="site-footer">
	<div class="wrap site-footer__grid">
		<div class="site-footer__brand">
			<?php echo silq_logo( 'dark', array( 'class' => 'site-footer__logo', 'loading' => 'lazy' ) ); // phpcs:ignore ?>
			<p class="site-footer__tagline">Real Advice, Real Results</p>
			<p>SILQ Law helps businesses, investors and families with Thai law — from setting up a company to buying property and resolving disputes.</p>
			<?php silq_contact_list(); ?>
		</div>

		<div>
			<h2 class="site-footer__heading">Services</h2>
			<ul class="link-list">
				<?php foreach ( array( 'company-formation', 'foreign-investment-market-entry', 'real-estate', 'visas-work-permits', 'dispute-resolution', 'regulatory-compliance' ) as $slug ) :
					foreach ( $groups as $items ) {
						foreach ( $items as $p ) {
							if ( $p['slug'] === $slug ) {
								printf( '<li><a href="%s">%s</a></li>', esc_url( silq_practice_url( $slug ) ), esc_html( $p['title'] ) );
							}
						}
					}
				endforeach; ?>
				<li><a href="<?php echo esc_url( home_url( '/our-services/' ) ); ?>">All services</a></li>
			</ul>
		</div>

		<div>
			<h2 class="site-footer__heading">Firm</h2>
			<ul class="link-list">
				<li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About us</a></li>
				<li><a href="<?php echo esc_url( home_url( '/our-team/' ) ); ?>">Our team</a></li>
				<li><a href="<?php echo esc_url( home_url( '/news-and-updates/' ) ); ?>">News &amp; updates</a></li>
				<li><a href="<?php echo esc_url( home_url( '/international-support/' ) ); ?>">International support</a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact us</a></li>
			</ul>
		</div>

		<div>
			<h2 class="site-footer__heading">Offices</h2>
			<ul class="office-list">
				<?php foreach ( $contact['offices'] as $o ) : ?>
					<li><strong><?php echo esc_html( $o['name'] ); ?></strong><br><?php echo esc_html( $o['address'] ); ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="wrap site-footer__base">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> SILQ Royal Co., Ltd.</p>
		<ul class="link-list link-list--inline">
			<li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">Privacy</a></li>
			<li><a href="<?php echo esc_url( home_url( '/about-us/terms-and-conditions-engagement/' ) ); ?>">Terms of engagement</a></li>
		</ul>
		<p class="site-footer__disclaimer">Information on this site is general and is not legal advice. Past results do not guarantee a similar outcome.</p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
