<?php
/**
 * ObjectsAlternativeTexts test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the ObjectsAlternativeTexts test.
 *
 * @since 1.0.0
 */
class ObjectsAlternativeTexts extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'objects_alternative_texts';
		$this->title            = __( 'Alternative texts for objects', 'screen-reader-check' );
		$this->description      = __( 'Embedded multimedia objects should have alternative content. If using an alternative text, it should at least provide a description of the content.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv-all';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H53',
			'title'  => __( 'Using the body of the <code>object</code> element', 'screen-reader-check' ),
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
		$objects = $dom->find( 'object,embed' );

		if ( count( $objects ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no objects in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $objects as $object ) {
			if ( ! $object->hasChildren( true ) ) {
				$result['messages'][] = $this->wrap_message( __( 'The following object does not have any alternative content:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $object->outerHtml() ), $object->getLineNo() );
				$has_errors = true;
			} else {
				$children = $object->hasChildren( true );
				if ( 1 === count( $children ) && ! $children[0]->isTextNode() && ! in_array( $children[0]->getTagName(), array( 'object', 'embed' ) ) ) {
					$alternative = $children[0];
					if ( 'img' === $alternative->getTagName() ) {
						$alt = $alternative->getAttribute( 'alt' );
						if ( ! $alt ) {
							$result['messages'][] = $this->wrap_message( __( 'The following object uses an image as alternative content which however does not provide an alternative text itself:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $object->outerHtml() ), $object->getLineNo() );
							$has_errors = true;
						} else {
							$src = $alternative->getAttribute( 'src' );
							if ( is_string( $src ) ) {
								if ( false !== strpos( $src, $alt ) ) {
									$result['messages'][] = $this->wrap_message( __( 'The following object uses an image as alternative which itself seems to have an auto-generated <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $object->outerHtml() ), $object->getLineNo() );
									$has_errors = true;
								} elseif ( 80 < strlen( $src ) ) {
									$result['messages'][] = $this->wrap_message( __( 'The following object uses an image as alternative which itself uses a very long <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $object->outerHtml() ), $object->getLineNo() );
									$has_errors = true;
								}
							}
						}
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All objects in the HTML code have valid alternatives provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
