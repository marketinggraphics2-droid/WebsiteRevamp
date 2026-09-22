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
	if ( ! function_exists( 'dq_manages_sitemap' ) || ! dq_manages_sitemap() ) {
		return $ids; // the sitemap is the site's, not the theme's — see dq_manages_sitemap()
	}
	return array_merge( (array) $ids, array_keys( dq_shadowed_product_pages() ) );
} );

/* Core sitemap (no SEO plugin): products are listed under dq_product, so drop the shadowed pages —
   the /products/ page (behind the archive) and the product pages. */
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
	if ( 'page' !== $type || ! function_exists( 'dq_manages_sitemap' ) || ! dq_manages_sitemap() ) {
		return $args; // leave the page sitemap exactly as WordPress built it
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
	$base    = dq_product_defaults( true );
	$live    = dq_product_defaults();
	$shipped = function_exists( 'dq_product_live_content' ) ? dq_product_live_content() : array();
	$n       = 0;
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
			if ( '' === $cur ) {
				continue;
			}
			$cur    = trim( (string) $cur );
			$stale  = $cur === trim( (string) $old );                                       // the old catalogue copy
			$stale  = $stale || $cur === trim( (string) dq_product_field_text( $live[ $key ], $field ) ); // repeats the current theme copy
			/* Copy that ships in inc/product-content.php is the SEO source: it replaces whatever an
			   earlier theme version seeded, unless the editor changed that field in WP Admin. */
			$edited = (array) get_post_meta( $post->ID, '_dq_edited_fields', true );
			$stale  = $stale || ( isset( $shipped[ $key ] ) && array_key_exists( $field, $shipped[ $key ] ) && ! in_array( $field, $edited, true ) );
			if ( $stale ) {
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

/* Every theme version: product copy that changed in inc/product-content.php reaches the pages
   (the seeder writes copy into post meta once, and meta beats code). Runs on the first admin
   load of a version, like the landing-page re-import; fields edited in WP Admin are kept. */
add_action( 'init', function () {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	if ( get_option( 'dq_product_content_ver' ) === DQ_VERSION ) {
		return;
	}
	update_option( 'dq_product_content_ver', DQ_VERSION ); // first, so a failure cannot loop
	dq_create_missing_products();
	dq_refresh_product_content();
	dq_add_missing_product_menu_items();
	dq_order_product_menu_items();
}, 27 );

/**
 * Put the "Our Products" sub-items in the order dq_product_menu_order() names. Items are matched
 * to products by post ID, then by URL; unmatched items keep their relative order at the end.
 * Returns the number of items moved.
 */
function dq_order_product_menu_items() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu      = empty( $locations['primary'] ) ? null : wp_get_nav_menu_object( (int) $locations['primary'] );
	if ( ! $menu ) {
		$menu = wp_get_nav_menu_object( 'Primary Menu' );
	}
	if ( ! $menu ) {
		return 0;
	}
	$items  = wp_get_nav_menu_items( $menu->term_id );
	$parent = 0;
	foreach ( $items as $mi ) {
		if ( ! $mi->menu_item_parent && ( 'Our Products' === trim( $mi->title ) || untrailingslashit( $mi->url ) === untrailingslashit( dq_products_url() ) ) ) {
			$parent = (int) $mi->ID;
			break;
		}
	}
	if ( ! $parent ) {
		return 0;
	}
	$by_id  = array();
	$by_url = array();
	foreach ( dq_get_products() as $p ) {
		if ( $p['id'] ) {
			$by_id[ (int) $p['id'] ] = $p['key'];
		}
		$by_url[ untrailingslashit( $p['url'] ) ] = $p['key'];
	}
	$rank = array_flip( dq_product_menu_order() );
	$kids = array();
	foreach ( $items as $mi ) {
		if ( (int) $mi->menu_item_parent !== $parent ) {
			continue;
		}
		$key = isset( $by_id[ (int) $mi->object_id ] ) ? $by_id[ (int) $mi->object_id ] : ( isset( $by_url[ untrailingslashit( $mi->url ) ] ) ? $by_url[ untrailingslashit( $mi->url ) ] : '' );
		$kids[] = array( 'item' => $mi, 'rank' => isset( $rank[ $key ] ) ? $rank[ $key ] : 1000 + (int) $mi->menu_order );
	}
	usort( $kids, function ( $a, $b ) { return $a['rank'] <=> $b['rank']; } );
	$moved = 0;
	$pos   = 1;
	foreach ( $kids as $k ) {
		$mi = $k['item'];
		if ( (int) $mi->menu_order !== $pos ) {
			wp_update_post( array( 'ID' => $mi->ID, 'menu_order' => $pos ) );
			$moved++;
		}
		$pos++;
	}
	return $moved;
}

/**
 * Products added to the catalogue after a site was seeded (IQ People, IQ Workplace, IQ Tech
 * Institute, Sept 2026) get their dq_product post here — the same record the seeder would have
 * written, so the page, the listing row, the home card and the sitemap all appear. Returns the
 * number created.
 */
function dq_create_missing_products() {
	$n = 0;
	foreach ( dq_product_defaults() as $key => $p ) {
		if ( dq_find_post_by_meta( 'dq_product', '_dq_product_key', $key ) ) {
			continue;
		}
		$id = wp_insert_post( array(
			'post_type'    => 'dq_product',
			'post_status'  => 'publish',
			'post_title'   => $p['name'],
			'post_name'    => $p['slug'],
			'post_excerpt' => $p['description'],
			'post_content' => '',
			'menu_order'   => $p['order'],
		) );
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		update_post_meta( $id, '_dq_product_key', $key );
		if ( ! empty( $p['seo_title'] ) ) {
			update_post_meta( $id, '_dq_seo_title', $p['seo_title'] );
		}
		if ( ! empty( $p['seo_description'] ) ) {
			update_post_meta( $id, '_dq_seo_description', $p['seo_description'] );
		}
		/* No field meta: the record reads straight from the catalogue until an editor changes it. */
		$n++;
	}
	if ( $n ) {
		flush_rewrite_rules();
	}
	return $n;
}

/**
 * Every product has an entry under "Our Products" in the saved primary menu. Products with no
 * item yet (matched by post, then by URL) are appended after the last existing product item.
 * Returns the number added.
 */
function dq_add_missing_product_menu_items() {
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu      = empty( $locations['primary'] ) ? null : wp_get_nav_menu_object( (int) $locations['primary'] );
	if ( ! $menu ) {
		$menu = wp_get_nav_menu_object( 'Primary Menu' );
	}
	if ( ! $menu ) {
		return 0;
	}
	$items  = wp_get_nav_menu_items( $menu->term_id );
	$parent = 0;
	foreach ( $items as $mi ) {
		if ( ! $mi->menu_item_parent && ( 'Our Products' === trim( $mi->title ) || untrailingslashit( $mi->url ) === untrailingslashit( dq_products_url() ) ) ) {
			$parent = (int) $mi->ID;
			break;
		}
	}
	if ( ! $parent ) {
		return 0;
	}
	$have_ids = array();
	$have_url = array();
	$last_pos = 0;
	foreach ( $items as $mi ) {
		if ( (int) $mi->menu_item_parent !== $parent ) {
			continue;
		}
		$have_ids[] = (int) $mi->object_id;
		$have_url[] = untrailingslashit( $mi->url );
		$last_pos   = max( $last_pos, (int) $mi->menu_order );
	}
	$n = 0;
	foreach ( dq_get_products() as $p ) {
		if ( ( $p['id'] && in_array( (int) $p['id'], $have_ids, true ) ) || in_array( untrailingslashit( $p['url'] ), $have_url, true ) ) {
			continue;
		}
		$args = array( 'menu-item-title' => $p['menu_label'], 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent, 'menu-item-position' => ++$last_pos );
		if ( $p['id'] ) {
			$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => 'dq_product', 'menu-item-object-id' => (int) $p['id'] );
		} else {
			$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => $p['url'] );
		}
		if ( ! is_wp_error( wp_update_nav_menu_item( $menu->term_id, 0, $args ) ) ) {
			$n++;
		}
	}
	return $n;
}

