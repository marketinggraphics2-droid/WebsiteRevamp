<?php
/**
 * One-click content import: products, testimonials, news posts, pages, menus,
 * site options. Runs on theme activation and from Appearance → DynamIQ Setup.
 * Optional step: sideload the bundled images into the Media Library so every
 * image gets a /wp-content/uploads/ URL that can be managed from WP Admin.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/*
 * Activation runs before `init`, i.e. before the custom post types exist, so we only
 * raise a flag here and do the actual import on the next admin request at init:20
 * (post types register at init:10). Rewrite rules are flushed at that point too.
 */
add_action( 'after_switch_theme', function () {
	update_option( 'dq_needs_setup', 1 );
	/* The landing-page fill must not depend on someone opening wp-admin afterwards (activation
	   from the Customizer never does): queue it as a one-off cron job as well. WP-Cron runs on
	   the next page view (or via the host's system cron), so the pages fill in within a minute. */
	if ( ! wp_next_scheduled( 'dq_landing_auto_import' ) ) {
		wp_schedule_single_event( time() + 10, 'dq_landing_auto_import' );
	}
} );
add_action( 'dq_landing_auto_import', function () {
	if ( function_exists( 'dq_auto_import_landing_pages' ) ) {
		dq_auto_import_landing_pages();
	}
} );
add_action( 'init', function () {
	if ( ! is_admin() || ! get_option( 'dq_needs_setup' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	delete_option( 'dq_needs_setup' );
	if ( ! get_option( 'dq_seeded' ) ) {
		dq_seed_content();
	}
	if ( function_exists( 'dq_auto_import_landing_pages' ) ) {
		dq_auto_import_landing_pages(); // fills empty "Blogs" landing pages copied from the old theme
	}
	flush_rewrite_rules();
}, 20 );

/**
 * Re-point the "Blogs" sub-items of the saved Primary Menu at their landing pages when
 * those pages exist (custom fallback links only). Safe to run on any site: it touches
 * nothing but those menu items. Returns the number of items updated.
 */
function dq_repair_blog_menu_links() {
	/* The menu that actually renders (assigned to the primary location) first, then the seeded name. */
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu      = empty( $locations['primary'] ) ? null : wp_get_nav_menu_object( (int) $locations['primary'] );
	if ( ! $menu ) {
		$menu = wp_get_nav_menu_object( 'Primary Menu' );
	}
	if ( ! $menu ) {
		return 0;
	}
	$landing = array();
	foreach ( dq_blog_landing_items() as $b ) {
		if ( $b['object_id'] ) {
			$landing[ $b['title'] ] = $b;
		}
	}
	$n        = 0;
	$services = dq_services_page();
	foreach ( wp_get_nav_menu_items( $menu->term_id ) as $mi ) {
		/* Top-level "Our Services" still on the home anchor → the imported /our-services/ page. */
		if ( $services && ! $mi->menu_item_parent && 'custom' === $mi->type && dq_is_services_anchor( $mi->url ) ) {
			wp_update_nav_menu_item( $menu->term_id, $mi->ID, array(
				'menu-item-title'     => $mi->title,
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => 0,
				'menu-item-position'  => (int) $mi->menu_order,
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $services->ID,
			) );
			$n++;
			continue;
		}
		if ( ! $mi->menu_item_parent || 'custom' !== $mi->type || ! isset( $landing[ $mi->title ] ) ) {
			continue;
		}
		$b = $landing[ $mi->title ];
		if ( untrailingslashit( $mi->url ) === untrailingslashit( $b['url'] ) ) {
			continue;
		}
		wp_update_nav_menu_item( $menu->term_id, $mi->ID, array(
			'menu-item-title'     => $mi->title,
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => (int) $mi->menu_item_parent,
			'menu-item-position'  => (int) $mi->menu_order,
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => get_post_type( $b['object_id'] ),
			'menu-item-object-id' => $b['object_id'],
		) );
		$n++;
	}
	return $n;
}

/**
 * Create the Contact Us page (/contact-us/) if missing and give it the old site's title tag /
 * meta description. Returns the page ID. Safe to call repeatedly.
 */
function dq_ensure_contact_page() {
	$page = get_page_by_path( 'contact-us' );
	if ( $page ) {
		$id = (int) $page->ID;
		if ( 'publish' !== $page->post_status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
	} else {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Contact Us', 'post_name' => 'contact-us' ) );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	if ( ! get_post_meta( $id, '_dq_seo_title', true ) ) {
		update_post_meta( $id, '_dq_seo_title', 'Contact Us - DynamIQes' );
	}
	if ( ! get_post_meta( $id, '_dq_seo_description', true ) ) {
		update_post_meta( $id, '_dq_seo_description', 'Get in touch with DynamIQ Enterprise Solution. Reach our team for free business analysis, SAP B1 consulting, or IT support — we\'re here to help.' );
	}
	return $id;
}

/**
 * Create the About Us page (/about-us/) if missing, with the old site's title tag / meta description
 * (page-about-us.php renders the live copy). Returns the page ID. Safe to call repeatedly.
 */
function dq_ensure_about_page() {
	$page = get_page_by_path( 'about-us' );
	if ( $page ) {
		$id = (int) $page->ID;
		if ( 'publish' !== $page->post_status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
	} else {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'About Us', 'post_name' => 'about-us' ) );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	if ( ! get_post_meta( $id, '_dq_seo_title', true ) ) {
		update_post_meta( $id, '_dq_seo_title', 'About DynamiQ — Filipino SAP Partner Since 2019' );
	}
	if ( ! get_post_meta( $id, '_dq_seo_description', true ) ) {
		update_post_meta( $id, '_dq_seo_description', 'Learn about DynamIQ Enterprise Solution – a Filipino IT consultancy and SAP Gold Partner committed to driving business efficiency and growth.' );
	}
	return (int) $id;
}

/**
 * Re-point a saved Primary Menu's top-level "About Us" from the home #about anchor to the /about-us/
 * page (the old site's nav target). Returns 1 when an item was changed.
 */
function dq_repair_about_menu_link() {
	$page = dq_about_page();
	if ( ! $page ) {
		return 0;
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu      = empty( $locations['primary'] ) ? null : wp_get_nav_menu_object( (int) $locations['primary'] );
	if ( ! $menu ) {
		$menu = wp_get_nav_menu_object( 'Primary Menu' );
	}
	if ( ! $menu ) {
		return 0;
	}
	foreach ( wp_get_nav_menu_items( $menu->term_id ) as $mi ) {
		if ( ! $mi->menu_item_parent && 'custom' === $mi->type && 'About Us' === $mi->title && dq_is_about_anchor( $mi->url ) ) {
			wp_update_nav_menu_item( $menu->term_id, $mi->ID, array(
				'menu-item-title'     => 'About Us',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => 0,
				'menu-item-position'  => (int) $mi->menu_order,
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $page->ID,
			) );
			return 1;
		}
	}
	return 0;
}

/* Sites seeded before the About Us page existed: create it and fix the menu once. */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_about_page_v1' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	dq_ensure_about_page();
	dq_repair_about_menu_link();
	update_option( 'dq_about_page_v1', time() );
}, 21 );

/**
 * Re-point a saved Primary Menu's top-level "Contact Us" from the home #contact anchor to the
 * /contact-us/ page. Returns 1 when an item was changed.
 */
function dq_repair_contact_menu_link() {
	$page = function_exists( 'dq_contact_page' ) ? dq_contact_page() : null;
	if ( ! $page ) {
		return 0;
	}
	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$menu      = empty( $locations['primary'] ) ? null : wp_get_nav_menu_object( (int) $locations['primary'] );
	if ( ! $menu ) {
		$menu = wp_get_nav_menu_object( 'Primary Menu' );
	}
	if ( ! $menu ) {
		return 0;
	}
	$n = 0;
	foreach ( wp_get_nav_menu_items( $menu->term_id ) as $mi ) {
		if ( ! $mi->menu_item_parent && 'custom' === $mi->type && 'Contact Us' === $mi->title && dq_is_contact_anchor( $mi->url ) ) {
			wp_update_nav_menu_item( $menu->term_id, $mi->ID, array(
				'menu-item-title'     => 'Contact Us',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => 0,
				'menu-item-position'  => (int) $mi->menu_order,
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => (int) $page->ID,
			) );
			$n++;
		}
	}
	return $n;
}

/* Sites seeded before the Contact Us page existed: create it and fix the menu once, on the
   next admin request (no re-seed needed). */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_contact_page_v1' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	dq_ensure_contact_page();
	dq_repair_contact_menu_link();
	update_option( 'dq_contact_page_v1', time() );
}, 21 );

