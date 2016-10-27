<?php
/**
 * OrganizedSelectLists test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the OrganizedSelectLists test.
 *
 * @since 1.0.0
 */
class OrganizedSelectLists extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'organized_select_lists';
		$this->title            = __( 'Organized Select Lists', 'screen-reader-check' );
		$this->description      = __( 'Any select lists that use groups to separate their options must use <code>optgroup</code> to do so. Typographic characters must not be used to indicate groups.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H85',
			'title'  => __( 'Using OPTGROUP to group OPTION elements inside a SELECT', 'screen-reader-check' ),
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
		$selects = $dom->find( 'select' );

		if ( count( $selects ) === 0 ) {
			$result['type'] = 'info';
			$result['messages'][] = __( 'There are no select lists in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $selects as $select ) {
			$optgroups = $select->find( 'optgroup' );
			if ( count( $optgroups ) === 0 ) {
				$options = $select->find( 'option' );
				foreach ( $options as $option ) {
					$text = $option->text();
					if ( preg_match( '/^(&nbsp;|&rarr;|&gt;|\-|_)/', $text ) ) {
						$result['messages'][] = $this->wrap_message( __( 'The following select list uses typographic characters to indicate groups:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $select->outerHtml() ), $select->getLineNo() );
						$has_errors = true;
						break;
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['messages'][] = __( 'All select lists in the HTML code use valid markup.', 'screen-reader-check' );
		}

		return $result;
	}
}
