<?php
/**
 * Contact Us (/contact-us/) — the old site's nav target. Picked up by slug (page-{slug}.php).
 *
 * Structure mirrors dynamiqes.com/contact-us/ (SEO): H1 "Transforming Business with SAP
 * Excellence" + intro paragraph, then H2 "Get in Touch" with the office details and the
 * enquiry form (shared template-parts/contact-section). The page title stays "Contact Us"
 * for <title> / menus; the article H1 lives in _dq_contact_h1 (default = live copy). Any
 * content added to the page in WP Admin replaces the default intro paragraph.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$headline = get_post_meta( get_the_ID(), '_dq_contact_h1', true );
if ( ! $headline ) {
	$headline = __( 'Transforming Business with SAP Excellence', 'dynamiqes' );
}
$intro = trim( get_the_content() );
if ( '' === $intro ) {
	$intro = '<p>' . esc_html__( 'Discover the power of seamless SAP integration and optimization. Our dedicated team stands ready to guide you through a world of possibilities. From consultations to implementations, we\'re here to redefine your business landscape. Reach out and unlock the full potential of SAP with personalized solutions tailored to elevate your enterprise. Your success story starts with a simple connection — contact us today.', 'dynamiqes' ) . '</p>';
} else {
	$intro = apply_filters( 'the_content', $intro );
}
?>
<main id="main">
	<article <?php post_class( 'contact-page' ); ?>>
		<header class="page-hero page-hero--photo" style="--hero-photo:url('<?php echo esc_url( dq_page_hero_photo( 'contact-us-banner.jpg' ) ); ?>')"><?php /* the live page's banner photo (Featured Image overrides it) */ ?>
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal( 'fade' ); ?>><?php the_title(); ?></span>
				<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
				<div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div>
			</div>
		</header>
	</article>
	<?php get_template_part( 'template-parts/contact-section', null, array( 'heading' => __( 'Get in Touch', 'dynamiqes' ), 'aside_heading' => '' ) ); ?>
</main>
<?php
get_footer();
