<?php
/**
 * CaptchaAlternatives test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the CaptchaAlternatives test.
 *
 * @since 1.0.0
 */
class CaptchaAlternatives extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'captcha_alternatives';
		$this->title            = __( 'Alternatives for CAPTCHAs', 'screen-reader-check' );
		$this->description      = __( 'Every image-based CAPTCHAs should have a non-image-based alternative provided.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv-all';

		$this->links[] = array();
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
	protected function run( $result, $dom ) {
		$images = $dom->find( 'img' );

		if ( count( $images ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no CAPTCHAs in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$captcha_requests = array();

		$found = false;
		foreach ( $images as $image ) {
			// Assume that CAPTCHAs can be detected through one of its primary attributes.
			$class = $image->getAttribute( 'class' );
			if ( ! is_string( $class ) || false === stripos( $class, 'captcha' ) ) {
				$id = $image->getAttribute( 'id' );
				if ( ! is_string( $id ) || false === stripos( $id, 'captcha' ) ) {
					$src = $image->getAttribute( 'src' );
					if ( ! is_string( $src ) || false === stripos( $src, 'captcha' ) ) {
						continue;
					}
				}
			}

			$found = true;

			$identifier = $image->getAttribute( 'id' );
			if ( ! is_string( $identifier ) ) {
				$identifier = $image->getAttribute( 'src' );
				if ( ! is_string( $identifier ) ) {
					continue;
				}
				$identifier = $this->sanitize_src( $identifier );
			}

			$has_alternative = $this->get_option( $identifier . '_has_alternative' );
			if ( $has_alternative ) {
				if ( 'yes' !== $has_alternative ) {
					$result['message_codes'][] = 'error_missing_captcha_alternative';
					$result['messages'][] = $this->wrap_message( __( 'The following CAPTCHA is missing a non-image based alternative:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
					$has_errors = true;
				}
			} else {
				if ( ! in_array( $identifier, $captcha_requests ) ) {
					$image_requests[] = $identifier;
					$result['request_data'][] = array(
						'slug'          => $identifier . '_has_alternative',
						'type'          => 'select',
						'label'         => __( 'CAPTCHA Alernative', 'screen-reader-check' ),
						'description'   => sprintf( __( 'Does the CAPTCHA in line %s have a non-image based alternative provided?', 'screen-reader-check' ), $image->getLineNo() ),
						'options'       => array(
							array(
								'value'   => 'yes',
								'label'   => __( 'Yes', 'screen-reader-check' ),
							),
							array(
								'value'   => 'no',
								'label'   => __( 'No', 'screen-reader-check' ),
							),
						),
						'default'       => 'no',
					);
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no CAPTCHAs in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All CAPTCHAs in the HTML code have valid non-image alternatives provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
