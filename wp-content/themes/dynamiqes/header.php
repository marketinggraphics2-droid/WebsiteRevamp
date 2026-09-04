<?php
/**
 * Document head, top CTA bar and primary navigation (shared by every page).
 *
 * @package dynamiqes
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php if ( ! has_site_icon() ) : ?>
<link rel="icon" type="image/svg+xml" href="<?php echo esc_url( DQ_URI . '/assets/logos/IQ_Logo.svg' ); ?>">
<?php endif; ?>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'dynamiqes' ); ?></a>

<div class="header-form">
	<div class="wrap">
		<div class="header-form__cta">
			<p><?php echo esc_html( get_theme_mod( 'dq_header_cta_text', 'Something slowing your business down?' ) ); ?></p>
			<a href="<?php echo esc_url( dq_home_anchor( 'contact' ) ); ?>"><?php echo esc_html( get_theme_mod( 'dq_header_cta_label', "Let's Figure it out!" ) ); ?> <span class="arr" aria-hidden="true">→</span></a>
		</div>
		<?php /* Top-bar search · UI only for now (no form, nothing submits). Swap for get_search_form() when it goes live. */ ?>
		<div class="topbar-search" role="search">
			<input type="search" class="topbar-search__input" placeholder="<?php esc_attr_e( 'Search…', 'dynamiqes' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'dynamiqes' ); ?>" autocomplete="off">
			<button type="button" class="topbar-search__btn" aria-label="<?php esc_attr_e( 'Search', 'dynamiqes' ); ?>"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.8-3.8"/></svg></button>
		</div>
	</div>
</div>

<nav class="nav" id="nav" aria-label="<?php esc_attr_e( 'Primary', 'dynamiqes' ); ?>">
	<div class="wrap">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-link" aria-label="<?php esc_attr_e( 'DynamIQ home', 'dynamiqes' ); ?>"><?php echo dq_logo_img( 'nav' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<input type="checkbox" id="navCheck" class="nav-check" aria-label="<?php esc_attr_e( 'Toggle menu', 'dynamiqes' ); ?>">
		<?php dq_primary_menu(); ?>
		<label for="navCheck" class="nav-burger" aria-hidden="true"><span></span><span></span><span></span></label>
	</div>
</nav>
