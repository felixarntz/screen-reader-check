<?php
/**
 * DeprecatedUsage test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the DeprecatedUsage test.
 *
 * @since 1.0.0
 */
class DeprecatedUsage extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'deprecated_usage';
		$this->title            = __( 'Avoiding Usage of Deprecated Elements and Attributes', 'screen-reader-check' );
		$this->description      = __( 'Elements and attributes that have been deprecated since HTML 4.01 or have never been part of any specification must not be used.', 'screen-reader-check' );
		$this->guideline_title  = __( '4.1.1 Parsing', 'screen-reader-check' );
		$this->guideline_anchor = 'ensure-compat-parses';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H88',
			'title'  => __( 'Using HTML according to spec', 'screen-reader-check' ),
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
		$elements = $dom->find( implode( ',', $this->get_deprecated_tags() ) );
		foreach ( $elements as $element ) {
			$result['message_codes'][] = 'deprecated_tag';
			$result['messages'][] = sprintf( __( 'The deprecated tag <code>%1$s</code> is used in line %2$s.', 'screen-reader-check' ), $element->getTagName(), $element->getLineNo() );
		}

		foreach ( $this->get_deprecated_attributes() as $deprecated_attribute ) {
			$elements = $dom->find( '[' . $deprecated_attribute . ']' );
			foreach ( $elements as $element ) {
				$result['message_codes'][] = 'deprecated_attribute';
				$result['messages'][] = sprintf( __( 'The deprecated attribute <code>%1$s</code> is used in line %2$s.', 'screen-reader-check' ), $deprecated_attribute, $element->getLineNo() );
			}
		}

		foreach ( $this->get_deprecated_attributes_with_blacklist() as $deprecated_attribute => $blacklist ) {
			$selectors = array();
			foreach ( $blacklist as $tag ) {
				$selectors[] = $tag . '[' . $deprecated_attribute . ']';
			}
			$elements = $dom->find( implode( ',', $selectors ) );
			foreach ( $elements as $element ) {
				$result['message_codes'][] = 'deprecated_attribute_with_tag';
				$result['messages'][] = sprintf( __( 'The attribute <code>%1$s</code> is deprecated with the tag <code>%2$s</code>, but used that way in line %3$s.', 'screen-reader-check' ), $deprecated_attribute, $element->getTagName(), $element->getLineNo() );
			}
		}

		foreach ( $this->get_deprecated_attributes_with_whitelist() as $deprecated_attribute => $whitelist ) {
			$elements = $dom->find( '[' . $deprecated_attribute . ']' );
			foreach ( $elements as $element ) {
				$tagname = $element->getTagName();
				if ( ! in_array( $tagname, $whitelist, true ) ) {
					$result['message_codes'][] = 'deprecated_attribute_with_tag';
					$result['messages'][] = sprintf( __( 'The attribute <code>%1$s</code> is deprecated with the tag <code>%2$s</code>, but used that way in line %3$s.', 'screen-reader-check' ), $deprecated_attribute, $tagname, $element->getLineNo() );
				}
			}
		}

		if ( empty( $result['message_codes'] ) ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'No usage of deprecated tags or attributes was found.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Returns a list of deprecated tags.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Deprecated tags.
	 */
	protected function get_deprecated_tags() {
		return array(
			'applet',
			'basefont',
			'blink',
			'center',
			'dir',
			'font',
			'isindex',
			'marqee',
			'menu',
			's',
			'strike',
			'u',
		);
	}

	/**
	 * Returns a list of deprecated attributes.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Deprecated attributes.
	 */
	protected function get_deprecated_attributes() {
		return array(
			'alink',
			'background',
			'bgcolor',
			'clear',
			'compact',
			'hspace',
			'language',
			'link',
			'noshade',
			'nowrap',
			'prompt',
			'start',
			'text',
			'version',
			'vlink',
			'vspace',
		);
	}

	/**
	 * Returns a list of deprecated attributes and the tags on which they are deprecated.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Deprecated attributes as keys, their respective blacklist array as values.
	 */
	protected function get_deprecated_attributes_with_blacklist() {
		return array(
			'border' => array( 'img', 'object' ),
			'height' => array( 'th', 'td' ),
			'size'   => array( 'hr' ),
			'type'   => array( 'li', 'ol', 'ul' ),
			'value'  => array( 'li' ),
			'width'  => array( 'hr', 'th', 'td', 'pre' ),
		);
	}

	/**
	 * Returns a list of deprecated attributes and the tags on which they are still allowed.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Deprecated attributes as keys, their respective whitelist array as values.
	 */
	protected function get_deprecated_attributes_with_whitelist() {
		return array(
			'align' => array( 'col', 'colgroup', 'tbody', 'td', 'tfoot', 'th', 'thead', 'tr' ),
		);
	}
}