/** Slugs, Blogs page, rewrite rules — the whole parity pass. Returns a report for DynamIQ Setup. */
function dq_adopt_live_urls() {
	$moved = dq_migrate_product_slugs();
	$blogs = dq_ensure_blogs_page();
	$reset = dq_refresh_product_content();
	/* The page existing is not enough: on the live database the nav's top-level "Blogs" item
	   resolves to News & Events, so every visitor who clicks Blogs lands on the wrong page. */
	$menu = function_exists( 'dq_repair_blog_menu_links' ) ? dq_repair_blog_menu_links() : 0;
	flush_rewrite_rules();
	update_option( 'dq_live_urls_v1', time() );
	update_option( 'dq_live_content_v1', time() );
	return array(
		sprintf( '%d product URLs moved to the live slugs', $moved ),
		sprintf( '%d stale product fields reset to the live copy', $reset ),
		$blogs ? 'Blogs index at ' . wp_make_link_relative( get_permalink( $blogs ) ) : 'Blogs page could not be created',
		sprintf( '%d nav menu links repointed at the imported pages', $menu ),
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

/* The nav's top-level "Blogs" item points at News & Events on the live database, and sites
   seeded before 1.2.4 already passed the dq_live_urls_v1 pass above, so they need a run of
   their own. Admin-side and once, like the other repairs in this file. */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_blog_menu_v2' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	update_option( 'dq_blog_menu_v2', time() );
	if ( function_exists( 'dq_ensure_blogs_page' ) ) {
		dq_ensure_blogs_page();
	}
	if ( function_exists( 'dq_repair_blog_menu_links' ) ) {
		dq_repair_blog_menu_links();
	}
}, 27 );

