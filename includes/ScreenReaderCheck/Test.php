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
	 * Further Reading links for the test.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $links = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		// Empty constructor
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
		if ( ! empty( $args ) ) {
			$prefixed_args = array();
			foreach ( $args as $option => $value ) {
				// Array values will be merged, not replaced.
				if ( is_array( $value ) ) {
					$old_option = $check->get_option( $this->slug . '_' . $option );
					if ( $old_option ) {
						$value = array_merge( $old_option, $value );
					}
				}

				$prefixed_args[ $this->slug . '_' . $option ] = $value;
			}

			$check->update_options( $prefixed_args );
		}

		$result = $this->run( array(
			'type' => 'error',
			'messages' => array(),
			'request_data' => array(),
		), $dom, $check );

		$result['test_slug'] = $this->slug;
		$result['test_title'] = $this->title;
		$result['test_description'] = $this->description;
		$result['test_links'] = $this->links;
		$result['check_id'] = $check->get_id();

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
	 * Runs the test on a given DOM object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array                        $result The default result array with keys
	 *                                             `type`, `messages` and `request_data`.
	 * @param ScreenReaderCheck\Parser\Dom $dom    The DOM object to check.
	 * @param ScreenReaderCheck\Check      $check  The check object.
	 * @return array The modified result array.
	 */
	protected abstract function run( $result, $dom, $check );

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
