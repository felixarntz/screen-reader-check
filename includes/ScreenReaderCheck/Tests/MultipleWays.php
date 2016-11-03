<?php
/**
 * MultipleWays test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the MultipleWays test.
 *
 * @since 1.0.0
 */
class MultipleWays extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'multiple_ways';
		$this->title            = __( 'Multiple ways', 'screen-reader-check' );
		$this->description      = __( 'There must be at least two alternative ways to access content, for example through navigation and search.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.4.5 Multiple Ways', 'screen-reader-check' );
		$this->guideline_anchor = 'navigation-mechanisms-mult-loc';
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
		$found = false;

		$forms = $dom->find( 'form' );
		foreach ( $forms as $form ) {
			$search = $form->find( 'input' );
			if ( count( $search ) === 1 ) {
				if ( in_array( $search->getAttribute( 'type' ), array( 'search' ), true )
					|| in_array( $search->getAttribute( 'id' ), array( 'search', 's' ), true )
					|| in_array( $search->getAttribute( 'name' ), array( 'search', 's' ), true )
					|| false !== strpos( $search->getAttribute( 'class' ), 'search' )
				) {
					$found = true;
					break;
				}

				if ( in_array( $form->getAttribute( 'id' ), array( 'search', 's' ), true )
					|| false !== strpos( $form->getAttribute( 'class' ), 'search' )
				) {
					$found = true;
					break;
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'warning';
			$result['message_codes'][] = 'missing_search_form';
			$result['messages'][] = __( 'No search form was detected on the page. It is recommended to provide such functionality.', 'screen-reader-check' );
			return $result;
		}

		$result['type'] = 'success';
		$result['message_codes'][] = 'success';
		$result['messages'][] = __( 'A search form was successfully detected on the page.', 'screen-reader-check' );

		return $result;
	}
}
