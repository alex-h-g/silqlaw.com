<?php
/**
 * Tag, category, author and date archives.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();

$title = '';
$lead  = '';
if ( is_tag() || is_category() ) {
	$title = single_term_title( '', false );
	$lead  = term_description();
} elseif ( is_author() ) {
	$title = get_the_author_meta( 'display_name', get_queried_object_id() );
	$lead  = get_the_author_meta( 'description', get_queried_object_id() );
} else {
	$title = wp_strip_all_tags( get_the_archive_title() );
}
$paged = max( 1, (int) get_query_var( 'paged' ) );
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<p class="eyebrow"><?php echo is_author() ? 'Author' : ( is_tag() ? 'Topic' : 'News & updates' ); ?></p>
		<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
		<?php if ( $lead ) : ?>
			<div class="page-hero__lead"><?php echo wp_kses_post( wpautop( $lead ) ); ?></div>
		<?php endif; ?>
	</div>
</header>

<section class="section section--tight">
	<div class="wrap">
		<div class="archive-tools">
			<?php silq_topic_chips(); ?>
			<?php silq_search_form(); ?>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="insight-grid">
				<?php
				$n = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/card', null, array( 'lead' => 1 === $paged && 0 === $n ) );
					$n++;
				endwhile;
				?>
			</div>
			<?php silq_pagination(); ?>
		<?php else : ?>
			<p>No articles yet.</p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
