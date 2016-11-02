<?php
/**
 * TimingAdjustable test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the TimingAdjustable test.
 *
 * @since 1.0.0
 */
class TimingAdjustable extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'timing_adjustable';
		$this->title            = __( 'Timing Adjustable', 'screen-reader-check' );
		$this->description      = __( 'Contents must be shown without time limit, or at least there have to be controls to disable it or increase the duration. Links should be opened without delay.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.2.1 Timing Adjustable', 'screen-reader-check' );
		$this->guideline_anchor = 'time-limits-required-behaviors';
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
		$http_equiv_refresh = $dom->find( 'meta[http-equiv="refresh"]', false, true );
		if ( $http_equiv_refresh ) {
			$content = $http_equiv_refresh->getAttribute( 'content' );
			if ( null !== $content && 0 !== absint( $content ) ) {
				$result['messages'][] = $this->wrap_message( __( 'A meta tag with an invalid value for <code>http-equiv=&quot;refresh&quot;</code> was found:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $http_equiv_refresh->outerHtml() ), $http_equiv_refresh->getLineNo() );
				return $result;
			}
		}

		$result['type'] = 'success';
		$result['messages'][] = __( 'No problems were found in the HTML code.', 'screen-reader-check' );

		return $result;
	}
}
