<?php
/**
 * IQ Suite product catalogue: default content, field map and accessors.
 *
 * Products live in the `dq_product` post type. Every field below can be edited
 * in WP Admin (Products → edit → "Product details"). Image fields accept either a
 * path relative to the theme (assets/…) or a full URL from the Media Library.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve an image reference to an absolute URL.
 * Relative theme paths (assets/…) become theme URLs; full URLs pass through.
 */
function dq_asset( $path ) {
	$path = trim( (string) $path );
	if ( '' === $path ) {
		return '';
	}
	if ( preg_match( '#^(https?:)?//#i', $path ) || 0 === strpos( $path, 'data:' ) ) {
		return $path;
	}
	return DQ_URI . '/' . ltrim( $path, '/' );
}

/** Field definitions shared by the meta box, the seeder and the accessors. */
function dq_product_field_map() {
	return array(
		'menu_label'     => array( 'text', __( 'Menu label', 'dynamiqes' ), __( 'Short name used in the navigation dropdown (e.g. IQ Tax Module).', 'dynamiqes' ) ),
		'title'          => array( 'text', __( 'Hero headline', 'dynamiqes' ), __( 'The H1 on the product page.', 'dynamiqes' ) ),
		'description'    => array( 'textarea', __( 'Hero description', 'dynamiqes' ), __( 'One paragraph under the headline. Also used as the meta description fallback.', 'dynamiqes' ) ),
		'logo'           => array( 'image', __( 'Product logo (dark)', 'dynamiqes' ), '' ),
		'logo_light'     => array( 'image', __( 'Product logo (white)', 'dynamiqes' ), __( 'Used over dark backgrounds (hero strip, card hover).', 'dynamiqes' ) ),
		'hero'           => array( 'image', __( 'Hero screenshot', 'dynamiqes' ), __( 'Or set a Featured Image; the featured image wins.', 'dynamiqes' ) ),
		'background'     => array( 'image', __( 'Hero background photo', 'dynamiqes' ), '' ),
		'overview_image' => array( 'image', __( 'Overview image', 'dynamiqes' ), '' ),
		'feature_image'  => array( 'image', __( 'Features image', 'dynamiqes' ), __( 'Leave empty to hide the features showcase image.', 'dynamiqes' ) ),
		'card_art'       => array( 'image', __( 'Home card artwork', 'dynamiqes' ), __( 'Monitor render shown on the IQ Suite card on the home page.', 'dynamiqes' ) ),
		'card_photo'     => array( 'image', __( 'Home card hover photo', 'dynamiqes' ), '' ),
		'card_tagline'   => array( 'text', __( 'Home card tagline', 'dynamiqes' ), __( 'One short benefit line shown over the photo on the IQ Suite card at rest (e.g. Run my business better).', 'dynamiqes' ) ),
		'card_title'     => array( 'text', __( 'Home card title', 'dynamiqes' ), __( 'Two or three words shown under the logo when the IQ Suite card is hovered (e.g. Self-Service Portal).', 'dynamiqes' ) ),
		'card_desc'      => array( 'textarea', __( 'Home card description', 'dynamiqes' ), __( 'Two short sentences, revealed when the card is hovered.', 'dynamiqes' ) ),
		'strip_desc'     => array( 'text', __( 'Hero strip tooltip', 'dynamiqes' ), __( 'One line shown when hovering the logo in the home hero.', 'dynamiqes' ) ),
		'listing'        => array( 'lines', __( 'Products page paragraphs', 'dynamiqes' ), __( 'One paragraph per line.', 'dynamiqes' ) ),
		'overview'       => array( 'lines', __( 'Overview paragraphs', 'dynamiqes' ), __( 'One paragraph per line.', 'dynamiqes' ) ),
		'closing'        => array( 'text', __( 'Overview closing line (bold)', 'dynamiqes' ), '' ),
		'features_intro' => array( 'textarea', __( 'Features intro', 'dynamiqes' ), '' ),
		'features'       => array( 'features', __( 'Feature groups', 'dynamiqes' ), __( 'One group per line: Title | item; item; item', 'dynamiqes' ) ),
		'faqs'           => array( 'faqs', __( 'FAQs', 'dynamiqes' ), __( 'One per line: Question | Answer', 'dynamiqes' ) ),
	);
}

