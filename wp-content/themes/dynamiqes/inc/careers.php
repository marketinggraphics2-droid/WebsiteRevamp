<?php
/**
 * Careers — the old site's /career/ listing page and its /careers/<job>/ detail pages.
 *
 * dynamiqes.com has a "Careers" nav item that opens /career/ (H1 "Discover Your Career Path with
 * Us", the "DynamIQ Enterprise Solution Hiring Position" job board filtered by Metro Manila / Cebu,
 * culture + core values copy, HR contact) and one page per opening at /careers/<slug>/ (summary,
 * Job Responsibilities, Qualifications, Apply Now). This file recreates that structure with the
 * same URLs (SEO):
 *
 *  - post type dq_career  → /careers/<slug>/ (single-dq_career.php)
 *  - page "career"        → /career/          (page-career.php lists the published openings)
 *  - dq_seed_careers()    → creates the eight openings, pulling each description from the live
 *                           page when reachable; dq_career_default_jobs() is the offline fallback
 *  - dq_repair_careers_menu_link() → re-points a saved menu's "Careers" (old home #contact anchor)
 *
 * Openings are ordinary posts afterwards: edit / add / unpublish them under Careers in WP Admin.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------ */
/* Post type + meta                                                    */
/* ------------------------------------------------------------------ */

add_action( 'init', function () {
	if ( 'dq_career' !== dq_career_post_type() ) {
		return; // the site already has the live careers post type (a copy of the dynamiqes.com database)
	}
	register_post_type( 'dq_career', array(
		'labels'        => array(
			'name'          => __( 'Careers', 'dynamiqes' ),
			'singular_name' => __( 'Job Opening', 'dynamiqes' ),
			'add_new_item'  => __( 'Add New Job Opening', 'dynamiqes' ),
			'edit_item'     => __( 'Edit Job Opening', 'dynamiqes' ),
			'all_items'     => __( 'All Job Openings', 'dynamiqes' ),
			'menu_name'     => __( 'Careers', 'dynamiqes' ),
		),
		'public'        => true,
		'has_archive'   => false, // the listing is the /career/ page, as on the old site
		'rewrite'       => array( 'slug' => 'careers', 'with_front' => false ),
		'menu_icon'     => 'dashicons-businessperson',
		'menu_position' => 8,
		'supports'      => array( 'title', 'editor', 'excerpt', 'page-attributes', 'revisions' ),
		'show_in_rest'  => true,
	) );
} );

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dq_career_details', __( 'Job details', 'dynamiqes' ), 'dq_career_meta_box', 'dq_career', 'side', 'high' );
} );

function dq_career_meta_box( $post ) {
	wp_nonce_field( 'dq_career_save', 'dq_career_nonce' );
	$loc  = get_post_meta( $post->ID, '_dq_location', true );
	$type = get_post_meta( $post->ID, '_dq_job_type', true );
	echo '<p><label for="dq_location"><strong>' . esc_html__( 'Location', 'dynamiqes' ) . '</strong></label><br><input type="text" class="widefat" id="dq_location" name="dq_location" value="' . esc_attr( $loc ) . '" placeholder="Metro Manila"></p>';
	echo '<p><label for="dq_job_type"><strong>' . esc_html__( 'Employment type', 'dynamiqes' ) . '</strong></label><br><input type="text" class="widefat" id="dq_job_type" name="dq_job_type" value="' . esc_attr( $type ) . '" placeholder="Full Time"></p>';
	echo '<p class="description">' . esc_html__( 'The Excerpt is the short "Job Description" shown on the Careers page card; the editor holds the full Job Responsibilities / Qualifications.', 'dynamiqes' ) . '</p>';
}

