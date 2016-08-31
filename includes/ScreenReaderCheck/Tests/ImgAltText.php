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
		$this->description = __( 'All images must provide a valid alt attribute which describes what is shown on the image in clear human language. If an image is purely decorative as part of the design, it must still provide an empty alt attribute.', 'screen-reader-check' );
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
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no images in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $images as $image ) {
			$alt = $image->getAttribute( 'alt' );
			if ( null === $alt ) {
				$result['messages'][] = $this->wrap_message( __( 'The following image is missing an alt attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
				$has_errors = true;
			} elseif ( empty( $alt ) ) {
				$src = $image->getAttribute( 'src' );
				$image_type = $this->get_option( 'image_type_' . $src );
				if ( $image_type ) {
					if ( 'content' === $image_type ) {
						$result['messages'][] = $this->wrap_message( __( 'The following image has an empty alt attribute although it is part of the content:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'An empty alt attribute is only acceptable for decorative images.', 'screen-reader-check' ), $image->getLineNo() );
						$has_errors = true;
					}
				} else {
					$result['request_data'][] = array(
						'slug'          => 'image_type_' . $src,
						'type'          => 'select',
						'label'         => __( 'Image Type', 'screen-reader-check' ),
						'description'   => sprintf( __( 'Choose whether the image %s is a decorative image or part of actual content.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
						'options'       => array(
							'content'     => __( 'Part of content', 'screen-reader-check' ),
							'decorative'  => __( 'Decorative', 'screen-reader-check' ),
						),
						'default'       => 'content',
					);
				}
			} else {
				$src = $image->getAttribute( 'src' );
				if ( is_string( $src ) && false !== strpos( $src, $alt ) ) {
					$result['messages'][] = $this->wrap_message( __( 'The following image seems to have an auto-generated alt attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'Alt attributes should describe the image in clear human language.', 'screen-reader-check' ), $image->getLineNo() );
					$has_errors = true;
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All images in the HTML code have valid <code>alt</code> tags provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
