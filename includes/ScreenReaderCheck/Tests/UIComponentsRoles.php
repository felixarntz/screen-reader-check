<?php
/**
 * UIComponentsRoles test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the UIComponentsRoles test.
 *
 * @since 1.0.0
 */
class UIComponentsRoles extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'ui_components_roles';
		$this->title            = __( 'Proper Roles for UI Components', 'screen-reader-check' );
		$this->description      = __( 'In case non-semantic elements are used as buttons or other interface components, they should have proper role attributes.', 'screen-reader-check' );
		$this->guideline_title  = __( '4.1.2 Name, Role, Value', 'screen-reader-check' );
		$this->guideline_anchor = 'ensure-compat-rsv';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H91',
			'title'  => __( 'Using HTML form controls and links', 'screen-reader-check' ),
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
		$unsemantic_links = $dom->find( 'a[href="#"]' );

		if ( count( $unsemantic_links ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no non-semantically used <code>a</code> tags in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		foreach ( $unsemantic_links as $link ) {
			$role = $link->getAttribute( 'role' );
			if ( ! $role ) {
				$result['message_codes'][] = 'missing_role_attribute';
				$result['messages'][] = $this->wrap_message( __( 'The following non-semantically used <code>a</code> tag is missing a <code>role</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
				$has_errors = true;
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All non-semantically used <code>a</code> tags in the HTML code have valid <code>role</code> attributes provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
