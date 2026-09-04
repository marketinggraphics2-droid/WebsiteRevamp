<?php
/**
 * 404 template.
 *
 * @package dynamiqes
 */

get_header();
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'Error 404', 'dynamiqes' ); ?></span>
			<h1<?php dq_reveal(); ?>><?php esc_html_e( 'Page not found', 'dynamiqes' ); ?></h1>
			<p<?php dq_reveal(); ?>><?php esc_html_e( 'The page you are looking for may have moved or no longer exists.', 'dynamiqes' ); ?></p>
		</div>
	</section>
	<section>
		<div class="wrap">
			<div class="not-found"<?php dq_reveal(); ?>>
				<p><?php esc_html_e( 'Try a search, or head back to the home page or our products.', 'dynamiqes' ); ?></p>
				<?php get_search_form(); ?>
				<p style="margin-top:24px">
					<a class="btn btn-orange" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
					<a class="tlink" href="<?php echo esc_url( dq_products_url() ); ?>"><?php esc_html_e( 'View Products', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
				</p>
			</div>
		</div>
	</section>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
