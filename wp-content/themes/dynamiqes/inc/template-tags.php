<?php
/**
 * Template helpers: navigation walker, site data (services, trust logos, socials,
 * testimonials, news), reveal attributes, image helpers.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** Absolute URL to a home-page section anchor (works from any page). */
function dq_home_anchor( $id ) {
	return home_url( '/' ) . '#' . ltrim( $id, '#' );
}

/** URL of the products listing. */
function dq_products_url() {
	$url = get_post_type_archive_link( 'dq_product' );
	return $url ? $url : home_url( '/products/' );
}

/** URL of the blog index (the WordPress posts page, "Blogs"). */
function dq_blog_url() {
	$page = (int) get_option( 'page_for_posts' );
	if ( $page ) {
		return get_permalink( $page );
	}
	return home_url( '/blog/' );
}

/**
 * URL of News & Events: the site's news post type archive when one exists,
 * otherwise the "News & Events" page (page-news-events.php lists the news categories).
 */
function dq_news_url() {
	$pt = function_exists( 'dq_source_post_type' ) ? dq_source_post_type( 'news' ) : 'post';
	if ( 'post' !== $pt ) {
		$archive = get_post_type_archive_link( $pt );
		if ( $archive ) {
			return $archive;
		}
	}
	$page = get_page_by_path( 'news-events' );
	if ( $page && 'publish' === $page->post_status && (int) $page->ID !== (int) get_option( 'page_for_posts' ) ) {
		return get_permalink( $page );
	}
	return dq_home_anchor( 'news-events' );
}

/** Category IDs that count as "news" when news lives in regular posts (Customizer: News categories). */
function dq_news_category_ids() {
	$names = array_filter( array_map( 'trim', explode( ',', get_theme_mod( 'dq_news_categories', 'Events, Community, News' ) ) ) );
	$ids   = array();
	foreach ( $names as $n ) {
		$term = get_term_by( 'name', $n, 'category' );
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $n ), 'category' );
		}
		if ( $term && $term->count > 0 ) {
			$ids[] = (int) $term->term_id;
		}
	}
	return $ids;
}

/** Echo scroll-reveal attributes. */
function dq_reveal( $variant = '', $delay = null ) {
	echo ' data-reveal' . ( $variant ? '="' . esc_attr( $variant ) . '"' : '' );
	if ( null !== $delay ) {
		echo ' style="--d:' . (int) $delay . 'ms"';
	}
}

/** Brand logo <img>. $variant: nav | footer. */
function dq_logo_img( $variant = 'nav' ) {
	if ( 'footer' === $variant ) {
		$src   = get_theme_mod( 'dq_logo_footer', '' );
		$src   = $src ? $src : DQ_URI . '/assets/logos/DynamIQ_Enterprise_Solution_Inc__with_Tagline_Logo_blk.svg';
		$class = 'logo-footer';
		$alt   = get_bloginfo( 'name' ) . ' Inc.';
	} else {
		$custom = get_theme_mod( 'dq_logo_nav', '' );
		if ( ! $custom && has_custom_logo() ) {
			$custom = wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' );
		}
		$src   = $custom ? $custom : DQ_URI . '/assets/logos/DynamIQ_Logo_blk.svg';
		$class = 'logo-nav';
		$alt   = 'DynamIQ';
	}
	return '<img class="' . esc_attr( $class ) . '" src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" width="' . ( 'footer' === $variant ? 186 : 158 ) . '" height="' . ( 'footer' === $variant ? 70 : 35 ) . '">';
}

/* ------------------------------------------------------------------ */
/* Navigation                                                          */
/* ------------------------------------------------------------------ */

class DQ_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="dropdown">';
	}
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = empty( $item->classes ) ? array() : (array) $item->classes;
		$has     = in_array( 'menu-item-has-children', $classes, true );
		$current = (bool) array_intersect( array( 'current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent' ), $classes );
		$li      = ( 0 === $depth && $has ) ? ' class="has-children"' : '';
		$a       = $current ? ' class="active"' : '';
		$target  = $item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '';
		$output .= '<li' . $li . '><a' . $a . ' href="' . esc_url( $item->url ) . '"' . $target . '>' . esc_html( $item->title ) . '</a>';
	}
	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= '</li>';
	}
}

