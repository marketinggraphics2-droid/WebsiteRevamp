<?php
/**
 * About Us (/about-us/) — picked up by slug (page-{slug}.php).
 *
 * Structure mirrors dynamiqes.com/about-us/ (SEO): H1 "About DynamIQ" + intro, H2 "Mission",
 * H2 "Vision", H2 "Our Core Values" with four H4 values, H2 "Our Products" (H3 per product),
 * H2 "Services We Offer", H2 "Hear From Our Customers" (H3 per client). Copy comes from
 * inc/page-content.php (dq_about_copy(), verbatim from the live page); content typed into the
 * page in WP Admin replaces the intro. The page title stays for <title> / menus; the article H1
 * lives in _dq_about_h1 (default = live copy).
 *
 * Photos are the live page's own (assets/pages/about/): the Mission / Vision icons, the four
 * core-value icons, one screenshot per product and the "Services We Offer" monitor. A product
 * without a dedicated About screenshot falls back to its product hero (dq_get_products()).
 *
 * @package dynamiqes
 */

get_header();
the_post();

$copy     = dq_about_copy();
$headline = get_post_meta( get_the_ID(), '_dq_about_h1', true );
$headline = $headline ? $headline : $copy['h1'];
$intro    = trim( get_the_content() );
$intro    = '' === $intro ? implode( '', array_map( function ( $p ) { return '<p>' . dq_inline_html( $p ) . '</p>'; }, $copy['intro'] ) ) : apply_filters( 'the_content', $intro );
$products = dq_get_products();
$by_name  = array();
foreach ( $products as $prod ) {
	$by_name[ strtolower( $prod['name'] ) ] = $prod;
}
$testimonials = dq_testimonials();

