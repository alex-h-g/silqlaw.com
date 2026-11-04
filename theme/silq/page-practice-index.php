<?php
/**
 * Template Name: Services index
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$pages   = silq_practice_pages();
$filters = silq_practice_filter_terms();
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title">How we can help</h1>
		<p class="page-hero__lead">Legal help for business owners, investors and families in Thailand — from setting up a company and buying property to visas, licences and disputes. Pick a topic to see how the law works and what we do.</p>
	</div>
</header>

<section class="section section--tight">
	<div class="wrap">
		<?php if ( $filters ) : ?>
			<div class="filters" role="group" aria-label="<?php esc_attr_e( 'Filter services', 'silq' ); ?>" data-filters="practice">
				<button type="button" class="chip" aria-pressed="true" data-filter="*">All</button>
				<?php foreach ( $filters as $t ) : ?>
					<button type="button" class="chip" aria-pressed="false" data-filter="<?php echo esc_attr( $t->slug ); ?>"><?php echo esc_html( $t->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<ul class="practice-grid" data-filter-list="practice">
			<?php
			foreach ( $pages as $p ) :
				$types = silq_practice_groups( $p->post_name );
				$intro = get_post_meta( $p->ID, '_yoast_wpseo_metadesc', true ) ?: wp_trim_words( wp_strip_all_tags( $p->post_content ), 22 );
				?>
				<li class="practice-tile" data-types="<?php echo esc_attr( implode( ' ', $types ) ); ?>">
					<a href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<?php echo get_the_post_thumbnail( $p, 'medium_large', array( 'class' => 'practice-tile__image', 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(min-width: 64rem) 30vw, 100vw' ) ); ?>
						<span class="practice-tile__body">
							<span class="practice-tile__title"><?php echo esc_html( silq_practice_title( $p ) ); ?></span>
							<span class="practice-tile__text"><?php echo esc_html( wp_trim_words( $intro, 24 ) ); ?></span>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>

<?php
get_footer();
