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
	$n = 0;
	foreach ( wp_get_nav_menu_items( $menu->term_id ) as $mi ) {
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

/** Find a post by meta key/value. */
function dq_find_post_by_meta( $type, $key, $value ) {
	$found = get_posts( array( 'post_type' => $type, 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => $key, 'meta_value' => $value, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
}

/** Create/refresh everything. Idempotent. */
function dq_seed_content() {
	$report = array();

	/* Site identity + permalinks */
	if ( 'WordPress' === get_option( 'blogname' ) || ! get_option( 'dq_seeded' ) ) {
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
			foreach ( dq_product_field_map() as $field => $def ) {
				$v = $p[ $field ] ?? '';
				if ( 'lines' === $def[0] ) {
					$v = dq_lines_to_text( $v );
				} elseif ( 'features' === $def[0] ) {
					$v = dq_features_to_text( $v );
				} elseif ( 'faqs' === $def[0] ) {
					$v = dq_faqs_to_text( $v );
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

	/* News posts (skipped when the site already has news content) */
	$count = 0;
	foreach ( $have_news ? array() : dq_default_news() as $i => $n ) {
		if ( dq_find_post_by_meta( 'post', '_dq_seed_id', 'n' . $i ) ) {
			continue;
		}
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
			'post_content'  => '<!-- wp:paragraph --><p>' . esc_html( $n['title'] ) . '</p><!-- /wp:paragraph -->',
			'post_category' => array( $cat_id ),
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_dq_seed_id', 'n' . $i );
			update_post_meta( $id, '_dq_thumb', $n['image'] );
			$count++;
		}
	}
	$report[] = $have_news ? 'news: using existing content (' . dq_source_post_type( 'news' ) . ')' : sprintf( '%d news posts created', $count );

	/* Pages: Home + News & Events */
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home', 'post_content' => '<!-- wp:paragraph --><p>This page uses the theme\'s Home template. Edit hero and section copy in Appearance → Customize → DynamIQ Theme.</p><!-- /wp:paragraph -->' ) );
	} else {
		$home_id = $home->ID;
	}
	/* Blogs = the WordPress posts index. News & Events = the news post type archive when the
	   site has one, otherwise the "News & Events" page (page-news-events.php, news categories). */
	$blog = get_page_by_path( 'blog' );
	if ( ! $blog ) {
		$blog_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Blogs', 'post_name' => 'blog' ) );
	} else {
		$blog_id = $blog->ID;
	}
	$news = get_page_by_path( 'news-events' );
	if ( ! $news ) {
		$news_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'News & Events', 'post_name' => 'news-events' ) );
	} else {
		$news_id = $news->ID;
	}
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $home_id );
	update_option( 'page_for_posts', $blog_id );
	$report[] = 'Home, Blogs and News & Events pages set';

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
		$add( 'Our Services', home_url( '/#services' ) );
		$add( 'About Us', home_url( '/#about' ) );
		$blogs = $add( 'Blogs', $blog_url, 0, $blog_id );
		foreach ( dq_blog_landing_items() as $b ) {
			$add( $b['title'], $b['url'], $blogs, $b['object_id'] );
		}
		$add( 'News & Events', $news_url );
		$add( 'Careers', home_url( '/#contact' ) );
		$add( 'Contact Us', home_url( '/#contact' ) );
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
