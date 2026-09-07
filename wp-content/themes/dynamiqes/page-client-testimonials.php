<?php
/**
 * Client Testimonials (/client-testimonials/) — picked up by slug (page-{slug}.php).
 *
 * Structure mirrors dynamiqes.com/client-testimonials/ (SEO): H1 "Client Testimonials", then one
 * block per client — the quote, the logo and the client name as an H5 (as on the live page).
 * Composition follows the live page too: copy on the left, the client's video on the right
 * (dq_testimonial_story() in inc/testimonial-media.php). Testimonials come from the site's
 * testimonial post type (the live customer_testimonial items, /testimonials/<client>/) or the
 * theme's own, via dq_testimonials().
 *
 * @package dynamiqes
 */

get_header();
the_post();

$testimonials = dq_testimonials( true );
$intro        = trim( get_the_content() );
?>
<main id="main">
	<article <?php post_class( 'testimonials-page' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'What they say', 'dynamiqes' ); ?></span>
				<h1<?php dq_reveal( '', 60 ); ?>><?php the_title(); ?></h1>
				<?php if ( '' !== $intro ) : ?><div class="page-hero-intro"<?php dq_reveal( '', 120 ); ?>><?php echo apply_filters( 'the_content', $intro ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
			</div>
		</header>
		<section class="testimonials-list">
			<div class="wrap">
				<div class="testi-stories">
					<?php foreach ( $testimonials as $i => $t ) : ?>
						<?php dq_testimonial_story( $t, 'h5', array( 'link' => true, 'reveal' => dq_reveal_attr( '', $i ? 60 : 0 ) ) ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
