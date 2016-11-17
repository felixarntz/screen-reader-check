<?php
/**
 * StructuralQuotes test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the StructuralQuotes test.
 *
 * @since 1.0.0
 */
class StructuralQuotes extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'structural_quotes';
		$this->title            = __( 'Structural elements for quotes', 'screen-reader-check' );
		$this->description      = __( 'Quotes that are their own paragraph should be marked with the structural HTML element <code>blockquote</code>.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';
		$this->may_request_data = true;

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H49',
			'title'  => __( 'Using semantic markup to mark emphasized or special text', 'screen-reader-check' ),
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
		$blockquotes = $dom->find( 'blockquote' );

		$has_errors = false;
		$has_warnings = false;

		if ( count( $blockquotes ) === 0 ) {
			$has_blockquotes = $this->get_option( 'has_blockquotes' );
			if ( $has_blockquotes ) {
				if ( 'yes' === $has_blockquotes ) {
					$result['message_codes'][] = 'error_missing_blockquote_markup_for_quotes';
					$result['messages'][] = __( 'The page contains blockquotes that do not use proper blockquote markup.', 'screen-reader-check' );
					$has_errors = true;
				} else {
					$result['type'] = 'skipped';
					$result['message_codes'][] = 'skipped';
					$result['messages'][] = __( 'There are no blockquotes in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
					return $result;
				}
			} else {
				$result['request_data'][] = array(
					'slug'          => 'has_blockquotes',
					'type'          => 'select',
					'label'         => __( 'Quotes available', 'screen-reader-check' ),
					'description'   => __( 'Specify whether the page contains quotes which are their own paragraph.', 'screen-reader-check' ),
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

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All quotes in the HTML code use proper blockquote markup.', 'screen-reader-check' );
		}

		return $result;
	}
}
