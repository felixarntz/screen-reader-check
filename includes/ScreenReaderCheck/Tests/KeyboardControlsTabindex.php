<?php
/**
 * KeyboardControlsTabindex test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the KeyboardControlsTabindex test.
 *
 * @since 1.0.0
 */
class KeyboardControlsTabindex extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'keyboard_controls_tabindex';
		$this->title            = __( 'Keyboard Accessible: Tabindex', 'screen-reader-check' );
		$this->description      = __( 'The website should also be accessible when using only the keyboard.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.1.1 Keyboard', 'screen-reader-check' );
		$this->guideline_anchor = 'keyboard-operation-keyboard-operable';
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
		$tabindexes = $dom->find( '[tabindex]' );

		if ( count( $tabindexes ) === 0 ) {
			$result['type'] = 'info';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no tags with <code>tabindex</code> attributes in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $tabindexes as $tabindex ) {
			$value = (int) $tabindex->getAttribute( 'tabindex' );
			if ( $value > 0 ) {
				$result['message_codes'][] = 'tabindex_greater_than_0';
				$result['messages'][] = $this->wrap_message( __( 'The <code>tabindex</code> attribute of the following element is greater than 0:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $tabindex->outerHtml() ), $tabindex->getLineNo() );
				$has_errors = true;
			} else {
				if ( $value === -1 ) {
					$result['message_codes'][] = 'tabindex_minus_1';
					$result['messages'][] = $this->wrap_message( __( 'The <code>tabindex</code> attribute of the following element is set to -1, thus can only reached via JavaScript:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $tabindex->outerHtml() ), $tabindex->getLineNo() );
					$has_warnings = true;
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All tags with <code>tabindex</code> attributes in the HTML code use non-problematic values.', 'screen-reader-check' );
		}

		return $result;
	}
}
