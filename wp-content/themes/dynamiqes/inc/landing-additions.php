<?php
/**
 * Sections the Revamp Dynamiq design (Figma, Sept 2026) adds to the imported SEO landing pages.
 *
 * The landing pages keep the copy imported from dynamiqes.com (inc/landing-import.php); the
 * redesign inserts one data table per page — an ERP comparison, a BIR-compliance checklist, a
 * step-by-step registration guide. Those never existed on the old pages, so they live here,
 * keyed by page slug, and are spliced into the parsed section groups right after the H2 the
 * design places them under. Copy transcribed from the design frames.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return array<string, array<int, array>> slug => list of additions. Each addition:
 *   after   => the H2 (case-insensitive prefix match) the new section follows; '' = end
 *   title   => the section's H2
 *   intro   => paragraphs above the table
 *   kicker  => an optional H3 line above the table
 *   table   => rows; the first row is the header
 *   closing => paragraphs below the table
 */
function dq_landing_additions() {
	return array(
		'sap-software-philippines' => array(
			array(
				'after' => 'Why Choose SAP Business One as your SAP software',
				'title' => 'SAP Business One vs. Other ERP Systems for SMEs',
				'intro' => array( 'Choosing the right ERP depends on your business size, processes, budget, and growth plans. This table compares SAP Business One with other popular ERP systems to help SMEs understand which solution may best fit their operational needs.' ),
				'table' => array(
					array( 'Criteria', 'SAP Business One', 'Microsoft Dynamics 365 Business Central', 'Oracle NetSuite', 'Odoo' ),
					array( 'Best fit', 'SMEs that need a proven ERP for finance, inventory, sales, purchasing, and operations', 'SMEs already using Microsoft 365 and looking for strong finance and productivity integration', 'Growing businesses that need a cloud-native ERP with strong financial and multi-entity capabilities', 'Small to mid-sized businesses that want a flexible, modular, and cost-conscious system' ),
					array( 'Core strengths', 'Solid operational ERP for SMEs, especially those with inventory, trading, distribution, or light manufacturing needs', 'Familiar Microsoft interface, strong Excel/Power BI integration, and good finance functionality', 'Strong cloud ERP suite with financials, reporting, inventory, CRM, and scalability', 'Modular apps, broad functionality, and flexible customization options' ),
					array( 'Deployment', 'Cloud, hosted, or on-premise options depending on partner setup', 'Mainly cloud-based', 'Cloud-based', 'Cloud or self-hosted, depending on edition and implementation' ),
					array( 'Customization', 'Partner-supported add-ons and industry-specific extensions', 'Microsoft AppSource, Power Platform, and partner solutions', 'Cloud-based SuiteApps, workflows, scripts, and partner customization', 'Highly customizable, with many official and community apps' ),
					array( 'SME suitability', 'Good fit for SMEs that want structured ERP processes and local partner support', 'Good fit for Microsoft-centered teams that want tight integration with existing tools', 'Often better suited for scaling companies with more complex reporting or multi-location needs', 'Good fit for businesses that can manage configuration and want flexibility at a lower starting cost' ),
					array( 'Potential consideration', 'User experience and flexibility can depend on implementation and add-ons', 'May require add-ons or configuration for industry-specific needs', 'Can be more costly or complex than some SMEs require', 'Can become complex without proper implementation governance' ),
					array( 'Overall positioning', 'A practical ERP choice for SMEs that need dependable finance and operations management', 'A strong alternative for companies already invested in Microsoft tools', 'A strong option for cloud-first companies preparing for growth', 'A flexible option for businesses prioritizing modularity and cost control' ),
					array( 'Best advantage', 'Best all-around SME ERP when the business needs proven finance, inventory, sales, purchasing, reporting, and operational control in one system.', 'Best for SMEs already standardized on Microsoft 365, Power BI, Outlook, Teams, and Azure.', 'Best for fast-growing, cloud-first businesses needing multi-entity, global, and advanced suite capabilities.', 'Best for businesses that want modular flexibility and potentially lower entry cost.' ),
					array( 'Potential limitation', 'May require the right SAP Business One partner to configure industry-specific workflows and integrations properly.', 'May be less ideal for companies that are not already Microsoft-centric.', 'Best for fast-growing, cloud-first businesses needing multi-entity, global, and advanced suite capabilities.', 'Can be broader and more complex than necessary for smaller SMEs.' ),
					array( 'Overall SME positioning', 'A strong option for SMEs already using Microsoft tools and looking for familiar workflows and integrations.', 'A strong option for SMEs already using Microsoft tools and looking for familiar workflows and integrations.', 'Excellent for cloud-first, high-growth, multi-entity companies.', 'A flexible option for businesses that prefer a modular system with lower entry cost and room for customization.' ),
				),
			),
		),
		'accounting-system-philippines' => array(
			array(
				'after' => 'Simplify Compliance with DynamIQ',
				'title' => 'BIR-Compliant CAS Features Checklist',
				'intro' => array(
					'An accounting system should do more than record transactions. For businesses in the Philippines, it should also support BIR compliance through accurate tax reports, computerized books of accounts, audit trails, secure data handling, and organized filing processes.',
					'With SAP Business One and DynamIQ’s Tax Module, SMEs can manage essential compliance requirements in one integrated system, helping reduce manual work, improve reporting accuracy, and make tax preparation easier.',
				),
				'table' => array(
					array( 'Feature Area', 'Checklist Item' ),
					array( 'BIR Registration Readiness', 'The system can be registered as a CAS, CBA, or CAS component with the taxpayer’s RDO/LT Office before use. BIR RMO No. 9-2021 requires taxpayers intending to use CAS/CBA/components to register the system and comply with the Standard Functional and Technical Requirements.' ),
					array( 'Books of Accounts', 'Can generate computerized books of accounts such as General Journal, General Ledger, Sales Journal, Purchase Journal, Inventory Book, and applicable accounting record. RMO No. 9-2021 defines CBA as systems that generate books such as these.' ),
					array( 'Full Accounting Cycle', 'Supports transaction recording from sales, purchases, receivables, payables, inventory, payroll, invoicing, and financial reporting. CAS is defined as covering the full accounting cycle from transaction inception to receipts/invoices and financial reports.' ),
					array( 'BIR-Required Reports', 'Can generate required reports such as Void Reports, Summary List of Sales and Purchases, and applicable discount reports for Senior Citizens, PWDs, National Athletes, and Coaches.' ),
					array( 'Invoices and Receipts', 'Can generate system-issued principal and/or supplementary invoices or receipts with mandatory BIR information. System-generated invoices/receipts must comply with required invoice/receipt information under applicable BIR rules.' ),
					array( 'Sequential Numbering', 'Supports sequential, unique, and controlled invoice/receipt numbering to prevent duplication, skipping, or unauthorized reuse.' ),
					array( 'Audit Trail / Activity Logs', 'Maintains an audit trail showing chronological transactions, user activities, edits, approvals, and system changes providing a transparent history of activity. BIR’s standard functional requirements include the ability to generate an audit trail or activity log that is secured and can be printed.' ),
					array( 'Void and Cancellation Controls', 'Tracks voided, cancelled, reversed, or adjusted transactions with reason, user, date, and reference document.' ),
					array( 'User Access Controls', 'Supports role-based access, user IDs, passwords, approval levels, and restrictions based on assigned permissions.' ),
					array( 'Data Integrity Controls', 'Prevents unauthorized editing or deletion of posted transactions, sales data, invoice numbers, tax amounts, and accounting records. BIR may revoke authority/registration for tampering with sales data or data/software integrity.' ),
					array( 'Tax Configuration', 'Supports Philippine tax setup such as VAT, non-VAT, withholding tax, zero-rated, exempt, and other applicable tax treatments.' ),
					array( 'Standard Audit File Export', 'Can export computerized books and accounting records in Standard Audit File format, not merely PDF. RMO No. 9-2021 states that computerized books and records should be in SAF and that PDF is not acceptable for this purpose.' ),
					array( 'Year-End Submission Support', 'Supports soft-copy registration/submission of computerized books and accounting records within 30 calendar days from the close of the taxable year.' ),
					array( 'Data Retention', 'Stores and preserves electronic books, accounting records, invoices, and supporting documents for the required retention period under existing BIR rules.' ),
					array( 'Downtime Procedure', 'Supports a documented downtime process, including use of manual pre-printed and pre-numbered invoices/receipts with approved ATP when needed. RMO No. 9-2021 allows manual invoices/receipts during downtime, with reserved sets not exceeding 1,000 at a time.' ),
					array( 'System Change Controls', 'Has procedures for major upgrades, module changes, financial-impact changes, or version changes. BIR rules require prior notification/approval for major repairs, upgrades, integrations, or modifications affecting financial aspects.' ),
					array( 'Minor Enhancement Notifications', 'Allows documentation of minor changes such as UI changes, bug fixes, and performance improvements, which should be notified in writing to the RDO/LT Office.' ),
					array( 'Branch / Multi-Company Support', 'Can identify which head office, branch, company, or entity is using the system, especially where shared systems or servers are involved. RMO No. 9-2021 requires affiliated companies, branches, and similar entities to register systems with their respective RDO/LT offices.' ),
					array( 'Backup and Recovery', 'Supports secure data backup, recovery, and archiving to protect accounting records from loss or corruption.' ),
					array( 'Electronic Invoicing Readiness', 'Where applicable, supports structured electronic invoices and electronic sales reporting. RR No. 11-2025 defines electronic invoices as system-generated invoices in a structured format that can be electronically extracted and transmitted to the BIR for electronic sales reporting.' ),
				),
			),
		),
		'bir-cas-philippines' => array(
			array(
				'after' => 'Registration Process for BIR CAS',
				'title' => 'CAS Application for BIR Registration: Step-by-Step Guide',
				'table' => array(
					array( 'Step', 'What to Do', 'Key Documents / Output' ),
					array( '1', 'Confirm the scope of the system', 'Identify if the application covers CAS, CBA, ESS, middleware, or CAS components.' ),
					array( '2', 'Verify taxpayer and RDO/LT Office details', 'Confirm registered name, TIN, branch code, registered address, and filing office.' ),
					array( '3', 'Review system readiness', 'Check if the system can generate required invoices, receipts, books, reports, audit trails, and exports. Annex B lists the functional and technical requirements.' ),
					array( '4', 'Prepare the sworn statement and Annex C-1', 'Include system description, invoice/receipt/document details, forms, records, and report specifications.' ),
					array( '5', 'Generate sample invoices/receipts', 'Prepare sample principal and supplementary receipts/invoices, if applicable.' ),
					array( '6', 'Generate sample books and reports', 'Include sample Books of Accounts and other system-generated reports.' ),
					array( '7', 'Print the audit trail', 'Provide a printed copy of the activity log generated by the system.' ),
					array( '8', 'Complete and sign Annex B', 'Use the BIR’s Standard Functional and Technical Requirements checklist.' ),
					array( '9', 'Prepare additional documents, if applicable', 'Include certification from the parent/affiliated company if the software license is under another entity, plus representative authorization if needed.' ),
					array( '10', 'Include certification from the parent/affiliated company if the software license is under another entity, plus representative authorization if needed.', 'The 2026 BIR Citizen’s Charter lists ORUS submission as an option, with document uploads in jpeg, png, or pdf format up to 25 MB.' ),
					array( '11', 'Monitor application status', 'The ORUS process shows a total processing time of 3 days and no processing fee, provided that the submitted documentary requirements are valid and complete.' ),
					array( '12', 'Secure the Acknowledgement Certificate', 'Keep the AC, submitted documents, sample outputs, and system documentation for records and post-evaluation.' ),
					array( '13', 'Manage future system changes', 'Major financial-impact upgrades, module changes, integrations, or version changes may require prior BIR notification/approval; RR No. 6-2022 also lists tampering and unapproved major changes as grounds for revocation.' ),
				),
			),
		),
	);
}

