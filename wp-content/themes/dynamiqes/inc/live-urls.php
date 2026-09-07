<?php
/**
 * Live-site URL parity.
 *
 * dynamiqes.com's sitemap is the SEO benchmark, and the theme also runs on a copy of the live
 * database (Kinsta staging), so its URL structure must line up with the old site's in both
 * situations:
 *
 *  - Products render at the live URLs (/products/sap-business-one-philippines/ …). dq_product owns
 *    /products/, so the copied live *pages* at those paths are shadowed — unreachable, but still
 *    present. The product post renders; its title tag / meta description come from the shadowed
 *    page (Yoast meta or the theme's SEO box) so nothing indexed changes, and the sitemap lists
 *    each URL once (dq_shadowed_product_pages()).
 *  - The slugs theme <= 1.1.0 used (/products/sap-business-one/, /products/dynamiq-tax/ …) and
 *    /blog/ 301 to the live URLs.
 *  - The posts index is /blogs/ (live slug). /news-events/ stays a page (page-news-events.php),
 *    never the posts page.
 *  - One-off upgrade (option dq_live_urls_v1) on the next admin load: renames seeded products to
 *    the live slugs and re-points page_for_posts at /blogs/. Also runnable from DynamIQ Setup.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Lookups                                                             */
/* ------------------------------------------------------------------ */

/** Published page at a URL path ("products/dynamiq-portal"), or null. */
function dq_page_at_path( $path ) {
	$page = get_page_by_path( trim( (string) $path, '/' ) );
	return ( $page && 'publish' === $page->post_status ) ? $page : null;
}

/**
 * The live-site page the current product URL shadows: the /products/ page on the archive,
 * /products/<slug>/ on a product. Null elsewhere or when no such page was copied over.
 */
function dq_shadowed_page() {
	static $cache = array();
	if ( is_post_type_archive( 'dq_product' ) ) {
		$path = 'products';
	} elseif ( is_singular( 'dq_product' ) ) {
		$path = 'products/' . get_post_field( 'post_name', get_queried_object_id() );
	} else {
		return null;
	}
	if ( ! array_key_exists( $path, $cache ) ) {
		$cache[ $path ] = dq_page_at_path( $path );
	}
	return $cache[ $path ];
}

/**
 * Title tag ('title') or meta description ('description') the shadowed page carries: the theme's
 * SEO box first, then the page's Yoast meta — Yoast variables rendered, and Yoast's page template
 * used when the page has no custom value, exactly what the live page outputs. '' when nothing.
 */
function dq_shadowed_seo( $what, $page = null ) {
	$page = $page ? $page : dq_shadowed_page();
	if ( ! $page ) {
		return '';
	}
	$title = ( 'title' === $what );
	$own   = get_post_meta( $page->ID, $title ? '_dq_seo_title' : '_dq_seo_description', true );
	if ( $own ) {
		return $own;
	}
	$tpl = (string) get_post_meta( $page->ID, $title ? '_yoast_wpseo_title' : '_yoast_wpseo_metadesc', true );
	if ( '' === $tpl && class_exists( 'WPSEO_Options' ) ) {
		$tpl = (string) WPSEO_Options::get( $title ? 'title-page' : 'metadesc-page', '' );
	}
	if ( false !== strpos( $tpl, '%%' ) ) {
		return function_exists( 'wpseo_replace_vars' ) ? trim( wpseo_replace_vars( $tpl, $page ) ) : '';
	}
	return $tpl;
}

