<?php
/**
 * Search results.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">Search</h1>
		<?php silq_search_form(); ?>
	</div>
</header>

<section class="section section--tight">
	<div class="wrap">
		<?php if ( have_posts() ) : ?>
			<p class="small"><?php echo esc_html( sprintf( '%d results for “%s”', (int) $GLOBALS['wp_query']->found_posts, get_search_query() ) ); ?></p>
			<div class="insight-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card' );
				endwhile;
				?>
			</div>
			<?php silq_pagination(); ?>
		<?php else : ?>
			<p>Nothing matched “<?php echo esc_html( get_search_query() ); ?>”. Try a broader term, or browse <a href="<?php echo esc_url( home_url( '/news-and-updates/' ) ); ?>">all insights</a>.</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