/** Splice the current page's additions into the parsed groups, after the H2 each names. */
add_filter( 'dq_lp_groups', function ( $groups ) {
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return $groups;
	}
	$all = dq_landing_additions();
	if ( empty( $all[ $post->post_name ] ) || ! is_array( $groups ) ) {
		return $groups;
	}
	foreach ( $all[ $post->post_name ] as $add ) {
		$group = array_merge( dq_lp_new_group(), array(
			'title'   => $add['title'],
			'intro'   => isset( $add['intro'] ) ? (array) $add['intro'] : array(),
			'table'   => $add['table'],
			'kicker'  => isset( $add['kicker'] ) ? $add['kicker'] : '',
			'closing' => isset( $add['closing'] ) ? (array) $add['closing'] : array(),
		) );
		$at = count( $groups );
		if ( '' !== $add['after'] ) {
			$needle = mb_strtolower( $add['after'] );
			foreach ( $groups as $i => $g ) {
				if ( 0 === strpos( mb_strtolower( wp_strip_all_tags( (string) $g['title'] ) ), $needle ) ) {
					$at = $i + 1;
					break;
				}
			}
		}
		array_splice( $groups, $at, 0, array( $group ) );
	}
	return $groups;
} );

/** The table section: head, optional kicker, the table, closing copy. */
function dq_lp_table_section( array $g, $alt ) {
	$rows = $g['table'];
	$head = array_shift( $rows );
	$html = '<section class="lp-section lp-table' . $alt . '"><div class="wrap">' . dq_lp_head( $g, '' );
	if ( ! empty( $g['kicker'] ) ) {
		$html .= '<h3 class="lp-table-kicker">' . esc_html( $g['kicker'] ) . '</h3>';
	}
	$html .= '<div class="spec-table-wrap"' . dq_reveal_attr() . '><table class="spec-table' . ( count( (array) $head ) > 3 ? ' is-wide' : '' ) . '"><thead><tr>';
	foreach ( (array) $head as $cell ) {
		$html .= '<th scope="col">' . wp_kses_post( $cell ) . '</th>';
	}
	$html .= '</tr></thead><tbody>';
	foreach ( $rows as $row ) {
		$html .= '<tr>';
		foreach ( (array) $row as $cell ) {
			$html .= '<td>' . wp_kses_post( $cell ) . '</td>';
		}
		$html .= '</tr>';
	}
	$html .= '</tbody></table></div>';
	foreach ( (array) $g['closing'] as $para ) {
		$html .= '<p class="lp-table-closing">' . wp_kses_post( $para ) . '</p>';
	}
	return $html . '</div></section>';
}