/** Published products whose URL is also a copied live page's URL → [ product ID => page ID ]. */
function dq_shadowed_product_pages() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = array();
	if ( ! dq_page_at_path( 'products' ) ) { // child paths need the parent, so nothing was copied
		return $map;
	}
	foreach ( get_posts( array( 'post_type' => 'dq_product', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $id ) {
		$page = dq_page_at_path( 'products/' . get_post_field( 'post_name', $id ) );
		if ( $page ) {
			$map[ (int) $id ] = (int) $page->ID;
		}
	}
	return $map;
}

/** The Blogs page — live slug "blogs"; theme <= 1.1.0 seeded "blog". Null when neither exists. */
function dq_blogs_page() {
	foreach ( array( 'blogs', 'blog' ) as $slug ) {
		$page = dq_page_at_path( $slug );
		if ( $page ) {
			return $page;
		}
	}
	return null;
}

/* ------------------------------------------------------------------ */
/* Head: the shadowed page's SEO meta wins (Yoast)                     */
/* ------------------------------------------------------------------ */
/* Without a plugin the same values flow through inc/seo.php (dq_shadowed_seo() there). */

add_filter( 'wpseo_title', function ( $title ) {
	$t = dq_shadowed_seo( 'title' );
	return $t ? $t : $title;
} );
add_filter( 'wpseo_opengraph_title', function ( $title ) {
	$t = dq_shadowed_seo( 'title' );
	return $t ? $t : $title;
} );
add_filter( 'wpseo_metadesc', function ( $desc ) {
	$d = dq_shadowed_seo( 'description' );
	return $d ? $d : $desc;
} );
add_filter( 'wpseo_opengraph_desc', function ( $desc ) {
	$d = dq_shadowed_seo( 'description' );
	return $d ? $d : $desc;
} );

/* ------------------------------------------------------------------ */
/* Sitemaps: every URL once                                            */
/* ------------------------------------------------------------------ */

/* Yoast: the copied live pages keep their entries (page-sitemap.xml, as on dynamiqes.com); the
   products rendering at those URLs stay out, so the index matches the live one. */
add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', function ( $ids ) {
	return array_merge( (array) $ids, array_keys( dq_shadowed_product_pages() ) );
} );

/* Core sitemap (no SEO plugin): products are listed under dq_product, so drop the shadowed pages —
   the /products/ page (behind the archive) and the product pages. */
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
	if ( 'page' !== $type ) {
		return $args;
	}
	$exclude  = array_values( dq_shadowed_product_pages() );
	$products = dq_page_at_path( 'products' );
	if ( $products ) {
		$exclude[] = (int) $products->ID;
	}
	if ( $exclude ) {
		$args['post__not_in'] = array_merge( (array) ( $args['post__not_in'] ?? array() ), $exclude );
	}
	return $args;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* 301s: theme <= 1.1.0 URLs → live URLs                               */
/* ------------------------------------------------------------------ */