/** Live About-page images, keyed by the slug of the heading they sit next to. */
$about_img = function ( $name ) {
	$files = array(
		'mission'          => 'mission.png',
		'vision'           => 'vision.png',
		'driven'           => 'driven.png',
		'dependable'       => 'dependable.png',
		'dedicated'        => 'dedicated.png',
		'data-security'    => 'data-security.png',
		'sap-business-one' => 'sap-business-one.png',
		'iq-portal'        => 'iq-portal.png',
		'iq-tax'           => 'iq-tax.png',
		'iq-barcode'       => 'iq-barcode.png',
		'iq-link'          => 'iq-link.png',
		'iq-desk'          => 'iq-desk.png',
		'iq-ecom'          => 'iq-ecom.png',
		'services'         => 'services-we-offer.png',
	);
	$key = sanitize_title( $name );
	return isset( $files[ $key ] ) ? dq_asset( 'assets/pages/about/' . $files[ $key ] ) : '';
};
?>
<main id="main">
	<article <?php post_class( 'about-page' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal( 'fade' ); ?>><?php the_title(); ?></span>
				<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
				<div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div>
			</div>
		</header>

		<section class="about-mv">
			<div class="wrap about-mv-grid">
				<article class="about-card"<?php dq_reveal(); ?>>
					<span class="about-icon"><img src="<?php echo esc_url( $about_img( 'mission' ) ); ?>" alt="" width="67" height="59" loading="lazy"></span>
					<span class="eyebrow"><?php esc_html_e( 'Why we exist', 'dynamiqes' ); ?></span>
					<h2><?php esc_html_e( 'Mission', 'dynamiqes' ); ?></h2>
					<p><?php echo dq_inline_html( $copy['mission'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</article>
				<article class="about-card"<?php dq_reveal( '', 100 ); ?>>
					<span class="about-icon"><img src="<?php echo esc_url( $about_img( 'vision' ) ); ?>" alt="" width="67" height="59" loading="lazy"></span>
					<span class="eyebrow"><?php esc_html_e( 'Where we are going', 'dynamiqes' ); ?></span>
					<h2><?php esc_html_e( 'Vision', 'dynamiqes' ); ?></h2>
					<p><?php echo dq_inline_html( $copy['vision'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</article>
			</div>
		</section>

		<section class="about-values">
			<div class="wrap">
				<div class="sec-head center"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'Culture', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['values_h2'] ); ?></h2>
				</div>
				<div class="career-values">
					<?php foreach ( $copy['values'] as $i => $v ) : $icon = $about_img( $v[0] ); ?>
					<article class="feature-group"<?php dq_reveal( '', $i * 90 ); ?>>
						<?php if ( $icon ) : ?><span class="about-icon about-icon--sm"><img src="<?php echo esc_url( $icon ); ?>" alt="" width="50" height="50" loading="lazy"></span><?php endif; ?>
						<h4><?php echo esc_html( $v[0] ); ?></h4>
						<p><?php echo dq_inline_html( $v[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="about-products">
			<div class="wrap">
				<div class="sec-head"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'The IQ Suite', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['products_h2'] ); ?></h2>
					<p><?php echo dq_inline_html( $copy['products_intro'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
				</div>
				<div class="product-list">
					<?php
					foreach ( $copy['products'] as $i => $ap ) :
						$prod = $by_name[ strtolower( $ap['name'] ) ] ?? null;
						$shot = $about_img( $ap['name'] );
						$shot = $shot ? $shot : ( $prod && $prod['hero'] ? $prod['hero'] : '' );
						?>
					<article class="product-row"<?php dq_reveal(); ?>>
						<?php if ( $shot ) : ?><div class="product-media"><img src="<?php echo esc_url( $shot ); ?>" alt="<?php echo esc_attr( $ap['name'] . ' interface' ); ?>" loading="lazy"></div><?php endif; ?>
						<div class="product-copy">
							<?php if ( $prod && $prod['logo'] ) : ?><img class="product-logo" src="<?php echo esc_url( $prod['logo'] ); ?>" alt="<?php echo esc_attr( $ap['name'] ); ?>" loading="lazy"><?php endif; ?>
							<h3><?php echo esc_html( $ap['name'] ); ?></h3>
							<?php foreach ( $ap['paras'] as $para ) : ?><p><?php echo dq_inline_html( $para ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
							<a class="text-link" href="<?php echo esc_url( $prod ? $prod['url'] : dq_products_url() ); ?>"><?php esc_html_e( 'VIEW PRODUCT', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="about-services">
			<div class="wrap about-services-grid">
				<div<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'What we do', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['services_h2'] ); ?></h2>
					<p><?php echo dq_inline_html( $copy['services_p'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<div class="hero-actions"><a class="btn btn-primary" href="<?php echo esc_url( dq_services_url() ); ?>"><?php esc_html_e( 'GO TO SERVICES', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a></div>
				</div>
				<figure class="about-services-media"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $about_img( 'services' ) ); ?>" alt="<?php echo esc_attr( $copy['services_h2'] ); ?>" width="628" height="513" loading="lazy"></figure>
			</div>
		</section>

		<?php if ( $testimonials ) : ?>
		<section class="hear-from-our-customer" id="testimonials"><?php /* same marquee as the homepage testimonials; the client name is an H3 here because the live about page has one per testimonial */ ?>
			<div class="wrap">
				<div class="sec-head"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'What they say', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['customers_h2'] ); ?></h2>
				</div>
			</div>
			<div class="stories-marq">
				<div class="stories-track">
					<?php foreach ( $testimonials as $t ) : ?>
					<article class="story">
						<div class="story-head">
							<span class="story-logo"><?php if ( $t['logo'] ) : ?><img src="<?php echo esc_url( $t['logo'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>" loading="lazy"><?php endif; ?></span>
							<span class="story-q" aria-hidden="true">&rdquo;</span>
						</div>
						<blockquote><?php echo esc_html( $t['quote'] ); ?></blockquote>
						<div class="story-foot">
							<div class="story-who"><h3 class="story-name"><?php echo esc_html( $t['name'] ); ?></h3><?php if ( ! empty( $t['role'] ) ) : ?><p class="story-role"><?php echo esc_html( $t['role'] ); ?></p><?php endif; ?></div>
							<?php if ( ! empty( $t['more'] ) ) : ?><a class="story-more" href="<?php echo esc_url( ! empty( $t['link'] ) ? $t['link'] : '#testimonials' ); ?>"><?php echo esc_html( $t['more'] ); ?> →</a><?php endif; ?>
						</div>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
