<?php
/**
 * Template Name: Insights (News & Updates)
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$q     = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 13,
		'paged'               => $paged,
		'ignore_sticky_posts' => true,
	)
);
// Pagination helpers read the global query var.
set_query_var( 'paged', $paged );
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">News &amp; legal updates</h1>
		<p class="page-hero__lead">Plain-English updates on Thai law for business owners, investors and people living in Thailand.</p>
	</div>
</header>

<section class="section section--tight">
	<div class="wrap">
		<div class="archive-tools">
			<?php silq_topic_chips(); ?>
			<?php silq_search_form(); ?>
		</div>

		<div class="insight-grid">
			<?php
			$n = 0;
			while ( $q->have_posts() ) :
				$q->the_post();
				get_template_part( 'template-parts/card', null, array( 'lead' => 1 === $paged && 0 === $n ) );
				$n++;
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<?php silq_pagination( $q ); ?>
	</div>
</section>

<?php
get_footer();
