<?php
/**
 * Test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a test.
 *
 * @since 1.0.0
 */
abstract class Test {
	/**
	 * The identifier of the test.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The title of the test.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * The description of the test.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $description = '';

	/**
	 * The title of the related guideline.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $guideline_title = '';

	/**
	 * The anchor ID of the related guideline in https://www.w3.org/TR/WCAG20/.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $guideline_anchor = '';

	/**
	 * Further Reading links for the test.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $links = array();

	/**
	 * Whether this test may request additional user input.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $may_request_data = false;

	/**
	 * The stats class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Stats
	 */
	private $stats;

	/**
	 * Current check object.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Check
	 */
	private $current_check;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		// Empty constructor.
	}

	/**
	 * Performs the test for a given check and returns the result.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\Parser\Dom $dom   The DOM object to check.
	 * @param ScreenReaderCheck\Check      $check The check object.
	 * @param array                        $args  Additional arguments.
	 * @return ScreenReaderCheck\TestResult The result for the test.
	 */
	public function get_result( $dom, $check, $args = array() ) {
		$this->check = $check;

		if ( ! empty( $args ) ) {
			$prefixed_args = array();
			foreach ( $args as $option => $value ) {
				// Array values will be merged, not replaced.
				if ( is_array( $value ) ) {
					$old_option = $this->check->get_option( $this->slug . '_' . $option );
					if ( $old_option ) {
						$value = array_merge( $old_option, $value );
					}
				}

				$prefixed_args[ $this->slug . '_' . $option ] = $value;
			}

			$this->check->update_options( $prefixed_args );
		}

		$result = $this->run( array(
			'type'          => 'error',
			'messages'      => array(),
			'message_codes' => array(),
			'request_data'  => array(),
		), $dom );

		if ( ! empty( $result['request_data'] ) ) {
			$result['type']          = 'info';
			$result['messages']      = array();
			$result['message_codes'] = array();
		}

		$result['test_slug']             = $this->slug;
		$result['test_title']            = $this->title;
		$result['test_description']      = $this->description;
		$result['test_guideline_title']  = $this->guideline_title;
		$result['test_guideline_anchor'] = $this->guideline_anchor;
		$result['test_links']            = $this->links;
		$result['check_id']              = $this->check->get_id();

		$this->check = null;

		if ( $this->stats && is_a( $this->stats, 'ScreenReaderCheck\Stats' ) ) {
			$this->stats->log_result( $result );
		}

		return new TestResult( $result );
	}

	/**
	 * Returns the slug for this test.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns the title of this test.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the description of this test.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Returns the links for this test.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test links.
	 */
	public function get_links() {
		return $this->links;
	}

	/**
	 * Returns the title of the related guideline.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The guideline title.
	 */
	public function get_guideline_title() {
		return $this->guideline_title;
	}

	/**
	 * Returns the anchor ID of the related guideline in https://www.w3.org/TR/WCAG20/.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The guideline anchor ID.
	 */
	public function get_guideline_anchor() {
		return $this->guideline_anchor;
	}

	/**
	 * Checks whether this test may request data.
	 *
	 * Some tests never ask for any additional user input.
	 * This method allows to differentiate between the two types.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if the test may request data, false otherwise.
	 */
	public function may_request_data() {
		return $this->may_request_data;
	}

	/**
	 * Sets the stats class instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\Stats $stats The stats class instance.
	 */
	public function set_stats( $stats ) {
		$this->stats = $stats;
	}

	/**
	 * Runs the test on a given DOM object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array                        $result The default result array with keys
	 *                                             `type`, `messages` and `request_data`.
	 * @param ScreenReaderCheck\Parser\Dom $dom    The DOM object to check.
	 * @return array The modified result array.
	 */
	protected abstract function run( $result, $dom );

	/**
	 * Returns all test options for the current check.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Test options as $option => $value pairs.
	 */
	protected function get_options() {
		if ( ! $this->check ) {
			return array();
		}

		$all_options = $this->check->get_options();

		$test_options = array();
		foreach ( $all_options as $key => $value ) {
			if ( 0 !== strpos( $key, $this->slug . '_' ) ) {
				continue;
			}

			$test_options[ substr( $key, strlen( $this->slug . '_' ) ) ] = $value;
		}

		return $test_options;
	}

	/**
	 * Returns the value for a specific test option for the current check.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $option Name of the option to get.
	 * @return mixed The value of the test option, or null if not specified.
	 */
	protected function get_option( $option ) {
		if ( ! $this->check ) {
			return null;
		}

		return $this->check->get_option( $this->slug . '_' . $option );
	}

	/**
	 * Returns all global options for the current check.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Global options as $option => $value pairs.
	 */
	protected function get_global_options() {
		if ( ! $this->check ) {
			return array();
		}

		$all_options = $this->check->get_options();

		$global_options = array();
		foreach ( $all_options as $key => $value ) {
			if ( 0 !== strpos( $key, 'global_' ) ) {
				continue;
			}

			$global_options[ substr( $key, strlen( 'global_' ) ) ] = $value;
		}

		return $global_options;
	}

	/**
	 * Returns the value for a specific global option for the current check.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $option Name of the option to get.
	 * @return mixed The value of the global option, or null if not specified.
	 */
	protected function get_global_option( $option ) {
		if ( ! $this->check ) {
			return null;
		}

		return $this->check->get_option( 'global_' . $option );
	}

	/**
	 * Sanitizes a source.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $src A source URL.
	 * @return The sanitized source.
	 */
	protected function sanitize_src( $src ) {
		$src = str_replace( ' ', '-', strtolower( $src ) );

		return str_replace( array( '../', './', '://', '/', '.', '[', ']', '=' ), '--', $src );
	}

	/**
	 * Transforms a source path into a link.
	 *
	 * If the path is relative, the full URL is constructed if possible.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $src The source.
	 * @return string Link to the source file, or the plain source if impossible to create link.
	 */
	protected function linkify_src( $src ) {
		if ( 0 === strpos( $src, 'http://' ) || 0 === strpos( $src, 'https://' ) ) {
			return '<a href="' . $src . '" target="_blank">' . $src . '</a>';
		}

		$url = $this->check->get_url();
		if ( $url ) {
			$url = trailingslashit( $url ) . ltrim( $src );
			return '<a href="' . $url . '" target="_blank">' . $src . '</a>';
		}

		return $src;
	}

	/**
	 * Checks whether the source has a specific file extension.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string       $src        The source.
	 * @param string|array $extensions String or array of file extensions to check for.
	 * @return bool True whether the source has one of the extensions, false otherwise.
	 */
	protected function src_has_extension( $src, $extensions ) {
		$extensions = (array) $extensions;

		$regex = '/\.(' . implode( '|', $extensions ) . ')(\?|$)/i';

		if ( preg_match( $regex, $src ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Wraps a message including its line number.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $message The message.
	 * @param bool   $line_no Optional. The line number the message applies to. Default false.
	 * @return string The wrapped message.
	 */
	protected function wrap_message( $message, $line_no = false ) {
		if ( $line_no ) {
			$message = '<strong>' . sprintf( __( 'Line %d:', 'screen-reader-check' ), $line_no ) . '</strong> ' . $message;
		}

		return $message;
	}

	/**
	 * Wraps a code snippet into a read-only textarea.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $code The code snippet.
	 * @return string The textarea containing the code snippet.
	 */
	protected function wrap_code( $code ) {
		$rows = substr_count( $code, "\n" ) + 2;

		return '<textarea rows="' . $rows . '" class="code-snippet" readonly="readonly">' . esc_textarea( $code ) . '</textarea>';
	}
}
