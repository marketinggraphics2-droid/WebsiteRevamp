<?php
/**
 * Generic page (Privacy Policy, Careers, etc.).
 *
 * @package dynamiqes
 */

get_header();
the_post();
$img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'dq-wide' ) : '';
?>
<main id="main">
	<article <?php post_class(); ?>>
		<header class="page-hero">
			<div class="wrap">
				<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?><p<?php dq_reveal(); ?>><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			</div>
		</header>
		<div class="entry">
			<div class="wrap">
				<?php if ( $img ) : ?><div class="entry-media" style="margin:0 0 32px"><img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>"></div><?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>
			</div>
		</div>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
