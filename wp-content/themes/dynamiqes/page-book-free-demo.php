<?php
/**
 * Book a FREE DEMO (/book-free-demo/) — the old site's CTA target: an H1 and the enquiry form.
 * Picked up by slug (page-{slug}.php); the seeder creates the page and its title tag /
 * meta description ("Book a Free Demo | DynamIQes"). Any content added to the page in WP Admin
 * appears between the heading and the form.
 *
 * @package dynamiqes
 */

get_header();
the_post();
?>
<main id="main">
	<article <?php post_class( 'book-demo-page' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?><p<?php dq_reveal(); ?>><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			</div>
		</header>
		<?php if ( '' !== trim( get_the_content() ) ) : ?>
		<div class="entry">
			<div class="wrap">
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</div>
		<?php endif; ?>
	</article>
	<?php /* live /book-free-demo/ has the H1 only: the form renders without its own headings */ get_template_part( 'template-parts/contact-section', null, array( 'heading' => '', 'aside_heading' => '' ) ); ?>
</main>
<?php
get_footer();
