<?php
/**
 * Check class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a check.
 *
 * @since 1.0.0
 */
class Check {
	/**
	 * The check ID.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var int
	 */
	private $id = 0;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The check ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns the URL of the web page checked.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string URL of the web page checked, or empty string if not provided.
	 */
	public function get_url() {
		return get_post_meta( $this->id, 'src_url', true );
	}

	/**
	 * Returns the full HTML code checked.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The HTML code checked.
	 */
	public function get_html() {
		return get_post_meta( $this->id, 'src_html', true );
	}

	/**
	 * Returns all test results for this check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of ScreenReaderCheck\TestResult objects.
	 */
	public function get_test_results() {
		return array_map( array( 'ScreenReaderCheck\Util', 'result_args_to_result' ), get_post_meta( $this->id, 'src_test_results' ) );
	}

	/**
	 * Adds a new test result to the check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\TestResult $result The test result to add.
	 * @return bool True on success, false on failure.
	 */
	public function add_test_result( $result ) {
		return (bool) add_post_meta( $this->id, 'src_test_results', $result->to_array() );
	}

	/**
	 * Updates an existing test result in the check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\TestResult $result The test result to update.
	 * @return bool True on success, false on failure.
	 */
	public function update_test_result( $result ) {
		$old_results = $this->get_test_results();

		foreach ( $old_results as $old_result ) {
			if ( $result->get_test_slug() === $old_result->get_test_slug() ) {
				return (bool) update_post_meta( $this->id, 'src_test_results', $result->to_array(), $old_result->to_array() );
			}
		}

		return false;
	}
}
