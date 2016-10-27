<?php
/**
 * GraphicalUIAlternativeTextsImageMaps test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the GraphicalUIAlternativeTextsImageMaps test.
 *
 * @since 1.0.0
 */
class GraphicalUIAlternativeTextsImageMaps extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'graphical_ui_alternative_texts_image_maps';
		$this->title            = __( 'Alternative texts for graphical UI elements: Image Maps', 'screen-reader-check' );
		$this->description      = __( 'Graphical UI elements must have alternative texts. All the <code>area</code> tags of image maps need to provide helpful alternative texts.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv-all';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H24',
			'title'  => __( 'Providing text alternatives for the <code>area</code> elements of image maps', 'screen-reader-check' ),
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
		$areas = $dom->find( 'map area' );

		if ( count( $areas ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no image maps in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $areas as $area ) {
			$alt = $area->getAttribute( 'alt' );

			if ( ! $alt ) {
				$result['messages'][] = $this->wrap_message( __( 'The following <code>area</code> tag of an image map is missing an alternative text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
				$has_errors = true;
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All image maps in the HTML code have valid alternative texts provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
