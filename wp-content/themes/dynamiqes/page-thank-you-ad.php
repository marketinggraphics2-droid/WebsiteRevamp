<?php
/**
 * Thank you (/thank-you-ad/) - the confirmation page for the ad campaigns.
 * Layout lives in template-parts/thank-you.php (review items G3/G4).
 *
 * @package dynamiqes
 */

get_header();
the_post();
get_template_part( 'template-parts/thank-you', null, array(
	'next' => array(
		array( __( 'Our Products', 'dynamiqes' ), dq_products_url(), __( 'SAP Business One and the IQ Suite', 'dynamiqes' ) ),
		array( __( 'Client Testimonials', 'dynamiqes' ), dq_testimonials_hub_url(), __( 'How Philippine businesses use our systems', 'dynamiqes' ) ),
		array( __( 'Blogs', 'dynamiqes' ), dq_blog_url(), __( 'ERP, compliance and growth insights', 'dynamiqes' ) ),
	),
) );
get_footer();
