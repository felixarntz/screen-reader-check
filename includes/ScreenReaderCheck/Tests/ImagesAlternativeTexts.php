<?php
/**
 * ImagesAlternativeTexts test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the ImagesAlternativeTexts test.
 *
 * @since 1.0.0
 */
class ImagesAlternativeTexts extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'images_alternative_texts';
		$this->title            = __( 'Alternative texts for images', 'screen-reader-check' );
		$this->description      = __( 'Informative images must have alternative texts that should (if possible) serve the same purpose as the image itself. Images that do not have an informative purpose, such as spacers or decorative images, do not require an alternative text, but should use an empty <code>alt</code> attribute.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.1.1 Non-text Content', 'screen-reader-check' );
		$this->guideline_anchor = 'text-equiv-all';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H37',
			'title'  => __( 'Using <code>alt</code> attributes on <code>img</code> elements', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H67',
			'title'  => __( 'Using null alt text and no title attribute on img elements for images that AT should ignore', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H45',
			'title'  => __( 'Using longdesc', 'screen-reader-check' ),
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
		$images = $dom->find( 'img' );

		if ( count( $images ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no images in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$image_requests = array();

		$found = false;
		foreach ( $images as $image ) {
			// Skip CAPTCHAs.
			$class = $image->getAttribute( 'class' );
			if ( is_string( $class ) && false !== stripos( $class, 'captcha' ) ) {
				continue;
			}
			$id = $image->getAttribute( 'id' );
			if ( is_string( $id ) && false !== stripos( $id, 'captcha' ) ) {
				continue;
			}
			$src = $image->getAttribute( 'src' );
			if ( is_string( $src ) && false !== stripos( $src, 'captcha' ) ) {
				continue;
			}

			// Skip object alternatives.
			$parent_tag = $image->getParent()->getTagName();
			if ( in_array( $parent_tag, array( 'object', 'embed' ) ) ) {
				continue;
			}

			$found = true;

			$alt = $image->getAttribute( 'alt' );
			if ( null === $alt ) {
				$result['messages'][] = $this->wrap_message( __( 'The following image is missing an <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
				$has_errors = true;
			} elseif ( empty( $alt ) ) {
				$src = $image->getAttribute( 'src' );
				if ( is_string( $src ) ) {
					$sanitized_src = $this->sanitize_src( $src );

					$image_type = $this->get_option( 'image_type_' . $sanitized_src );
					if ( $image_type ) {
						if ( 'content' === $image_type ) {
							$result['messages'][] = $this->wrap_message( __( 'The following image has an empty <code>alt</code> attribute although it is informative:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'An empty <code>alt</code> attribute is only acceptable for non-informative images.', 'screen-reader-check' ), $image->getLineNo() );
							$has_errors = true;
						} else {
							$title = $image->getAttribute( 'title' );
							if ( $title ) {
								$result['messages'][] = $this->wrap_message( __( 'The following non-informative image uses the <code>title</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ), $image->getLineNo() );
								$has_warnings = true;
							}
						}
					} else {
						if ( ! in_array( $sanitized_src, $image_requests ) ) {
							$result['request_data'][] = array(
								'slug'          => 'image_type_' . $sanitized_src,
								'type'          => 'select',
								'label'         => __( 'Image Type', 'screen-reader-check' ),
								'description'   => sprintf( __( 'Choose whether the image %s is a purely decorative image or part of informative content.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
								'options'       => array(
									array(
										'value'   => 'content',
										'label'   => __( 'Part of content', 'screen-reader-check' ),
									),
									array(
										'value'   => 'decorative',
										'label'   => __( 'Decorative', 'screen-reader-check' ),
									),
								),
								'default'       => 'content',
							);
						}
					}
				}
			} else {
				$src = $image->getAttribute( 'src' );
				if ( is_string( $src ) ) {
					if ( false !== strpos( $src, $alt ) ) {
						$result['messages'][] = $this->wrap_message( __( 'The following image seems to have an auto-generated <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'Alternative texts should describe the image in clear human language.', 'screen-reader-check' ), $image->getLineNo() );
						$has_errors = true;
					} else {
						$blacklist = array( 'spacer', 'placeholder', 'empty', 'leer' );
						if ( in_array( trim( strtolower( $src ) ), $blacklist ) ) {
							$result['messages'][] = $this->wrap_message( __( 'The following image uses a non-descriptive <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'Alternative texts should describe the image in clear human language, or be empty for decorative images.', 'screen-reader-check' ), $image->getLineNo() );
							$has_errors = true;
						} else {
							if ( 80 < strlen( $alt ) ) {
								$result['messages'][] = $this->wrap_message( __( 'The following image uses a very long <code>alt</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $image->outerHtml() ) . '<br>' . __( 'If a longer description is necessary for the image, the <code>longdesc</code> attribute should be used.', 'screen-reader-check' ), $image->getLineNo() );
								$has_errors = true;
							}
						}
					}
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no images in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All images in the HTML code have valid <code>alt</code> attributes provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
