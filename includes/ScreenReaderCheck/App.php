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
	 * The checks class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ScreenReaderCheck\Checks
	 */
	public $checks;

	/**
	 * The domains class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ScreenReaderCheck\Domains
	 */
	public $domains;

	/**
	 * The tests class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ScreenReaderCheck\Tests
	 */
	public $tests;

	/**
	 * The stats class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ScreenReaderCheck\Stats
	 */
	public $stats;

	/**
	 * The AJAX handler class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var ScreenReaderCheck\AjaxHandler
	 */
	public $ajax;

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

		$this->domains = new Domains();
		add_action( 'init', array( $this->domains, 'register_post_type' ) );
		//add_action( 'manage_posts_extra_tablenav', array( $this->domains, 'render_delete_domain' ) );
		//add_action( 'admin_action_src_delete_domain', array( $this->domains, 'handle_delete_domain' ) );
		//add_action( 'admin_notices', array( $this->domains, 'maybe_show_domain_deleted_notice' ) );

		$this->checks = new Checks( $this->domains );
		add_action( 'init', array( $this->checks, 'register_post_type' ) );
		add_filter( 'manage_src_check_posts_columns', array( $this->checks, 'register_post_type_columns' ) );
		add_action( 'manage_src_check_posts_custom_column', array( $this->checks, 'render_post_type_site_category_column' ), 10, 2 );

		$this->tests = new Tests( $this->checks );

		$this->stats = new Stats( $this->checks, $this->tests );
		add_action( 'admin_menu', array( $this->stats, 'register_menu_item' ) );

		$ajax = new AjaxHandler();
		add_action( 'admin_init', array( $ajax, 'init' ) );
		add_action( 'wp_enqueue_scripts', array( $ajax, 'register_script' ), 1 );

		$ajax->register_action( 'create_check', array( $this->checks, 'ajax_create' ) );
		$ajax->register_action( 'run_next_test', array( $this->tests, 'ajax_run_next_test' ) );
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
