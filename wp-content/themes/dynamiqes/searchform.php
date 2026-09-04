<?php
/**
 * Search form (get_search_form()).
 *
 * @package dynamiqes
 */

$dq_search_id = 'dq-search-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $dq_search_id ); ?>"><?php esc_html_e( 'Search for:', 'dynamiqes' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $dq_search_id ); ?>" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search…', 'dynamiqes' ); ?>" required>
	<button type="submit" class="btn btn-orange"><?php esc_html_e( 'Search', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></button>
</form>
