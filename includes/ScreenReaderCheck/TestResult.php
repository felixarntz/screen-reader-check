<?php
/**
 * Test result class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a single test result.
 *
 * @since 1.0.0
 */
class TestResult {
	/**
	 * The identifier of the test performed.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $test_slug = '';

	/**
	 * The title of the test performed.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $test_title = '';

	/**
	 * The check ID this result belongs to.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var int
	 */
	private $check_id = 0;

	/**
	 * The result type. Either 'success', 'info', 'warning' or 'error'.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $type = 'error';

	/**
	 * Array of messages for this result.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $messages = array();

	/**
	 * Array of additional request data.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $request_data = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Test result arguments.
	 */
	public function __construct( $args = array() ) {
		foreach ( $args as $key => $value ) {
			if ( ! isset( $this->$key ) ) {
				continue;
			}
			$this->$key = $value;
		}
	}

	/**
	 * Returns the identifier of the test performed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test identifier.
	 */
	public function get_test_slug() {
		return $this->test_slug;
	}

	/**
	 * Returns the title of the test performed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test title.
	 */
	public function get_test_title() {
		return $this->test_title;
	}

	/**
	 * Returns the check ID this result belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int The check ID.
	 */
	public function get_check_id() {
		return $this->check_id;
	}

	/**
	 * Returns the result type.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The result type.
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Returns all messages for this result.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The messages.
	 */
	public function get_messages() {
		return $this->messages;
	}

	/**
	 * Returns all request data for this result.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The request data.
	 */
	public function get_request_data() {
		return $this->request_data;
	}

	/**
	 * Checks whether this is a completed test result.
	 *
	 * A test is considered complete when at least one message has been added to the result and
	 * when there is no request data missing a response.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if completed, false otherwise.
	 */
	public function is_done() {
		if ( empty( $this->messages ) ) {
			return false;
		}

		foreach ( $this->request_data as $request ) {
			if ( empty( $request['value'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array representation of this test result.
	 *
	 * Used for storing in the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array The test result representation.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}
}
