<?php
/**
 * DynamIQ Enterprise Solution theme bootstrap.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

define( 'DQ_VERSION', '1.1.3' );
define( 'DQ_DIR', get_template_directory() );
define( 'DQ_URI', get_template_directory_uri() );

require DQ_DIR . '/inc/product-data.php';
require DQ_DIR . '/inc/product-content.php';
require DQ_DIR . '/inc/setup.php';
require DQ_DIR . '/inc/live-post-types.php'; // the live site's own post types (testimonials, news, careers …) when the DB carries them
require DQ_DIR . '/inc/post-types.php';
require DQ_DIR . '/inc/template-tags.php';
require DQ_DIR . '/inc/news-content.php';
require DQ_DIR . '/inc/customizer.php';
require DQ_DIR . '/inc/seo.php';
require DQ_DIR . '/inc/contact-form.php';
require DQ_DIR . '/inc/seeder.php';
require DQ_DIR . '/inc/landing-import.php';
require DQ_DIR . '/inc/landing-sections.php';
require DQ_DIR . '/inc/careers.php';
require DQ_DIR . '/inc/live-urls.php';
require DQ_DIR . '/inc/page-content.php';
require DQ_DIR . '/inc/testimonial-media.php';
