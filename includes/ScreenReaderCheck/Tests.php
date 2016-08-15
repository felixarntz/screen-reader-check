<?php
/**
 * Tests class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * This class performs general test-related functions.
 *
 * @since 1.0.0
 */
class Tests {
	/**
	 * The checks class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Checks
	 */
	private $checks;

	/**
	 * Array of $test_slug => $sort_index pairs.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $test_order = array();

	/**
	 * Array of $sort_index => $test_instance pairs.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $tests = array();

	/**
	 * Constructor.
	 *
	 * Loads all tests.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\Checks $checks The checks class instance.
	 */
	public function __construct( $checks ) {
		$class_names = array(
			'ImgAltText',
		);

		$index = 0;
		foreach ( $class_names as $class_name ) {
			$full_class_name = 'ScreenReaderCheck\Tests\\' . $class_name;
			$instance = new $full_class_name();

			$this->tests[] = $instance;
			$this->test_order[ $instance->get_slug() ] = $index;

			$index++;
		}

		$this->checks = $checks;
	}

	/**
	 * Runs the next test for a given check.
	 *
	 * Note that there is one specific error object that can be returned which does not
	 * actually represent an error. The object with error code `check_completed` simply
	 * denotes that there is no next test because all tests have already been completed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int   $check_id Check ID to run the next test for.
	 * @param array $args     Additional arguments for the test.
	 * @return ScreenReaderCheck\TestResult|WP_Error The test result on success, or an error object on failure.
	 */
	public function run_next_test( $check_id, $args = array() ) {
		$check_id = absint( $check_id );

		$check = $this->checks->get( $check_id );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		$last_result = $this->get_last_result( $check );

		$last_test_slug = null;
		$done = true;
		if ( $last_result ) {
			$last_test_slug = $last_result->get_test_slug();
			$done = $last_result->is_done();
		}

		// If test is done, run the next. If not, continue the last one.
		if ( $done ) {
			$test = $this->get_next_test( $last_test_slug );
		} else {
			$test = $this->get_test( $last_test_slug );
		}

		if ( false === $test ) {
			return new WP_Error( 'test_not_found', __( 'An internal error occurred. The test to perform was not found.', 'screen-reader-check' ) );
		}

		if ( true === $test ) {
			return new WP_Error( 'check_completed', __( 'All tests for this check have already completed.', 'screen-reader-check' ) );
		}

		$dom = Util::parse_html( $check->get_html() );

		$result = $test->get_result( $dom, $check_id, $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $done ) {
			if ( ! $check->add_test_result( $result ) ) {
				return new WP_Error( 'result_not_added', __( 'An internal error occurred. It was not possible to add the test result.', 'screen-reader-check' ) );
			}
		} else {
			if ( ! $check->update_test_result( $result, $last_result ) ) {
				return new WP_Error( 'result_not_updated', __( 'An internal error occurred. It was not possible to update the test result.', 'screen-reader-check' ) );
			}
		}

		return $result;
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
			case 'ajax_run_next_test':
				call_user_func_array( array( $this, $method ), $args );
				break;
		}
	}

	/**
	 * Returns the test for a given slug.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $test_slug The test slug.
	 * @return ScreenReaderCheck\Test The test object.
	 */
	private function get_test( $test_slug ) {
		if ( ! isset( $this->test_order[ $test_slug ] ) ) {
			return false;
		}

		return $this->tests[ $this->test_order[ $test_slug ] ];
	}

	/**
	 * Returns next test in the hierarchy.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param string $test_slug The test slug of the last test.
	 * @return ScreenReaderCheck\Test|bool The test object for the next test, true if all tests
	 *                                     have finished, or false if test not found.
	 */
	private function get_next_test( $last_test_slug = null ) {
		if ( ! $last_test_slug ) {
			if ( empty( $this->tests ) ) {
				return true;
			}

			return $this->tests[0];
		}

		if ( ! isset( $this->test_order[ $last_test_slug ] ) ) {
			return false;
		}

		$next_index = $this->test_order[ $last_test_slug ] + 1;
		if ( ! isset( $this->tests[ $next_index ] ) ) {
			return true;
		}

		return $this->tests[ $next_index ];
	}

	/**
	 * Returns the latest test result for a check.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param ScreenReaderCheck\Check $check The check object.
	 * @return ScreenReaderCheck\TestResult|null The latest test result object.
	 */
	private function get_last_result( $check ) {
		$results = $check->get_test_results();
		if ( empty( $results ) ) {
			return null;
		}

		return $results[ count( $results ) - 1 ];
	}

	/**
	 * AJAX callback to run the next test.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data Arguments passed through AJAX.
	 * @return array|WP_Error Response array on success, or error object on failure.
	 */
	private function ajax_run_next_test( $data ) {
		if ( ! isset( $data['check_id'] ) ) {
			return new WP_Error( 'missing_id', __( 'Missing check ID.', 'screen-reader-check' ) );
		}

		$check_id = absint( $data['check_id'] );
		unset( $data['check_id'] );

		$result = $this->run_next_test( $check_id, $data );
		if ( is_wp_error( $result ) ) {
			if ( 'check_completed' === $result->get_error_code() ) {
				return array(
					'test_slug'      => '',
					'test_title'     => '',
					'check_id'       => $check_id,
					'type'           => 'success',
					'messages'       => array( __( 'All tests completed.', 'screen-reader-check' ) ),
					'request_data'   => array(),
					'check_complete' => true,
				);
			}
			return $result;
		}

		return array_merge( $result->to_array(), array( 'check_complete' => false ) );
	}
}