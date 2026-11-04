<?php
/**
 * Article card. Args: lead (bool) — larger first card.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

$lead    = ! empty( $args['lead'] );
$authors = function_exists( 'get_post_authors' ) ? get_post_authors( get_the_ID() ) : array();
$names   = $authors ? wp_list_pluck( $authors, 'display_name' ) : array( get_the_author() );
?>
<article class="card<?php echo $lead ? ' card--lead' : ''; ?>">
	<a class="card__link" href="<?php the_permalink(); ?>">
		<?php if ( has_post_thumbnail() ) : ?>
			<?php the_post_thumbnail( $lead ? 'large' : 'medium_large', array( 'class' => 'card__image', 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => $lead ? '(min-width: 64rem) 50vw, 100vw' : '(min-width: 64rem) 25vw, 100vw' ) ); ?>
		<?php endif; ?>
		<span class="card__body">
			<span class="card__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time></span>
			<span class="card__title"><?php the_title(); ?></span>
			<?php if ( $lead ) : ?>
				<span class="card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></span>
			<?php endif; ?>
			<span class="card__byline"><?php echo esc_html( implode( ', ', $names ) ); ?></span>
		</span>
	</a>
</article>
