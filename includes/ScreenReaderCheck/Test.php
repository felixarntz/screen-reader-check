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
	 * @param PHPHtmlParser\Dom $dom      The DOM object to check.
	 * @param int               $check_id The check ID.
	 * @param array             $args     Additional arguments.
	 * @return ScreenReaderCheck\TestResult The result for the test.
	 */
	public function get_result( $dom, $check_id, $args = array() ) {
		$result = $this->run( array(
			'type' => 'error',
			'messages' => array(),
			'request_data' => array(),
		), $dom, $args );

		$result['test_slug'] = $this->get_slug();
		$result['check_id'] = $check_id;

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
	 * Runs the test on a given DOM object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array             $result The default result array with keys
	 *                                  `type`, `messages` and `request_data`.
	 * @param PHPHtmlParser\Dom $dom    The DOM object to check.
	 * @param array             $args   Additional arguments.
	 * @return array The modified result array.
	 */
	protected abstract function run( $result, $dom, $args = array() );

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
		$rows = substr_count( $code, "\n" ) + 1;

		return '<textarea rows="' . $rows . '" class="code-snippet" readonly="readonly">' . esc_textarea( $code ) . '</textarea>';
	}
}
