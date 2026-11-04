<?php
/**
 * Single article.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

ob_start();
the_content();
$body = ob_get_clean();
$toc  = $GLOBALS['silq_toc'] ?? array();

$authors = silq_post_authors();
$tags    = get_the_tags() ?: array();
$topics  = array_values( array_filter( $tags, fn( $t ) => isset( silq_topics()[ $t->slug ] ) ) );

$related = new WP_Query(
	array(
		'post_type'           => 'post',
		'posts_per_page'      => 3,
		'post__not_in'        => array( get_the_ID() ),
		'tag__in'             => wp_list_pluck( $topics ?: $tags, 'term_id' ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
	)
);
?>

<article class="article">
	<header class="article__head wrap">
		<?php silq_breadcrumbs(); ?>
		<?php if ( $topics ) : ?>
			<p class="eyebrow">
				<?php
				echo implode( ' · ', array_map( fn( $t ) => '<a href="' . esc_url( get_tag_link( $t ) ) . '">' . esc_html( silq_topics()[ $t->slug ] ) . '</a>', array_slice( $topics, 0, 2 ) ) ); // phpcs:ignore
				?>
			</p>
		<?php endif; ?>
		<h1 class="article__title"><?php the_title(); ?></h1>
		<p class="article__meta">
			<span class="article__authors"><?php
			echo implode( ', ', array_map( fn( $a ) => '<a href="' . esc_url( $a['url'] ) . '">' . esc_html( $a['name'] ) . '</a>', $authors ) ); // phpcs:ignore
			?></span>
			<span aria-hidden="true">·</span>
			<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
			<span aria-hidden="true">·</span>
			<?php echo esc_html( silq_reading_time() ); ?> min read
		</p>
	</header>

	<?php if ( has_post_thumbnail() ) : ?>
		<figure class="article__image wrap">
			<?php the_post_thumbnail( 'full', array( 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 78rem) 78rem, 100vw' ) ); ?>
		</figure>
	<?php endif; ?>

	<div class="wrap layout-aside layout-aside--article">
		<?php if ( count( $toc ) >= 3 ) : ?>
			<aside class="aside toc" aria-label="Contents">
				<h2 class="aside__heading">Contents</h2>
				<ol>
					<?php foreach ( $toc as list( $id, $text ) ) : ?>
						<li><a href="#<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $text ); ?></a></li>
					<?php endforeach; ?>
				</ol>
			</aside>
		<?php endif; ?>

		<div class="prose article__body">
			<?php echo $body; // phpcs:ignore ?>

			<?php if ( $tags ) : ?>
				<ul class="tag-list" aria-label="Tags">
					<?php foreach ( $tags as $t ) : ?>
						<li><a class="chip" href="<?php echo esc_url( get_tag_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="authors">
				<?php foreach ( $authors as $a ) : ?>
					<div class="author">
						<?php if ( $a['img'] ) : ?><img class="author__img" src="<?php echo esc_url( $a['img'] ); ?>" alt="" width="64" height="64" loading="lazy"><?php endif; ?>
						<div>
							<p class="author__name"><a href="<?php echo esc_url( $a['url'] ); ?>"><?php echo esc_html( $a['name'] ); ?></a></p>
							<?php if ( $a['bio'] ) : ?><p class="author__bio"><?php echo esc_html( wp_trim_words( $a['bio'], 40 ) ); ?></p><?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<p class="article__note">This article is general information on Thai law at the date of publication and is not legal advice for any specific situation. For questions about your own circumstances, <a href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>">contact us</a>.</p>
		</div>
	</div>
</article>

<?php if ( $related->have_posts() ) : ?>
<section class="section section--mist">
	<div class="wrap">
		<header class="section-head"><h2>Related reading</h2></header>
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

<?php
get_footer();
