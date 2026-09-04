<?php
/**
 * Product detail page (SAP Business One and every IQ Suite module).
 *
 * @package dynamiqes
 */

get_header();
the_post();
$p = dq_get_product( get_the_ID() );
?>
<main id="main">
	<section class="detail-hero">
		<div class="product-hero-bg" aria-hidden="true" style="--product-hero-bg:url('<?php echo esc_url( $p['background'] ); ?>')"></div>
		<div class="wrap">
			<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamiqes' ); ?>"><a href="<?php echo esc_url( dq_products_url() ); ?>"><?php esc_html_e( 'Our Products', 'dynamiqes' ); ?></a> / <?php echo esc_html( $p['name'] ); ?></nav>
			<div class="hero-grid">
				<div class="hero-copy">
					<?php if ( $p['logo'] ) : ?><img class="hero-logo" src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>"<?php dq_reveal(); ?>><?php endif; ?>
					<h1<?php dq_reveal(); ?>><?php echo esc_html( $p['title'] ); ?></h1>
					<p<?php dq_reveal(); ?>><?php echo esc_html( $p['description'] ); ?></p>
					<div class="hero-actions">
						<a class="btn btn-primary" href="<?php echo esc_url( dq_home_anchor( 'contact' ) ); ?>"><?php esc_html_e( 'INQUIRE NOW', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
						<a class="btn btn-outline" href="#overview"><?php esc_html_e( 'PRODUCT OVERVIEW', 'dynamiqes' ); ?></a>
					</div>
				</div>
				<div class="hero-media"<?php dq_reveal(); ?>>
					<?php if ( $p['hero'] ) : ?><img src="<?php echo esc_url( $p['hero'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' software displayed on a monitor' ); ?>" fetchpriority="high"><?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="overview" id="overview">
		<div class="wrap overview-grid">
			<div class="overview-media"<?php dq_reveal(); ?>>
				<?php if ( $p['overview_image'] ) : ?><img src="<?php echo esc_url( $p['overview_image'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' product overview' ); ?>" loading="lazy"><?php endif; ?>
			</div>
			<div class="overview-copy">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<h2<?php dq_reveal(); ?>><?php esc_html_e( 'Product Overview', 'dynamiqes' ); ?></h2>
				<?php foreach ( $p['overview'] as $para ) : ?><p<?php dq_reveal(); ?>><?php echo esc_html( $para ); ?></p><?php endforeach; ?>
				<?php if ( $p['closing'] ) : ?><p class="closing"<?php dq_reveal(); ?>><?php echo esc_html( $p['closing'] ); ?></p><?php endif; ?>
				<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : // extra copy written in the block editor ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<section class="features portal-features">
		<div class="wrap portal-features-layout">
			<div class="portal-features-copy">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<h2<?php dq_reveal(); ?>><?php echo esc_html( sprintf( __( '%s Features', 'dynamiqes' ), $p['name'] ) ); ?></h2>
				<p<?php dq_reveal(); ?>><?php echo esc_html( $p['features_intro'] ); ?></p>
				<div class="feature-grid">
					<?php foreach ( $p['features'] as $g ) : ?>
					<article class="feature-group"<?php dq_reveal(); ?>>
						<h3><?php echo esc_html( $g['title'] ); ?></h3>
						<ul><?php foreach ( $g['items'] as $it ) : ?><li><?php echo esc_html( $it ); ?></li><?php endforeach; ?></ul>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
			<?php if ( $p['feature_image'] ) : ?>
			<div class="showcase-frame"<?php dq_reveal(); ?>><img src="<?php echo esc_url( $p['feature_image'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' feature interface' ); ?>" loading="lazy"></div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( ! empty( $p['faqs'] ) ) : ?>
	<section class="faq">
		<div class="wrap faq-grid">
			<div class="faq-head">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<h2<?php dq_reveal(); ?>><?php esc_html_e( 'Frequently Asked Questions (FAQ)', 'dynamiqes' ); ?></h2>
			</div>
			<div class="faq-list"<?php dq_reveal(); ?>>
				<?php foreach ( $p['faqs'] as $f ) : ?>
				<details><summary><?php echo esc_html( $f[0] ); ?></summary><p><?php echo esc_html( $f[1] ); ?></p></details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
