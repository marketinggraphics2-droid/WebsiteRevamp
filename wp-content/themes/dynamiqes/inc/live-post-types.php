<?php
/**
 * Live-site post types.
 *
 * dynamiqes.com's own theme registers its content types in functions.php — customer_testimonial
 * (/testimonials/<client>/), news-events (/news-event/<slug>/), careers (/careers/<job>/), services
 * and industries. The live install runs no post-type plugin (only Contact Form 7, its add-ons,
 * Smush and Yoast), so the moment this theme replaces the old one those registrations vanish and
 * every one of those URLs — all listed in the live Yoast sitemap — would 404 and drop out of it.
 *
 * This file keeps them alive: when the database holds posts of a live type that nothing else has
 * registered, the type is registered here with the live name, rewrite slug and REST base, so the
 * URLs, the sitemap entries (Yoast lists public types) and the admin screens stay exactly as they
 * were. The rest of the theme already renders these types (single-customer_testimonial.php,
 * single-careers.php, single.php for news; dq_source_post_type() / dq_career_post_type() pick
 * them as the content source when they carry posts).
 *
 * Nothing is registered on a site without such posts (a fresh install, the local preview), so the
 * theme's own dq_testimonial / dq_career types work there as before. Runs at init priority 1 so
 * the theme's later checks (post_type_exists) see the live types, and so a plugin that does
 * register one of them is respected (its registration replaces this one).
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** The live types this theme can stand in for: name → registration args. */
function dq_live_post_type_defs() {
	return array(
		'customer_testimonial' => array(
			'labels'   => array( 'name' => __( 'Customer Testimonials', 'dynamiqes' ), 'singular_name' => __( 'Customer Testimonial', 'dynamiqes' ), 'menu_name' => __( 'Customer Testimonials', 'dynamiqes' ) ),
			'rewrite'  => array( 'slug' => 'testimonials', 'with_front' => false ),
			'icon'     => 'dashicons-format-quote',
			'position' => 6,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
		),
		'news-events' => array(
			'labels'   => array( 'name' => __( 'News & Events', 'dynamiqes' ), 'singular_name' => __( 'News / Event', 'dynamiqes' ), 'menu_name' => __( 'News & Events', 'dynamiqes' ) ),
			'rewrite'  => array( 'slug' => 'news-event', 'with_front' => false ),
			'icon'     => 'dashicons-megaphone',
			'position' => 7,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields', 'revisions' ),
		),
		'careers' => array(
			'labels'     => array( 'name' => __( 'Careers', 'dynamiqes' ), 'singular_name' => __( 'Job Opening', 'dynamiqes' ), 'menu_name' => __( 'Careers', 'dynamiqes' ) ),
			'rewrite'    => array( 'slug' => 'careers', 'with_front' => false ),
			'icon'       => 'dashicons-businessperson',
			'position'   => 8,
			'supports'   => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'taxonomies' => array( 'category' ), // the live type is filed under the blog categories
		),
		'services' => array(
			'labels'   => array( 'name' => __( 'Services', 'dynamiqes' ), 'singular_name' => __( 'Service', 'dynamiqes' ), 'menu_name' => __( 'Services (live)', 'dynamiqes' ) ),
			'rewrite'  => array( 'slug' => 'services', 'with_front' => false ),
			'icon'     => 'dashicons-admin-tools',
			'position' => 9,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
		),
		'industries' => array(
			'labels'   => array( 'name' => __( 'Industries', 'dynamiqes' ), 'singular_name' => __( 'Industry', 'dynamiqes' ), 'menu_name' => __( 'Industries (live)', 'dynamiqes' ) ),
			'rewrite'  => array( 'slug' => 'industries', 'with_front' => false ),
			'icon'     => 'dashicons-building',
			'position' => 9,
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
		),
	);
}

/**
 * Live types that have posts in this database (any status but trash / auto-draft). Cached for a
 * day; the cache is dropped whenever such a post is saved or deleted, and on theme switch.
 */
function dq_live_post_types_present() {
	$cached = get_transient( 'dq_live_post_types' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	global $wpdb;
	$names = array_keys( dq_live_post_type_defs() );
	$in    = implode( ',', array_fill( 0, count( $names ), '%s' ) );
	$found = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_type FROM {$wpdb->posts} WHERE post_type IN ($in) AND post_status NOT IN ('trash','auto-draft')", $names ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$found = is_array( $found ) ? array_values( array_intersect( $names, $found ) ) : array();
	set_transient( 'dq_live_post_types', $found, DAY_IN_SECONDS );
	return $found;
}

/** True when the live type is registered (by anything) and carries published posts. */
function dq_live_post_type_active( $name ) {
	if ( ! post_type_exists( $name ) ) {
		return false;
	}
	$count = wp_count_posts( $name );
	return $count && (int) $count->publish > 0;
}

add_action( 'init', function () {
	$defs = dq_live_post_type_defs();
	foreach ( dq_live_post_types_present() as $name ) {
		if ( post_type_exists( $name ) ) {
			continue; // a plugin (or mu-plugin) owns it — leave its registration alone
		}
		$def = $defs[ $name ];
		register_post_type( $name, array(
			'labels'        => $def['labels'],
			'public'        => true,
			'has_archive'   => false, // the live sitemap lists singles only; no new archive URLs
			'rewrite'       => $def['rewrite'],
			'query_var'     => true,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'rest_base'     => $name, // as on dynamiqes.com (/wp-json/wp/v2/<name>)
			'menu_icon'     => $def['icon'],
			'menu_position' => $def['position'],
			'supports'      => $def['supports'],
			'taxonomies'    => isset( $def['taxonomies'] ) ? $def['taxonomies'] : array(),
		) );
	}
}, 1 );

/* Keep the presence cache honest. */
function dq_live_post_types_bust( $post_id ) {
	$type = get_post_type( $post_id );
	if ( $type && array_key_exists( $type, dq_live_post_type_defs() ) ) {
		delete_transient( 'dq_live_post_types' );
	}
}
add_action( 'save_post', 'dq_live_post_types_bust' );
add_action( 'deleted_post', 'dq_live_post_types_bust' );
add_action( 'after_switch_theme', function () {
	delete_transient( 'dq_live_post_types' );
} );

/* Rewrite rules must include the live types the first time they are registered here (the theme
   switch flush in inc/seeder.php runs after init, so the rules are already complete then; this
   covers a database that gains its first live-type post later). */
add_action( 'init', function () {
	$present = dq_live_post_types_present();
	if ( get_option( 'dq_live_post_types_flushed' ) !== implode( ',', $present ) ) {
		flush_rewrite_rules( false );
		update_option( 'dq_live_post_types_flushed', implode( ',', $present ) );
	}
}, 99 );
