<?php
/**
 * Privacy Policy (/privacy-policy/) — picked up by slug (page-{slug}.php).
 *
 * Structure mirrors dynamiqes.com/privacy-policy/ (SEO): H1 "Privacy Policy" and the notice
 * body with its H5 section headings. The live body is in inc/page-content.php
 * (dq_privacy_policy_html()) and is used while the page holds no real content — WordPress'
 * "Suggested text" privacy template counts as empty. Real content typed in WP Admin wins.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$content = trim( get_the_content() );
$is_wp_default = '' === $content || false !== strpos( $content, 'Suggested text' ) || ( false !== strpos( $content, 'Who we are' ) && false !== strpos( $content, 'Embedded content from other websites' ) );
?>
<main id="main">
	<article <?php post_class( 'privacy-page' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
				<?php if ( has_excerpt() ) : ?><p<?php dq_reveal(); ?>><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
			</div>
		</header>
		<div class="entry">
			<div class="wrap">
				<div class="entry-content privacy-content">
					<?php if ( $is_wp_default ) { echo wp_kses_post( dq_privacy_policy_html() ); } else { the_content(); } ?>
				</div>
			</div>
		</div>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
