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
			<h1<?php dq_reveal(); ?>><?php esc_html_e( '404 Error', 'dynamiqes' ); ?></h1><?php /* H1 + first line as on the live 404 */ ?>
			<p<?php dq_reveal(); ?>><?php esc_html_e( 'Sorry, the page you were looking for was not found.', 'dynamiqes' ); ?></p>
		</div>
	</section>
	<section>
		<div class="wrap">
			<div class="not-found"<?php dq_reveal(); ?>>
				<p><?php esc_html_e( 'Try a search, or head back to the home page or our products.', 'dynamiqes' ); ?></p>
				<?php get_search_form(); ?>
				<?php /* the two links sit in a spaced, centred row rather than an inline run (review items G7/G8) */ ?>
				<div class="not-found-actions">
					<a class="btn btn-orange" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
					<a class="tlink" href="<?php echo esc_url( dq_products_url() ); ?>"><?php esc_html_e( 'View Products', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
				</div>
			</div>
		</div>
	</section>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
