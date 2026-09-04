<?php
/**
 * Shared "We'd like to hear from you" CTA band — closes the products listing, product pages,
 * blog/archive pages and generic pages. Texts are editable in Customize → DynamIQ Theme.
 *
 * @package dynamiqes
 */

$dq_cta_title = get_theme_mod( 'dq_cta_title', __( "We'd like to hear from you", 'dynamiqes' ) );
$dq_cta_text  = get_theme_mod( 'dq_cta_text', __( 'Tell us about your business and we will show you how SAP Business One and the IQ Suite can streamline your operations, compliance and growth.', 'dynamiqes' ) );
$dq_cta_label = get_theme_mod( 'dq_cta_label', __( 'Get Your Free Business Analysis', 'dynamiqes' ) );
$dq_cta_url   = get_theme_mod( 'dq_cta_url', '' );
if ( ! $dq_cta_url ) {
	$dq_cta_url = dq_home_anchor( 'contact' );
}
?>
<section class="cta-section" id="cta">
	<div class="wrap">
		<div class="cta-panel"<?php dq_reveal( 'scale' ); ?>>
			<h2><?php echo esc_html( $dq_cta_title ); ?></h2>
			<?php if ( $dq_cta_text ) : ?><p><?php echo esc_html( $dq_cta_text ); ?></p><?php endif; ?>
			<div class="dynamiq-cta"><a href="<?php echo esc_url( $dq_cta_url ); ?>"><?php echo esc_html( $dq_cta_label ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
			<?php $dq_contact = dq_contact_info(); if ( ! empty( $dq_contact['phone1'] ) ) : ?>
			<p class="cta-phone"><a href="<?php echo esc_attr( dq_tel( $dq_contact['phone1'] ) ); ?>"><?php echo dq_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $dq_contact['phone1'] ); ?></a></p>
			<?php endif; ?>
		</div>
	</div>
</section>
