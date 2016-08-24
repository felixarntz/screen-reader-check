<?php
/**
 * Domain class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a domain.
 *
 * A domain, in the terms of this plugin, is mostly a storage object for options.
 *
 * @since 1.0.0
 */
class Domain {
	/**
	 * The domain ID.
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
	 * @param int $id The domain ID.
	 */
	public function __construct( $id ) {
		$this->id = $id;
	}

	/**
	 * Returns the ID of this domain.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return int The domain ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Returns the actual domain name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The domain name.
	 */
	public function get_name() {
		return get_the_title( $this->id );
	}

	/**
	 * Returns all options for the domain.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Domain options.
	 */
	public function get_options() {
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
		$options = $this->get_options();

		$options[ $option ] = $value;

		return (bool) update_post_meta( $this->id, 'src_options', $options );
	}
}