/* Imported landing pages follow the importer. On every theme update the stored copies are read
   again from the live pages, so an importer fix reaches the site without a manual step - the
   09-14 review was looking at staging content imported before the 1.2.x fixes (no in-text
   links, no contact form, glued words, a card icon as the section photo).

   1.3.2 scheduled this through WP-Cron and it never ran on staging (the version was stamped,
   so it never retried either). It now runs in the request itself: four pages per admin page
   load, the pending list saved before each batch so a failure cannot repeat the same slugs,
   and the version stamped only when the list is empty. A notice in wp-admin reports the
   result. The auto-import for empty pages (inc/seeder.php) stays as it is. */
add_action( 'init', function () {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! function_exists( 'dq_import_landing_pages' ) ) {
		return;
	}
	if ( get_option( 'dq_landing_import_ver' ) === DQ_VERSION ) {
		return;
	}
	$pending = get_option( 'dq_landing_import_pending' );
	if ( get_option( 'dq_landing_import_pending_ver' ) !== DQ_VERSION || ! is_array( $pending ) ) {
		$pending = dq_landing_slugs();
		update_option( 'dq_landing_import_pending_ver', DQ_VERSION );
		update_option( 'dq_landing_import_report', array() );
	}
	$batch = array_splice( $pending, 0, 4 );
	update_option( 'dq_landing_import_pending', $pending ); // first, so a fatal cannot loop on the same slugs
	if ( $batch ) {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 150 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		$only = function () use ( $batch ) {
			return $batch;
		};
		add_filter( 'dq_landing_slugs', $only, 99 );
		$report = dq_import_landing_pages( false );
		remove_filter( 'dq_landing_slugs', $only, 99 );
		update_option( 'dq_landing_import_report', array_merge( (array) get_option( 'dq_landing_import_report', array() ), (array) $report ) );
	}
	if ( ! $pending ) {
		update_option( 'dq_landing_import_ver', DQ_VERSION );
		update_option( 'dq_landing_import_done', time() );
	}
}, 27 );

/* One notice when the re-import finishes, with the importer's own report; dismissed by reading. */
add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) || ! get_option( 'dq_landing_import_done' ) ) {
		return;
	}
	$report = (array) get_option( 'dq_landing_import_report', array() );
	delete_option( 'dq_landing_import_done' );
	$failed = array_filter( $report, function ( $l ) { return false === strpos( $l, 'updated' ); } );
	echo '<div class="notice notice-' . ( $failed ? 'warning' : 'success' ) . ' is-dismissible"><p><strong>'
		. esc_html( sprintf( __( 'DynamIQ %s: landing pages re-imported from the live site.', 'dynamiqes' ), DQ_VERSION ) ) . '</strong> '
		. esc_html( sprintf( __( '%1$d pages updated, %2$d failed.', 'dynamiqes' ), count( $report ) - count( $failed ), count( $failed ) ) )
		. ( $failed ? '<br>' . esc_html( implode( ' | ', $failed ) ) : '' ) . '</p></div>';
} );

