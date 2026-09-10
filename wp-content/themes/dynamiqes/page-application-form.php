<?php
/**
 * Application Form (/application-form/).
 *
 * The review's notes on this page were that it "is not formatted properly, missing contact form
 * as well" and that the CTA/contact band at the bottom does not belong here (items G1 and G2).
 * So: a proper hero, the application form beside the HR details and the current openings, and
 * no closing CTA band — the page already is the conversion step.
 *
 * The imported intro is discarded on purpose: it was the old form's Position options flattened
 * into a paragraph ("PositionTechnical ConsultantFunctional Consultant…").
 *
 * @package dynamiqes
 */

get_header();
the_post();

$headline = get_post_meta( get_the_ID(), '_dq_landing_h1', true );
$headline = $headline ? $headline : get_the_title();
$hr       = function_exists( 'dq_career_hr' ) ? dq_career_hr() : array( 'email' => '', 'phone' => '' );
$contact  = dq_contact_info();
$jobs     = function_exists( 'dq_career_jobs' ) ? dq_career_jobs() : array();
$career   = function_exists( 'dq_career_url' ) ? dq_career_url() : home_url( '/career/' );

/* Copy typed into the page in WP Admin wins; otherwise a written intro, never the imported one. */
$intro = trim( get_the_content() );
$intro = '' !== $intro
	? apply_filters( 'the_content', $intro )
	: '<p>' . esc_html__( 'Tell us which role you are after and a little about yourself. Our HR team reviews every application and will come back to you directly.', 'dynamiqes' ) . '</p>';
?>
<main id="main">
	<article <?php post_class( 'application-page' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal( 'fade' ); ?>><?php esc_html_e( 'Careers', 'dynamiqes' ); ?></span>
				<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
				<div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div>
			</div>
		</header>

		<section class="application-body" id="apply">
			<div class="wrap application-grid">
				<div class="application-form-side">
					<h2<?php dq_reveal(); ?>><?php esc_html_e( 'Apply Now', 'dynamiqes' ); ?></h2>
					<?php get_template_part( 'template-parts/application-form' ); ?>
				</div>
				<aside class="application-aside"<?php dq_reveal( 'right' ); ?>>
					<h3><?php esc_html_e( 'HR Contact Information', 'dynamiqes' ); ?></h3>
					<p><?php esc_html_e( 'Prefer email? Send your CV and the position you are applying for straight to our HR team.', 'dynamiqes' ); ?></p>
					<ul class="application-contact">
						<?php if ( ! empty( $hr['email'] ) ) : ?>
						<li><a href="mailto:<?php echo esc_attr( $hr['email'] ); ?>"><?php echo esc_html( $hr['email'] ); ?></a></li>
						<?php endif; ?>
						<?php if ( ! empty( $hr['phone'] ) ) : ?>
						<li><a href="<?php echo esc_attr( dq_tel( $hr['phone'] ) ); ?>"><?php echo esc_html( $hr['phone'] ); ?></a></li>
						<?php endif; ?>
						<?php if ( ! empty( $contact['hours'] ) ) : ?><li><?php echo esc_html( $contact['hours'] ); ?></li><?php endif; ?>
						<?php if ( ! empty( $contact['address'] ) ) : ?><li><?php echo esc_html( $contact['address'] ); ?></li><?php endif; ?>
					</ul>
					<?php if ( $jobs ) : ?>
					<p class="application-openings-label"><?php esc_html_e( 'Currently hiring', 'dynamiqes' ); ?></p>
					<ul class="application-openings">
						<?php foreach ( array_slice( $jobs, 0, 6 ) as $job ) : ?>
						<li><a href="<?php echo esc_url( add_query_arg( 'position', rawurlencode( $job['title'] ), get_permalink() ) ); ?>#apply"><?php echo esc_html( $job['title'] ); ?></a><?php if ( $job['location'] ) : ?> <span><?php echo esc_html( $job['location'] ); ?></span><?php endif; ?></li>
						<?php endforeach; ?>
					</ul>
					<a class="tlink" href="<?php echo esc_url( $career ); ?>"><?php esc_html_e( 'See all openings', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">&rarr;</span></a>
					<?php endif; ?>
				</aside>
			</div>
		</section>
	</article>
	<?php /* no closing CTA band: the review asked for it off this page (item G2) */ ?>
</main>
<?php
get_footer();
