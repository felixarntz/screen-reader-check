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
	 * The domain object for this check.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Domain
	 */
	private $domain;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The check ID.
	 * @param ScreenReaderCheck\Domain|null The domain object for this check, or null.
	 */
	public function __construct( $id, $domain ) {
		$this->id = $id;
		$this->domain = $domain;
	}

	/**
	 * Returns the ID of this check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int The check ID.
	 */
	public function get_id() {
		return $this->id;
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
		$html = get_post_meta( $this->id, 'src_html', true );
		if ( is_array( $html ) ) {
			// HTML is stored in chunks.
			$output = '';

			foreach ( $html as $chunk_name ) {
				$output .= get_post_meta( $this->id, $chunk_name, true );
			}

			return $output;
		}

		return $html;
	}

	/**
	 * Returns all options for the check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Check options as $option => $value pairs.
	 */
	public function get_options() {
		if ( $this->domain ) {
			return $this->domain->get_options();
		}

		$options = get_post_meta( $this->id, 'src_options', true );
		if ( ! $options ) {
			return array();
		}

		return $options;
	}

	/**
	 * Returns the value for a specific option.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $option Name of the option to get.
	 * @return mixed The value of the option, or null if not specified.
	 */
	public function get_option( $option ) {
		if ( $this->domain ) {
			return $this->domain->get_option( $option );
		}

		$options = $this->get_options();
		if ( ! isset( $options[ $option ] ) ) {
			return null;
		}

		return $options[ $option ];
	}

	/**
	 * Updates multiple options.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $options Options as $option => $value pairs.
	 * @return bool True on success, false on failure.
	 */
	public function update_options( $options ) {
		if ( $this->domain ) {
			return $this->domain->update_options( $options );
		}

		$old_options = $this->get_options();

		$new_options = array_merge( $old_options, $options );

		return (bool) update_post_meta( $this->id, 'src_options', $new_options );
	}

	/**
	 * Updates a single option.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $option Option name.
	 * @param mixed  $value  Option value.
	 * @return bool True on success, false on failure.
	 */
	public function update_option( $option, $value ) {
		if ( $this->domain ) {
			return $this->domain->update_option( $option, $value );
		}

		$options = $this->get_options();

		$options[ $option ] = $value;

		return (bool) update_post_meta( $this->id, 'src_options', $options );
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
	 * @param ScreenReaderCheck\TestResult      $result            The test result to update.
	 * @param ScreenReaderCheck\TestResult|null $result_to_replace Optional. The test result to replace.
	 *                                                             If omitted, it is detected automatically.
	 * @return bool True on success, false on failure.
	 */
	public function update_test_result( $result, $result_to_replace = null ) {
		if ( ! $result_to_replace ) {
			$old_results = $this->get_test_results();

			foreach ( $old_results as $old_result ) {
				if ( $result->get_test_slug() === $old_result->get_test_slug() ) {
					$result_to_replace = $old_result;
					break;
				}
			}

			if ( ! $result_to_replace ) {
				return false;
			}
		}

		return (bool) update_post_meta( $this->id, 'src_test_results', $result->to_array(), $result_to_replace->to_array() );
	}
}
