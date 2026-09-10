<?php
/**
 * Thank-you confirmation page.
 *
 * Shared by /thank-you-ad/ and /thank-you-accounting/, which the review found "not
 * formatted/designed properly" and closing with a CTA/contact band that makes no sense on a
 * confirmation page (items G3-G6). One centred panel that confirms the submission and offers
 * the next steps — and no CTA band.
 *
 * $args: 'next' => array of [ label, url, note ] links shown under the panel.
 *
 * @package dynamiqes
 */

$args     = isset( $args ) && is_array( $args ) ? $args : array();
$headline = get_post_meta( get_the_ID(), '_dq_landing_h1', true );
$headline = $headline ? $headline : get_the_title();
$intro    = get_post_meta( get_the_ID(), '_dq_landing_intro', true );
$typed    = trim( get_the_content() );
$contact  = dq_contact_info();

if ( '' !== $typed ) {
	$copy = apply_filters( 'the_content', $typed ); // copy typed into the page in WP Admin wins
} elseif ( $intro ) {
	$copy = wp_kses_post( $intro ); // the live page's own line
} else {
	$copy = '<p>' . esc_html__( 'We are thrilled to hear from you! Our team will get back in touch with you soon.', 'dynamiqes' ) . '</p>';
}

$next = ! empty( $args['next'] ) && is_array( $args['next'] ) ? $args['next'] : array();
?>
<main id="main">
	<article <?php post_class( 'thankyou-page' ); ?>>
		<section class="thankyou">
			<div class="wrap">
				<div class="thankyou-panel"<?php dq_reveal( 'scale' ); ?>>
					<span class="thankyou-mark" aria-hidden="true">
						<svg viewBox="0 0 24 24" focusable="false"><path d="M9.6 16.6 5 12l1.4-1.4 3.2 3.2 8-8L19 7.2l-9.4 9.4z"/></svg>
					</span>
					<h1><?php echo esc_html( $headline ); ?></h1>
					<div class="thankyou-copy"><?php echo $copy; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
					<?php if ( ! empty( $contact['phone1'] ) ) : ?>
					<p class="thankyou-phone"><?php esc_html_e( 'Need us sooner?', 'dynamiqes' ); ?> <a href="<?php echo esc_attr( dq_tel( $contact['phone1'] ) ); ?>"><?php echo esc_html( $contact['phone1'] ); ?></a></p>
					<?php endif; ?>
				</div>
				<?php if ( $next ) : ?>
				<div class="thankyou-next">
					<p class="thankyou-next-label"<?php dq_reveal( 'fade' ); ?>><?php esc_html_e( 'While you are here', 'dynamiqes' ); ?></p>
					<div class="thankyou-next-grid">
						<?php foreach ( $next as $i => $link ) : ?>
						<a class="thankyou-next-card" href="<?php echo esc_url( $link[1] ); ?>"<?php dq_reveal( '', $i * 70 ); ?>>
							<span class="thankyou-next-title"><?php echo esc_html( $link[0] ); ?></span>
							<?php if ( ! empty( $link[2] ) ) : ?><span class="thankyou-next-note"><?php echo esc_html( $link[2] ); ?></span><?php endif; ?>
							<span class="arr" aria-hidden="true">&rarr;</span>
						</a>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
			</div>
		</section>
	</article>
	<?php /* no closing CTA band: the review asked for it off these pages (items G4/G6) */ ?>
</main>