/** Find a post by meta key/value. */
function dq_find_post_by_meta( $type, $key, $value ) {
	$found = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => $key, 'meta_value' => $value, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
}

/** Give seeded news posts that still show the title-only placeholder their real article
 *  body (see inc/news-content.php). Returns how many posts were updated. */
function dq_backfill_news_bodies() {
	$filled = 0;
	foreach ( dq_default_news() as $i => $n ) {
		$id = dq_find_post_by_meta( 'post', '_dq_seed_id', 'n' . $i );
		if ( ! $id ) {
			$found = get_posts( array( 'post_type' => 'post', 'post_status' => 'any', 'name' => $n['slug'], 'posts_per_page' => 1, 'fields' => 'ids' ) );
			$id    = $found ? (int) $found[0] : 0;
		}
		if ( ! $id || ! dq_news_body_is_placeholder( $id ) ) {
			continue;
		}
		$body = dq_default_news_body( $n['slug'] );
		if ( $body ) {
			wp_update_post( array( 'ID' => $id, 'post_content' => $body ) );
			$filled++;
		}
	}
	return $filled;
}

/** Create/refresh everything. Idempotent. */
function dq_seed_content() {
	$report = array();

	/* Site identity + permalinks. Only a fresh install is renamed: a copy of the live site keeps its
	   Site Title, which every Yoast title template ends in ("… - DynamIQes"). */
	if ( in_array( get_option( 'blogname' ), array( '', 'WordPress', 'My WordPress' ), true ) ) {
		update_option( 'blogname', 'DynamIQ Enterprise Solution' );
		update_option( 'blogdescription', 'SAP Premier Partner Philippines' );
	}
	if ( ! get_option( 'permalink_structure' ) ) {
		update_option( 'permalink_structure', '/%postname%/' );
	}
	update_option( 'blog_public', 1 );

	/* Products */
	$count = 0;
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
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_dq_product_key', $key );
			/* The live page's title tag / meta description (inc/product-content.php) — kept verbatim, it ranks. */
			if ( ! empty( $p['seo_title'] ) ) {
				update_post_meta( $id, '_dq_seo_title', $p['seo_title'] );
			}
			if ( ! empty( $p['seo_description'] ) ) {
				update_post_meta( $id, '_dq_seo_description', $p['seo_description'] );
			}
			foreach ( dq_product_field_map() as $field => $def ) {
				$v = $p[ $field ] ?? '';
				if ( 'lines' === $def[0] ) {
					$v = dq_lines_to_text( $v );
				} elseif ( 'features' === $def[0] ) {
					$v = dq_features_to_text( $v );
				} elseif ( 'faqs' === $def[0] ) {
					$v = dq_faqs_to_text( $v );
				} elseif ( 'blocks' === $def[0] ) {
					$v = dq_sections_to_text( $v );
				}
				if ( '' !== $v ) {
					update_post_meta( $id, '_dq_' . $field, $v );
				}
			}
			$count++;
		}
	}
	$report[] = sprintf( '%d products created', $count );

	/* Testimonials (skipped when the site already has its own testimonials post type) */
	$count = 0;
	$have_testimonials = 'dq_testimonial' !== dq_source_post_type( 'testimonial' );
	$have_news         = 'post' !== dq_source_post_type( 'news' ) || wp_count_posts( 'post' )->publish > 1;
	foreach ( $have_testimonials ? array() : dq_default_testimonials() as $i => $t ) {
		if ( dq_find_post_by_meta( 'dq_testimonial', '_dq_seed_id', 't' . $i ) ) {
			continue;
		}
		$id = wp_insert_post( array( 'post_type' => 'dq_testimonial', 'post_status' => 'publish', 'post_title' => $t['name'], 'post_content' => $t['quote'], 'menu_order' => $i ) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_dq_seed_id', 't' . $i );
			update_post_meta( $id, '_dq_logo', $t['logo'] );
			if ( $t['role'] ) {
				update_post_meta( $id, '_dq_role', $t['role'] );
			}
			update_post_meta( $id, '_dq_more_label', $t['more'] );
			$count++;
		}
	}
	$report[] = $have_testimonials ? 'testimonials: using existing post type ' . dq_source_post_type( 'testimonial' ) : sprintf( '%d testimonials created', $count );

	/* News posts (skipped when the site already has news content). Bodies come from
	   inc/news-content.php; posts seeded earlier with the title-only placeholder are
	   backfilled in place so their URLs and IDs stay put. */
	$count = 0;
	$filled = dq_backfill_news_bodies();
	foreach ( $have_news ? array() : dq_default_news() as $i => $n ) {
		if ( dq_find_post_by_meta( 'post', '_dq_seed_id', 'n' . $i ) ) {
			continue;
		}
		$body = dq_default_news_body( $n['slug'] );
		$cat = term_exists( $n['cat'], 'category' );
		if ( ! $cat ) {
			$cat = wp_insert_term( $n['cat'], 'category' );
		}
		$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : (int) $cat;
		$id     = wp_insert_post( array(
			'post_type'     => 'post',
			'post_status'   => 'publish',
			'post_title'    => $n['title'],
			'post_name'     => $n['slug'],
			'post_date'     => $n['date'] . ' 09:00:00',
			'post_content'  => $body ? $body : '<!-- wp:paragraph --><p>' . esc_html( $n['title'] ) . '</p><!-- /wp:paragraph -->',
			'post_category' => array( $cat_id ),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_dq_seed_id', 'n' . $i );
			update_post_meta( $id, '_dq_thumb', $n['image'] );
			$count++;
		}
	}
	$report[] = $have_news ? 'news: using existing content (' . dq_source_post_type( 'news' ) . ')' : sprintf( '%d news posts created, %d bodies backfilled', $count, $filled );

	/* Pages: Home + News & Events */
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home', 'post_content' => '<!-- wp:paragraph --><p>This page uses the theme\'s Home template. Edit hero and section copy in Appearance → Customize → DynamIQ Theme.</p><!-- /wp:paragraph -->' ) );
	} else {
		$home_id = $home->ID;
	}
	/* Blogs = the WordPress posts index at /blogs/ (live slug; inc/live-urls.php). News & Events =
	   the news post type when the site has one (page-news-events.php lists it), otherwise the same
	   page listing the news categories. */
	$blog_id = dq_ensure_blogs_page();
	$news    = get_page_by_path( 'news-events' );
	if ( ! $news ) {
		$news_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'News & Events', 'post_name' => 'news-events' ) );
	} else {
		$news_id = $news->ID;
	}
	/* Our Services lives at /our-services/ like the old site. An empty shell is enough: the
	   landing importer fills it from dynamiqes.com (auto-import on first admin load, or the
	   setup page), and page-landing.php renders the live copy read-only until then. */
	$services = get_page_by_path( 'our-services' );
	if ( ! $services ) {
		$services_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Our Services', 'post_name' => 'our-services' ) );
		update_post_meta( $services_id, '_wp_page_template', 'page-landing.php' );
	} else {
		$services_id = $services->ID;
	}
	/* Book a FREE DEMO: the old site's CTA target (/book-free-demo/), rendered by
	   page-book-free-demo.php (H1 + the enquiry form). Title tag / description as on the old site. */
	$demo = get_page_by_path( 'book-free-demo' );
	if ( ! $demo ) {
		$demo_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Book a FREE DEMO today!', 'post_name' => 'book-free-demo' ) );
	} else {
		$demo_id = $demo->ID;
	}
	if ( $demo_id && ! is_wp_error( $demo_id ) ) {
		if ( ! get_post_meta( $demo_id, '_dq_seo_title', true ) ) {
			update_post_meta( $demo_id, '_dq_seo_title', 'Book a Free Demo | DynamIQes' );
		}
		if ( ! get_post_meta( $demo_id, '_dq_seo_description', true ) ) {
			update_post_meta( $demo_id, '_dq_seo_description', 'Discover how Dynamiqes can revolutionize your processes. Our innovative solutions will take your business to the next level. Sign up for a free demo today!' );
		}
	}
	/* Contact Us and About Us: the old site's nav targets (/contact-us/, /about-us/). */
	$contact_id = dq_ensure_contact_page();
	$about_id   = dq_ensure_about_page();
	update_option( 'dq_about_page_v1', time() );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	$report[] = 'Home, Blogs, News & Events, Our Services, Book a FREE DEMO and Contact Us pages set';
	/* Products were created on the live slugs above; the parity pass fixes up anything an earlier
	   seed left on the old slugs and marks itself done. */
	$report   = array_merge( $report, dq_adopt_live_urls() );

	/* Remove WordPress' sample content if still untouched. */
	$hello = get_page_by_path( 'hello-world', OBJECT, 'post' );
	if ( $hello && 'publish' === $hello->post_status && false !== strpos( $hello->post_content, 'Welcome to WordPress' ) ) {
		wp_trash_post( $hello->ID );
		$report[] = 'sample "Hello world!" post trashed';
	}
	$sample_page = get_page_by_path( 'sample-page' );
	if ( $sample_page && 'publish' === $sample_page->post_status ) {
		wp_trash_post( $sample_page->ID );
	}

	/* Existing menus built by an earlier version pointed "Blogs" at the news page — repoint. */
	$existing_menu = wp_get_nav_menu_object( 'Primary Menu' );
	if ( $existing_menu ) {
		$blogs_parent = 0;
		foreach ( wp_get_nav_menu_items( $existing_menu->term_id ) as $mi ) {
			if ( 'Blogs' === $mi->title && (int) $mi->object_id === (int) $news_id ) {
				wp_update_nav_menu_item( $existing_menu->term_id, $mi->ID, array( 'menu-item-title' => 'Blogs', 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $blog_id, 'menu-item-status' => 'publish', 'menu-item-parent-id' => 0, 'menu-item-position' => (int) $mi->menu_order ) );
				$blogs_parent = $mi->ID;
			}
		}
		$landing = array();
		foreach ( dq_blog_landing_items() as $b ) {
			$landing[ $b['title'] ] = $b;
		}
		foreach ( wp_get_nav_menu_items( $existing_menu->term_id ) as $mi ) {
			/* Sub-items of Blogs: always point at their landing page when it exists (else blog / product fallback). */
			if ( $mi->menu_item_parent && isset( $landing[ $mi->title ] ) && untrailingslashit( $mi->url ) !== untrailingslashit( $landing[ $mi->title ]['url'] ) ) {
				$b    = $landing[ $mi->title ];
				$args = array( 'menu-item-title' => $mi->title, 'menu-item-status' => 'publish', 'menu-item-parent-id' => (int) $mi->menu_item_parent, 'menu-item-position' => (int) $mi->menu_order );
				if ( $b['object_id'] ) {
					$args += array( 'menu-item-type' => 'post_type', 'menu-item-object' => get_post_type( $b['object_id'] ), 'menu-item-object-id' => $b['object_id'] );
				} else {
					$args += array( 'menu-item-type' => 'custom', 'menu-item-url' => $b['url'] );
				}
				wp_update_nav_menu_item( $existing_menu->term_id, $mi->ID, $args );
			}
			/* Earlier seeders linked "Contact Us" to the home #contact anchor; the old site has /contact-us/. */
			if ( $contact_id && ! $mi->menu_item_parent && 'custom' === $mi->type && 'Contact Us' === $mi->title && dq_is_contact_anchor( $mi->url ) ) {
				wp_update_nav_menu_item( $existing_menu->term_id, $mi->ID, array( 'menu-item-title' => 'Contact Us', 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $contact_id, 'menu-item-status' => 'publish', 'menu-item-parent-id' => 0, 'menu-item-position' => (int) $mi->menu_order ) );
			}
			/* Earlier seeders linked "Our Services" to the home #services anchor; the old site has a page. */
			if ( ! $mi->menu_item_parent && 'custom' === $mi->type && dq_is_services_anchor( $mi->url ) ) {
				wp_update_nav_menu_item( $existing_menu->term_id, $mi->ID, array( 'menu-item-title' => $mi->title, 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $services_id, 'menu-item-status' => 'publish', 'menu-item-parent-id' => 0, 'menu-item-position' => (int) $mi->menu_order ) );
			}
			if ( 'News & Events' === $mi->title && 'page' === $mi->object && (int) $mi->object_id === (int) $news_id ) {
				wp_update_nav_menu_item( $existing_menu->term_id, $mi->ID, array( 'menu-item-title' => 'News & Events', 'menu-item-type' => 'custom', 'menu-item-url' => dq_news_url(), 'menu-item-status' => 'publish', 'menu-item-parent-id' => 0, 'menu-item-position' => (int) $mi->menu_order ) );
			}
		}
	}

	/* Primary menu */
	$menu_name = 'Primary Menu';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
		$products_url = home_url( '/products/' );
		$news_url     = dq_news_url();
		$blog_url     = get_permalink( $blog_id );
		$add = function ( $title, $url, $parent = 0, $object_id = 0 ) use ( $menu_id ) {
			$args = array( 'menu-item-title' => $title, 'menu-item-status' => 'publish', 'menu-item-parent-id' => $parent );
			if ( $object_id ) {
				$args['menu-item-type']      = 'post_type';
				$args['menu-item-object']    = get_post_type( $object_id );
				$args['menu-item-object-id'] = $object_id;
			} else {
				$args['menu-item-type'] = 'custom';
				$args['menu-item-url']  = $url;
			}
			return wp_update_nav_menu_item( $menu_id, 0, $args );
		};
		$prod_parent = $add( 'Our Products', $products_url );
		foreach ( dq_product_defaults() as $key => $p ) {
			$pid = dq_find_post_by_meta( 'dq_product', '_dq_product_key', $key );
			$add( $p['menu_label'], home_url( '/products/' . $p['slug'] . '/' ), $prod_parent, $pid );
		}
		$add( 'Our Services', get_permalink( $services_id ), 0, $services_id );
		$add( 'About Us', $about_id ? get_permalink( $about_id ) : home_url( '/about-us/' ), 0, $about_id );
		$blogs = $add( 'Blogs', $blog_url, 0, $blog_id );
		foreach ( dq_blog_landing_items() as $b ) {
			$add( $b['title'], $b['url'], $blogs, $b['object_id'] );
		}
		$add( 'News & Events', $news_url );
		/* Careers is a dedicated page on the old site (/career/), see inc/careers.php. */
		$career_id = function_exists( 'dq_ensure_career_page' ) ? dq_ensure_career_page() : 0;
		$add( 'Careers', $career_id ? get_permalink( $career_id ) : home_url( '/career/' ), 0, $career_id );
		$add( 'Contact Us', get_permalink( $contact_id ), 0, $contact_id );
		$locations            = get_theme_mod( 'nav_menu_locations', array() );
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
		$report[] = 'Primary menu created and assigned';
	}

	update_option( 'dq_seeded', time() );
	flush_rewrite_rules();
	return $report;
}