add_action( 'save_post_dq_career', function ( $post_id ) {
	if ( ! isset( $_POST['dq_career_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dq_career_nonce'] ), 'dq_career_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( 'dq_location' => '_dq_location', 'dq_job_type' => '_dq_job_type' ) as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
} );

/* ------------------------------------------------------------------ */
/* URLs + copy                                                         */
/* ------------------------------------------------------------------ */

/**
 * Post type the openings live in: the live site's own `careers` type when the theme runs on a copy of
 * the dynamiqes.com database (same /careers/<slug>/ URLs), otherwise the theme's dq_career.
 */
function dq_career_post_type() {
	static $pt = null;
	if ( null === $pt ) {
		$pt = post_type_exists( 'careers' ) ? 'careers' : 'dq_career';
	}
	return $pt;
}

/** Location / employment type of an opening, whichever meta key the source post type uses. */
function dq_career_meta( $post_id, $what ) {
	$keys = 'location' === $what ? array( '_dq_location', 'location', 'job_location', '_location', 'job-location' ) : array( '_dq_job_type', 'job_type', 'employment_type', 'type', '_job_type', 'job-type' );
	foreach ( $keys as $k ) {
		$v = get_post_meta( $post_id, $k, true );
		if ( is_string( $v ) && '' !== trim( $v ) ) {
			return trim( $v );
		}
	}
	return '';
}

/** The Careers listing page (slug career, as on dynamiqes.com/career/). Null until it exists. */
function dq_career_page() {
	$page = get_page_by_path( 'career' );
	return ( $page && 'publish' === $page->post_status ) ? $page : null;
}

/** URL of the Careers listing; falls back to the slug URL so the nav is right before seeding. */
function dq_career_url() {
	$page = dq_career_page();
	return $page ? get_permalink( $page ) : home_url( '/career/' );
}

/** HR contact shown on the Careers pages (old site: "HR CONTACT INFORMATION"). */
function dq_career_hr() {
	return apply_filters( 'dq_career_hr', array(
		'phone' => '+63 917 703 2701',
		'email' => 'hr@dynamiqes.com',
	) );
}

/** The listing page's copy, verbatim from dynamiqes.com/career/ (kept for SEO). */
function dq_career_page_copy() {
	return apply_filters( 'dq_career_page_copy', array(
		'h1'          => 'Discover Your Career Path with Us',
		'intro'       => 'Explore rewarding opportunities to join our dynamic team! We\'re seeking talented individuals who are eager to innovate and drive positive change in the business and technology landscape. Explore our open positions below and take the first step towards a rewarding and fulfilling career with us.',
		'hiring_h2'   => 'DynamIQ Enterprise Solution Hiring Position',
		'hiring_lead' => 'Join our dynamic team and be part of a collaborative environment where innovation thrives and careers flourish.',
		'join_h2'     => 'Join Us and Empower Businesses with SAP Business One',
		'join_p'      => 'Dive into the transformative capabilities of SAP Business One with our comprehensive introduction video. Gain valuable insights into how this integrated business management solution streamlines operations, catalyzes growth, and elevates efficiency across companies of every scale. Discover how your role can shape the success story of businesses worldwide.',
		'culture_h2'  => 'Experience the Vibrant Culture of DynamIQ: Where Innovation Meets Collaboration',
		'culture_p'   => 'At DynamIQ, we cultivate an environment where innovation thrives and collaboration fuels success. Join us and experience a vibrant workplace culture where your ideas are valued, and your potential is unleashed.',
		'values_h2'   => 'Our Core Values',
		'values_lead' => 'Our core values serve as the compass of our culture, shaping every decision and action we take towards excellence and integrity in all we do.',
		'values'      => array(
			array( 'Driven', 'Our company offers a wide range of technology based solutions, with the aim of helping businesses achieve their goals, improve their efficiency, and stay ahead of the competition.', 'driven.png' ),
			array( 'Dependable', 'We are committed to provide our clients with the most relevant and responsive IT support services, delivered with a customer-oriented, professional, in-depth, and, knowledgeable attitude.', 'dependable.png' ),
			array( 'Dedicated', 'The company is passionate in helping businesses improved their operational efficiency and productivity by continuously educating, training and, developing our experienced team members, to ensure that we adapt to the dynamic business landscape.', 'dedicated.png' ),
			array( 'Data Security', 'We are committed to ensuring the confidentiality, integrity, and availability of our customers\' information by adhering to strict data security protocols.', 'data.png' ),
		),
		'seo_title'   => 'DynamIQes - Career',
		'seo_desc'    => 'Discover career opportunities with DynamIQ Enterprise Solution. Join our innovative team in Metro Manila or Cebu and shape the future of business tech.',
	) );
}

/**
 * The eight openings on dynamiqes.com (slug = the old /careers/<slug>/ URL). Seeded as dq_career
 * posts; the summary is the card's "Job Description" (post excerpt). Also the read-only fallback
 * for the listing when no opening has been published yet.
 */
function dq_career_default_jobs() {
	$fc  = 'Responsible for understanding business requirements and transforming them into Business Process in the SAP System, providing procedure manual and end-user training to SAP business users, analyzing and resolving SAP System issues, and enhancing parts of the system according to client requests or specifications, in compliance with the overall direction, policies, and process of DynamIQ Enterprise Solution Inc.';
	$se  = 'Primary responsible for the generation of sales by bringing new business to the Company and retaining old clients, apply product knowledge to meet specific business needs, as well as proper and timely compliance with sales procedural, documentation, and reportorial requirements as prescribed by the Company.';
	return apply_filters( 'dq_career_default_jobs', array(
		array( 'slug' => 'project-manager-sap-business-one', 'title' => 'Project Manager – SAP Business One', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => 'Primary responsible for the monitoring and controlling of project activities, conducting business process analysis, and ensuring resolution of all customer complaints/escalations relating to functional, development, and technical services rendered by the business units.' ),
		array( 'slug' => 'sap-b1-helpdesk-support', 'title' => 'Helpdesk Support – SAP Business One', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => 'Responsible for providing technical support and assistance of user-reported issues and complaints via phone call, email, or ticketing system in compliance with the overall direction, policies, and process of DynamIQ Enterprise Solution Inc.' ),
		array( 'slug' => 'sap-s-4-hana-consultant', 'title' => 'SAP S/4 HANA Consultant', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => $fc ),
		array( 'slug' => 'functional-consultant-sap-business-one', 'title' => 'Functional Consultant – SAP Business One', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => $fc ),
		array( 'slug' => 'technical-consultant-sap-business-one', 'title' => 'Technical Consultant – SAP Business One', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => 'Primary responsible for providing solutions and troubleshoots issues regarding SAP Business One, SQL/Hana, and Crystal Reports as well as asses and recommend customers\' requirements with regards to technical infrastructure, in compliance with the overall direction, policies, and process of DynamIQ Enterprise Solution Inc.' ),
		array( 'slug' => 'sales-executive-manila', 'title' => 'Sales Executive – SAP Business One', 'location' => 'Metro Manila', 'type' => 'Full Time', 'summary' => $se ),
		array( 'slug' => 'functional-consultant-sap-business-one-cebu', 'title' => 'Functional Consultant – SAP Business One', 'location' => 'Cebu', 'type' => 'Full Time', 'summary' => $fc ),
		array( 'slug' => 'sales-executive', 'title' => 'Sales Executive – SAP Business One', 'location' => 'Cebu', 'type' => 'Full Time', 'summary' => $se ),
	) );
}

/**
 * Published openings for the listing / "See Other Jobs": [ id, slug, title, url, location, type,
 * summary, has_content ]. Falls back to dq_career_default_jobs() (url = '' → card shows Apply
 * instead of Read More) when nothing has been published yet.
 */
function dq_career_jobs( $exclude_id = 0 ) {
	$posts = get_posts( array(
		'post_type'      => dq_career_post_type(),
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
		'post__not_in'   => $exclude_id ? array( (int) $exclude_id ) : array(),
	) );
	$out = array();
	foreach ( $posts as $p ) {
		$out[] = array(
			'id'          => $p->ID,
			'slug'        => $p->post_name,
			'title'       => get_the_title( $p ),
			'url'         => get_permalink( $p ),
			'location'    => dq_career_meta( $p->ID, 'location' ),
			'type'        => dq_career_meta( $p->ID, 'type' ),
			'summary'     => trim( wp_strip_all_tags( $p->post_excerpt ? $p->post_excerpt : $p->post_content ) ),
			'has_content' => '' !== trim( $p->post_content ),
		);
	}
	if ( ! $out && ! $exclude_id ) {
		foreach ( dq_career_default_jobs() as $j ) {
			$out[] = $j + array( 'id' => 0, 'url' => '', 'has_content' => false );
		}
	}
	return apply_filters( 'dq_career_jobs', $out );
}

/** Locations present in a job list, in first-seen order. */
function dq_career_locations( $jobs ) {
	$locs = array();
	foreach ( $jobs as $j ) {
		if ( ! empty( $j['location'] ) && ! in_array( $j['location'], $locs, true ) ) {
			$locs[] = $j['location'];
		}
	}
	return $locs;
}

/** mailto: for an application to HR, subject pre-filled with the position. */
function dq_career_apply_url( $title = '' ) {
	$hr = dq_career_hr();
	return 'mailto:' . $hr['email'] . ( $title ? '?subject=' . rawurlencode( 'Application: ' . $title ) : '' );
}

/* ------------------------------------------------------------------ */
/* Import from the live site                                           */
/* ------------------------------------------------------------------ */

/**
 * Fetch one opening from dynamiqes.com/careers/<slug>/ and reduce it to
 * [ title, location, type, summary, content ] — content is the H2 + list blocks that follow the
 * summary ("Job Responsibilities", "Qualifications"), stopping at "See Other Jobs".
 */
function dq_career_fetch_job( $slug, $html = '' ) {
	if ( '' === $html ) {
		$base = function_exists( 'dq_landing_source_base' ) ? dq_landing_source_base() : 'https://dynamiqes.com';
		$res  = wp_remote_get( $base . '/careers/' . $slug . '/', array( 'timeout' => 45, 'user-agent' => 'Mozilla/5.0 (DynamIQ theme importer)' ) );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( 'dq_http', sprintf( 'HTTP %d for careers/%s', wp_remote_retrieve_response_code( $res ), $slug ) );
		}
		$html = wp_remote_retrieve_body( $res );
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return new WP_Error( 'dq_dom', 'PHP DOM extension is not available.' );
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $html );
	libxml_clear_errors();
	$xp = new DOMXPath( $doc );
	$h1 = $xp->query( '//h1' )->item( 0 );
	if ( ! $h1 ) {
		return new WP_Error( 'dq_parse', 'No H1 on careers/' . $slug );
	}
	$clean = function ( $t ) {
		return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $t, ENT_QUOTES, 'UTF-8' ) ) );
	};
	$out = array( 'title' => $clean( $h1->textContent ), 'location' => '', 'type' => '', 'summary' => '', 'content' => '' );
	$cur = '';
	foreach ( $xp->query( '//h1/following::*[self::h2 or self::h3 or self::p or self::ul]' ) as $n ) {
		$name = strtolower( $n->nodeName );
		$t    = $clean( $n->textContent );
		if ( '' === $t ) {
			continue;
		}
		if ( preg_match( '/^(See Other Jobs|DYNAMIQ ENTERPRISE SOLUTION INC)/i', $t ) ) {
			break;
		}
		if ( 'h2' === $name || 'h3' === $name ) {
			$cur          = $t;
			$out['content'] .= '<h2>' . esc_html( $t ) . '</h2>';
			continue;
		}
		if ( 'ul' === $name && $cur ) {
			$items = '';
			foreach ( $xp->query( './li', $n ) as $li ) {
				$lt = $clean( $li->textContent );
				if ( '' !== $lt ) {
					$items .= '<li>' . esc_html( $lt ) . '</li>';
				}
			}
			if ( $items ) {
				$out['content'] .= '<ul>' . $items . '</ul>';
			}
			continue;
		}
		if ( 'p' === $name ) {
			if ( ! $cur && ! $out['location'] && false !== strpos( $t, '|' ) && mb_strlen( $t ) < 60 ) {
				$parts           = array_map( 'trim', explode( '|', $t, 2 ) );
				$out['location'] = $parts[0];
				$out['type']     = isset( $parts[1] ) ? $parts[1] : '';
			} elseif ( ! $cur && ! $out['summary'] && mb_strlen( $t ) > 60 ) {
				$out['summary'] = $t;
			} elseif ( $cur && mb_strlen( $t ) > 20 && ! preg_match( '/^Apply Now/i', $t ) ) {
				$out['content'] .= '<p>' . esc_html( $t ) . '</p>';
			}
		}
	}
	return $out;
}

