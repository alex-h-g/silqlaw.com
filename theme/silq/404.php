<?php
/**
 * Not found.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<h1 class="page-hero__title">Page not found</h1>
		<p class="page-hero__lead">The page may have moved. These are good places to continue:</p>
	</div>
</header>

<section class="section section--tight">
	<div class="wrap">
		<ul class="link-list link-list--inline">
			<li><a class="text-link" href="<?php echo esc_url( home_url( '/our-services/' ) ); ?>">Our services</a></li>
			<li><a class="text-link" href="<?php echo esc_url( home_url( '/news-and-updates/' ) ); ?>">News &amp; updates</a></li>
			<li><a class="text-link" href="<?php echo esc_url( home_url( '/our-team/' ) ); ?>">Our team</a></li>
			<li><a class="text-link" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">Contact</a></li>
		</ul>
		<div style="margin-top:2rem"><?php silq_search_form(); ?></div>
	</div>
</section>

<?php
get_footer();