/**
 * Sideload bundled images into the Media Library and attach them as featured images.
 * Products: hero → featured image. Posts: news photo. Testimonials: logo.
 */
function dq_import_media() {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$done = 0;
	$items = array();
	foreach ( get_posts( array( 'post_type' => 'dq_product', 'posts_per_page' => -1, 'post_status' => 'any' ) ) as $p ) {
		$items[] = array( $p->ID, dq_asset( get_post_meta( $p->ID, '_dq_hero', true ) ), $p->post_title . ' – product screenshot' );
	}
	foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => -1, 'post_status' => 'any', 'meta_key' => '_dq_thumb' ) ) as $p ) {
		$items[] = array( $p->ID, dq_asset( get_post_meta( $p->ID, '_dq_thumb', true ) ), $p->post_title );
	}
	foreach ( get_posts( array( 'post_type' => 'dq_testimonial', 'posts_per_page' => -1, 'post_status' => 'any', 'meta_key' => '_dq_logo' ) ) as $p ) {
		$items[] = array( $p->ID, dq_asset( get_post_meta( $p->ID, '_dq_logo', true ) ), $p->post_title . ' logo' );
	}
	foreach ( $items as $it ) {
		list( $post_id, $url, $desc ) = $it;
		if ( ! $url || has_post_thumbnail( $post_id ) ) {
			continue;
		}
		$att = media_sideload_image( $url, $post_id, $desc, 'id' );
		if ( ! is_wp_error( $att ) ) {
			update_post_meta( $att, '_wp_attachment_image_alt', $desc );
			set_post_thumbnail( $post_id, $att );
			$done++;
		}
	}
	return $done;
}

