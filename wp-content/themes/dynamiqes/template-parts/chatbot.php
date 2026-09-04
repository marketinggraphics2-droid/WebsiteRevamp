<?php
/**
 * Chatbot widget — MOCKUP ONLY. Pure markup + CSS (open/close is a CSS checkbox
 * toggle); nothing is wired to a backend yet. Output from footer.php on every page.
 *
 * @package dynamiqes
 */

$dq_bot_name = get_theme_mod( 'dq_chat_name', __( 'DynamIQ Assistant', 'dynamiqes' ) );
$dq_bot_logo = DQ_URI . '/assets/logos/IQ_Logo.svg';
?>
<div class="dq-chat" id="dq-chat" data-mockup="true">
	<input type="checkbox" id="dqChatToggle" class="dq-chat__toggle" aria-hidden="true" tabindex="-1">

	<section class="dq-chat__panel" role="dialog" aria-label="<?php echo esc_attr( $dq_bot_name ); ?>" aria-modal="false">
		<header class="dq-chat__head">
			<span class="dq-chat__avatar"><img src="<?php echo esc_url( $dq_bot_logo ); ?>" alt="" width="22" height="22"></span>
			<div class="dq-chat__title">
				<strong><?php echo esc_html( $dq_bot_name ); ?></strong>
				<span class="dq-chat__status"><i aria-hidden="true"></i><?php esc_html_e( 'Online · replies in minutes', 'dynamiqes' ); ?></span>
			</div>
			<label for="dqChatToggle" class="dq-chat__close" role="button" aria-label="<?php esc_attr_e( 'Close chat', 'dynamiqes' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg></label>
		</header>

		<div class="dq-chat__body">
			<p class="dq-chat__day"><?php esc_html_e( 'Today', 'dynamiqes' ); ?></p>

			<div class="dq-chat__msg is-bot">
				<span class="dq-chat__mini"><img src="<?php echo esc_url( $dq_bot_logo ); ?>" alt="" width="16" height="16"></span>
				<div class="dq-chat__bubble">
					<?php esc_html_e( 'Hi there! 👋 I’m the DynamIQ Assistant. Something slowing your business down? Let’s figure it out together.', 'dynamiqes' ); ?>
					<time>9:41 AM</time>
				</div>
			</div>

			<div class="dq-chat__msg is-bot">
				<span class="dq-chat__mini"><img src="<?php echo esc_url( $dq_bot_logo ); ?>" alt="" width="16" height="16"></span>
				<div class="dq-chat__bubble">
					<?php esc_html_e( 'What can I help you with today?', 'dynamiqes' ); ?>
					<div class="dq-chat__chips" aria-label="<?php esc_attr_e( 'Suggested replies', 'dynamiqes' ); ?>">
						<button type="button"><?php esc_html_e( 'Book a free demo', 'dynamiqes' ); ?></button>
						<button type="button"><?php esc_html_e( 'SAP Business One pricing', 'dynamiqes' ); ?></button>
						<button type="button"><?php esc_html_e( 'BIR CAS compliance', 'dynamiqes' ); ?></button>
						<button type="button"><?php esc_html_e( 'Talk to a consultant', 'dynamiqes' ); ?></button>
					</div>
				</div>
			</div>

			<div class="dq-chat__msg is-user">
				<div class="dq-chat__bubble">
					<?php esc_html_e( 'We’re looking for a BIR-accredited accounting system for a 40-person company.', 'dynamiqes' ); ?>
					<time>9:42 AM</time>
				</div>
			</div>

			<div class="dq-chat__msg is-bot">
				<span class="dq-chat__mini"><img src="<?php echo esc_url( $dq_bot_logo ); ?>" alt="" width="16" height="16"></span>
				<div class="dq-chat__bubble">
					<?php esc_html_e( 'Great fit. SAP Business One with our IQ Tax module is BIR CAS-ready and scales well for teams your size. Want me to line up a free business analysis?', 'dynamiqes' ); ?>
					<time>9:42 AM</time>
				</div>
			</div>

			<div class="dq-chat__msg is-bot is-typing" aria-label="<?php esc_attr_e( 'Assistant is typing', 'dynamiqes' ); ?>">
				<span class="dq-chat__mini"><img src="<?php echo esc_url( $dq_bot_logo ); ?>" alt="" width="16" height="16"></span>
				<div class="dq-chat__bubble"><span></span><span></span><span></span></div>
			</div>
		</div>

		<footer class="dq-chat__composer">
			<input type="text" placeholder="<?php esc_attr_e( 'Type your message…', 'dynamiqes' ); ?>" aria-label="<?php esc_attr_e( 'Message', 'dynamiqes' ); ?>" disabled>
			<button type="button" class="dq-chat__send" aria-label="<?php esc_attr_e( 'Send', 'dynamiqes' ); ?>" disabled><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 11.5 21 3l-8.5 18-2.5-7.5L3 11.5z"/></svg></button>
			<p class="dq-chat__note"><?php esc_html_e( 'Mockup — not connected yet', 'dynamiqes' ); ?></p>
		</footer>
	</section>

	<label for="dqChatToggle" class="dq-chat__launcher" role="button" aria-label="<?php esc_attr_e( 'Open chat', 'dynamiqes' ); ?>">
		<svg class="dq-chat__ico-open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.2-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 12h.01M12 12h.01M15.5 12h.01" stroke-width="2.8"/></svg>
		<svg class="dq-chat__ico-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
		<span class="dq-chat__badge" aria-hidden="true">1</span>
	</label>
</div>
