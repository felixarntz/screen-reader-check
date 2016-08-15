<?php
/**
 * ImgAltText test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the ImgAltText test.
 *
 * @since 1.0.0
 */
class ImgAltText extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug = 'img_alt_text';
		$this->title = __( 'Image Alternative Texts', 'screen-reader-check' );
	}

	/**
	 * Runs the test on a given DOM object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array             $result The default result array with keys
	 *                                  `type`, `messages` and `request_data`.
	 * @param PHPHtmlParser\Dom $dom    The DOM object to check.
	 * @param array             $args   Additional arguments.
	 * @return array The modified result array.
	 */
	protected function run( $result, $dom, $args = array() ) {
		$images = $dom->find( 'img' );

		if ( count( $images ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no images in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		foreach ( $images as $image ) {
			$alt = $image->getAttribute( 'alt' );
			if ( null === $alt ) {
				$result['messages'][] = __( 'Missing alt attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() );
			}
		}

		if ( empty( $result['messages'] ) ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All images in the HTML code have <code>alt</code> tags provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
