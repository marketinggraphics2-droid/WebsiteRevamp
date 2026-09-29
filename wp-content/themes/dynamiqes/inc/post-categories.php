<?php
/**
 * Blog categories for posts that still sit in "Uncategorized".
 *
 * The live database ships 192 posts, 186 of them in Uncategorized, while the eight blog
 * categories the taxonomy was designed around already exist (accounting-financial-management,
 * ai-digital-transformation, business-growth-industry-insights, e-commerce-solutions,
 * erp-sap-business-one, inventory-warehouse-management, it-solutions-cloud-computing,
 * tax-bir-compliance). The card chip on /blogs/ shows a post's first category, so pages 2+
 * of the listing had no chips at all (SEO Hacker pre-live check 2026-09-29).
 *
 * Once per theme version, on the first admin load, every post whose only category is
 * Uncategorized is filed under the category its title points at (first matching rule wins,
 * the most specific topics first, "Business Growth & Industry Insights" as the catch-all).
 * Only categories that exist on the site are used; nothing is created. Posts an editor has
 * already categorised are never touched. Each assignment is recorded in `_dq_auto_category`
 * (the slug that was set) so it can be reviewed or reverted in WP Admin.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** Ordered title rules: category slug => regex (case-insensitive). */
function dq_post_category_rules() {
	return array(
		'tax-bir-compliance'              => '/\b(bir|cas|tax|taxes|vat|e-?invoic\w*|withholding)\b/i',
		'inventory-warehouse-management'  => '/\b(inventor\w*|warehous\w*|barcod\w*|stock\w*|supply chain|traceab\w*|order accuracy|cycle count\w*|logistics|picking)\b/i',
		'e-commerce-solutions'            => '/\b(e-?commerce|ecom|online (?:store|selling|shop\w*)|shopee|lazada|storefront|marketplace\w*)\b/i',
		'ai-digital-transformation'       => '/\b(ai|artificial intelligence|automat\w*|digital transformation|machine learning|chatbot\w*|digitali[sz]\w*)\b/i',
		'accounting-financial-management' => '/\b(account\w*|financ\w*|bookkeep\w*|payroll|audit\w*|cash ?flow|year-?end|close books|closing|budget\w*|invoic\w*|expense\w*)\b/i',
		'it-solutions-cloud-computing'    => '/\b(it solution\w*|it consulting|cloud\w*|cyber\w*|it (?:service|compan|provider|partner)\w*|software (?:provider|compan)\w*|hosting|on-?premise\w*|paperless)\b/i',
		'erp-sap-business-one'            => '/\b(erp|enterprise resource planning|sap|business one|b1|implementation\w*|module\w*|software)\b/i',
		'business-growth-industry-insights' => '/./',
	);
}

/** The category slug a title points at, or '' when none of the rule categories exists. */
function dq_post_category_for_title( $title, $existing ) {
	$title = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES, 'UTF-8' );
	foreach ( dq_post_category_rules() as $slug => $rx ) {
		if ( isset( $existing[ $slug ] ) && preg_match( $rx, $title ) ) {
			return $slug;
		}
	}
	return '';
}

/**
 * File every Uncategorized post under a category. Returns [ slug => count ] of what was set.
 */
function dq_categorize_uncategorized_posts() {
	$existing = array();
	foreach ( array_keys( dq_post_category_rules() ) as $slug ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( $term && ! is_wp_error( $term ) ) {
			$existing[ $slug ] = (int) $term->term_id;
		}
	}
	if ( ! $existing ) {
		return array();
	}
	$uncat = (int) get_option( 'default_category' );
	$done  = array();
	$ids   = get_posts( array(
		'post_type'      => 'post',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future', 'private' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'category__in'   => array( $uncat ),
	) );
	foreach ( $ids as $id ) {
		$cats = wp_get_post_categories( $id );
		if ( array_diff( $cats, array( $uncat ) ) ) {
			continue; // an editor already filed it somewhere
		}
		$slug = dq_post_category_for_title( get_the_title( $id ), $existing );
		if ( '' === $slug ) {
			continue;
		}
		wp_set_post_categories( $id, array( $existing[ $slug ] ), false ); // replaces Uncategorized
		update_post_meta( $id, '_dq_auto_category', $slug );
		$done[ $slug ] = isset( $done[ $slug ] ) ? $done[ $slug ] + 1 : 1;
	}
	return $done;
}

/* Once per theme version on the first admin load, like the other content updaters (inc/live-urls.php). */
add_action( 'init', function () {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'dq_post_categories_ver' ) === DQ_VERSION ) {
		return;
	}
	update_option( 'dq_post_categories_ver', DQ_VERSION ); // first, so a failure cannot loop
	$done = dq_categorize_uncategorized_posts();
	if ( $done ) {
		update_option( 'dq_post_categories_report', $done );
	}
}, 28 );

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$report = get_option( 'dq_post_categories_report' );
	if ( ! is_array( $report ) || ! $report ) {
		return;
	}
	delete_option( 'dq_post_categories_report' );
	$parts = array();
	foreach ( $report as $slug => $n ) {
		$term    = get_term_by( 'slug', $slug, 'category' );
		$parts[] = ( $term ? $term->name : $slug ) . ' ' . (int) $n;
	}
	echo '<div class="notice notice-success is-dismissible"><p><strong>'
		. esc_html( sprintf( __( 'DynamIQ %1$s: %2$d Uncategorized posts filed by title.', 'dynamiqes' ), DQ_VERSION, array_sum( $report ) ) ) . '</strong> '
		. esc_html( implode( ' · ', $parts ) ) . ' '
		. esc_html__( 'Each post carries the custom field _dq_auto_category with the category it was given; review under Posts and re-file where needed.', 'dynamiqes' )
		. '</p></div>';
} );
