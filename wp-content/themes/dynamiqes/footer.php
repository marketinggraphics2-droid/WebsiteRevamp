<?php
/**
 * Site footer (shared by every page).
 *
 * @package dynamiqes
 */

$c        = dq_contact_info();
$privacy  = get_privacy_policy_url();
$quick    = array(
	array( 'SAP System Services and Solutions Philippines', home_url( '/' ) ),
	array( 'SAP Business One', home_url( '/products/sap-business-one/' ) ),
	array( __( 'About Us', 'dynamiqes' ), dq_home_anchor( 'about' ) ),
	array( __( 'Contact Us', 'dynamiqes' ), dq_home_anchor( 'contact' ) ),
	array( __( 'Privacy Policy', 'dynamiqes' ), $privacy ? $privacy : dq_home_anchor( 'contact' ) ),
);
$explore  = array(
	array( __( 'Our Products', 'dynamiqes' ), dq_products_url() ),
	array( __( 'Our Services', 'dynamiqes' ), dq_home_anchor( 'services' ) ),
	array( __( 'Blogs', 'dynamiqes' ), dq_blog_url() ),
	array( __( 'News & Events', 'dynamiqes' ), dq_news_url() ),
	array( __( 'Testimonials', 'dynamiqes' ), dq_home_anchor( 'testimonials' ) ),
);
/* Home-page video marquee above the footer — same treatment as the Life-at-DynamIQ
 * photo marquee mid-page, but with clips: the four newest video uploads in the media
 * library (see dq_video_wall_items() for the placeholder top-up rules). Sources are
 * attached lazily by main.js so the clips never delay the rest of the page. */
$videos = is_front_page() ? dq_video_wall_items( 4 ) : array();
?>
<div class="site-end">
<?php if ( $videos ) : ?>
<section class="video-wall" aria-label="<?php esc_attr_e( 'DynamIQ in motion', 'dynamiqes' ); ?>">
	<div class="video-marq"<?php dq_reveal( 'fade' ); ?>>
		<div class="video-track">
			<?php foreach ( array( false, true ) as $dup ) : // second pass is the seamless-loop copy ?>
			<?php foreach ( $videos as $v ) : ?>
				<figure class="video-tile" data-video="<?php echo esc_url( $v['video'] ); ?>" data-poster="<?php echo esc_url( $v['poster'] ); ?>" data-label="<?php echo esc_attr( $v['label'] ); ?>"<?php echo $dup ? ' aria-hidden="true"' : ' role="button" tabindex="0" aria-label="' . esc_attr( sprintf( /* translators: %s: clip label */ __( 'Open %s in full view', 'dynamiqes' ), $v['label'] ) ) . '"'; ?>>
					<?php /* No src in the markup on purpose: the clips are large, the tiles are shipped twice
					         and main.js clones more sets to fill the track, so a src here would start every copy
					         downloading with the page. main.js attaches the tile's data-video once the wall is
					         near the viewport (see "5b · Video wall"). */ ?>
					<video muted loop playsinline preload="none"<?php if ( ! empty( $v['poster'] ) ) : ?> poster="<?php echo esc_url( $v['poster'] ); ?>"<?php endif; ?>></video>
					<span class="video-tile-hint" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z"/></svg></span>
				</figure>
			<?php endforeach; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>
<footer class="footer">
	<div class="wrap">
		<div class="footer-top">
			<div class="footer-brand"<?php dq_reveal(); ?>>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-link" aria-label="<?php esc_attr_e( 'DynamIQ Enterprise Solution Inc.', 'dynamiqes' ); ?>"><?php echo dq_logo_img( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<p class="name">DYNAMIQ ENTERPRISE SOLUTION INC.</p>
				<p class="addr"><?php echo esc_html( $c['address_short'] ); ?></p>
				<div class="socials">
					<?php foreach ( dq_socials() as $s ) : ?>
						<a href="<?php echo esc_url( $s[1] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $s[0] ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo esc_attr( $s[2] ); ?>"/></svg></a>
					<?php endforeach; ?>
					<a href="mailto:<?php echo esc_attr( $c['email'] ); ?>" aria-label="<?php esc_attr_e( 'Email', 'dynamiqes' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm0 4 8 5 8-5V6l-8 5-8-5v2z"/></svg></a>
				</div>
			</div>
			<div<?php dq_reveal( '', 80 ); ?>>
				<h4><?php esc_html_e( 'Quick Links', 'dynamiqes' ); ?></h4>
				<?php dq_footer_links( 'footer-quick', $quick ); ?>
			</div>
			<div<?php dq_reveal( '', 160 ); ?>>
				<h4><?php esc_html_e( 'Explore', 'dynamiqes' ); ?></h4>
				<?php dq_footer_links( 'footer-explore', $explore ); ?>
			</div>
			<div<?php dq_reveal( '', 240 ); ?>>
				<h4><?php esc_html_e( 'Contact', 'dynamiqes' ); ?></h4>
				<ul>
					<li><?php echo esc_html( $c['address'] ); ?></li>
					<?php if ( $c['phone1'] ) : ?><li><a href="<?php echo esc_attr( dq_tel( $c['phone1'] ) ); ?>"><?php echo esc_html( $c['phone1'] ); ?></a></li><?php endif; ?>
					<?php if ( $c['phone2'] ) : ?><li><a href="<?php echo esc_attr( dq_tel( $c['phone2'] ) ); ?>"><?php echo esc_html( $c['phone2'] ); ?></a></li><?php endif; ?>
					<li><a href="mailto:<?php echo esc_attr( $c['email'] ); ?>"><?php echo esc_html( $c['email'] ); ?></a></li>
					<li><?php echo esc_html( $c['hours'] ); ?></li>
				</ul>
			</div>
		</div>
		<div class="footer-base">
			<p>Copyright © <?php echo esc_html( gmdate( 'Y' ) ); ?> <b>DYNAMIQ Enterprise Solution</b>.<?php $credit = get_theme_mod( 'dq_footer_credit', 'SEO by SEO-HACKER. Optimized and Maintained by Sean Si' ); if ( $credit ) { echo ' ' . esc_html( $credit ); } ?></p>
		</div>
	</div>
</footer>
</div><!-- .site-end -->
<?php if ( $videos ) : ?>
<!-- video lightbox: one shared player, fed by whichever tile was clicked. Lives outside
     .site-end / .video-wall (both position:relative + z-index) so its own z-index wins
     over the sticky nav and the chat widget. -->
<div class="video-lightbox" hidden>
	<div class="video-lightbox-backdrop" data-close></div>
	<div class="video-lightbox-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Video', 'dynamiqes' ); ?>">
		<button type="button" class="video-lightbox-close" data-close aria-label="<?php esc_attr_e( 'Close video', 'dynamiqes' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
		<div class="video-lightbox-stage"></div><!-- the clicked tile's own <video> is moved in here, so playback simply continues -->
		<p class="video-lightbox-cap"></p>
	</div>
</div>
<?php endif; ?>
<?php get_template_part( 'template-parts/chatbot' ); // mockup widget, no backend yet ?>
<?php wp_footer(); ?>
</body>
</html>