/**
 * SEO landing articles listed under "Blogs" (same slugs as dynamiqes.com).
 * Each resolves to the existing page/post with that slug; if the site does not have it,
 * the link falls back to the given URL (blog index or the related product page).
 * Returns [ title, url, object_id ].
 */
function dq_blog_landing_items() {
	$items = array(
		array( 'Accounting System Provider in the Philippines', 'accounting-system-philippines', dq_blog_url() ),
		array( 'Top ERP Solutions Provider in the Philippines', 'erp-solutions-philippines', dq_blog_url() ),
		array( 'Top IT Solution Company in the Philippines', 'it-solutions-company-philippines', dq_blog_url() ),
		array( 'Best SAP Software Provider Philippines', 'sap-software-philippines', dq_blog_url() ),
		array( 'Top Barcode Inventory System Philippines', 'barcode-inventory-system-philippines', home_url( '/products/dynamiq-barcode/' ) ),
		array( 'BIR CAS Provider in the Philippines', 'bir-cas-philippines', home_url( '/products/dynamiq-tax/' ) ),
	);
	$out = array();
	foreach ( $items as $it ) {
		$found = get_page_by_path( $it[1], OBJECT, array( 'page', 'post' ) );
		if ( $found && 'publish' === $found->post_status ) {
			$out[] = array( 'title' => $it[0], 'url' => get_permalink( $found ), 'object_id' => $found->ID );
		} else {
			$out[] = array( 'title' => $it[0], 'url' => $it[2], 'object_id' => 0 );
		}
	}
	return $out;
}

/** Default navigation structure (used as fallback and by the seeder). */
function dq_default_menu_items() {
	$products = array();
	foreach ( dq_get_products() as $p ) {
		$products[] = array( 'title' => $p['menu_label'], 'url' => $p['url'], 'object_id' => $p['id'] );
	}
	$news = dq_news_url();
	$blog = dq_blog_url();
	return array(
		array( 'title' => __( 'Our Products', 'dynamiqes' ), 'url' => dq_products_url(), 'children' => $products ),
		array( 'title' => __( 'Our Services', 'dynamiqes' ), 'url' => dq_home_anchor( 'services' ) ),
		array( 'title' => __( 'About Us', 'dynamiqes' ), 'url' => dq_home_anchor( 'about' ) ),
		array( 'title' => __( 'Blogs', 'dynamiqes' ), 'url' => $blog, 'children' => dq_blog_landing_items() ),
		array( 'title' => __( 'News & Events', 'dynamiqes' ), 'url' => $news ),
		array( 'title' => __( 'Careers', 'dynamiqes' ), 'url' => dq_home_anchor( 'contact' ) ),
		array( 'title' => __( 'Contact Us', 'dynamiqes' ), 'url' => dq_home_anchor( 'contact' ) ),
	);
}