/* Pages whose Featured Image is a logo, icon, seal or check mark lose that Featured Image once
   per theme version (the attachment itself stays). The templates refuse such an image anyway;
   this also keeps it out of og:image and any listing. Landing pages cloned from the old site
   carried check.png that way. */
add_action( 'init', function () {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) || ! function_exists( 'dq_main_image_ok' ) ) {
		return;
	}
	if ( get_option( 'dq_thumb_gate_ver' ) === DQ_VERSION ) {
		return;
	}
	update_option( 'dq_thumb_gate_ver', DQ_VERSION );
	$dropped = array();
	foreach ( get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'post_status' => 'any', 'meta_key' => '_thumbnail_id', 'fields' => 'ids' ) ) as $pid ) {
		$tid = (int) get_post_thumbnail_id( $pid );
		$url = $tid ? wp_get_attachment_url( $tid ) : '';
		if ( $url && ! dq_main_image_ok( $url, $tid ) ) {
			delete_post_thumbnail( $pid );
			$dropped[] = get_post_field( 'post_name', $pid ) . ' (' . rawurldecode( basename( $url ) ) . ')';
		}
	}
	if ( $dropped ) {
		update_option( 'dq_thumb_gate_report', $dropped );
	}
}, 28 );

add_action( 'admin_notices', function () {
	$dropped = get_option( 'dq_thumb_gate_report' );
	if ( ! $dropped || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'dq_thumb_gate_report' );
	echo '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html( sprintf( __( 'DynamIQ %s: icon-type Featured Images removed from %d page(s).', 'dynamiqes' ), DQ_VERSION, count( (array) $dropped ) ) ) . '</strong> '
		. esc_html( implode( ' | ', (array) $dropped ) ) . '</p></div>';
} );

/**
 * Featured images cloned from the old site have no dq-card / dq-wide copies (those sizes did not
 * exist when they were uploaded), so listings were serving 1920px originals into 425px cards.
 * Generates the missing copies for images in use as Featured Images, a few per admin page load.
 *
 * @param int $limit Attachments to process in this call.
 * @return array{done:int,left:int}
 */
function dq_generate_missing_sizes( $limit = 5 ) {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$ids  = $wpdb->get_col( "SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND meta_value > 0" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$todo = array();
	foreach ( array_map( 'intval', $ids ) as $id ) {
		$meta = wp_get_attachment_metadata( $id );
		if ( ! is_array( $meta ) || empty( $meta['width'] ) || ! wp_attachment_is_image( $id ) ) {
			continue;
		}
		$need = ( (int) $meta['width'] > 800 && empty( $meta['sizes']['dq-card'] ) ) || ( (int) $meta['width'] > 1600 && empty( $meta['sizes']['dq-wide'] ) );
		if ( $need && is_readable( (string) get_attached_file( $id ) ) ) {
			$todo[] = $id;
		}
	}
	$done = 0;
	foreach ( array_slice( $todo, 0, $limit ) as $id ) {
		$new = wp_generate_attachment_metadata( $id, get_attached_file( $id ) );
		if ( is_array( $new ) && ! empty( $new['sizes'] ) ) {
			wp_update_attachment_metadata( $id, $new );
			$done++;
		}
	}
	return array( 'done' => $done, 'left' => max( 0, count( $todo ) - $done ) );
}

add_action( 'init', function () {
	if ( ! is_admin() || wp_doing_ajax() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'dq_sizes_ver' ) === DQ_VERSION ) {
		return;
	}
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
	}
	$r = dq_generate_missing_sizes( 5 );
	update_option( 'dq_sizes_done', (int) get_option( 'dq_sizes_done', 0 ) + $r['done'] );
	if ( 0 === $r['left'] ) {
		update_option( 'dq_sizes_ver', DQ_VERSION );
		if ( get_option( 'dq_sizes_done' ) ) {
			update_option( 'dq_sizes_report', (int) get_option( 'dq_sizes_done' ) );
		}
		delete_option( 'dq_sizes_done' );
	}
}, 29 );

add_action( 'admin_notices', function () {
	$n = get_option( 'dq_sizes_report' );
	if ( ! $n || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'dq_sizes_report' );
	echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html( sprintf( __( 'DynamIQ %1$s: card and banner copies generated for %2$d featured image(s).', 'dynamiqes' ), DQ_VERSION, (int) $n ) ) . '</strong></p></div>';
} );
