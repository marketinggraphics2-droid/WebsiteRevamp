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
?>
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
<?php get_template_part( 'template-parts/chatbot' ); // mockup widget, no backend yet ?>
<?php wp_footer(); ?>
</body>
</html>
