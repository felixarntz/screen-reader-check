<?php
/**
 * Stats class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class provides analytics and handles stats.
 *
 * @since 1.0.0
 */
class Stats {
	/**
	 * The checks class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Checks
	 */
	private $checks;

	/**
	 * The tests class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Tests
	 */
	private $tests;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\Checks $checks The checks class instance.
	 * @param ScreenReaderCheck\Tests  $tests  The tests class instance.
	 */
	public function __construct( $checks, $tests ) {
		$this->checks = $checks;
		$this->tests  = $tests;

		$this->tests->set_stats( $this );
	}

	/**
	 * Logs a test result for easy stats retrieval.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\TestResult|array $result Test result object or array.
	 */
	public function log_result( $result ) {
		if ( is_a( $result, 'ScreenReaderCheck\TestResult' ) ) {
			$result = $result->to_array();
		}

		$check_stats = get_post_meta( $result['check_id'], 'src_stats', true );
		if ( ! is_array( $check_stats ) ) {
			$check_stats = array(
				'total' => array(
					'error_count'   => 0,
					'warning_count' => 0,
				),
				'tests' => array(),
			);
		}

		if ( ! isset( $check_stats['tests'][ $result['test_slug'] ] ) ) {
			$check_stats['tests'][ $result['test_slug'] ] = array(
				'error_count'   => 0,
				'warning_count' => 0,
				'request_count' => 0,
				'skipped'       => false,
			);
		}

		if ( ! empty( $result['request_data'] ) ) {
			$check_stats['tests'][ $result['test_slug'] ]['request_count'] += count( $result['request_data'] );
		} elseif ( 'skipped' === $result['type'] ) {
			$check_stats['tests'][ $result['test_slug'] ]['skipped'] = true;
		} elseif ( in_array( $result['type'], array( 'warning', 'error' ), true ) ) {
			foreach ( $result['message_codes'] as $message_code ) {
				if ( 0 === strpos( $message_code, 'error_' ) ) {
					$check_stats['tests'][ $result['test_slug'] ]['error_count']++;
					$check_stats['total']['error_count']++;
				} elseif ( 0 === strpos( $message_code, 'warning_' ) ) {
					$check_stats['tests'][ $result['test_slug'] ]['warning_count']++;
					$check_stats['total']['warning_count']++;
				}
			}
		}

		update_post_meta( $result['check_id'], 'src_stats', $check_stats );
	}
}
