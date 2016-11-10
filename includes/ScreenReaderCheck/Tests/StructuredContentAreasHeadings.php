<?php
/**
 * StructuredContentAreasHeadings test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the StructuredContentAreasHeadings test.
 *
 * @since 1.0.0
 */
class StructuredContentAreasHeadings extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'structured_content_areas_headings';
		$this->title            = __( 'Structured Content Areas: Headings', 'screen-reader-check' );
		$this->description      = __( 'Different content areas, such as navigation, search or main content, should have section headings or be reachable through skip links.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.4.1 Bypass Blocks', 'screen-reader-check' );
		$this->guideline_anchor = 'navigation-mechanisms-skip';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H42',
			'title'  => __( 'Using h1-h6 to identify headings', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H69',
			'title'  => __( 'Providing heading elements at the beginning of each section of content', 'screen-reader-check' ),
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
		$sectioning_contents = $dom->find( 'section,article,nav,aside,main' );

		if ( count( $sectioning_contents ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no sectioning content tags in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$skip_links = $this->find_skip_links( $dom );

		$has_errors = false;
		$has_warnings = false;

		foreach ( $sectioning_contents as $sectioning_content ) {
			$heading = $sectioning_content->find( 'h1,h2,h3,h4,h5,h6', false, true );
			if ( ! $heading ) {
				$id = $sectioning_content->getAttribute( 'id' );
				if ( ! $id ) {
					$result['message_codes'][] = 'missing_heading_or_skip_link';
					$result['messages'][] = sprintf( __( 'The %1$s in line %2$s has neither a heading nor a skip link leading to it.', 'screen-reader-check' ), $sectioning_content->getTagName(), $sectioning_content->getLineNo() );
					$has_errors = true;
				} else {
					$found_skip_link = false;
					foreach ( $skip_links as $skip_link ) {
						$href = $skip_link->getAttribute( 'href' );
						if ( $href && $href === '#' . $id ) {
							$found_skip_link = true;
							break;
						}
					}
					if ( ! $found_skip_link ) {
						$result['message_codes'][] = 'missing_heading_or_skip_link';
						$result['messages'][] = sprintf( __( 'The %1$s in line %2$s has neither a heading nor a skip link leading to it.', 'screen-reader-check' ), $sectioning_content->getTagName(), $sectioning_content->getLineNo() );
						$has_errors = true;
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All sectioning content tags in the HTML code have valid headings or skip links provided.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Finds skip links in the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param ScreenReaderCheck\Parser\Dom $dom The DOM object to search in.
	 * @return array Array of skip link nodes.
	 */
	protected function find_skip_links( $dom ) {
		$skip_links = $dom->find( 'a.skip-link' );
		if ( count( $skip_links ) > 0 ) {
			return $skip_links;
		}

		$links = $dom->find( 'a[href]' );

		$skip_links = array();

		foreach ( $links as $link ) {
			$href = $link->getAttribute( 'href' );
			if ( 0 === strpos( $href, '#' ) ) {
				$skip_links[] = $link;
			} else {
				break;
			}
		}

		return $skip_links;
	}
}
