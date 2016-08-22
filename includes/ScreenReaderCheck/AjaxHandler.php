<?php
/**
 * AJAX Handler class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class handles all AJAX requests.
 *
 * @since 1.0.0
 */
class AjaxHandler {
	/**
	 * AJAX actions registered.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $actions = array();

	/**
	 * Registers an AJAX action with its callback.
	 *
	 * The callback function must either return an array of data on success,
	 * or a WP_Error object on failure.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string   $action   The action name.
	 * @param callable $callback The callback.
	 */
	public function register_action( $action, $callback ) {
		$this->actions[ $action ] = $callback;
	}

	/**
	 * Magic caller for semi-private methods.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method Method name.
	 * @param array  $args   Method arguments.
	 */
	public function __call( $method, $args ) {
		switch ( $method ) {
			case 'init':
			case 'handle':
			case 'register_script':
				call_user_func_array( array( $this, $method ), $args );
				break;
		}
	}

	/**
	 * Registers the hooks for all registered AJAX actions.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function init() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		foreach ( $this->actions as $action => $callback ) {
			add_action( 'wp_ajax_src_' . $action, array( $this, 'handle' ) );
			add_action( 'wp_ajax_nopriv_src_' . $action, array( $this, 'handle' ) );
		}
	}

	/**
	 * Handles an AJAX action.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function handle() {
		$action = str_replace( array( 'wp_ajax_nopriv_src_', 'wp_ajax_src_' ), '', current_action() );

		if ( ! isset( $_REQUEST['nonce'] ) ) {
			wp_send_json_error( __( 'Missing nonce.', 'screen-reader-check' ) );
		}

		if ( ! check_ajax_referer( 'src_ajax_' . $action, 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'screen-reader-check' ) );
		}

		if ( ! is_callable( $this->actions[ $action ] ) ) {
			wp_send_json_error( __( 'Invalid action.', 'screen-reader-check' ) );
		}

		$data = wp_unslash( $_REQUEST );
		unset( $data['nonce'] );

		$result = call_user_func( $this->actions[ $action ], $data );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * Registers the script needed to interact with the plugin's AJAX actions.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_script() {
		$args = array(
			'nonces'  => array(),
		);
		foreach ( $this->actions as $action => $callback ) {
			$args['nonces'][ $action ] = wp_create_nonce( 'src_ajax_' . $action );
		}

		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'screen-reader-check', App::instance()->url( 'assets/dist/js/screen-reader-check' . $min . '.js' ), array( 'jquery', 'wp-util' ), '1.0.0', true );
		wp_localize_script( 'screen-reader-check', 'screenReaderCheck', $args );
	}
}
