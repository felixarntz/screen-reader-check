<?php
/**
 * MiuseTypographicalCharacters test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the MiuseTypographicalCharacters test.
 *
 * @since 1.0.0
 */
class MisuseTypographicalCharacters extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'misuse_typographical_characters';
		$this->title            = __( 'Misuse of typographical characters', 'screen-reader-check' );
		$this->description      = __( 'Typographical characters like whitespace must not be used to format text. Similarly, hypens or similar characters should not be used to create horizontal lines.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';
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
		$has_errors = false;
		$has_warnings = false;

		$elements = $dom->find( '*', true );
		foreach ( $elements as $element ) {
			if ( ! $element->isTextNode() ) {
				continue;
			}

			$whitespace = array();
			if ( preg_match( '/(&nbsp;){2,}/m', $element->text(), $whitespace ) ) {
				$result['messages'][] = $this->wrap_message( __( 'Whitespace must not be used to format text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $whitespace[0] ), $element->getLineNo() );
				$has_errors = true;
			}

			$hyphen = array();
			if ( preg_match( '/(\-\-\-|___)+/m', $element->text(), $hyphen ) ) {
				$result['messages'][] = $this->wrap_message( __( 'Whitespace must not be used to format text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $hyphen[0] ), $element->getLineNo() );
				$has_errors = true;
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'No invalid usages of tags or lack of structure have been found.', 'screen-reader-check' );
		}

		return $result;
	}
}