/** Primary navigation: assigned menu, or the default structure. */
function dq_primary_menu() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'nav-menu',
			'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
			'walker'         => new DQ_Nav_Walker(),
			'depth'          => 2,
		) );
		return;
	}
	$request = ( isset( $GLOBALS['wp'] ) && ! empty( $GLOBALS['wp']->request ) ) ? $GLOBALS['wp']->request : '';
	$current = trailingslashit( home_url( $request ) );
	echo '<ul class="nav-menu">';
	foreach ( dq_default_menu_items() as $item ) {
		$has    = ! empty( $item['children'] );
		$active = ( trailingslashit( $item['url'] ) === $current ) || ( $has && is_singular( 'dq_product' ) && 'Our Products' === $item['title'] );
		echo '<li' . ( $has ? ' class="has-children"' : '' ) . '><a' . ( $active ? ' class="active"' : '' ) . ' href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
		if ( $has ) {
			echo '<ul class="dropdown">';
			foreach ( $item['children'] as $c ) {
				echo '<li><a href="' . esc_url( $c['url'] ) . '">' . esc_html( $c['title'] ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
}

/** Footer link list from a menu location, or a fallback array of [title, url]. */
function dq_footer_links( $location, $fallback ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array( 'theme_location' => $location, 'container' => false, 'items_wrap' => '<ul>%3$s</ul>', 'depth' => 1, 'fallback_cb' => false ) );
		return;
	}
	echo '<ul>';
	foreach ( $fallback as $l ) {
		echo '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
	}
	echo '</ul>';
}

/* ------------------------------------------------------------------ */
/* Site data                                                           */
/* ------------------------------------------------------------------ */

/** Contact details (Customizer → DynamIQ: Contact). */
function dq_contact_info() {
	return array(
		'address' => get_theme_mod( 'dq_address', 'No. 12 Tagdalit Street, Manresa, Quezon City 1115' ),
		'address_short' => get_theme_mod( 'dq_address_short', '12 Tagdalit Street, Brgy. Manresa, Quezon City' ),
		'phone1'  => get_theme_mod( 'dq_phone_1', '+63 917-630-4848' ),
		'phone2'  => get_theme_mod( 'dq_phone_2', '+63(2) 8365 0228' ),
		'email'   => get_theme_mod( 'dq_contact_email_public', 'sales@dynamiqes.com' ),
		'hours'   => get_theme_mod( 'dq_hours', 'Monday-Friday 8:00 AM – 5:00 PM' ),
		'city'    => 'Metro Manila',
	);
}

/** tel: href from a display number. */
function dq_tel( $display ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', str_replace( '(2)', '2', $display ) );
}

/** Social profiles. */
function dq_socials() {
	$all = array(
		'facebook'  => array( 'Facebook', get_theme_mod( 'dq_social_facebook', 'https://www.facebook.com/dynamiqenterprise' ), 'M13 22v-8h2.6l.4-3H13V9c0-.9.3-1.5 1.6-1.5H16V4.9c-.3 0-1.2-.1-2.3-.1-2.3 0-3.7 1.4-3.7 3.9V11H7.5v3H10v8h3z' ),
		'instagram' => array( 'Instagram', get_theme_mod( 'dq_social_instagram', 'https://www.instagram.com/dynamiqenterprise/' ), 'M12 8.8A3.2 3.2 0 1 0 12 15.2 3.2 3.2 0 0 0 12 8.8zm0 5.3a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM16.5 4H7.5A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4zm2.4 12.5a2.4 2.4 0 0 1-2.4 2.4h-9a2.4 2.4 0 0 1-2.4-2.4v-9a2.4 2.4 0 0 1 2.4-2.4h9a2.4 2.4 0 0 1 2.4 2.4v9zm-2.2-9a.8.8 0 1 1-1.6 0 .8.8 0 0 1 1.6 0z' ),
		'twitter'   => array( 'Twitter / X', get_theme_mod( 'dq_social_twitter', 'https://twitter.com/dynamiqesInc' ), 'M17.5 3h3l-6.6 7.5L22 21h-6l-4.3-5.7L6.7 21H3.6l7-8L2 3h6.2l3.9 5.2L17.5 3zm-1 16h1.7L7.6 4.8H5.8L16.5 19z' ),
		'tiktok'    => array( 'TikTok', get_theme_mod( 'dq_social_tiktok', 'https://www.tiktok.com/@dynamiqesi' ), 'M16.5 3c.3 2 1.5 3.6 3.5 3.9v2.5c-1.3 0-2.5-.4-3.5-1.1v6.2c0 3.1-2.5 5.5-5.5 5.5S5.5 17.6 5.5 14.5 8 9 11 9c.3 0 .6 0 .9.1v2.6c-.3-.1-.6-.2-.9-.2-1.6 0-2.9 1.3-2.9 2.9s1.3 2.9 2.9 2.9 2.9-1.3 2.9-2.9V3h2.6z' ),
		'youtube'   => array( 'YouTube', get_theme_mod( 'dq_social_youtube', 'https://www.youtube.com/@dynamiqenterprise' ), 'M22 8.2s-.2-1.4-.8-2c-.8-.8-1.6-.8-2-.9C16.4 5 12 5 12 5s-4.4 0-7.2.3c-.4 0-1.2 0-2 .9-.6.6-.8 2-.8 2S1.8 9.8 1.8 11.5v1c0 1.7.2 3.3.2 3.3s.2 1.4.8 2c.8.8 1.8.8 2.3.9C6.9 19 12 19 12 19s4.4 0 7.2-.3c.4 0 1.2 0 2-.9.6-.6.8-2 .8-2s.2-1.6.2-3.3v-1c0-1.7-.2-3.3-.2-3.3zM9.9 14.6V9.4l4.6 2.6-4.6 2.6z' ),
		'linkedin'  => array( 'LinkedIn', get_theme_mod( 'dq_social_linkedin', 'https://www.linkedin.com/company/dynamiqesofficial/' ), 'M6.9 8.8H4V20h2.9V8.8zM5.4 4a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4zM20 20h-2.9v-5.5c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9V20H10.3V8.8h2.8v1.5h.1c.4-.7 1.4-1.5 2.8-1.5 3 0 3.6 2 3.6 4.5V20z' ),
	);
	return array_filter( $all, function ( $s ) { return ! empty( $s[1] ); } );
}

/** Service steps (home timeline). */
function dq_services() {
	return apply_filters( 'dq_services', array(
		array( 'num' => '01', 'title' => 'Consultation', 'text' => 'We help you map which ERP features fit your needs today — and the ones you\'ll grow into tomorrow.', 'image' => 'assets/services/consultation.jpg' ),
		array( 'num' => '02', 'title' => 'Implementation', 'text' => 'A straightforward yet responsive rollout that gets your business planning software fully optimized.', 'image' => 'assets/services/implementation.jpg' ),
		array( 'num' => '03', 'title' => 'Development', 'text' => 'Custom-built processes for the parts of your business that don\'t fit an out-of-the-box mold.', 'image' => 'assets/services/development.jpg' ),
		array( 'num' => '04', 'title' => 'Training', 'text' => 'We train your team so they get the most out of the new system from day one.', 'image' => 'assets/services/training.jpg' ),
		array( 'num' => '05', 'title' => 'Technical & Helpdesk Support', 'text' => 'Ongoing updates and support that keep your system running smoothly as your company grows.', 'image' => 'assets/services/support.jpg' ),
	) );
}

/** Lifestyle gallery photos (home carousel between Why Choose and Our Services).
 *  Placeholders reuse the services photos until the final set is dropped in. */
function dq_gallery_photos() {
	return apply_filters( 'dq_gallery_photos', array(
		array( 'Consultation session with a DynamIQ specialist', 'assets/services/consultation.jpg' ),
		array( 'Team collaborating during an implementation', 'assets/services/implementation.jpg' ),
		array( 'Developers reviewing a custom process', 'assets/services/development.jpg' ),
		array( 'Hands-on training for a client team', 'assets/services/training.jpg' ),
		array( 'Helpdesk specialist supporting a customer', 'assets/services/support.jpg' ),
	) );
}

/** Footer video wall: the newest video uploads in the media library, normalised to
 *  video / poster / label. Poster = the attachment's featured image if one is set
 *  (Media > edit > "Featured image" on a video). When fewer than $count clips have
 *  been uploaded, the strip is topped up with bundled placeholder clips so it is
 *  never thin; with no uploads at all it is placeholders only. */
function dq_video_wall_items( $count = 4 ) {
	$out   = array();
	$posts = get_posts( array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'video',
		'post_status'    => 'inherit',
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	foreach ( $posts as $a ) {
		$url = wp_get_attachment_url( $a->ID );
		if ( ! $url ) {
			continue;
		}
		$poster   = has_post_thumbnail( $a->ID ) ? get_the_post_thumbnail_url( $a->ID, 'dq-wide' ) : '';
		$title    = trim( get_the_title( $a ) );
		$out[]    = array(
			'video'  => $url,
			'poster' => $poster ? $poster : '',
			'label'  => '' !== $title ? $title : __( 'Video', 'dynamiqes' ),
		);
	}
	if ( count( $out ) < $count ) {
		$fill = array(
			array( 'video' => dq_hero_video_url(), 'poster' => dq_hero_poster_url(), 'label' => __( 'DynamIQ in motion', 'dynamiqes' ) ),
			array( 'video' => file_exists( DQ_DIR . '/assets/video/contact-gradient.mp4' ) ? DQ_URI . '/assets/video/contact-gradient.mp4' : '', 'poster' => '', 'label' => __( 'Brand reel', 'dynamiqes' ) ),
			array( 'video' => 'https://dynamiqes.com/wp-content/themes/dynamiqes/assets/images/homepage/dynamiqes-video-banner.mp4', 'poster' => '', 'label' => __( 'SAP Business One overview', 'dynamiqes' ) ),
		);
		$have = wp_list_pluck( $out, 'video' );
		foreach ( $fill as $f ) {
			if ( count( $out ) >= $count ) {
				break;
			}
			if ( $f['video'] && ! in_array( $f['video'], $have, true ) ) {
				$out[] = $f;
			}
		}
	}
	return apply_filters( 'dq_video_wall_items', array_slice( $out, 0, $count ) );
}

/** Client logos for the trust marquee. */
function dq_trust_logos() {
	return apply_filters( 'dq_trust_logos', array(
		array( 'MacroAsia Corporation', 'assets/trust/MacroAsia-Corporation-Logo-1.webp' ),
		array( 'Philippine Allied Enterprises Corp.', 'assets/trust/PAEC-Logo.webp' ),
		array( 'Presline Steel Products Inc.', 'assets/trust/PreslineLogo.webp' ),
		array( 'Toyo Adtec Healthcare Products Inc.', 'assets/trust/Toyo-Adtec-Logo.webp' ),
		array( 'Tosoh Polyvin Corporation', 'assets/trust/Tosoh.webp' ),
		array( 'Kenstand Philippines, Inc.', 'assets/trust/Kenstand-Philippines-Inc-min.webp' ),
		array( 'Florabel', 'assets/trust/FLORABEL.webp' ),
		array( 'Cecile\'s Pharmacy', 'assets/trust/Ceciles-removebg-preview.webp' ),
		array( 'Intelligent Skin Care', 'assets/trust/Intelligent-Skin-Care-Inc.webp' ),
	) );
}

/** SAP Business One feature bento (home). */
function dq_sap_features() {
	return apply_filters( 'dq_sap_features', array(
		array( 'Complete & Customizable', 'Aside from giving clear visibility to your entire business, it is also designed to be flexible and customizable. You can customize the business planning software\'s user-friendly interface through different forms and reports.' ),
		array( 'Easy Integration', 'The software is designed to be easily integrated with other systems and applications thanks to its wide range of integration options. Businesses can seamlessly connect the business planning software with third-party providers.' ),
		array( 'Easy Enablement and Quick Deployment', 'Every business has its special requirements for enterprise resource management. With SAP Business One\'s flexibility, you can select the best option that suits your business requirements whether it be for onsite or cloud implementation.' ),
		array( 'Cloud Ready and Offsite Access Capable', 'SAP Business One is a cloud-ready software where companies can manage their business operations anytime and anywhere. Whether it\'s deployed on-site or on the cloud, you can access this even on your mobile devices!' ),
		array( 'Wide Range of Business Analytics and Reports', 'This business planning software provides businesses with powerful analytic and reporting tools. This allows them to keep informed about their business and make decisions based on real-time data insights from the digital solution.' ),
	) );
}

/** Default testimonials (seeded into dq_testimonial; used as fallback). */
function dq_default_testimonials() {
	return array(
		array( 'name' => 'Spartans 3 Trading Corporation', 'role' => '', 'logo' => 'assets/testimonials/spartans3.png', 'more' => 'Read Spartans 3\'s Story', 'quote' => 'Thank you so much DynamIQ, to the whole team na nag ca-cater sa amin ngayon dahil ok na ok yung nagiging service nyo with us.' ),
		array( 'name' => 'Cecile\'s Pharmacy', 'role' => '', 'logo' => 'assets/testimonials/ceciles-pharmacy.png', 'more' => 'Read Cecile\'s Pharmacy\'s Story', 'quote' => 'SAP Business One became very attractive to us because it certainly provides what our business needs.' ),
		array( 'name' => 'Group Finance Controller for MacroAsia Corporation', 'role' => '', 'logo' => 'assets/trust/MacroAsia-Corporation-Logo-1.png', 'more' => 'Read MacroAsia\'s Story', 'quote' => 'With the help of DynamIQ, we were able to get accreditation from BIR. They were able to come up with the consolidation process which our previous provider cannot provide.' ),
		array( 'name' => 'Presline Steel Products Inc.', 'role' => '', 'logo' => 'assets/trust/PreslineLogo.png', 'more' => 'Read Presline Steel\'s Story', 'quote' => 'Presline Steel Products Inc. is a prominent Filipino corporation established in 1993 with a core focus on manufacturing high-quality metal parts and products tailored to the needs of the Philippine market. I am Mary Grace Reyes, with an extensive 25-year…' ),
		array( 'name' => 'VP for Finance Philippine Allied Enterprises Corp.', 'role' => '', 'logo' => 'assets/trust/PAEC-Logo.png', 'more' => 'Read PAEC\'s Story', 'quote' => 'Philippine Allied Enterprises Corporation (PAEC) started importing Bridgestone tires to the Philippines in the year 1953 and continues to be the sole distributor in the Philippines today. Having a lean and efficient organization with roughly 160 employees in addition to…' ),
		array( 'name' => 'Metalink Manufacturing Corp.', 'role' => '', 'logo' => 'assets/testimonials/metalink.png', 'more' => 'Read Metalink\'s Story', 'quote' => 'Being accustomed to using manual reports for years, having SAP in our Accounting Systems is a very great relief. SAP B1 assist us in centralizing data and making our work easier it also allows us to be more productive because…' ),
		array( 'name' => 'Toyo Adtec Healthcare Products Inc.', 'role' => '', 'logo' => 'assets/trust/Toyo-Adtec-Logo.png', 'more' => 'Read Toyo Adtec\'s Story', 'quote' => 'Experience with DynamIQ team, it\'s really dynamic. It has been positive and collaborative. DynamIQ team shows strong technical expertise. Also, flexibility and commitment to ensuring that SAP Business One supports are complex business structures, especially with the two industries we…' ),
		array( 'name' => 'Tosoh Polyvin Corporation', 'role' => '', 'logo' => 'assets/trust/Tosoh.png', 'more' => 'Read Tosoh Polyvin\'s Story', 'quote' => 'With DynamIQ introducing SAP Business One to us, it\'s actually a big help. DynamIQ is also easier to transact with. We have no experience kasi with SAP Business One first time namin na encounter si SAP Business One, the struggle…' ),
		array( 'name' => 'Kenstand Philippines, Inc.', 'role' => '', 'logo' => 'assets/trust/Kenstand-Philippines-Inc-min.png', 'more' => 'Read Kenstand\'s Story', 'quote' => 'As a subsidiary of Kenstand Investment Limited, an international trading company based in Hong Kong, Kenstand Philippines, Inc. has had an excellent experience with DynamIQ Enterprise Solution Inc. The implementation of SAP Business One (SAP B1) has significantly streamlined our…' ),
		array( 'name' => 'Florabel', 'role' => '', 'logo' => 'assets/trust/FLORABEL.png', 'more' => 'Read Florabel\'s Story', 'quote' => 'Previously, we struggled with scheduling, which impacted the preparation of our financial reports. However, with SAP B1, our reporting process has become more efficient and customizable to meet our company\'s specific needs. So far, we are very satisfied with the…' ),
		array( 'name' => 'Ms. O. Ayun', 'role' => 'Finance and Accounting Head', 'logo' => 'assets/testimonials/avatar.png', 'more' => 'Read Ms. O. Ayun\'s Story', 'quote' => 'Previously, we faced issues with inefficient manual processes, delayed financial reports, and outdated information. After implementing SAP B1, we\'ve seen streamlined operations, improved data accuracy and reporting, and better financial management and compliance. The transition was smooth, thanks to the…' ),
		array( 'name' => 'Intelligent Skin Care', 'role' => '', 'logo' => 'assets/trust/Intelligent-Skin-Care-Inc.png', 'more' => 'Read Intelligent Skin Care\'s Story', 'quote' => 'The highlight in implementing the SAP B1 is that we are able to simplify the previous long processes that we were practicing. We appreciate the patience of DynamIQ with us. I also think DynamIQ was able to embrace us with…' ),
	);
}

/**
 * Which post type holds testimonials / news on this install.
 * Existing sites (e.g. the current dynamiqes.com) already have "Customer Testimonials"
 * and "News and Events" post types; reuse them instead of the theme's own when present.
 * Override in Customize → DynamIQ Theme → Content sources.
 */
function dq_source_post_type( $kind ) {
	$mod = get_theme_mod( 'dq_pt_' . $kind, 'auto' );
	if ( $mod && 'auto' !== $mod && post_type_exists( $mod ) ) {
		return $mod;
	}
	$needles = 'testimonial' === $kind ? array( 'testimonial' ) : array( 'news', 'event' );
	$default = 'testimonial' === $kind ? 'dq_testimonial' : 'post';
	foreach ( get_post_types( array( 'show_ui' => true ), 'objects' ) as $pt ) {
		if ( in_array( $pt->name, array( 'dq_testimonial', 'dq_product', 'dq_inquiry', 'post', 'page', 'attachment' ), true ) ) {
			continue;
		}
		$hay = strtolower( $pt->name . ' ' . $pt->label );
		foreach ( $needles as $n ) {
			if ( false !== strpos( $hay, $n ) && wp_count_posts( $pt->name )->publish > 0 ) {
				return $pt->name;
			}
		}
	}
	return $default;
}

/** Testimonials from the CPT (fallback: defaults). */
function dq_testimonials() {
	$posts = get_posts( array( 'post_type' => dq_source_post_type( 'testimonial' ), 'posts_per_page' => -1, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ), 'post_status' => 'publish' ) );
	$out   = array();
	foreach ( $posts as $p ) {
		$logo  = has_post_thumbnail( $p ) ? get_the_post_thumbnail_url( $p, 'medium' ) : dq_asset( get_post_meta( $p->ID, '_dq_logo', true ) );
		$quote = wp_strip_all_tags( strip_shortcodes( $p->post_content ) );
		if ( '' === trim( $quote ) && $p->post_excerpt ) {
			$quote = wp_strip_all_tags( $p->post_excerpt );
		}
		$more  = get_post_meta( $p->ID, '_dq_more_label', true );
		$link  = get_post_meta( $p->ID, '_dq_link', true );
		if ( ! $link && 'dq_testimonial' !== $p->post_type && is_post_type_viewable( $p->post_type ) ) {
			$link = get_permalink( $p );
			$more = $more ? $more : sprintf( __( 'Read %s\'s Story', 'dynamiqes' ), $p->post_title );
		}
		$out[] = array(
			'name'  => $p->post_title,
			'role'  => get_post_meta( $p->ID, '_dq_role', true ),
			'logo'  => $logo,
			'more'  => $more,
			'link'  => $link,
			'quote' => wp_trim_words( $quote, 45, '…' ),
		);
	}
	if ( empty( $out ) ) {
		foreach ( dq_default_testimonials() as $t ) {
			$t['logo'] = dq_asset( $t['logo'] );
			$t['link'] = '';
			$out[]     = $t;
		}
	}
	return $out;
}

/** Default news (seeded as posts; used as fallback). */
function dq_default_news() {
	return array(
		array( 'title' => 'DynamIQ Hosts Exclusive Event at SAP Philippines to Empower SMBs with SAP Business One Solutions', 'cat' => 'Events', 'date' => '2025-10-28', 'image' => 'assets/news/sap-philippines-event-2025.jpg', 'slug' => 'dynamiq-hosts-exclusive-event-at-sap-philippines' ),
		array( 'title' => 'Forest Foundation Philippines', 'cat' => 'Community', 'date' => '2025-03-28', 'image' => 'assets/news/forest-foundation-2025.jpg', 'slug' => 'forest-foundation-philippines' ),
		array( 'title' => 'DynamIQ Enterprise Solution Inc. Brings Hope and Happiness to National Children\'s Hospital', 'cat' => 'Community', 'date' => '2025-02-12', 'image' => 'assets/news/national-childrens-hospital-2025.jpg', 'slug' => 'brings-hope-and-happiness-to-national-childrens-hospital' ),
		array( 'title' => 'Channel Partner Event 2024 – Synergy: One Partnership, Countless Possibilities', 'cat' => 'Events', 'date' => '2024-07-03', 'image' => 'assets/news/channel-partner-event-2024.jpg', 'slug' => 'channel-partner-event-2024-synergy' ),
	);
}

/** Post image URL: featured image, then the `_dq_thumb` fallback. */
function dq_post_thumb_url( $post_id, $size = 'dq-card' ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, $size );
	}
	return dq_asset( get_post_meta( $post_id, '_dq_thumb', true ) );
}