/* ------------------------------------------------------------------ */
/* Admin page                                                          */
/* ------------------------------------------------------------------ */
add_action( 'admin_menu', function () {
	add_theme_page( __( 'DynamIQ Setup', 'dynamiqes' ), __( 'DynamIQ Setup', 'dynamiqes' ), 'manage_options', 'dq-setup', 'dq_setup_page' );
} );

function dq_setup_page() {
	$notice = '';
	if ( isset( $_POST['dq_action'] ) && check_admin_referer( 'dq_setup' ) && current_user_can( 'manage_options' ) ) {
		switch ( $_POST['dq_action'] ) {
			case 'seed':
				$notice = implode( '; ', dq_seed_content() );
				break;
			case 'media':
				$notice = sprintf( __( '%d images imported into the Media Library and set as featured images.', 'dynamiqes' ), dq_import_media() );
				break;
			case 'flush':
				flush_rewrite_rules();
				$notice = __( 'Permalinks flushed.', 'dynamiqes' );
				break;
			case 'live_urls':
				$notice = implode( '; ', dq_adopt_live_urls() );
				break;
			case 'landing':
				$notice = implode( '; ', dq_import_landing_pages( ! empty( $_POST['dq_sideload'] ) ) );
				break;
		}
	}
	$products = wp_count_posts( 'dq_product' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'DynamIQ Setup', 'dynamiqes' ); ?></h1>
		<?php if ( $notice ) : ?><div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div><?php endif; ?>
		<p><?php printf( esc_html__( 'Products published: %d. Home page: %s. News page: %s.', 'dynamiqes' ), (int) $products->publish, get_option( 'page_on_front' ) ? '✓' : '—', get_option( 'page_for_posts' ) ? '✓' : '—' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'dq_setup' ); ?>
			<h2><?php esc_html_e( '1. Import site content', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'Creates the 9 products, 12 testimonials, 4 news posts, the Home and News & Events pages and the primary menu. Safe to run again: existing items are skipped.', 'dynamiqes' ); ?></p>
			<button class="button button-primary" name="dq_action" value="seed"><?php esc_html_e( 'Run content import', 'dynamiqes' ); ?></button>
			<h2><?php esc_html_e( '2. Move images into the Media Library (optional)', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'Copies product screenshots, news photos and testimonial logos from the theme folder into /wp-content/uploads/ and sets them as Featured Images, so they can be replaced from WP Admin without touching theme files.', 'dynamiqes' ); ?></p>
			<button class="button" name="dq_action" value="media"><?php esc_html_e( 'Import images to Media Library', 'dynamiqes' ); ?></button>
			<h2><?php esc_html_e( '2b. Import SEO landing pages from dynamiqes.com (optional)', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'Recreates the six "Blogs" dropdown pages (Accounting System, ERP Solutions, IT Solutions Company, SAP Software, Barcode Inventory System, BIR CAS) as pages with the same slugs and the Landing page template. Existing pages with those slugs are updated. Needs outbound internet access.', 'dynamiqes' ); ?></p>
			<label><input type="checkbox" name="dq_sideload" value="1" checked> <?php esc_html_e( 'Copy hero images into the Media Library', 'dynamiqes' ); ?></label><br><br>
			<button class="button" name="dq_action" value="landing"><?php esc_html_e( 'Import landing pages', 'dynamiqes' ); ?></button>
			<h2><?php esc_html_e( '3. Permalinks', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'If /products/ shows a 404, flush the permalinks.', 'dynamiqes' ); ?></p>
			<button class="button" name="dq_action" value="flush"><?php esc_html_e( 'Flush permalinks', 'dynamiqes' ); ?></button>
			<h2><?php esc_html_e( '4. Live URLs', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'Puts the site on the dynamiqes.com URL structure: products at /products/<live slug>/ (the older short slugs 301 there), the Blogs index at /blogs/, News & Events as its own page. Runs once automatically after an update; safe to run again.', 'dynamiqes' ); ?></p>
			<button class="button" name="dq_action" value="live_urls"><?php esc_html_e( 'Adopt live URLs', 'dynamiqes' ); ?></button>
		</form>
		<h2><?php esc_html_e( 'Where things live', 'dynamiqes' ); ?></h2>
		<ul style="list-style:disc;padding-left:20px">
			<li><?php esc_html_e( 'Products → each product: hero text, images, features, FAQs (Product details box) and SEO box.', 'dynamiqes' ); ?></li>
			<li><?php esc_html_e( 'Testimonials → quote, client logo, role.', 'dynamiqes' ); ?></li>
			<li><?php esc_html_e( 'Posts → News & Events (category = tag shown on the card).', 'dynamiqes' ); ?></li>
			<li><?php esc_html_e( 'Appearance → Customize → DynamIQ Theme: hero copy/video, contact details, socials, SEO defaults, verification codes, analytics.', 'dynamiqes' ); ?></li>
			<li><?php esc_html_e( 'Appearance → Menus: Primary Menu, footer link lists.', 'dynamiqes' ); ?></li>
			<li><?php esc_html_e( 'Inquiries → every contact form submission (also emailed).', 'dynamiqes' ); ?></li>
		</ul>
	</div>
	<?php
}

/* Nudge after activation */
add_action( 'admin_notices', function () {
	if ( get_option( 'dq_seeded' ) && ! get_option( 'dq_notice_dismissed' ) && current_user_can( 'manage_options' ) ) {
		echo '<div class="notice notice-info is-dismissible"><p><strong>DynamIQ theme:</strong> ' . esc_html__( 'Content imported. Review products, menus and SEO defaults under', 'dynamiqes' ) . ' <a href="' . esc_url( admin_url( 'themes.php?page=dq-setup' ) ) . '">Appearance → DynamIQ Setup</a>.</p></div>';
		update_option( 'dq_notice_dismissed', 1 );
	}
} );
