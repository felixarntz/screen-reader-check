<?php
/**
 * GraphicalUIAlternativeTextsButtons test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the GraphicalUIAlternativeTextsButtons test.
 *
 * @since 1.0.0
 */
class GraphicalUIAlternativeTextsButtons extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'graphical_ui_alternative_texts_buttons';
		$this->title            = __( 'Alternative texts for graphical UI elements: Buttons', 'screen-reader-check' );
		$this->description      = __( 'Graphical UI elements must have alternative texts. Alternative texts for buttons should describe the action they trigger.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv-all';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H36',
			'title'  => __( 'Using <code>alt</code> attributes on images used as submit buttons', 'screen-reader-check' ),
		);
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
		$image_buttons = $dom->find( 'input[type="image"]' );

		if ( count( $image_buttons ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no graphical buttons in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $image_buttons as $image_button ) {
			$alt = $image_button->getAttribute( 'alt' );

			if ( ! $alt ) {
				$result['message_codes'][] = 'missing_alternative_text';
				$result['messages'][] = $this->wrap_message( __( 'The following graphical button is missing an alternative text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
				$has_errors = true;
			} else {
				$src = $image_button->getAttribute( 'src' );
				if ( is_string( $src ) && false !== strpos( $src, $alt ) ) {
					$result['message_codes'][] = 'alt_attribute_part_of_src';
					$result['messages'][] = $this->wrap_message( __( 'The following graphical button seems to have an auto-generated <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image_button->outerHtml() ) . '<br>' . __( 'Alt attributes should describe the image in clear human language.', 'screen-reader-check' ), $image_button->getLineNo() );
					$has_errors = true;
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All graphical buttons in the HTML code have valid alternative texts provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
