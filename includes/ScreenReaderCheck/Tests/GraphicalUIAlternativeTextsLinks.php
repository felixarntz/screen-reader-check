<?php
/**
 * GraphicalUIAlternativeTextsLinks test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the GraphicalUIAlternativeTextsLinks test.
 *
 * @since 1.0.0
 */
class GraphicalUIAlternativeTextsLinks extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'graphical_ui_alternative_texts_links';
		$this->title            = __( 'Alternative texts for graphical UI elements: Links', 'screen-reader-check' );
		$this->description      = __( 'Graphical UI elements must have alternative texts. Alternative texts for linked graphics should describe the link target.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H30',
			'title'  => __( 'Providing link text that describes the purpose of a link for anchor elements', 'screen-reader-check' ),
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
		$images = $dom->find( 'a > img' );

		if ( count( $images ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no graphical links in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$found = false;
		foreach ( $images as $image ) {
			$link = $image->getParent();
			$link_children = $link->getChildren( true );
			if ( 1 < count( $link_children ) ) {
				continue;
			}

			$found = true;

			if ( ! $link->getAttribute( 'aria-label' ) && ! $image->getAttribute( 'alt' ) ) {
				$result['messages'][] = $this->wrap_message( __( 'The following graphical link is missing an alternative text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
				$has_errors = true;
			}
		}

		if ( ! $found ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no graphical links in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All graphical links in the HTML code have valid alternative texts provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