add_action( 'template_redirect', function () {
	if ( ! is_404() ) {
		return;
	}
	$request = trim( (string) $GLOBALS['wp']->request, '/' );
	$to      = '';
	if ( 'blog' === $request ) {
		$to = dq_blog_url();
	} elseif ( preg_match( '#^products/([^/]+)$#', $request, $m ) ) {
		$legacy = dq_product_legacy_slugs();
		if ( isset( $legacy[ $m[1] ] ) ) {
			$key   = $legacy[ $m[1] ];
			$posts = get_posts( array( 'post_type' => 'dq_product', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_dq_product_key', 'meta_value' => $key, 'fields' => 'ids' ) );
			$to    = $posts ? get_permalink( $posts[0] ) : home_url( '/products/' . dq_product_defaults()[ $key ]['slug'] . '/' );
		}
	}
	if ( $to && untrailingslashit( $to ) !== untrailingslashit( home_url( '/' . $request ) ) ) {
		wp_safe_redirect( $to, 301 );
		exit;
	}
}, 1 );

/* ------------------------------------------------------------------ */
/* Upgrade                                                             */
/* ------------------------------------------------------------------ */

/** Rename seeded products still on a theme <= 1.1.0 slug to the live one. Returns how many moved. */
function dq_migrate_product_slugs() {
	$legacy = dq_product_legacy_slugs();
	$moved  = 0;
	foreach ( dq_product_defaults() as $key => $p ) {
		$posts = get_posts( array( 'post_type' => 'dq_product', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_dq_product_key', 'meta_value' => $key ) );
		foreach ( $posts as $post ) {
			/* Only the theme's own old slugs move; a slug an editor chose deliberately stays. */
			if ( $post->post_name === $p['slug'] || ! isset( $legacy[ $post->post_name ] ) ) {
				continue;
			}
			wp_update_post( array( 'ID' => $post->ID, 'post_name' => $p['slug'] ) );
			$moved++;
		}
	}
	return $moved;
}

/**
 * Make /blogs/ the posts index: the copied live page when there is one, else the theme's old
 * "blog" page renamed, else a new page. Sets page_for_posts (which also frees /news-events/ when an
 * earlier seeder had made the news page the posts index). Returns the page ID, 0 on failure.
 */
function dq_ensure_blogs_page() {
	$blogs = get_page_by_path( 'blogs' );
	$blog  = get_page_by_path( 'blog' );
	if ( $blogs ) {
		$id = (int) $blogs->ID;
		if ( 'publish' !== $blogs->post_status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
		if ( $blog && 'publish' === $blog->post_status ) { // the old seeder's duplicate; /blog/ 301s from now on
			wp_update_post( array( 'ID' => (int) $blog->ID, 'post_status' => 'draft' ) );
		}
	} elseif ( $blog ) {
		$id = (int) $blog->ID;
		wp_update_post( array( 'ID' => $id, 'post_name' => 'blogs', 'post_status' => 'publish' ) );
	} else {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blogs', 'post_name' => 'blogs' ) );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	if ( (int) get_option( 'page_for_posts' ) !== (int) $id ) {
		update_option( 'page_for_posts', (int) $id );
	}
	return (int) $id;
}

/**
 * Products seeded from the theme's older catalogue copy (theme <= 1.1.0) carry that copy as meta,
 * which would hide the live dynamiqes.com content in inc/product-content.php. Drop every field
 * that still equals the old catalogue text (an editor's own wording is kept) and give products
 * without a title tag / description the live ones. Returns how many fields were reset.
 */
function dq_refresh_product_content() {
	$base = dq_product_defaults( true );
	$live = dq_product_defaults();
	$n    = 0;
	foreach ( get_posts( array( 'post_type' => 'dq_product', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $post ) {
		$key = get_post_meta( $post->ID, '_dq_product_key', true );
		if ( ! $key || ! isset( $base[ $key ] ) ) {
			continue;
		}
		foreach ( dq_product_field_map() as $field => $def ) {
			if ( ! array_key_exists( $field, $base[ $key ] ) ) {
				continue;
			}
			$old = $base[ $key ][ $field ];
			switch ( $def[0] ) {
				case 'lines':
					$old = dq_lines_to_text( $old );
					break;
				case 'features':
					$old = dq_features_to_text( $old );
					break;
				case 'faqs':
					$old = dq_faqs_to_text( $old );
					break;
				case 'blocks':
					$old = dq_sections_to_text( $old );
					break;
			}
			$cur = get_post_meta( $post->ID, '_dq_' . $field, true );
			if ( '' !== $cur && trim( (string) $cur ) === trim( (string) $old ) ) {
				delete_post_meta( $post->ID, '_dq_' . $field );
				$n++;
			}
		}
		if ( ! get_post_meta( $post->ID, '_dq_seo_title', true ) && ! empty( $live[ $key ]['seo_title'] ) ) {
			update_post_meta( $post->ID, '_dq_seo_title', $live[ $key ]['seo_title'] );
		}
		if ( ! get_post_meta( $post->ID, '_dq_seo_description', true ) && ! empty( $live[ $key ]['seo_description'] ) ) {
			update_post_meta( $post->ID, '_dq_seo_description', $live[ $key ]['seo_description'] );
		}
	}
	return $n;
}

/* Sites seeded before the live copy shipped: reset once, on the next admin load. */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_live_content_v1' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	update_option( 'dq_live_content_v1', time() );
	dq_refresh_product_content();
}, 26 );

/** Slugs, Blogs page, rewrite rules — the whole parity pass. Returns a report for DynamIQ Setup. */
function dq_adopt_live_urls() {
	$moved = dq_migrate_product_slugs();
	$blogs = dq_ensure_blogs_page();
	$reset = dq_refresh_product_content();
	flush_rewrite_rules();
	update_option( 'dq_live_urls_v1', time() );
	update_option( 'dq_live_content_v1', time() );
	return array(
		sprintf( '%d product URLs moved to the live slugs', $moved ),
		sprintf( '%d stale product fields reset to the live copy', $reset ),
		$blogs ? 'Blogs index at ' . wp_make_link_relative( get_permalink( $blogs ) ) : 'Blogs page could not be created',
		sprintf( '%d products share a URL with a copied live page (title tag / description inherited, sitemap de-duplicated)', count( dq_shadowed_product_pages() ) ),
	);
}

/* Sites seeded on theme <= 1.1.0: adopt the live URLs once, on the next admin load. */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_live_urls_v1' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	update_option( 'dq_live_urls_v1', time() ); // first, so a failure cannot loop
	dq_adopt_live_urls();
}, 25 );
