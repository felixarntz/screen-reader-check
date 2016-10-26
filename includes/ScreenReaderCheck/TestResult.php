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
	 * The description of the test performed.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var string
	 */
	private $test_description = '';

	/**
	 * The title of the test-related guideline.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $test_guideline_title = '';

	/**
	 * The anchor ID of the test-related guideline in https://www.w3.org/TR/WCAG20/.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $test_guideline_anchor = '';

	/**
	 * Further Reading links for the test performed.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $test_links = array();

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
	 * Returns the description of the test performed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test description.
	 */
	public function get_test_description() {
		return $this->test_description;
	}

	/**
	 * Returns the title of the test-related guideline.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The guideline title.
	 */
	public function get_test_guideline_title() {
		return $this->test_guideline_title;
	}

	/**
	 * Returns the anchor ID of the test-related guideline in https://www.w3.org/TR/WCAG20/.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The guideline anchor ID.
	 */
	public function get_test_guideline_anchor() {
		return $this->test_guideline_anchor;
	}

	/**
	 * Returns the links of the test performed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The test links.
	 */
	public function get_test_links() {
		return $this->test_links;
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

		if ( ! empty( $this->request_data ) ) {
			return false;
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
