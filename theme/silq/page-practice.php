<?php
/**
 * Template Name: Service page
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$slug       = get_post_field( 'post_name' );
$headline   = get_post_meta( get_the_ID(), '_silq_headline', true ) ?: silq_practice_title( get_post() );
$standfirst = get_post_meta( get_the_ID(), '_silq_standfirst', true ) ?: ( has_excerpt() ? get_the_excerpt() : '' );
list( $group, $siblings ) = silq_practice_siblings( $slug );
$related    = silq_related_insights( $slug );
?>

<header class="page-hero<?php echo has_post_thumbnail() ? ' page-hero--image' : ''; ?>">
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'full', array( 'class' => 'page-hero__image', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '100vw' ) ); ?>
	<?php endif; ?>
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<?php if ( $group ) : ?><p class="eyebrow eyebrow--light"><?php echo esc_html( $group ); ?></p><?php endif; ?>
		<h1 class="page-hero__title"><?php echo esc_html( $headline ); ?></h1>
		<?php if ( $standfirst ) : ?><p class="page-hero__lead"><?php echo esc_html( $standfirst ); ?></p><?php endif; ?>
	</div>
</header>

<div class="wrap layout-aside section">
	<article class="prose">
		<?php the_content(); ?>
	</article>

	<aside class="aside" aria-label="<?php esc_attr_e( 'About this service', 'silq' ); ?>">
		<div class="aside__card">
			<h2 class="aside__heading">Speak with us</h2>
			<p>Have a question about <?php echo esc_html( silq_practice_title( get_post() ) ); ?>? Ask us. We reply within one business day, in English or Thai.</p>
			<?php silq_contact_list( 'contact-list contact-list--dark' ); ?>
		</div>
		<?php if ( $siblings ) : ?>
			<div class="aside__block">
				<h2 class="aside__heading">Related services</h2>
				<ul class="aside__links">
					<?php foreach ( $siblings as $s ) : ?>
						<li><a href="<?php echo esc_url( silq_practice_url( $s['slug'] ) ); ?>"><?php echo esc_html( $s['title'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>
	</aside>
</div>

<?php if ( $related->have_posts() ) : ?>
<section class="section section--mist">
	<div class="wrap">
		<header class="section-head section-head--row">
			<div>
				<p class="eyebrow">News &amp; updates</p>
				<h2>Related reading</h2>
			</div>
			<a class="text-link" href="<?php echo esc_url( home_url( '/news-and-updates/' ) ); ?>">All news &amp; updates <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a>
		</header>
		<div class="insight-grid">
			<?php
			while ( $related->have_posts() ) :
				$related->the_post();
				get_template_part( 'template-parts/card' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
<?php endif; ?>

<section class="section" id="enquiry">
	<div class="wrap enquiry">
		<div class="enquiry__intro">
			<h2>Ask us a question</h2>
			<p>Tell us a little about your situation. A lawyer who works in this area will read it and reply, usually within one business day. Everything you send is kept confidential.</p>
		</div>
		<div class="enquiry__form">
			<?php echo do_shortcode( '[formidable id=5]' ); // phpcs:ignore ?>
		</div>
	</div>
</section>

<?php
get_footer();
