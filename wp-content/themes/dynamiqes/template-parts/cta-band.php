<?php
/**
 * Shared "We'd like to hear from you" CTA band — closes the products listing, product pages,
 * blog/archive pages and generic pages. Texts are editable in Customize → DynamIQ Theme.
 *
 * The title is a styled paragraph, not a heading: the live dynamiqes.com pages end without an
 * extra H2, and the sub-pages must keep the old heading outline (SEO). Pages where the old site
 * does have a heading here pass it in: dq_cta_band( array( 'heading' => 'Have a Question or Need
 * a Demo?' ) ) renders an H2 with that text (blog posts).
 *
 * @package dynamiqes
 */

$args         = isset( $args ) && is_array( $args ) ? $args : array();
$dq_cta_title = get_theme_mod( 'dq_cta_title', __( "We'd like to hear from you", 'dynamiqes' ) );
$dq_cta_text  = get_theme_mod( 'dq_cta_text', __( 'Tell us about your business and we will show you how SAP Business One and the IQ Suite can streamline your operations, compliance and growth.', 'dynamiqes' ) );
$dq_cta_label = get_theme_mod( 'dq_cta_label', __( 'Get Your Free Business Analysis', 'dynamiqes' ) );
$dq_cta_url   = get_theme_mod( 'dq_cta_url', '' );
if ( ! $dq_cta_url ) {
	$dq_cta_url = dq_book_demo_url(); // old site: /book-free-demo/
}
$heading = ! empty( $args['heading'] ) ? $args['heading'] : ''; // live H2 text, when the old page has one
if ( ! empty( $args['text'] ) ) {
	$dq_cta_text = $args['text'];
}
?>
<section class="cta-section" id="cta">
	<div class="wrap">
		<div class="cta-panel"<?php dq_reveal( 'scale' ); ?>>
			<?php if ( $heading ) : ?>
			<h2><?php echo esc_html( $heading ); ?></h2>
			<?php else : ?>
			<p class="cta-title"><?php echo esc_html( $dq_cta_title ); ?></p>
			<?php endif; ?>
			<?php if ( $dq_cta_text ) : ?><p><?php echo esc_html( $dq_cta_text ); ?></p><?php endif; ?>
			<div class="dynamiq-cta"><a href="<?php echo esc_url( $dq_cta_url ); ?>"><?php echo esc_html( $dq_cta_label ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
			<?php $dq_contact = dq_contact_info(); if ( ! empty( $dq_contact['phone1'] ) ) : ?>
			<p class="cta-phone"><a href="<?php echo esc_attr( dq_tel( $dq_contact['phone1'] ) ); ?>"><?php echo dq_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $dq_contact['phone1'] ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>
</section>
