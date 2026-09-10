<?php
/**
 * Products listing (/products/).
 *
 * @package dynamiqes
 */

get_header();

$products = dq_get_products();
$sap      = dq_get_product_by_key( 'sap' );
$suite    = array_values( array_filter( $products, function ( $p ) { return 'sap' !== $p['key']; } ) );
?>
<main id="main">
	<section class="hero">
		<div class="wrap">
			<span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'Our Products', 'dynamiqes' ); ?></span>
			<h1<?php dq_reveal(); ?>><?php esc_html_e( 'Our Solutions', 'dynamiqes' ); ?></h1>
			<p<?php dq_reveal(); ?>>We are more than just a software vendor. We devise a total ENTERPRISE RESOURCE PLANNING (ERP) SYSTEM experience that is truly customer-focused and remarkably life centered to sustain a balance between your personal and professional priorities. Here are some of our products:</p>
			<div class="hero-actions"><a class="btn btn-primary" href="<?php echo esc_url( dq_home_anchor( 'contact' ) ); ?>"><?php esc_html_e( 'INQUIRE NOW', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a></div>
		</div>
	</section>

	<?php if ( $sap ) : ?>
	<section class="sap-feature" id="sap-business-one">
		<div class="wrap">
			<article class="sap-panel"<?php dq_reveal(); ?>>
				<?php /* the caption belongs inside the media column: as a sibling it took the panel's
				   second grid cell, which pushed the copy onto an empty second row and left the
				   section looking half-finished (review items B1/B2). */ ?>
				<div class="sap-media">
					<img src="<?php echo esc_url( $sap['hero'] ); ?>" alt="<?php echo esc_attr( $sap['name'] . ' interface' ); ?>" loading="lazy">
					<p class="sap-caption"><?php esc_html_e( 'Make your business Run Easier with SAP Business One 10.0', 'dynamiqes' ); ?></p>
				</div>
				<div class="sap-copy">
					<img class="sap-logo" src="<?php echo esc_url( $sap['logo'] ); ?>" alt="<?php echo esc_attr( $sap['name'] ); ?>" loading="lazy">
					<h2><?php echo esc_html( $sap['name'] ); ?></h2>
					<?php foreach ( $sap['listing'] as $para ) : ?><p><?php echo esc_html( $para ); ?></p><?php endforeach; ?>
					<a class="text-link" href="<?php echo esc_url( $sap['url'] ); ?>"><?php esc_html_e( 'VIEW PRODUCT', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
				</div>
			</article>
		</div>
	</section>
	<?php endif; ?>

	<section class="suite">
		<div class="wrap">
			<div class="suite-head">
				<div><span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'Our Products', 'dynamiqes' ); ?></span><p class="sec-title"<?php dq_reveal(); ?>><?php esc_html_e( 'The IQ Suite — built in-house on SAP Business One', 'dynamiqes' ); ?></p><?php /* styled like an H2, but the live /products/ outline has no heading here */ ?></div>
			</div>
			<div class="product-list">
				<?php foreach ( $suite as $p ) : ?>
				<article class="product-row" id="iq-<?php echo esc_attr( $p['key'] ); ?>"<?php dq_reveal(); ?>>
					<div class="product-media"><img src="<?php echo esc_url( $p['hero'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' interface' ); ?>" loading="lazy"></div>
					<div class="product-copy">
						<img class="product-logo" src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>">
						<h3><?php echo esc_html( $p['name'] ); ?></h3>
						<?php $paras = ! empty( $p['listing'] ) ? $p['listing'] : array( $p['description'] ); foreach ( $paras as $para ) : ?><p><?php echo esc_html( $para ); ?></p><?php endforeach; ?>
						<a class="text-link" href="<?php echo esc_url( $p['url'] ); ?>"><?php esc_html_e( 'VIEW PRODUCT', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
					</div>
				</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php /* live /products/ closes with the contact block: H2 "We’d Like To Hear From You", H3 "Get in Touch" */ get_template_part( 'template-parts/contact-section', null, array( 'heading' => __( 'We’d Like To Hear From You', 'dynamiqes' ), 'aside_heading' => __( 'Get in Touch', 'dynamiqes' ) ) ); ?>
</main>
<?php
get_footer();