/** Latest news items normalised for the home section (featured + list). */
function dq_news_items( $count = 4 ) {
	$pt   = dq_source_post_type( 'news' );
	$args = array( 'post_type' => $pt, 'posts_per_page' => $count, 'post_status' => 'publish', 'ignore_sticky_posts' => true );
	if ( 'post' === $pt ) {
		$cats = dq_news_category_ids();
		if ( $cats ) {
			$args['category__in'] = $cats;
		}
	}
	$posts = get_posts( $args );
	$out   = array();
	foreach ( $posts as $p ) {
		$cat = __( 'News', 'dynamiqes' );
		foreach ( get_object_taxonomies( $p->post_type ) as $tax ) {
			$terms = get_the_terms( $p->ID, $tax );
			if ( $terms && ! is_wp_error( $terms ) && ! in_array( $tax, array( 'post_tag', 'post_format' ), true ) ) {
				$cat = $terms[0]->name;
				break;
			}
		}
		$out[] = array(
			'title' => get_the_title( $p ),
			'cat'   => $cat,
			'date'  => get_the_date( 'M j, Y', $p ),
			'image' => dq_post_thumb_url( $p->ID, 'dq-wide' ),
			'url'   => get_permalink( $p ),
		);
	}
	if ( empty( $out ) ) {
		foreach ( array_slice( dq_default_news(), 0, $count ) as $n ) {
			$out[] = array( 'title' => $n['title'], 'cat' => $n['cat'], 'date' => date_i18n( 'M j, Y', strtotime( $n['date'] ) ), 'image' => dq_asset( $n['image'] ), 'url' => dq_news_url() );
		}
	}
	return $out;
}

/** Hero video sources. */
function dq_hero_video_url() {
	$v = get_theme_mod( 'dq_hero_video', '' );
	if ( $v ) {
		return $v;
	}
	if ( file_exists( DQ_DIR . '/assets/video/hero-banner.mp4' ) ) {
		return DQ_URI . '/assets/video/hero-banner.mp4';
	}
	return 'https://dynamiqes.com/wp-content/themes/dynamiqes/assets/images/homepage/dynamiqes-video-banner.mp4';
}
function dq_hero_poster_url() {
	$p = get_theme_mod( 'dq_hero_poster', '' );
	return $p ? $p : DQ_URI . '/assets/video/hero-poster.jpg';
}

/** Shared "We'd like to hear from you" CTA band (products + product pages). */
function dq_cta_band() {
	get_template_part( 'template-parts/cta-band' );
}

/** Phone icon path (reused). */
function dq_icon_phone() {
	return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8a15 15 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.24 11 11 0 0 0 3.5.56 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11 11 0 0 0 .56 3.5 1 1 0 0 1-.24 1l-2.2 2.3z"/></svg>';
}
