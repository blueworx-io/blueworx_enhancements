<?php
/**
 * Single sign-on: the sign-in button.
 *
 * The label is rendered server-side, so nothing has to correct it in JavaScript
 * after the page has loaded, and the icon is inline rather than an icon font, so
 * the button costs no extra request.
 *
 * @package BlueWorxLabs
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The padlock icon shown on the button.
 *
 * @return string Inline SVG.
 */
function blueworx_sso_icon_svg() {
	return '<svg class="blueworx-sso-button__icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
		. '<rect x="3" y="11" width="18" height="11" rx="2" />'
		. '<path d="M7 11V7a5 5 0 0 1 10 0v4" />'
		. '</svg>';
}

/**
 * Builds the sign-in button.
 *
 * @param array $args {
 *     Optional.
 *
 *     @type string $intent      'login' (the default) or 'register'.
 *     @type string $label       Override the configured label.
 *     @type string $redirect_to Where to send the person afterwards.
 * }
 * @return string Button markup, or an empty string when there is nothing to show.
 */
function blueworx_sso_button_html( $args = array() ) {
	if ( ! blueworx_sso_enabled() || is_user_logged_in() ) {
		return '';
	}

	$intent = blueworx_sso_intent( isset( $args['intent'] ) ? $args['intent'] : 'login' );
	$label  = isset( $args['label'] ) ? trim( (string) $args['label'] ) : '';

	if ( '' === $label ) {
		$label = trim( (string) blueworx_sso_option( 'register' === $intent ? 'register_button_label' : 'button_label' ) );
	}

	if ( '' === $label ) {
		$label = 'register' === $intent
			? __( 'Join with single sign-on', 'blueworx-labs-wordpress' )
			: __( 'Sign in with single sign-on', 'blueworx-labs-wordpress' );
	}

	return sprintf(
		'<a class="blueworx-sso-button blueworx-sso-button--%4$s" href="%1$s">%2$s<span class="blueworx-sso-button__label">%3$s</span></a>',
		esc_url( blueworx_sso_login_url( isset( $args['redirect_to'] ) ? $args['redirect_to'] : '', $intent ) ),
		blueworx_sso_icon_svg(),
		esc_html( $label ),
		esc_attr( $intent )
	);
}

/**
 * Prints the button on the login form.
 *
 * @return void
 */
function blueworx_sso_render_login_button() {
	echo wp_kses( blueworx_sso_button_html(), blueworx_sso_button_allowed_html() );
}
add_action( 'login_form', 'blueworx_sso_render_login_button' );

/**
 * Renders the button anywhere on the site.
 *
 * @param array $atts Shortcode attributes: intent, label, redirect_to.
 * @return string Button markup.
 */
function blueworx_sso_button_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'intent'      => 'login',
			'label'       => '',
			'redirect_to' => '',
		),
		$atts,
		'blueworx_sso_button'
	);

	return wp_kses( blueworx_sso_button_html( $atts ), blueworx_sso_button_allowed_html() );
}
add_shortcode( 'blueworx_sso_button', 'blueworx_sso_button_shortcode' );

/**
 * The markup the button is allowed to produce.
 *
 * @return array Allowed tags for wp_kses().
 */
function blueworx_sso_button_allowed_html() {
	return array(
		'a'    => array(
			'class' => array(),
			'href'  => array(),
		),
		'span' => array( 'class' => array() ),
		'svg'  => array(
			'class'            => array(),
			'width'            => array(),
			'height'           => array(),
			'viewbox'          => array(),
			'fill'             => array(),
			'stroke'           => array(),
			'stroke-width'     => array(),
			'stroke-linecap'   => array(),
			'stroke-linejoin'  => array(),
			'aria-hidden'      => array(),
			'focusable'        => array(),
		),
		'rect' => array(
			'x'      => array(),
			'y'      => array(),
			'width'  => array(),
			'height' => array(),
			'rx'     => array(),
		),
		'path' => array( 'd' => array() ),
	);
}

/**
 * Hides the WordPress password form on the sign-in screen.
 *
 * CSS rather than removing the markup: the form is what every password manager,
 * every "lost your password" flow and every fallback below depends on, and a
 * setting that deletes it leaves nothing to fall back TO.
 *
 * ?blueworx-password=1 always shows it again. That escape hatch is the only
 * reason this setting is safe to offer at all — it means the worst case is an
 * administrator who has to be told about a query string, not one who has lost
 * the site.
 *
 * @return void
 */
function blueworx_sso_maybe_hide_password_form() {
	if ( ! function_exists( 'blueworx_sso_hide_password_form' ) || ! blueworx_sso_hide_password_form() ) {
		return;
	}

	// Read-only: it decides whether a form is painted, nothing is written.
	if ( isset( $_GET['blueworx-password'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	echo '<style id="blueworx-sso-hide-password">#loginform p:not(.blueworx-sso-actions),#loginform .user-pass-wrap,#loginform .forgetmenot,#loginform .submit{display:none}</style>';
}
add_action( 'login_head', 'blueworx_sso_maybe_hide_password_form' );

/**
 * The sentence shown to somebody whose sign-in did not work.
 *
 * One wording, wherever it is shown. It deliberately says nothing about which
 * step failed — the detail lives in the sign-on log, where only the site owner
 * can read it.
 *
 * @return string Translated sentence.
 */
function blueworx_sso_failure_message() {
	return __( 'We could not sign you in. Please try again.', 'blueworx-labs-wordpress' );
}

/**
 * Whether this request is a page somebody was sent to by a failed sign-in.
 *
 * The feature has to be on for this to mean anything. Without that check the
 * query string alone would paint an alarming red banner across any page of any
 * site, for anyone who typed it.
 *
 * @return bool
 */
function blueworx_sso_showing_failure() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Presentation only; the flag carries no meaning beyond "show a notice".
	return isset( $_GET['blueworx_sso_error'] ) && ! is_admin() && blueworx_sso_enabled();
}

/**
 * Prints the failure notice on the front of the site.
 *
 * A failed sign-in now lands on an ordinary page rather than the login screen,
 * so the notice has to travel with it — otherwise the person is bounced to the
 * home page with no idea why, which looks exactly like a broken link.
 *
 * Styles are inline and the markup is printed only on the redirected request,
 * so nothing is loaded on the other pages of the site.
 *
 * @return void
 */
function blueworx_sso_render_failure_notice() {
	static $shown = false;

	if ( $shown || ! blueworx_sso_showing_failure() ) {
		return;
	}

	$shown = true;

	printf(
		'<div class="blueworx-sso-notice" role="alert"><p class="blueworx-sso-notice__text">%1$s</p></div>'
		. '<style id="blueworx-sso-notice-style">.blueworx-sso-notice{box-sizing:border-box;width:100%%;margin:0;padding:14px 20px;background:#fdecec;border-bottom:1px solid #f0b8b8;color:#7a1c1c;font-size:15px;line-height:1.5;text-align:center}.blueworx-sso-notice__text{margin:0}</style>',
		esc_html( blueworx_sso_failure_message() )
	);
}
add_action( 'wp_body_open', 'blueworx_sso_render_failure_notice' );

/**
 * Prints the notice for themes that never call wp_body_open().
 *
 * Plenty of older themes do not, and a notice nobody sees is the same as no
 * notice at all. The static guard in the renderer means a theme that supports
 * both hooks still only shows one.
 *
 * @return void
 */
function blueworx_sso_render_failure_notice_fallback() {
	blueworx_sso_render_failure_notice();
}
add_action( 'wp_footer', 'blueworx_sso_render_failure_notice_fallback' );
