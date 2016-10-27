<?php
/**
 * StructuralLists test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the StructuralLists test.
 *
 * @since 1.0.0
 */
class StructuralLists extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'structural_lists';
		$this->title            = __( 'Structural elements for lists', 'screen-reader-check' );
		$this->description      = __( 'Valid list markup, such as <code>ul</code>, <code>ol</code> and <code>dl</code>, should be used for lists on the page.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H48',
			'title'  => __( 'Using ol, ul and dl for lists or groups of links', 'screen-reader-check' ),
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
		$lists = $dom->find( 'ul,ol,dl' );
		$navs  = $dom->find( 'nav' );

		$has_errors = false;
		$has_warnings = false;

		if ( count( $lists ) === 0 ) {
			$has_lists = $this->get_option( 'has_lists' );
			if ( $has_lists ) {
				if ( 'yes' === $has_lists ) {
					$result['messages'][] = __( 'The page contains lists that do not use proper list markup.', 'screen-reader-check' );
					$has_errors = true;
				} elseif ( count( $navs ) === 0 ) {
					$result['type'] = 'info';
					$result['messages'][] = __( 'There are no lists in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
					return $result;
				}
			} else {
				$result['request_data'][] = array(
					'slug'          => 'has_lists',
					'type'          => 'select',
					'label'         => __( 'Lists available', 'screen-reader-check' ),
					'description'   => __( 'Specify whether the page contains lists.', 'screen-reader-check' ),
					'options'       => array(
						array(
							'value'   => 'yes',
							'label'   => __( 'Yes', 'screen-reader-check' ),
						),
						array(
							'value'   => 'no',
							'label'   => __( 'No', 'screen-reader-check' ),
						),
					),
					'default'       => 'yes',
				);
			}
		}

		foreach ( $navs as $nav ) {
			$links    = $nav->find( 'a' );
			$list_tag = $nav->find( 'ul,ol', false, true );
			if ( count( $links ) > 3 && ! $list_tag ) {
				$result['messages'][] = $this->wrap_message( __( 'The following menu does not use list markup although it contains more than three links', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $nav->outerHtml() ), $nav->getLineNo() );
				$has_errors = true;
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All lists in the HTML code use proper list markup.', 'screen-reader-check' );
		}

		return $result;
	}
}
