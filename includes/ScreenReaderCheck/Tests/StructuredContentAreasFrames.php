<?php
/**
 * StructuredContentAreasFrames test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the StructuredContentAreasFrames test.
 *
 * @since 1.0.0
 */
class StructuredContentAreasFrames extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'structured_content_areas_frames';
		$this->title            = __( 'Structured Content Areas: Frames', 'screen-reader-check' );
		$this->description      = __( 'Frames must have descriptive title attributes.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.4.1 Bypass Blocks', 'screen-reader-check' );
		$this->guideline_anchor = 'navigation-mechanisms-skip';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H64',
			'title'  => __( 'Using the <code>title</code> attribute of the <code>frame</code> and <code>iframe</code> elements', 'screen-reader-check' ),
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
		$frames = $dom->find( 'frame,iframe' );

		if ( count( $frames ) === 0 ) {
			$result['type'] = 'info';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no frames in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$frame_requests = array();

		foreach ( $frames as $frame ) {
			$title = $frame->getAttribute( 'title' );

			if ( null === $title ) {
				$result['message_codes'][] = 'missing_title_attribute';
				$result['messages'][] = $this->wrap_message( __( 'The following frame is missing a <code>title</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $frame->outerHtml() ), $frame->getLineNo() );
				$has_errors = true;
			} elseif ( empty( $title ) ) {
				$src = $frame->getAttribute( 'src' );
				if ( is_string( $src ) ) {
					$sanitized_src = $this->sanitize_src( $src );

					$frame_type = $this->get_option( 'frame_type_' . $sanitized_src );
					if ( $frame_type ) {
						if ( 'content' === $frame_type ) {
							$result['message_codes'][] = 'empty_title_attribute_content';
							$result['messages'][] = $this->wrap_message( __( 'The following frame has an empty <code>title</code> attribute although it provides actual content:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $frame->outerHtml() ) . '<br>' . __( 'An empty <code>title</code> attribute is only acceptable for layout frames.', 'screen-reader-check' ), $frame->getLineNo() );
							$has_errors = true;
						}
					} else {
						if ( ! in_array( $sanitized_src, $frame_requests ) ) {
							$frame_requests[] = $sanitized_src;
							$result['request_data'][] = array(
								'slug'          => 'frame_type_' . $sanitized_src,
								'type'          => 'select',
								'label'         => __( 'Frame Type', 'screen-reader-check' ),
								'description'   => sprintf( __( 'Choose whether the frame %s is purely a layout frame or actually provides content.', 'screen-reader-check' ), $this->linkify_src( $src ) ),
								'options'       => array(
									array(
										'value'   => 'content',
										'label'   => __( 'Content frame', 'screen-reader-check' ),
									),
									array(
										'value'   => 'decorative',
										'label'   => __( 'Layout frame', 'screen-reader-check' ),
									),
								),
								'default'       => 'content',
							);
						}
					}
				}
			} else {
				$src = $frame->getAttribute( 'src' );
				if ( is_string( $src ) ) {
					if ( false !== strpos( $src, $title ) ) {
						$result['message_codes'][] = 'title_attribute_part_of_src';
						$result['messages'][] = $this->wrap_message( __( 'The following frame seems to have an auto-generated <code>title</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $frame->outerHtml() ) . '<br>' . __( 'The title should describe the frame in clear human language.', 'screen-reader-check' ), $frame->getLineNo() );
						$has_errors = true;
					} else {
						if ( preg_match( '/(top|right|bottom|left|outer|inner)/i', $src ) ) {
							$result['message_codes'][] = 'title_attribute_contains_position';
							$result['messages'][] = $this->wrap_message( __( 'The following frame uses the <code>title</code> attribute to describe the position of the frame:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $frame->outerHtml() ), $frame->getLineNo() );
							$has_errors = true;
						}
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All frames in the HTML code have valid <code>title</code> attributes provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
