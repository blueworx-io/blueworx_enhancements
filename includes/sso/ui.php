<?php
/**
 * Single sign-on: the sign-in button.
 *
 * The label is rendered server-side, so nothing has to correct it in JavaScript
 * after the page has loaded. The button is the label and nothing else: it is
 * dropped into headers and page content a site owner has already styled, and an
 * icon of ours only fights with whatever is around it.
 *
 * @package BlueWorxLabs
 */

// Prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
		'<a class="blueworx-sso-button blueworx-sso-button--%3$s" href="%1$s"><span class="blueworx-sso-button__label">%2$s</span></a>',
		esc_url( blueworx_sso_login_url( isset( $args['redirect_to'] ) ? $args['redirect_to'] : '', $intent ) ),
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
