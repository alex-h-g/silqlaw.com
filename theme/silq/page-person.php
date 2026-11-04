<?php
/**
 * Template Name: Team profile
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$slug   = get_post_field( 'post_name' );
$member = current( array_filter( silq_team(), fn( $m ) => $m['slug'] === $slug ) ) ?: array( 'name' => get_the_title(), 'role' => '', 'office' => '', 'img' => (int) get_post_thumbnail_id() );
?>

<header class="page-hero page-hero--plain">
	<div class="wrap page-hero__content">
		<?php silq_breadcrumbs(); ?>
		<h1 class="page-hero__title"><?php echo esc_html( $member['name'] ); ?></h1>
		<p class="page-hero__lead"><?php echo esc_html( $member['role'] . ( $member['office'] ? ' · ' . $member['office'] : '' ) ); ?></p>
	</div>
</header>

<div class="wrap section profile">
	<div class="profile__photo">
		<?php echo silq_img( (int) $member['img'], 'large', array( 'alt' => $member['name'], 'class' => 'profile__image', 'loading' => 'eager', 'sizes' => '(min-width: 52rem) 30vw, 100vw' ) ); // phpcs:ignore ?>
	</div>
	<article class="prose">
		<?php the_content(); ?>
		<p><a class="text-link" href="<?php echo esc_url( home_url( '/our-team/' ) ); ?>">Back to the team <?php echo silq_icon( 'arrow' ); // phpcs:ignore ?></a></p>
	</article>
</div>

<?php get_template_part( 'template-parts/contact-band' ); ?>

<?php
get_footer();