/**
 * Create the Careers listing page (/career/) if missing, with the old site's title tag / meta
 * description. Returns the page ID. Safe to call repeatedly.
 */
function dq_ensure_career_page() {
	$copy = dq_career_page_copy();
	$page = get_page_by_path( 'career' );
	if ( $page ) {
		$id = (int) $page->ID;
		if ( 'publish' !== $page->post_status ) {
			wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
		}
	} else {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Careers', 'post_name' => 'career' ) );
	}
	if ( ! $id || is_wp_error( $id ) ) {
		return 0;
	}
	if ( ! get_post_meta( $id, '_dq_seo_title', true ) ) {
		update_post_meta( $id, '_dq_seo_title', $copy['seo_title'] );
	}
	if ( ! get_post_meta( $id, '_dq_seo_description', true ) ) {
		update_post_meta( $id, '_dq_seo_description', $copy['seo_desc'] );
	}
	return (int) $id;
}

/**
 * Create the eight openings as dq_career posts (skipping slugs that already exist). Each
 * description is fetched from the live page when $fetch_live and the site is reachable;
 * otherwise the post gets the summary only and can be completed in WP Admin. Returns a report.
 */
function dq_seed_careers( $fetch_live = true ) {
	$report = array();
	$made   = 0;
	foreach ( dq_career_default_jobs() as $i => $job ) {
		if ( get_page_by_path( $job['slug'], OBJECT, 'dq_career' ) ) {
			continue;
		}
		$content = '';
		$live    = $fetch_live ? dq_career_fetch_job( $job['slug'] ) : null;
		if ( $live && ! is_wp_error( $live ) ) {
			$content = $live['content'];
			if ( $live['summary'] ) {
				$job['summary'] = $live['summary'];
			}
			if ( $live['location'] ) {
				$job['location'] = $live['location'];
			}
			if ( $live['type'] ) {
				$job['type'] = $live['type'];
			}
		} elseif ( is_wp_error( $live ) ) {
			$report[] = $job['slug'] . ': ' . $live->get_error_message() . ' (summary only)';
		}
		$id = wp_insert_post( array(
			'post_type'    => 'dq_career',
			'post_status'  => 'publish',
			'post_title'   => $job['title'],
			'post_name'    => $job['slug'],
			'post_excerpt' => $job['summary'],
			'post_content' => $content,
			'menu_order'   => $i,
		) );
		if ( $id && ! is_wp_error( $id ) ) {
			update_post_meta( $id, '_dq_location', $job['location'] );
			update_post_meta( $id, '_dq_job_type', $job['type'] );
			update_post_meta( $id, '_dq_seed_id', 'career-' . $job['slug'] );
			if ( $content ) {
				update_post_meta( $id, '_dq_career_source', 'https://dynamiqes.com/careers/' . $job['slug'] . '/' );
			}
			$made++;
		}
	}
	$report[] = sprintf( '%d job openings created', $made );
	return $report;
}

