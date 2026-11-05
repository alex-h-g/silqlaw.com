<?php
/**
 * Template Name: Topics
 *
 * Every topic with two or more articles, most-read first.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();
$topics  = silq_all_topics();
$popular = array_slice( $topics, 0, 12, true );
$more    = array_slice( $topics, 12, null, true );
$chip    = function ( WP_Term $t, string $label ) {
	printf( '<li><a class="chip" href="%s">%s <span class="chip__count">%d</span></a></li>', esc_url( get_term_link( $t ) ), esc_html( $label ), (int) $t->count );
};
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">Topics</h1>
		<p class="page-hero__lead">Find our articles by subject. Pick a topic to see everything we have written on it, newest first.</p>
	</div>
</header>

<section class="section">
	<div class="wrap topics">
		<h2>Popular topics</h2>
		<ul class="topic-list topic-list--popular">
			<?php foreach ( $popular as $label => $t ) { $chip( $t, $label ); } ?>
		</ul>
		<?php if ( $more ) : ?>
			<h2>More topics</h2>
			<ul class="topic-list">
				<?php
				uksort( $more, 'strcasecmp' );
				foreach ( $more as $label => $t ) {
					$chip( $t, $label );
				}
				?>
			</ul>
		<?php endif; ?>
		<div class="topics__search">
			<h2>Looking for something else?</h2>
			<?php silq_search_form(); ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/contact-band' ); ?>

<?php
get_footer();
