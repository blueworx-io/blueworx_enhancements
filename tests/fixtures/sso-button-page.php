<?php
/**
 * Test fixture — NOT part of the plugin.
 *
 * Publishes a page carrying both single sign-on buttons, so a test can see what
 * each one renders as. The login screen only ever shows the sign-in button, and
 * only to somebody signed out, which leaves three of the four states with
 * nowhere to be looked at.
 *
 * Usage: php sso-button-page.php /absolute/path/to/wp-load.php create|delete
 *
 * @package BlueWorxLabsTests
 */

$wp_load = isset( $argv[1] ) ? $argv[1] : null;
$command = isset( $argv[2] ) ? $argv[2] : 'create';

if ( ! $wp_load || ! is_file( $wp_load ) ) {
	fwrite( STDERR, 'wp-load.php not found: ' . var_export( $wp_load, true ) . "\n" );
	exit( 1 );
}

require $wp_load;

const SSO_BUTTON_PAGE_SLUG = 'blueworx-sso-button-fixture';

$existing = get_page_by_path( SSO_BUTTON_PAGE_SLUG );

if ( 'delete' === $command ) {
	if ( $existing ) {
		wp_delete_post( $existing->ID, true );
	}

	echo "deleted\n";
	exit( 0 );
}

$content = '[blueworx_sso_button]' . "\n\n" . '[blueworx_sso_button intent="register"]';

if ( $existing ) {
	wp_update_post(
		array(
			'ID'           => $existing->ID,
			'post_content' => $content,
			'post_status'  => 'publish',
		)
	);

	echo get_permalink( $existing->ID ) . "\n";
	exit( 0 );
}

$id = wp_insert_post(
	array(
		'post_title'   => 'BlueWorx SSO button fixture',
		'post_name'    => SSO_BUTTON_PAGE_SLUG,
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_content' => $content,
	)
);

if ( is_wp_error( $id ) ) {
	fwrite( STDERR, $id->get_error_message() . "\n" );
	exit( 1 );
}

echo get_permalink( $id ) . "\n";
