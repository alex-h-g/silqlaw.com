<?php
/**
 * Site header.
 *
 * @package silq
 */

defined( 'ABSPATH' ) || exit;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'silq' ); ?></a>

<header class="site-header" data-header>
	<div class="wrap site-header__inner">
		<a class="site-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'SILQ Law — home', 'silq' ); ?>">
			<?php echo silq_logo( 'light', array( 'class' => 'site-logo__img', 'fetchpriority' => 'high' ) ); // phpcs:ignore ?>
		</a>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" data-nav-toggle>
			<?php echo silq_icon( 'menu' ); // phpcs:ignore ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'silq' ); ?></span>
		</button>

		<nav class="site-nav" id="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'silq' ); ?>" data-nav>
			<ul class="site-nav__list">
				<?php foreach ( silq_nav_items() as list( $label, $path ) ) : ?>
					<li><a href="<?php echo esc_url( home_url( $path ) ); ?>"<?php echo ( untrailingslashit( wp_parse_url( home_url( $path ), PHP_URL_PATH ) ) === untrailingslashit( (string) wp_parse_url( $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH ) ) ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<div class="site-nav__actions">
				<a class="button button--small" href="<?php echo esc_url( home_url( '/contact-us/' ) ); ?>"><?php esc_html_e( 'Contact us', 'silq' ); ?></a>
			</div>
		</nav>
	</div>
</header>

<main id="main" class="site-main">
