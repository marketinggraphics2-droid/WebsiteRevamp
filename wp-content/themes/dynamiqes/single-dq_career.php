<?php
/**
 * Job opening (/careers/<slug>/) — mirrors dynamiqes.com/careers/<slug>/: H1 title, location |
 * type, summary, Apply Now, then the Job Responsibilities / Qualifications lists, and
 * "See Other Jobs" (three more openings).
 *
 * @package dynamiqes
 */

get_header();
the_post();

$id       = get_the_ID();
$location = dq_career_meta( $id, 'location' );
$type     = dq_career_meta( $id, 'type' );
$summary  = trim( wp_strip_all_tags( get_the_excerpt() ) );
$hr       = dq_career_hr();
$apply    = dq_career_apply_url( get_the_title() );
$others   = array_slice( dq_career_jobs( $id ), 0, 3 );
$content  = trim( get_the_content() );
$meta     = implode( ' | ', array_filter( array( $location, $type ) ) );
$pin      = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>';
?>
<main id="main">
	<article <?php post_class( 'career-single' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamiqes' ); ?>"><a href="<?php echo esc_url( dq_career_url() ); ?>"><?php esc_html_e( 'Careers', 'dynamiqes' ); ?></a> / <?php the_title(); ?></nav>
				<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
				<?php if ( $meta ) : ?><p class="career-meta"<?php dq_reveal( 'fade', 60 ); ?>><?php echo esc_html( $meta ); ?></p><?php endif; ?>
				<?php if ( $summary ) : ?><div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><p><?php echo esc_html( $summary ); ?></p></div><?php endif; ?>
				<div class="hero-actions"<?php dq_reveal( '', 160 ); ?>>
					<a class="btn btn-primary" href="<?php echo esc_url( $apply ); ?>"><?php esc_html_e( 'Apply Now', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a><?php /* live: "Apply Now" only */ ?>
				</div>
			</div>
		</header>

		<section class="career-body">
			<div class="wrap">
				<div class="entry-content landing-content career-content"<?php dq_reveal(); ?>>
					<?php if ( '' !== $content ) : ?>
						<?php the_content(); ?>
					<?php else : ?>
						<p class="h2"><?php esc_html_e( 'Job Description', 'dynamiqes' ); ?></p>
						<p><?php echo esc_html( $summary ); ?></p>
						<p><?php esc_html_e( 'The full list of responsibilities and qualifications is available from our HR team — send us your CV and we will get back to you.', 'dynamiqes' ); ?></p>
					<?php endif; ?>
				</div>
				<aside class="career-apply"<?php dq_reveal( '', 120 ); ?>>
					<p class="h3"><?php esc_html_e( 'Apply for this position', 'dynamiqes' ); ?></p><?php /* live outline: H1, the content's H2s, then "See Other Jobs" */ ?>
					<?php if ( $location ) : ?><p class="career-loc"><?php echo $pin; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $location ); ?><?php if ( $type ) : ?> <span class="career-type">· <?php echo esc_html( $type ); ?></span><?php endif; ?></p><?php endif; ?>
					<p><?php esc_html_e( 'Email your CV to our HR team with the position title as the subject.', 'dynamiqes' ); ?></p>
					<a class="btn btn-primary" href="<?php echo esc_url( $apply ); ?>"><?php esc_html_e( 'Apply Now', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
					<ul>
						<li><a href="<?php echo esc_url( $apply ); ?>"><?php echo esc_html( $hr['email'] ); ?></a></li>
						<li><a href="<?php echo esc_attr( dq_tel( $hr['phone'] ) ); ?>"><?php echo esc_html( $hr['phone'] ); ?></a></li>
					</ul>
				</aside>
			</div>
		</section>

		<?php if ( $others ) : ?>
		<section class="career-others">
			<div class="wrap">
				<h2<?php dq_reveal(); ?>><?php esc_html_e( 'See Other Jobs', 'dynamiqes' ); ?></h2>
				<div class="career-grid">
					<?php foreach ( $others as $i => $j ) : ?>
					<article class="career-card"<?php dq_reveal( '', $i * 80 ); ?>>
						<h3><?php echo esc_html( $j['title'] ); ?></h3>
						<?php if ( $j['location'] ) : ?><p class="career-loc"><?php echo $pin; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $j['location'] ); ?></p><?php endif; ?>
						<h4><?php esc_html_e( 'Job Description', 'dynamiqes' ); ?></h4>
						<p><?php echo esc_html( wp_trim_words( $j['summary'], 28, '…' ) ); ?></p>
						<a class="text-link" href="<?php echo esc_url( $j['url'] ); ?>"><?php esc_html_e( 'Read More', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
