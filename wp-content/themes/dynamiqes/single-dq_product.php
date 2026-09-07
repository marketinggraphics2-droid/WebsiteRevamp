<?php
/**
 * Product detail page (SAP Business One and every IQ Suite module).
 *
 * Structure follows the live dynamiqes.com product page section by section (SEO): the H1 and hero
 * paragraph, then the ordered `sections` of the product — Product Overview, the feature / benefit
 * groups (H2 + H3 items), in-page CTAs and the FAQ — exactly as the old page has them, so the
 * heading outline is unchanged. Only the presentation is the new design system. Content comes
 * from inc/product-content.php (live copy) overridden by the Product details meta box.
 *
 * @package dynamiqes
 */

get_header();
the_post();
$p        = dq_get_product( get_the_ID() );
$sections = dq_product_sections( $p );
$has_body = false;
foreach ( $sections as $s ) {
	if ( in_array( $s['type'], array( 'overview', 'generic' ), true ) ) {
		$has_body = true;
	}
}
$demo_url = dq_book_demo_url();
$showcase = $p['feature_image']; // mockup shown beside the first feature group section

/** One H3 item group: title, optional paragraph, optional list. */
$render_items = function ( $items ) {
	echo '<div class="feature-grid">';
	foreach ( $items as $g ) {
		echo '<article class="feature-group"' . dq_reveal_attr() . '>';
		echo '<h3>' . esc_html( $g['title'] ) . '</h3>';
		if ( ! empty( $g['text'] ) ) {
			echo '<p>' . dq_inline_html( $g['text'] ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		if ( ! empty( $g['items'] ) ) {
			echo '<ul>';
			foreach ( $g['items'] as $it ) {
				echo '<li>' . dq_inline_html( $it ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</ul>';
		}
		echo '</article>';
	}
	echo '</div>';
};
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
					<p<?php dq_reveal(); ?>><?php echo dq_inline_html( $p['description'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
					<div class="hero-actions">
						<a class="btn btn-primary" href="<?php echo esc_url( $demo_url ); ?>"><?php esc_html_e( 'INQUIRE NOW', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a><?php /* live hero: this one link only */ ?>
					</div>
				</div>
				<div class="hero-media"<?php dq_reveal(); ?>>
					<?php if ( $p['hero'] ) : ?><img src="<?php echo esc_url( $p['hero'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' software displayed on a monitor' ); ?>" fetchpriority="high"><?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<?php if ( ! $has_body && $p['overview_image'] ) : /* IQ Link on the live site: hero, a diagram, then the FAQ */ ?>
	<section class="showcase product-diagram" id="overview">
		<div class="wrap"><div class="showcase-frame"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $p['overview_image'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' overview diagram' ); ?>" loading="lazy"></div></div>
	</section>
	<?php endif; ?>

	<?php $overview_done = false; foreach ( $sections as $i => $s ) : ?>
		<?php if ( 'overview' === $s['type'] ) : ?>
	<section class="overview"<?php echo $overview_done ? '' : ' id="overview"'; ?>>
		<div class="wrap overview-grid">
			<div class="overview-media"<?php dq_reveal(); ?>>
				<?php if ( $p['overview_image'] ) : ?><img src="<?php echo esc_url( $p['overview_image'] ); ?>" alt="<?php echo esc_attr( $p['name'] . ' product overview' ); ?>" loading="lazy"><?php endif; ?>
			</div>
			<div class="overview-copy">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<h2<?php dq_reveal(); ?>><?php echo esc_html( $p['overview_title'] ); ?></h2>
				<?php foreach ( $p['overview'] as $para ) : ?><p<?php dq_reveal(); ?>><?php echo dq_inline_html( $para ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
				<?php if ( $p['closing'] ) : ?><p class="closing"<?php dq_reveal(); ?>><?php echo dq_inline_html( $p['closing'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
				<?php if ( ! $overview_done && '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : // extra copy written in the block editor ?>
					<div class="entry-content"><?php the_content(); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<?php if ( ! empty( $s['items'] ) ) : ?><div class="wrap overview-items"><?php $render_items( $s['items'] ); ?></div><?php endif; ?>
	</section>
		<?php $overview_done = true; elseif ( 'generic' === $s['type'] ) : ?>
	<section class="features portal-features<?php echo $showcase ? ' has-showcase' : ''; ?>" id="<?php echo esc_attr( 'section-' . sanitize_title( $s['title'] ) ); ?>">
		<div class="wrap portal-features-layout">
			<div class="portal-features-copy">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<h2<?php dq_reveal(); ?>><?php echo esc_html( $s['title'] ); ?></h2>
				<?php foreach ( $s['intro'] as $para ) : ?><p<?php dq_reveal(); ?>><?php echo dq_inline_html( $para ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
				<?php if ( ! empty( $s['list'] ) ) : ?>
				<ul class="feature-list"<?php dq_reveal(); ?>><?php foreach ( $s['list'] as $li ) : ?><li><?php echo dq_inline_html( $li ); // phpcs:ignore WordPress.Security.EscapeOutput ?></li><?php endforeach; ?></ul>
				<?php endif; ?>
				<?php if ( ! empty( $s['items'] ) ) { $render_items( $s['items'] ); } ?>
				<?php foreach ( $s['closing'] as $para ) : ?><p class="closing"<?php dq_reveal(); ?>><?php echo dq_inline_html( $para ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
			</div>
			<?php if ( $showcase ) : ?>
			<div class="showcase-frame"<?php dq_reveal(); ?>><img src="<?php echo esc_url( $showcase ); ?>" alt="<?php echo esc_attr( $p['name'] . ' feature interface' ); ?>" loading="lazy"></div>
			<?php $showcase = ''; endif; ?>
		</div>
	</section>
		<?php elseif ( 'cta' === $s['type'] ) : ?>
	<section class="cta-section product-cta">
		<div class="wrap">
			<div class="cta-panel"<?php dq_reveal( 'scale' ); ?>>
				<?php if ( ! empty( $s['kicker'] ) ) : ?>
				<h3 class="cta-kicker"><?php echo esc_html( $s['kicker'] ); ?></h3>
				<?php if ( ! empty( $s['kicker_text'] ) ) : ?><p class="cta-kicker-text"><?php echo dq_inline_html( $s['kicker_text'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endif; ?>
				<?php endif; ?>
				<h2><?php echo esc_html( $s['title'] ); ?></h2>
				<?php foreach ( array_merge( $s['intro'], $s['closing'] ) as $para ) : ?><p><?php echo dq_inline_html( $para ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
				<div class="dynamiq-cta"><a href="<?php echo esc_url( $demo_url ); ?>"><?php esc_html_e( 'Get Your Free Business Analysis', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
			</div>
		</div>
	</section>
		<?php elseif ( 'faq' === $s['type'] && ! empty( $p['faqs'] ) ) : $q_h3 = ( 'h3' === $p['faq_heading'] ); ?>
	<section class="faq">
		<div class="wrap faq-grid">
			<div class="faq-head">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $p['name'] ); ?></span>
				<?php $faq_title = ! empty( $s['title'] ) ? $s['title'] : __( 'Frequently Asked Questions (FAQ)', 'dynamiqes' ); ?>
				<?php if ( false === $s['heading'] ) : /* the live IQ REM page styles this line as text, not a heading */ ?>
				<p class="faq-title"<?php dq_reveal(); ?>><?php echo esc_html( $faq_title ); ?></p>
				<?php else : ?>
				<h2<?php dq_reveal(); ?>><?php echo esc_html( $faq_title ); ?></h2>
				<?php endif; ?>
			</div>
			<div class="faq-list"<?php dq_reveal(); ?>>
				<?php foreach ( $p['faqs'] as $f ) : ?>
				<details>
					<summary><?php echo $q_h3 ? '<h3>' . esc_html( $f[0] ) . '</h3>' : esc_html( $f[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></summary>
					<div class="faq-answer"><?php echo dq_faq_answer_html( $f[1] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				</details>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
