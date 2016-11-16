<?php
/**
 * CaptchasAlternativeTexts test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the CaptchasAlternativeTexts test.
 *
 * @since 1.0.0
 */
class CaptchasAlternativeTexts extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'captchas_alternative_texts';
		$this->title            = __( 'Alternative texts for CAPTCHAs', 'screen-reader-check' );
		$this->description      = __( 'For image-based CAPTCHAs, the alternative text should describe the purpose of the CAPTCHA and where to find a non-image-based alternative.', 'screen-reader-check' );
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

			$alt = $image->getAttribute( 'alt' );
			if ( ! $alt ) {
				$result['message_codes'][] = 'error_missing_alternative_text';
				$result['messages'][] = $this->wrap_message( __( 'The following CAPTCHA is missing an alternative text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
				$has_errors = true;
			} else {
				if ( strtolower( $alt ) === 'captcha' ) {
					$result['message_codes'][] = 'error_non_descriptive_alternative_text';
					$result['messages'][] = $this->wrap_message( __( 'The following CAPTCHA does not have a helpful alternative text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
					$has_errors = true;
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
			$result['messages'][] = __( 'All CAPTCHAs in the HTML code have valid <code>alt</code> attributes provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
