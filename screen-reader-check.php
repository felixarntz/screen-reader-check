<?php
/*
Plugin Name: Screen Reader Check
Plugin URI:  https://screen-reader-check.felix-arntz.me
Description: A tool to help developers to make their HTML code accessible for screen reader users.
Version:     1.0.0
Author:      Felix Arntz
Author URI:  https://leaves-and-love.net
License:     GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Text Domain: screen-reader-check
Tags:        screen reader, accessibility, a11n, tool
*/
/**
 * Plugin initialization file
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

load_plugin_textdomain( 'screen-reader-check' );

if ( version_compare( phpversion(), '5.6.0', '<' ) ) {
	function src_php_notice() {
		?>
		<div class="notice notice-error">
			<p><?php _e( 'The plugin Screen Reader Check requires at least PHP version 5.6.', 'screen-reader-check' ); ?></p>
		</div>
		<?php
	}
	add_action( 'admin_notices', 'src_php_notice' );
	return;
}

if ( ! class_exists( 'ScreenReaderCheck\App' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

function src_load() {
	$screen_reader_check = call_user_func( array( 'ScreenReaderCheck\App', 'instance' ) );
	$screen_reader_check->initialize( __FILE__ );
}
add_action( 'plugins_loaded', 'src_load' );

function src_get() {
	return call_user_func( array( 'ScreenReaderCheck\App', 'instance' ) );
}
