<?php
/**
 * StructuralHeadings test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the StructuralHeadings test.
 *
 * @since 1.0.0
 */
class StructuralHeadings extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'structural_headings';
		$this->title            = __( 'Structural elements for headings', 'screen-reader-check' );
		$this->description      = __( 'Headings must be marked through the structural HTML elements <code>h1</code> to <code>h6</code> and provide a quick overview of the page contents.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';

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
		$headings = $dom->find( 'h1,h2,h3,h4,h5,h6' );

		if ( count( $headings ) === 0 ) {
			$result['type'] = 'warning';
			$result['message_codes'][] = 'no_headings_in_content';
			$result['messages'][] = __( 'There are no headings in the HTML code provided. Headings should be used to give your page an easily understandable structure.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$sectioning_contents = $dom->find( 'section,article,nav,aside' );
		if ( count( $sectioning_contents ) === 0 ) {
			$headings_wrong = $this->headings_nested_incorrectly( $headings );
			if ( $headings_wrong ) {
				$result['message_codes'][] = 'headings_nested_incorrectly_no_sectioning_content';
				$result['messages'][] = __( 'The following headings are nested incorrectly:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $headings_wrong );
				$has_warnings = true;
			}
		} else {
			$h1_count = 0;
			$heading_groups = array();

			foreach ( $headings as $heading ) {
				$next_sectioning_ancestor = $this->get_next_ancestor( $heading, array( 'body', 'main', 'blockquote', 'figure', 'td', 'details', 'dialog', 'fieldset', 'section', 'article', 'nav', 'aside' ) );
				if ( $next_sectioning_ancestor ) {
					$group = $next_sectioning_ancestor->getNodePath();
				} else {
					$group = 'global';
				}

				if ( ! isset( $heading_groups[ $group ] ) ) {
					$heading_groups[ $group ] = array();
				}

				$heading_groups[ $group ][] = $heading;

				if ( 'h1' === $heading->getTagName() ) {
					$h1_count++;
				}
			}

			foreach ( $heading_groups as $heading_group ) {
				$headings_wrong = $this->headings_nested_incorrectly( $heading_group );
				if ( $headings_wrong ) {
					$result['message_codes'][] = 'headings_nested_incorrectly_sectioning_content';
					$result['messages'][] = __( 'The following headings are nested incorrectly:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $headings_wrong );
					$has_warnings = true;
				}
			}

			if ( $h1_count <= 1 ) {
				$articles = $dom->find( 'article' );
				if ( count( $articles ) >= 2 || count( $sectioning_contents ) > 3 ) {
					$result['message_codes'][] = 'single_h1_only';
					$result['messages'][] = __( 'There is only one <code>h1</code> heading in the entire page although it contains several separate areas of content.', 'screen-reader-check' );
					$has_errors = true;
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'The heading structure and resulting document outline of the HTML code appears to be correct.', 'screen-reader-check' );
		}

		return $result;
	}

	protected function headings_nested_incorrectly( $headings ) {
		$lines = array();
		$prev_number = null;
		$is_incorrect = false;

		foreach ( $headings as $heading ) {
			$tag_name = $heading->getTagName();

			$lines[] = $tag_name . ': ' . trim( $heading->text() );

			$number = intval( substr( $tag_name, 1, 1 ) );

			if ( ! $is_incorrect && null !== $prev_number && $prev_number < $number && $number - $prev_number !== 1 ) {
				$is_incorrect = true;
			}

			$prev_number = $number;
		}

		if ( $is_incorrect ) {
			return implode( "\n", $lines );
		}

		return false;
	}

	protected function get_next_ancestor( $element, $tag_names ) {
		if ( ! $element->hasParent() ) {
			return null;
		}

		$parent = $element->getParent();

		if ( is_string( $tag_names ) && $tag_names === $parent->getTagName() ) {
			return $parent;
		} elseif ( is_array( $tag_names ) && in_array( $parent->getTagName(), $tag_names, true ) ) {
			return $parent;
		}

		return $this->get_next_ancestor( $parent, $tag_names );
	}
}