/* ---- text <-> array helpers (used by the meta boxes) ---- */
function dq_parse_lines( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = $line;
		}
	}
	return $out;
}
function dq_parse_features( $text ) {
	$out = array();
	foreach ( dq_parse_lines( $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$items = isset( $parts[1] ) ? array_values( array_filter( array_map( 'trim', explode( ';', $parts[1] ) ) ) ) : array();
		$out[] = array( 'title' => $parts[0], 'items' => $items );
	}
	return $out;
}
function dq_parse_faqs( $text ) {
	$out = array();
	foreach ( dq_parse_lines( $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( isset( $parts[1] ) ) {
			$out[] = array( $parts[0], $parts[1] );
		}
	}
	return $out;
}
function dq_lines_to_text( $arr ) {
	return implode( "\n", (array) $arr );
}
function dq_features_to_text( $arr ) {
	$lines = array();
	foreach ( (array) $arr as $g ) {
		$lines[] = $g['title'] . ' | ' . implode( '; ', $g['items'] );
	}
	return implode( "\n", $lines );
}
function dq_faqs_to_text( $arr ) {
	$lines = array();
	foreach ( (array) $arr as $f ) {
		$lines[] = $f[0] . ' | ' . $f[1];
	}
	return implode( "\n", $lines );
}

/**
 * Default catalogue, ported 1:1 from the live site (home cards, products page, product template).
 * Keys are stable identifiers stored in `_dq_product_key`.
 */
function dq_product_defaults() {
	static $data = null;
	if ( null !== $data ) {
		return $data;
	}
	$data = array(
		'sap' => array(
			'slug'           => 'sap-business-one',
			'name'           => 'SAP Business One',
			'menu_label'     => 'SAP Business One',
			'title'          => 'Run Your Entire Business with SAP Business One',
			'description'    => 'SAP Business One is an application that lets you manage your entire business operations easily and effectively. It comes with built-in modules that represent the business areas of your operation.',
			'logo'           => 'assets/products/official/sap-business-one-logo.png',
			'logo_light'     => 'assets/products/official/sap-business-one-logo-wht.svg',
			'background'     => 'assets/products/site-media/bg-products.jpg',
			'hero'           => 'assets/products/site-media/sap-business-one-banner.png',
			'overview_image' => 'assets/products/site-media/prod--overview.jpg',
			'feature_image'  => '',
			'card_art'       => 'assets/products/photos/all.jpg',
			'card_photo'     => 'assets/products/photos/all.jpg',
			'card_tagline'   => 'Run my business better',
			'card_title'     => 'Core ERP Platform',
			'card_desc'      => 'SAP Business One lets you manage your entire business — finance, sales, inventory, and operations — in one affordable platform. It\'s the flagship ERP for small and mid-market companies that the whole IQ Suite is built on.',
			'strip_desc'     => 'The ERP platform the whole IQ Suite is built on.',
			'listing'        => array(
				'SAP Business One is an application that lets you manage your entire business operations easily and effectively. It comes with built-in modules that represent the business areas of your operation.',
				'The main capabilities of the system range from financial management, sales and customer management, purchasing and inventory control, up to production planning, and project management. It also covers analytics and reporting so you can make timely decisions based on real-time data.',
				'SAP Business One is a full-scale and highly functional ERP system designed specifically for small businesses up to small and mid-size enterprises.',
			),
			'overview'       => array(
				'It is a single, affordable platform where you can manage your entire business. SAP Business One System is an affordable, easy-to-use enterprise resource planning system specifically designed for small to mid-market businesses by the world\'s largest enterprise resource planning maker.',
				'It integrates all major aspects of your business for end-to-end visibility, added efficiency, and improved operational control. This software enables real-time data access for faster, complete, more nimble decision-making.',
			),
			'closing'        => '',
			'features_intro' => 'SAP Business One is a full-scale and highly functional ERP system designed specifically for small businesses up to small and mid-size enterprises.',
			'features'       => array(
				array( 'title' => 'Complete & Customizable', 'items' => array( 'Aside from giving clear visibility to your entire business, it is also designed to be flexible and customizable.' ) ),
				array( 'title' => 'Easy Integration', 'items' => array( 'Businesses can seamlessly connect the business planning software with third-party providers.' ) ),
				array( 'title' => 'Cloud Ready and Offsite Access Capable', 'items' => array( 'Whether it\'s deployed on-site or on the cloud, you can access this even on your mobile devices!' ) ),
			),
			'faqs'           => array(
				array( 'Who is SAP Business One designed for?', 'SAP Business One is designed specifically for small businesses up to small and mid-size enterprises.' ),
				array( 'Can SAP Business One connect with other systems?', 'The software is designed to be easily integrated with other systems and applications thanks to its wide range of integration options.' ),
				array( 'Can SAP Business One be used offsite?', 'SAP Business One is a cloud-ready software where companies can manage their business operations anytime and anywhere.' ),
			),
		),
		'portal' => array(
			'slug'           => 'dynamiq-portal',
			'name'           => 'IQ Portal',
			'menu_label'     => 'IQ Portal',
			'title'          => 'Elevate Your SAP Business One Experience with the DynamIQ Portal',
			'description'    => 'The DynamIQ Portal is a secure, web-based extension designed to enhance how teams interact with SAP Business One. Built for authorized users across your organization, it provides real-time access to critical business data—anytime, anywhere—while respecting role-based permissions.',
			'logo'           => 'assets/products/iq-portal.svg',
			'logo_light'     => 'assets/products/iq-portal-wht.svg',
			'background'     => 'assets/products/photos/portal.jpg',
			'hero'           => 'assets/products/main/portal.png',
			'overview_image' => 'assets/products/site-media/dynamic-portal-product-overview.png',
			'feature_image'  => 'assets/products/site-media/dynamic-portal-features.png',
			'card_art'       => 'assets/products/official/portal.png',
			'card_photo'     => 'assets/products/photos/portal.jpg',
			'card_tagline'   => 'Let customers help themselves',
			'card_title'     => 'Self-Service Portal',
			'card_desc'      => 'Web self-service portal built on SAP Business One. Customers, vendors, and staff transact securely from any browser.',
			'strip_desc'     => 'Web self-service portal built on SAP Business One.',
			'listing'        => array(
				'A practical yet powerful piece to extend your ERP solution effectively through the web. A capable and useful ERP platform like SAP Business One can cover opportunities to reform any process which may tip the balance between work and life.',
				'We have exclusively designed a secured, practical and web-based Portal to extend your SAP Business One data through the web giving you the flexibility to access and work on important business data anytime, anywhere.',
			),
			'overview'       => array(
				'Unlock the full potential of your SAP Business One system with our powerful add-on, designed to seamlessly enhance your ERP experience through the web. Our secure, user-friendly portal allows you to access and manage critical business data anytime, anywhere.',
				'Tailored to provide a lighter and faster experience, this solution empowers you to extend selected data and functionalities of SAP Business for better and more personalized user experience and integration to other 3rd party solutions. This ensures that you can streamline processes and respond to business needs effectively, all while enjoying the flexibility to work on your terms.',
			),
			'closing'        => 'Elevate your SAP Business One experience and take control of your data today!',
			'features_intro' => 'The DynamIQ Portal, considerably an ideal front-end interface, allows seamless integration to SAP Business One.',
			'features'       => array(
				array( 'title' => 'Sales', 'items' => array( 'Sales Quotation', 'Sales Order', 'Sales Delivery', 'Sales Return' ) ),
				array( 'title' => 'Purchasing', 'items' => array( 'Purchase Request', 'Purchase Order', 'Good Receipt PO', 'Goods Return' ) ),
				array( 'title' => 'Inventory', 'items' => array( 'Goods Receipt', 'Goods Issue', 'Inventory Transfer Request', 'Inventory Transfer', 'Inventory Counting' ) ),
			),
			'faqs'           => array(
				array( 'Who can use the DynamIQ Portal?', 'The portal is designed for authorized users within your organization, such as business owners, managers, and team members who need real-time access to business data. Permissions can be customized based on user roles.' ),
				array( 'What kind of data can I access through the DynamIQ Portal?', 'You can access all business data in your SAP Business One system, including sales, purchasing, inventory levels, approvals, and more. What data you can see through the DynamIQ portal will be based on your account\'s access rights.' ),
				array( 'How secure is the DynamIQ Portal?', 'Security is a top priority. The portal uses end-to-end encryption and IQLicense to ensure secure and client-only access to protect sensitive business data.' ),
				array( 'Can I use the DynamIQ Portal on mobile devices?', 'Yes. The portal is fully responsive and optimized for desktops, tablets, and smartphones, allowing you to manage your business operations on the go.' ),
			),
		),
		'tax' => array(
			'slug'           => 'dynamiq-tax',
			'name'           => 'IQ Tax',
			'menu_label'     => 'IQ Tax Module',
			'title'          => 'Make Tax Filing and Compliance Easy and Accessible',
			'description'    => 'DYNAMIQ BIR TAX MODULE, our proprietary taxation program, is designed to make tax filing and compliance easy and accessible at a click of a button.',
			'logo'           => 'assets/products/iq-tax.svg',
			'logo_light'     => 'assets/products/iq-tax-wht.svg',
			'background'     => 'assets/products/photos/tax.jpg',
			'hero'           => 'assets/products/main/tax.png',
			'overview_image' => 'assets/products/site-media/tax---module.png',
			'feature_image'  => 'assets/products/official/tax.jpg',
			'card_art'       => 'assets/products/official/tax.png',
			'card_photo'     => 'assets/products/photos/tax.jpg',
			'card_tagline'   => 'Make compliance less painful',
			'card_title'     => 'BIR Tax Compliance',
			'card_desc'      => 'BIR-compliant tax computation and reporting for SAP B1. Keeps Philippine businesses CAS-ready with accurate filings.',
			'strip_desc'     => 'BIR-compliant tax computation and reporting for SAP B1.',
			'listing'        => array(
				'While BIR\'s intent to digitized is clear, many taxpayers oftentimes find themselves in disarray with the process because of its complicated forms and risky manual data entry. This is why businesses need a reliable accounting system in the Philippines. We have created an application suitably compatible with SAP Business One to reduce the tax filing bottlenecks and enhance paperless tax filing experience.',
				'DYNAMIQ BIR TAX MODULE, our proprietary taxation program, is designed to make tax filing and compliance easy and accessible at a click of a button. It is in compliance to the computerized accounting system (CAS) and in pursuance to BIR-issued Revenue Regulation (RR) 9-2009 and Revenue Memorandum 29-2002.',
			),
			'overview'       => array(
				'While BIR\'s intent to digitized is clear, many taxpayers oftentimes find themselves in disarray with the process because of its complicated forms and risky manual data entry. This is why businesses need a reliable accounting system in the Philippines.',
				'We have created an application suitably compatible with SAP Business One to reduce the tax filing bottlenecks and enhance paperless tax filing experience.',
			),
			'closing'        => '',
			'features_intro' => 'It is in compliance to the computerized accounting system (CAS) and in pursuance to BIR-issued Revenue Regulation (RR) 9-2009 and Revenue Memorandum 29-2002.',
			'features'       => array(
				array( 'title' => 'Tax Filing', 'items' => array( 'Tax filing and compliance easy and accessible at a click of a button.' ) ),
				array( 'title' => 'SAP Business One', 'items' => array( 'Suitably compatible with SAP Business One.' ) ),
				array( 'title' => 'Paperless Experience', 'items' => array( 'Reduce the tax filing bottlenecks.', 'Enhance paperless tax filing experience.' ) ),
			),
			'faqs'           => array(
				array( 'What is the DynamIQ BIR Tax Module?', 'DYNAMIQ BIR TAX MODULE is our proprietary taxation program.' ),
				array( 'Does IQ Tax work with SAP Business One?', 'We have created an application suitably compatible with SAP Business One.' ),
				array( 'How does IQ Tax support compliance?', 'It is in compliance to the computerized accounting system (CAS) and in pursuance to BIR-issued Revenue Regulation (RR) 9-2009 and Revenue Memorandum 29-2002.' ),
			),
		),
		'barcode' => array(
			'slug'           => 'dynamiq-barcode',
			'name'           => 'IQ Barcode',
			'menu_label'     => 'IQ Barcoding',
			'title'          => 'Bring Barcoding and Scanning into SAP Business One',
			'description'    => 'IQ Barcode is a barcode inventory system and a third-party integration for SAP Business One (SAP B1). It utilizes the logistics using barcoding and scanning items.',
			'logo'           => 'assets/products/iq-barcode.svg',
			'logo_light'     => 'assets/products/iq-barcode-wht.svg',
			'background'     => 'assets/products/photos/barcode.jpg',
			'hero'           => 'assets/products/main/barcode.png',
			'overview_image' => 'assets/products/site-media/barcode-product-overview.png',
			'feature_image'  => 'assets/products/site-media/dynamiq---barcoding.png',
			'card_art'       => 'assets/products/official/barcode.png',
			'card_photo'     => 'assets/products/photos/barcode.jpg',
			'card_tagline'   => 'Know what\'s happening with your inventory',
			'card_title'     => 'Barcode & Scanning',
			'card_desc'      => 'Barcode scanning for faster inventory and warehouse control. Speeds up receiving, picking, and counts with live accuracy.',
			'strip_desc'     => 'Barcode scanning for faster inventory and warehouse control.',
			'listing'        => array(
				'IQ Barcode is a barcode inventory system and a third-party integration for SAP Business One (SAP B1). It utilizes the logistics using barcoding and scanning items.',
			),
			'overview'       => array(
				'IQ Barcode is a barcode inventory system and a third-party integration for SAP Business One (SAP B1).',
				'It utilizes the logistics using barcoding and scanning items.',
			),
			'closing'        => '',
			'features_intro' => 'Barcode scanning for faster inventory and warehouse control.',
			'features'       => array(
				array( 'title' => 'Inventory', 'items' => array( 'Barcode inventory system.' ) ),
				array( 'title' => 'Integration', 'items' => array( 'Third-party integration for SAP Business One (SAP B1).' ) ),
				array( 'title' => 'Logistics', 'items' => array( 'Barcoding.', 'Scanning items.' ) ),
			),
			'faqs'           => array(
				array( 'What is IQ Barcode?', 'IQ Barcode is a barcode inventory system.' ),
				array( 'Does IQ Barcode integrate with SAP Business One?', 'IQ Barcode is a third-party integration for SAP Business One (SAP B1).' ),
				array( 'How does IQ Barcode support logistics?', 'It utilizes the logistics using barcoding and scanning items.' ),
			),
		),
		'link' => array(
			'slug'           => 'dynamiq-link',
			'name'           => 'IQ Link',
			'menu_label'     => 'IQ Link',
			'title'          => 'Create a Unified Single System with IQ Link',
			'description'    => 'IQ Link brings together various types of software sub-systems so that they create a unified single system.',
			'logo'           => 'assets/products/iq-link.svg',
			'logo_light'     => 'assets/products/iq-link-wht.svg',
			'background'     => 'assets/products/photos/link.jpg',
			'hero'           => 'assets/products/main/link.png',
			'overview_image' => 'assets/products/site-media/IQ-Link-desktop-v2.png',
			'feature_image'  => 'assets/products/site-media/IQ-Link-mobile-v2.png',
			'card_art'       => 'assets/products/official/link.png',
			'card_photo'     => 'assets/products/photos/link.jpg',
			'card_tagline'   => 'Make your systems talk to each other',
			'card_title'     => 'System Integration',
			'card_desc'      => 'Integration layer connecting SAP B1 to your other systems. Syncs e-commerce, banking, and third-party apps seamlessly.',
			'strip_desc'     => 'Integration layer connecting SAP B1 to your other systems.',
			'listing'        => array(
				'IQ Link brings together various types of software sub-systems so that they create a unified single system. Software integration maybe required on the following situations i.e., integrating SAP B1 to the current POS system used or any other related software. IQ Link has a status report which lets you know if there are any data inconsistencies as well.',
			),
			'overview'       => array(
				'Software integration maybe required on the following situations i.e., integrating SAP B1 to the current POS system used or any other related software.',
				'IQ Link has a status report which lets you know if there are any data inconsistencies as well.',
			),
			'closing'        => '',
			'features_intro' => 'Integration layer connecting SAP B1 to your other systems.',
			'features'       => array(
				array( 'title' => 'Unified System', 'items' => array( 'Brings together various types of software sub-systems.' ) ),
				array( 'title' => 'Software Integration', 'items' => array( 'Integrating SAP B1 to the current POS system used.', 'Any other related software.' ) ),
				array( 'title' => 'Status Report', 'items' => array( 'Lets you know if there are any data inconsistencies.' ) ),
			),
			'faqs'           => array(
				array( 'What does IQ Link connect?', 'IQ Link brings together various types of software sub-systems so that they create a unified single system.' ),
				array( 'Can IQ Link integrate SAP Business One with a POS?', 'Software integration may include integrating SAP B1 to the current POS system used or any other related software.' ),
				array( 'How can I check data inconsistencies?', 'IQ Link has a status report which lets you know if there are any data inconsistencies.' ),
			),
		),
		'rem' => array(
			'slug'           => 'dynamiq-rem',
			'name'           => 'IQ REM',
			'menu_label'     => 'IQ REM',
			'title'          => 'Manage Real Estate Operations with IQ REM',
			'description'    => 'IQ REM is a powerful and versatile add-on for SAP Business One (SAP B1), specifically designed to streamline and enhance the management of real estate sales and associated financial transactions.',
			'logo'           => 'assets/products/iq-rem.svg',
			'logo_light'     => 'assets/products/iq-rem-wht.svg',
			'background'     => 'assets/products/photos/rem.jpg',
			'hero'           => 'assets/products/main/rem.png',
			'overview_image' => 'assets/products/site-media/iq-rem-overview.png',
			'feature_image'  => 'assets/products/official/rem.png',
			'card_art'       => 'assets/products/official/rem.png',
			'card_photo'     => 'assets/products/photos/rem.jpg',
			'card_tagline'   => 'Keep every property on track',
			'card_title'     => 'Real Estate Management',
			'card_desc'      => 'Real estate and property management on SAP Business One. Streamlines leasing, billing, and collections in one system.',
			'strip_desc'     => 'Real estate and property management on SAP Business One.',
			'listing'        => array(
				'IQ REM is a powerful and versatile add-on for SAP Business One (SAP B1), specifically designed to streamline and enhance the management of real estate sales and associated financial transactions. Tailored for developers, real estate professionals, and businesses, IQ REM integrates seamlessly with SAP B1 to provide an all-in-one solution for handling the complexities of real estate operations.',
				'DynamIQ Real Estate Management empowers real estate businesses to focus on growth and customer satisfaction by reducing administrative burdens and increasing operational efficiency.',
			),
			'overview'       => array(
				'Tailored for developers, real estate professionals, and businesses, IQ REM integrates seamlessly with SAP B1 to provide an all-in-one solution for handling the complexities of real estate operations.',
				'DynamIQ Real Estate Management empowers real estate businesses to focus on growth and customer satisfaction by reducing administrative burdens and increasing operational efficiency.',
			),
			'closing'        => '',
			'features_intro' => 'Real estate and property management on SAP Business One.',
			'features'       => array(
				array( 'title' => 'Real Estate Sales', 'items' => array( 'Streamline and enhance the management of real estate sales.' ) ),
				array( 'title' => 'Financial Transactions', 'items' => array( 'Associated financial transactions.', 'Integrates seamlessly with SAP B1.' ) ),
				array( 'title' => 'Operational Efficiency', 'items' => array( 'Reducing administrative burdens.', 'Increasing operational efficiency.' ) ),
			),
			'faqs'           => array(
				array( 'Who is IQ REM designed for?', 'IQ REM is tailored for developers, real estate professionals, and businesses.' ),
				array( 'Does IQ REM integrate with SAP Business One?', 'IQ REM integrates seamlessly with SAP B1.' ),
				array( 'What operations does IQ REM support?', 'IQ REM provides an all-in-one solution for handling the complexities of real estate operations.' ),
			),
		),
		'ai' => array(
			'slug'           => 'dynamiq-ai',
			'name'           => 'IQ Ai',
			'menu_label'     => 'IQ Ai',
			'title'          => 'Turn Business Data into Real-Time Insights with IQ Ai',
			'description'    => 'IQ Ai is DynamIQ\'s next-generation Ai engine built to enhance SAP Business One. It uses machine learning, natural language processing, and automation to simplify complex tasks.',
			'logo'           => 'assets/products/iq-ai.svg',
			'logo_light'     => 'assets/products/iq-ai-wht.svg',
			'background'     => 'assets/products/photos/ai.jpg',
			'hero'           => 'assets/products/main/ai.png',
			'overview_image' => 'assets/products/site-media/IQ_AI_laptop.png',
			'feature_image'  => 'assets/products/site-media/IQ-Ai-screen.png',
			'card_art'       => 'assets/products/official/ai.png',
			'card_photo'     => 'assets/products/photos/ai.jpg',
			'card_tagline'   => 'Get smarter answers',
			'card_title'     => 'AI Insights & Automation',
			'card_desc'      => 'AI-powered insights and automation for SAP Business One. Surfaces trends and flags exceptions so you decide faster.',
			'strip_desc'     => 'AI-powered insights and automation for SAP Business One.',
			'listing'        => array(
				'IQ Ai is DynamIQ\'s next-generation Ai engine built to enhance SAP Business One. It uses machine learning, natural language processing, and automation to simplify complex tasks.',
				'Seamlessly integrated, this intelligent solution transforms raw business data into real-time insights and actionable recommendations—helping your team work smarter, faster, and more efficiently.',
			),
			'overview'       => array(
				'Seamlessly integrated, this intelligent solution transforms raw business data into real-time insights and actionable recommendations.',
				'Helping your team work smarter, faster, and more efficiently.',
			),
			'closing'        => '',
			'features_intro' => 'AI-powered insights and automation for SAP Business One.',
			'features'       => array(
				array( 'title' => 'Machine Learning', 'items' => array( 'Simplify complex tasks.' ) ),
				array( 'title' => 'Natural Language Processing', 'items' => array( 'Transform raw business data into real-time insights.' ) ),
				array( 'title' => 'Automation', 'items' => array( 'Actionable recommendations.', 'Work smarter, faster, and more efficiently.' ) ),
			),
			'faqs'           => array(
				array( 'What is IQ Ai?', 'IQ Ai is DynamIQ\'s next-generation Ai engine built to enhance SAP Business One.' ),
				array( 'What technologies does IQ Ai use?', 'It uses machine learning, natural language processing, and automation to simplify complex tasks.' ),
				array( 'What does IQ Ai do with business data?', 'This intelligent solution transforms raw business data into real-time insights and actionable recommendations.' ),
			),
		),
		'desk' => array(
			'slug'           => 'dynamiq-desk',
			'name'           => 'IQ Desk',
			'menu_label'     => 'IQ Desk',
			'title'          => 'Simplify Support and Asset Tracking with IQ Desk',
			'description'    => 'IQ Desk is an all-in-one IT service management and helpdesk solution that simplifies support and asset tracking.',
			'logo'           => 'assets/products/iq-desk.png',
			'logo_light'     => 'assets/products/iq-desk-wht.png',
			'background'     => 'assets/products/photos/desk.jpg',
			'hero'           => 'assets/products/main/desk.png',
			'overview_image' => 'assets/products/site-media/desk-overview.png',
			'feature_image'  => 'assets/products/official/desk.png',
			'card_art'       => 'assets/products/official/desk.png',
			'card_photo'     => 'assets/products/photos/desk.jpg',
			'card_tagline'   => 'Fix what\'s slowing your team down',
			'card_title'     => 'IT Helpdesk & Ticketing',
			'card_desc'      => 'IT helpdesk and ticketing integrated with SAP B1. Tracks issues, SLAs, and resolutions in the platform you already use.',
			'strip_desc'     => 'IT helpdesk and ticketing integrated with SAP B1.',
			'listing'        => array(
				'IQ Desk is an all-in-one IT service management and helpdesk solution that simplifies support and asset tracking. It streamlines ticket handling with AI-assisted responses, automated routing, and multi-channel support for faster resolution and better efficiency. It also includes PAR-based asset management for full visibility and accountability.',
			),
			'overview'       => array(
				'It streamlines ticket handling with AI-assisted responses, automated routing, and multi-channel support for faster resolution and better efficiency.',
				'It also includes PAR-based asset management for full visibility and accountability.',
			),
			'closing'        => '',
			'features_intro' => 'IT helpdesk and ticketing integrated with SAP B1.',
			'features'       => array(
				array( 'title' => 'Ticket Handling', 'items' => array( 'AI-assisted responses.', 'Automated routing.' ) ),
				array( 'title' => 'Multi-Channel Support', 'items' => array( 'Faster resolution.', 'Better efficiency.' ) ),
				array( 'title' => 'Asset Management', 'items' => array( 'PAR-based asset management.', 'Full visibility and accountability.' ) ),
			),
			'faqs'           => array(
				array( 'What is IQ Desk?', 'IQ Desk is an all-in-one IT service management and helpdesk solution.' ),
				array( 'How does IQ Desk streamline ticket handling?', 'It streamlines ticket handling with AI-assisted responses, automated routing, and multi-channel support.' ),
				array( 'Does IQ Desk support asset tracking?', 'It includes PAR-based asset management for full visibility and accountability.' ),
			),
		),
		'ecom' => array(
			'slug'           => 'dynamiq-ecom',
			'name'           => 'IQ Ecom',
			'menu_label'     => 'IQ Ecom',
			'title'          => 'Connect Your Online Store to SAP Business One',
			'description'    => 'IQ Ecom is an enterprise-grade B2B and B2C eCommerce platform fully integrated with SAP Business One.',
			'logo'           => 'assets/products/iq-ecom.svg',
			'logo_light'     => 'assets/products/iq-ecom-wht.svg',
			'background'     => 'assets/products/photos/ecom.jpg',
			'hero'           => 'assets/products/main/ecom.png',
			'overview_image' => 'assets/products/site-media/ecom-overview.png',
			'feature_image'  => 'assets/products/official/ecom.png',
			'card_art'       => 'assets/products/official/ecom.png',
			'card_photo'     => 'assets/products/photos/ecom.jpg',
			'card_tagline'   => 'Connect your online business',
			'card_title'     => 'E-Commerce Storefront',
			'card_desc'      => 'E-commerce storefront synced with SAP Business One. Products, prices, and orders update automatically as you sell.',
			'strip_desc'     => 'E-commerce storefront synced with SAP Business One.',
			'listing'        => array(
				'IQ Ecom is an enterprise-grade B2B and B2C eCommerce platform fully integrated with SAP Business One. It connects your online store to backend systems for seamless order processing, accurate inventory, and real-time data sync. It also supports customer-specific pricing and multi-warehouse management for efficient operations.',
			),
			'overview'       => array(
				'It connects your online store to backend systems for seamless order processing, accurate inventory, and real-time data sync.',
				'It also supports customer-specific pricing and multi-warehouse management for efficient operations.',
			),
			'closing'        => '',
			'features_intro' => 'E-commerce storefront synced with SAP Business One.',
			'features'       => array(
				array( 'title' => 'Order Processing', 'items' => array( 'Seamless order processing.', 'Real-time data sync.' ) ),
				array( 'title' => 'Inventory', 'items' => array( 'Accurate inventory.', 'Multi-warehouse management.' ) ),
				array( 'title' => 'Commerce', 'items' => array( 'B2B and B2C eCommerce platform.', 'Customer-specific pricing.' ) ),
			),
			'faqs'           => array(
				array( 'What is IQ Ecom?', 'IQ Ecom is an enterprise-grade B2B and B2C eCommerce platform fully integrated with SAP Business One.' ),
				array( 'How does IQ Ecom handle data?', 'It connects your online store to backend systems for accurate inventory and real-time data sync.' ),
				array( 'Does IQ Ecom support multiple warehouses?', 'It supports customer-specific pricing and multi-warehouse management for efficient operations.' ),
			),
		),
	);
	$i = 0;
	foreach ( $data as $k => &$p ) {
		$p['key']   = $k;
		$p['order'] = $i++;
	}
	unset( $p );
	return $data;
}

/**
 * Full product record for a dq_product post: defaults for its key, overridden by saved meta.
 * Image fields are resolved to absolute URLs; the Featured Image (if any) overrides the hero.
 *
 * @param int|WP_Post|null $post Product post.
 * @return array|null
 */
function dq_get_product( $post = null ) {
	$post = get_post( $post );
	if ( ! $post || 'dq_product' !== $post->post_type ) {
		return null;
	}
	$defaults = dq_product_defaults();
	$key      = get_post_meta( $post->ID, '_dq_product_key', true );
	$base     = isset( $defaults[ $key ] ) ? $defaults[ $key ] : array(
		'key' => $key ? $key : 'custom', 'slug' => $post->post_name, 'name' => $post->post_title, 'menu_label' => $post->post_title,
		'title' => $post->post_title, 'description' => '', 'logo' => '', 'logo_light' => '', 'background' => '', 'hero' => '',
		'overview_image' => '', 'feature_image' => '', 'card_art' => '', 'card_photo' => '', 'card_tagline' => '', 'card_title' => '', 'card_desc' => '', 'strip_desc' => '',
		'listing' => array(), 'overview' => array(), 'closing' => '', 'features_intro' => '', 'features' => array(), 'faqs' => array(), 'order' => 99,
	);
	$product = $base;
	foreach ( dq_product_field_map() as $field => $def ) {
		$raw = get_post_meta( $post->ID, '_dq_' . $field, true );
		if ( '' === $raw || null === $raw ) {
			continue;
		}
		switch ( $def[0] ) {
			case 'lines':
				$product[ $field ] = dq_parse_lines( $raw );
				break;
			case 'features':
				$product[ $field ] = dq_parse_features( $raw );
				break;
			case 'faqs':
				$product[ $field ] = dq_parse_faqs( $raw );
				break;
			default:
				$product[ $field ] = $raw;
		}
	}
	$product['id']   = $post->ID;
	$product['name'] = $post->post_title ? $post->post_title : $base['name'];
	$product['slug'] = $post->post_name;
	$product['url']  = get_permalink( $post );
	if ( '' === trim( (string) $product['description'] ) && $post->post_excerpt ) {
		$product['description'] = $post->post_excerpt;
	}
	foreach ( array( 'logo', 'logo_light', 'background', 'hero', 'overview_image', 'feature_image', 'card_art', 'card_photo' ) as $img ) {
		$product[ $img ] = dq_asset( $product[ $img ] );
	}
	if ( has_post_thumbnail( $post ) ) {
		$product['hero'] = get_the_post_thumbnail_url( $post, 'full' );
	}
	return $product;
}

/**
 * Ordered catalogue for listings (home grid, products page, menus).
 * Falls back to the defaults when the products have not been imported yet.
 */
function dq_get_products() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}
	$posts = get_posts( array(
		'post_type'        => 'dq_product',
		'posts_per_page'   => -1,
		'orderby'          => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'post_status'      => 'publish',
		'suppress_filters' => false,
	) );
	$out = array();
	foreach ( $posts as $p ) {
		$prod = dq_get_product( $p );
		if ( $prod ) {
			$out[] = $prod;
		}
	}
	if ( empty( $out ) ) {
		foreach ( dq_product_defaults() as $key => $p ) {
			$p['id']  = 0;
			$p['url'] = home_url( '/products/' . $p['slug'] . '/' );
			foreach ( array( 'logo', 'logo_light', 'background', 'hero', 'overview_image', 'feature_image', 'card_art', 'card_photo' ) as $img ) {
				$p[ $img ] = dq_asset( $p[ $img ] );
			}
			$out[] = $p;
		}
	}
	$cache = $out;
	return $cache;
}

/** Find one product record by key (e.g. 'sap'). */
function dq_get_product_by_key( $key ) {
	foreach ( dq_get_products() as $p ) {
		if ( $p['key'] === $key ) {
			return $p;
		}
	}
	return null;
}
