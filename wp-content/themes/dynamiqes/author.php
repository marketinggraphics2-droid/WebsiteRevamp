<?php
/**
 * Author page — the "Author Page" design (Revamp Dynamiq, September 2026).
 *
 * Photo hero with the portrait in a circle overlapping its bottom edge, name + LinkedIn,
 * About copy beside the Recognitions and Education cards, Areas of Expertise as icon cards,
 * then the "Other Relevant Professional Information" cards. The design ends on the footer
 * (no CTA band). Fields: Users → Profile → "Author page" (inc/author-profile.php).
 *
 * @package dynamiqes
 */

get_header();
$author = dq_author_profile( get_queried_object_id() );
if ( ! $author ) {
	get_template_part( '404' );
	return;
}
$has_side = $author['recognitions'] || $author['education'];
?>
<main id="main">
	<article class="author">
		<section class="author-hero" style="--author-bg:url('<?php echo esc_url( $author['hero_bg'] ); ?>')" aria-label="<?php echo esc_attr( $author['name'] ); ?>">
			<div class="author-hero-photo" aria-hidden="true"></div>
			<div class="wrap author-avatar-wrap">
				<div class="author-avatar"<?php dq_reveal( 'scale' ); ?>>
					<img src="<?php echo esc_url( $author['photo'] ); ?>" alt="<?php echo esc_attr( $author['name'] ); ?>" width="360" height="360" fetchpriority="high">
				</div>
			</div>
		</section>

		<section class="author-intro">
			<div class="wrap">
				<h1 class="author-name"<?php dq_reveal(); ?>>
					<?php echo esc_html( $author['name'] ); ?>
					<?php if ( $author['linkedin'] ) : ?>
					<a class="author-linkedin" href="<?php echo esc_url( $author['linkedin'] ); ?>" target="_blank" rel="noopener me" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: author name */ __( '%s on LinkedIn', 'dynamiqes' ), $author['name'] ) ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.45 20.45h-3.55v-5.57c0-1.33-.03-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.36V9h3.41v1.56h.05c.47-.9 1.63-1.85 3.36-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.79 0 0 .77 0 1.73v20.54C0 23.23.79 24 1.77 24h20.45c.98 0 1.78-.77 1.78-1.73V1.73C24 .77 23.2 0 22.22 0Z"/></svg></a>
					<?php endif; ?>
				</h1>
				<?php if ( $author['role'] ) : ?><p class="author-role"<?php dq_reveal(); ?>><?php echo esc_html( $author['role'] ); ?></p><?php endif; ?>

				<div class="author-about-grid<?php echo $has_side ? '' : ' is-single'; ?>">
					<div class="author-about"<?php dq_reveal(); ?>>
						<h2><?php echo esc_html( sprintf( /* translators: %s: author name */ __( 'About %s', 'dynamiqes' ), $author['name'] ) ); ?></h2>
						<?php foreach ( $author['about'] as $para ) : ?><p><?php echo wp_kses_post( nl2br( $para ) ); ?></p><?php endforeach; ?>
					</div>
					<?php if ( $has_side ) : ?>
					<aside class="author-side">
						<?php if ( $author['recognitions'] ) : ?>
						<div class="author-card"<?php dq_reveal( '', 80 ); ?>>
							<h3><?php esc_html_e( 'Formal Industry Recognitions', 'dynamiqes' ); ?></h3>
							<?php echo dq_author_lines_html( $author['recognitions'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php endif; ?>
						<?php if ( $author['education'] ) : ?>
						<div class="author-card"<?php dq_reveal( '', 160 ); ?>>
							<h3><?php esc_html_e( 'Education & Background', 'dynamiqes' ); ?></h3>
							<?php echo dq_author_lines_html( $author['education'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						</div>
						<?php endif; ?>
					</aside>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $author['expertise'] ) : ?>
		<section class="author-expertise">
			<div class="wrap">
				<h2 class="author-band-title"<?php dq_reveal(); ?>><?php esc_html_e( 'Areas of Expertise', 'dynamiqes' ); ?></h2>
				<ul class="author-expertise-grid">
					<?php foreach ( $author['expertise'] as $i => $item ) : ?>
					<li class="author-expertise-card"<?php dq_reveal( '', $i * 80 ); ?>>
						<span class="author-expertise-icon"><img src="<?php echo esc_url( dq_author_expertise_icon( $item ) ); ?>" alt="" loading="lazy" width="56" height="56"></span>
						<h3><?php echo esc_html( $item ); ?></h3>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( $author['professional'] ) : ?>
		<section class="author-info">
			<div class="wrap">
				<h2 class="author-info-title"<?php dq_reveal(); ?>><?php esc_html_e( 'Other Relevant Professional Information', 'dynamiqes' ); ?></h2>
				<div class="author-info-grid">
					<?php foreach ( $author['professional'] as $i => $card ) : ?>
					<div class="author-card author-info-card"<?php dq_reveal( '', $i * 80 ); ?>>
						<?php if ( $card['title'] ) : ?><h3><?php echo esc_html( $card['title'] ); ?></h3><?php endif; ?>
						<?php foreach ( $card['text'] as $para ) : ?><p><?php echo esc_html( $para ); ?></p><?php endforeach; ?>
						<?php if ( $card['items'] ) : ?>
						<ul class="author-bullets">
							<?php foreach ( $card['items'] as $it ) : ?><li><?php echo esc_html( $it ); ?></li><?php endforeach; ?>
						</ul>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php endif; ?>
	</article>
</main>
<?php
get_footer();
