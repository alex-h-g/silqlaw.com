<?php
/**
 * Template Name: Team
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">Our team</h1>
		<p class="page-hero__lead">Thai and international lawyers who work in English and Thai, with offices on the Eastern Seaboard and in Phuket.</p>
	</div>
</header>

<section class="section">
	<div class="wrap">
		<ul class="people-grid">
			<?php foreach ( silq_team() as $m ) : ?>
				<li class="person">
					<a href="<?php echo esc_url( home_url( '/our-team/' . $m['slug'] . '/' ) ); ?>">
						<?php echo silq_img( $m['img'], 'medium_large', array( 'alt' => $m['name'], 'class' => 'person__image', 'sizes' => '(min-width: 64rem) 22vw, 50vw' ) ); // phpcs:ignore ?>
						<span class="person__name"><?php echo esc_html( $m['name'] ); ?></span>
						<span class="person__role"><?php echo esc_html( $m['role'] . ( $m['office'] ? ' · ' . $m['office'] : '' ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php get_template_part( 'template-parts/contact-band' ); ?>

<?php
get_footer();
