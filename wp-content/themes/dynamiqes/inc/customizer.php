<?php
/**
 * Customizer: brand, hero, contact, socials, SEO defaults.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

add_action( 'customize_register', function ( WP_Customize_Manager $wp ) {
	$panel = 'dq_panel';
	$wp->add_panel( $panel, array( 'title' => __( 'DynamIQ Theme', 'dynamiqes' ), 'priority' => 20 ) );

	$text = function ( $id, $label, $default, $section, $type = 'text', $desc = '' ) use ( $wp ) {
		$wp->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => 'textarea' === $type ? 'sanitize_textarea_field' : ( 'url' === $type ? 'esc_url_raw' : 'sanitize_text_field' ) ) );
		$wp->add_control( $id, array( 'label' => $label, 'section' => $section, 'type' => $type, 'description' => $desc ) );
	};
	$image = function ( $id, $label, $section, $desc = '' ) use ( $wp ) {
		$wp->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'esc_url_raw' ) );
		$wp->add_control( new WP_Customize_Image_Control( $wp, $id, array( 'label' => $label, 'section' => $section, 'description' => $desc ) ) );
	};
	$check = function ( $id, $label, $default, $section ) use ( $wp ) {
		$wp->add_setting( $id, array( 'default' => $default, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
		$wp->add_control( $id, array( 'label' => $label, 'section' => $section, 'type' => 'checkbox' ) );
	};

	/* Brand */
	$wp->add_section( 'dq_brand', array( 'title' => __( 'Brand & header', 'dynamiqes' ), 'panel' => $panel ) );
	$image( 'dq_logo_nav', __( 'Navigation logo', 'dynamiqes' ), 'dq_brand', __( 'Defaults to assets/logos/DynamIQ_Logo_blk.svg', 'dynamiqes' ) );
	$image( 'dq_logo_footer', __( 'Footer logo (with tagline)', 'dynamiqes' ), 'dq_brand' );
	$text( 'dq_header_cta_text', __( 'Top bar text', 'dynamiqes' ), "Something slowing your business down? Let's Figure it out!", 'dq_brand' );
	$text( 'dq_header_cta_label', __( 'Top bar link label', 'dynamiqes' ), 'REQUEST NOW!', 'dq_brand' );
	$check( 'dq_google_fonts', __( 'Load Roboto from Google Fonts', 'dynamiqes' ), true, 'dq_brand' );

	/* Hero */
	$wp->add_section( 'dq_hero', array( 'title' => __( 'Home hero', 'dynamiqes' ), 'panel' => $panel ) );
	$text( 'dq_hero_title', __( 'Headline', 'dynamiqes' ), 'End-to-end SAP Software Solution with your SAP Premier Partner, DynamIQ', 'dq_hero', 'textarea' );
	$text( 'dq_hero_lede', __( 'Lede', 'dynamiqes' ), 'Streamline operations and drive your efficiency. As a SAP Premier partner, trust us to power your business\'s future—where innovation meets expertise in perfect harmony', 'dq_hero', 'textarea' );
	$text( 'dq_hero_video', __( 'Background video URL (mp4)', 'dynamiqes' ), '', 'dq_hero', 'url', __( 'Empty = bundled assets/video/hero-banner.mp4', 'dynamiqes' ) );
	$image( 'dq_hero_poster', __( 'Video poster image', 'dynamiqes' ), 'dq_hero' );
	$text( 'dq_hero_primary_label', __( 'Primary button label', 'dynamiqes' ), 'EXPLORE THE IQ SUITE', 'dq_hero' );
	$text( 'dq_hero_secondary_label', __( 'Secondary button label', 'dynamiqes' ), 'Book a Free Demo', 'dq_hero' );

	/* Home sections */
	$wp->add_section( 'dq_home', array( 'title' => __( 'Home sections', 'dynamiqes' ), 'panel' => $panel ) );
	$text( 'dq_trust_note', __( 'Trust line (above client logos)', 'dynamiqes' ), 'Trusted by manufacturing, healthcare, retail, and trading companies across the Philippines — including MacroAsia Corporation, Presline Steel, Toyo Adtec, and Cecile\'s Pharmacy.', 'dq_home', 'textarea' );
	$image( 'dq_sap_logo', __( 'SAP Business One logo (home section)', 'dynamiqes' ), 'dq_home' );
	$image( 'dq_partner_badge', __( 'SAP Premier Partner badge', 'dynamiqes' ), 'dq_home' );
	$image( 'dq_partner_photo', __( '"Partner in Growth" photo', 'dynamiqes' ), 'dq_home' );
	$check( 'dq_services_images', __( 'Show photos on the service steps', 'dynamiqes' ), true, 'dq_home' );
	$text( 'dq_contact_video', __( 'Contact section background video URL (mp4)', 'dynamiqes' ), '', 'dq_home', 'url', __( 'Empty = bundled assets/video/contact-gradient.mp4 if present. A compressed 3–5 MB clip is recommended.', 'dynamiqes' ) );
	$text( 'dq_footer_credit', __( 'Footer credit line', 'dynamiqes' ), 'SEO by SEO-HACKER. Optimized and Maintained by Sean Si', 'dq_home' );

	/* Content sources (reuse existing post types on migrated sites) */
	$wp->add_section( 'dq_sources', array( 'title' => __( 'Content sources', 'dynamiqes' ), 'panel' => $panel, 'description' => __( 'Which post types feed the home page. "Auto" picks an existing Testimonials / News post type when one has published items, otherwise the theme\'s own.', 'dynamiqes' ) ) );
	$choices = array( 'auto' => __( 'Auto-detect', 'dynamiqes' ) );
	foreach ( get_post_types( array( 'show_ui' => true ), 'objects' ) as $pt ) {
		if ( ! in_array( $pt->name, array( 'attachment', 'dq_inquiry', 'dq_product', 'page' ), true ) ) {
			$choices[ $pt->name ] = $pt->label . ' (' . $pt->name . ')';
		}
	}
	foreach ( array( 'testimonial' => __( 'Testimonials post type', 'dynamiqes' ), 'news' => __( 'News & Events post type', 'dynamiqes' ) ) as $k => $label ) {
		$wp->add_setting( 'dq_pt_' . $k, array( 'default' => 'auto', 'sanitize_callback' => 'sanitize_key' ) );
		$wp->add_control( 'dq_pt_' . $k, array( 'label' => $label, 'section' => 'dq_sources', 'type' => 'select', 'choices' => $choices ) );
	}
	$text( 'dq_news_categories', __( 'News categories (when news are regular posts)', 'dynamiqes' ), 'Events, Community, News', 'dq_sources', 'text', __( 'Comma-separated category names. Posts in these categories appear under News & Events; everything else is the Blog.', 'dynamiqes' ) );

	/* Contact */
	$wp->add_section( 'dq_contact', array( 'title' => __( 'Contact details', 'dynamiqes' ), 'panel' => $panel ) );
	$text( 'dq_contact_email', __( 'Inquiry notifications go to', 'dynamiqes' ), get_option( 'admin_email' ), 'dq_contact', 'text', __( 'Comma-separate several addresses.', 'dynamiqes' ) );
	$text( 'dq_contact_email_public', __( 'Public email', 'dynamiqes' ), 'sales@dynamiqes.com', 'dq_contact' );
	$text( 'dq_phone_1', __( 'Phone 1', 'dynamiqes' ), '+63 917-630-4848', 'dq_contact' );
	$text( 'dq_phone_2', __( 'Phone 2', 'dynamiqes' ), '+63(2) 8365 0228', 'dq_contact' );
	$text( 'dq_address', __( 'Address (full)', 'dynamiqes' ), 'No. 12 Tagdalit Street, Manresa, Quezon City 1115', 'dq_contact' );
	$text( 'dq_address_short', __( 'Address (footer)', 'dynamiqes' ), '12 Tagdalit Street, Brgy. Manresa, Quezon City', 'dq_contact' );
	$text( 'dq_hours', __( 'Office hours', 'dynamiqes' ), 'Monday-Friday 8:00 AM – 5:00 PM', 'dq_contact' );

	/* Socials */
	$wp->add_section( 'dq_social', array( 'title' => __( 'Social profiles', 'dynamiqes' ), 'panel' => $panel ) );
	foreach ( array( 'facebook' => 'https://www.facebook.com/dynamiqenterprise', 'instagram' => 'https://www.instagram.com/dynamiqenterprise/', 'twitter' => 'https://twitter.com/dynamiqesInc', 'tiktok' => 'https://www.tiktok.com/@dynamiqesi', 'youtube' => 'https://www.youtube.com/@dynamiqenterprise', 'linkedin' => 'https://www.linkedin.com/company/dynamiqesofficial/' ) as $k => $d ) {
		$text( 'dq_social_' . $k, ucfirst( $k ), $d, 'dq_social', 'url' );
	}

	/* SEO */
	$wp->add_section( 'dq_seo', array( 'title' => __( 'SEO defaults', 'dynamiqes' ), 'panel' => $panel ) );
	$text( 'dq_seo_home_title', __( 'Home page title tag', 'dynamiqes' ), 'DynamIQ — SAP Premier Partner Philippines', 'dq_seo' );
	$text( 'dq_seo_home_description', __( 'Home meta description', 'dynamiqes' ), 'DynamIQ is a Premier SAP implementation partner delivering SAP Business One and the IQ Suite for Philippine small and mid-market businesses.', 'dq_seo', 'textarea' );
	$image( 'dq_og_default_image', __( 'Default social share image (1200×630)', 'dynamiqes' ), 'dq_seo' );
	$text( 'dq_twitter_handle', __( 'Twitter/X handle', 'dynamiqes' ), '@dynamiqesInc', 'dq_seo' );
	$text( 'dq_org_founding', __( 'Organization founding year (schema)', 'dynamiqes' ), '', 'dq_seo' );
	$text( 'dq_verify_google', __( 'Google Search Console verification code', 'dynamiqes' ), '', 'dq_seo', 'text', __( 'Only the content value of the meta tag.', 'dynamiqes' ) );
	$text( 'dq_verify_bing', __( 'Bing verification code', 'dynamiqes' ), '', 'dq_seo' );
	$text( 'dq_head_scripts', __( 'Extra head code (GA4 / GTM etc.)', 'dynamiqes' ), '', 'dq_seo', 'textarea', __( 'Raw HTML, output inside <head>. Administrators only.', 'dynamiqes' ) );
} );

/* Raw head code needs an unfiltered sanitize for admins. */
add_action( 'customize_register', function ( $wp ) {
	$s = $wp->get_setting( 'dq_head_scripts' );
	if ( $s && current_user_can( 'unfiltered_html' ) ) {
		$s->sanitize_callback = null;
	}
}, 20 );