/** True when a saved menu URL is the old home-page "#contact" anchor. */
function dq_career_is_anchor( $url ) {
	$url = (string) $url;
	return '' !== $url && ( '#contact' === substr( $url, -8 ) || '/#contact/' === substr( $url, -10 ) );
}

/**
 * Re-point a saved Primary Menu's top-level "Careers" from the home #contact anchor (earlier
 * seeders) to the /career/ page. Returns 1 when an item was changed.
 */
function dq_repair_careers_menu_link() {
	$page = dq_career_page();
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
		if ( ! $mi->menu_item_parent && 'custom' === $mi->type && 'Careers' === $mi->title && dq_career_is_anchor( $mi->url ) ) {
			wp_update_nav_menu_item( $menu->term_id, $mi->ID, array(
				'menu-item-title'     => 'Careers',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => 0,
				'menu-item-position'  => (int) $mi->menu_order,
				'menu-item-type'      => 'post_type',
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
			) );
			return 1;
		}
	}
	return 0;
}

/**
 * Sites seeded before Careers existed (and freshly seeded ones, right after dq_seed_content):
 * create the page, the openings and fix the menu once, on the next admin load. Rewrite rules
 * are flushed so /careers/<slug>/ resolves.
 */
add_action( 'init', function () {
	if ( ! is_admin() || get_option( 'dq_careers_v1' ) || ! current_user_can( 'manage_options' ) || ! get_option( 'dq_seeded' ) ) {
		return;
	}
	update_option( 'dq_careers_v1', time() ); // first, so a slow live fetch cannot run twice
	dq_ensure_career_page();
	if ( 'dq_career' === dq_career_post_type() ) {
		dq_seed_careers( true ); // a copied live database already has its openings
	}
	dq_repair_careers_menu_link();
	flush_rewrite_rules();
}, 30 );
