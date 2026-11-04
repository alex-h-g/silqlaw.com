<?php
/**
 * Default page.
 *
 * - Elementor pages that used the old theme's header banner (themo-header): the theme renders the hero from that
 *   banner's own title/subtitle, then the Elementor content (the old banner widget is hidden by CSS).
 * - Elementor pages without that banner (e.g. landing pages): rendered exactly as designed — no added title.
 * - Normal editor pages: hero with the page title, content in a reading column.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;

get_header();
the_post();

$is_elementor = 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true );
$banner       = $is_elementor ? silq_legacy_banner( get_the_ID() ) : null;

if ( ! $is_elementor || $banner ) :
	$title = $banner['title'] ?? ( get_post_meta( get_the_ID(), '_silq_headline', true ) ?: get_the_title() );
	$lead  = $banner['lead'] ?? ( get_post_meta( get_the_ID(), '_silq_lead', true ) ?: ( has_excerpt() ? get_the_excerpt() : '' ) );
	?>
	<header class="page-hero page-hero--plain">
		<div class="wrap page-hero__content">
			<?php silq_breadcrumbs(); ?>
			<h1 class="page-hero__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( $lead ) : ?><p class="page-hero__lead"><?php echo esc_html( $lead ); ?></p><?php endif; ?>
		</div>
	</header>
<?php endif; ?>

<?php if ( $is_elementor ) : ?>
	<div class="elementor-content<?php echo $banner ? ' has-theme-hero' : ''; ?>">
		<?php the_content(); ?>
	</div>
<?php else : ?>
	<div class="wrap section">
		<div class="prose prose--page">
			<?php the_content(); ?>
		</div>
	</div>
<?php endif; ?>

<?php
get_footer();
