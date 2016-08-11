<?php
/**
 * App class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This is the main plugin class and handles all initialization.
 *
 * @since 1.0.0
 */
class App {
	/**
	 * Singleton to ensure that the plugin is only loaded once.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @return ScreenReaderCheck\App The plugin instance.
	 *
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Path to the plugin's main file.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $main_file = '';

	/**
	 * Whether the plugin has been initialized.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var bool
	 */
	private $initialized = false;

	/**
	 * Constructor.
	 *
	 * It is private to prevent duplicate instantiation.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @codeCoverageIgnore
	 */
	private function __construct() {
		// Empty constructor
	}

	/**
	 * Dummy magic method to prevent the plugin from being cloned.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @codeCoverageIgnore
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'screen-reader-check' ), '1.0.0' );
	}

	/**
	 * Dummy magic method to prevent the plugin from being unserialized.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @codeCoverageIgnore
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'screen-reader-check' ), '1.0.0' );
	}

	/**
	 * Initializes the plugin.
	 *
	 * Can only be called once.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $main_file Path to the plugin's main file.
	 */
	public function initialize( $main_file ) {
		if ( $this->initialized ) {
			return;
		}

		$this->initialized = true;
		$this->main_file = $main_file;

		$checks = new Checks();
		add_action( 'init', array( $checks, 'register_post_type' ) );
	}

	/**
	 * Returns the full path to a relative path for a plugin file or directory.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $rel_path Relative path.
	 * @return string Full path.
	 */
	public function path( $rel_path ) {
		return plugin_dir_path( $this->main_file ) . ltrim( $rel_path, '/' );
	}

	/**
	 * Returns the full URL to a relative path for a plugin file or directory.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $rel_path Relative path.
	 * @return string Full URL.
	 */
	public function url( $rel_path ) {
		return plugin_dir_url( $this->main_file ) . ltrim( $rel_path, '/' );
	}
}
