<?php
/**
 * DocumentLanguage test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the DocumentLanguage test.
 *
 * @since 1.0.0
 */
class DocumentLanguage extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'document_language';
		$this->title            = __( 'Document Language', 'screen-reader-check' );
		$this->description      = __( 'The main language of the document must be provided as attribute in the <code>html</code> tag.', 'screen-reader-check' );
		$this->guideline_title  = __( '3.1.1 Language of Page', 'screen-reader-check' );
		$this->guideline_anchor = 'meaning-doc-lang-id';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H57',
			'title'  => __( 'Using language attributes on the html element', 'screen-reader-check' ),
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
		$html = $dom->find( 'html', false, true );

		$has_errors = false;
		$has_warnings = false;

		$doctype = $dom->getDocumentType();
		if ( false !== strpos( $doctype, 'xhtml' ) ) {
			$xml_lang = $html->getAttribute( 'xml:lang' );
			if ( ! $xml_lang ) {
				$result['message_codes'][] = 'missing_xml_lang_attribute';
				$result['messages'][] = __( 'The <code>html</code> element is missing the <code>xml:lang</code> attribute.', 'screen-reader-check' );
				$has_errors = true;
			} elseif ( ! $this->is_lang_valid( $xml_lang ) ) {
				$result['message_codes'][] = 'invalid_xml_lang_attribute';
				$result['messages'][] = __( 'The <code>html</code> element has an invalid <code>xml:lang</code> attribute.', 'screen-reader-check' );
				$has_errors = true;
			}
		} else {
			$lang = $html->getAttribute( 'lang' );
			if ( ! $lang ) {
				$result['message_codes'][] = 'missing_lang_attribute';
				$result['messages'][] = __( 'The <code>html</code> element is missing the <code>lang</code> attribute.', 'screen-reader-check' );
				$has_errors = true;
			} elseif ( ! $this->is_lang_valid( $lang ) ) {
				$result['message_codes'][] = 'invalid_lang_attribute';
				$result['messages'][] = __( 'The <code>html</code> element has an invalid <code>lang</code> attribute.', 'screen-reader-check' );
				$has_errors = true;
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'The document language is properly provided through the <code>lang</code> attribute of the <code>html</code> element.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Checks whether a language attribute is valid.
	 *
	 * It must either be a two-letter language code or a locale code.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $lang The language attribute value.
	 * @return bool True if valid, false otherwise.
	 */
	protected function is_lang_valid( $lang ) {
		if ( preg_match( '/^([a-z]{2}|[a-z]{2}\-[A-Z]{2})$/', $lang ) ) {
			return true;
		}

		if ( in_array( $lang, array( 'zh-Hans', 'zh-Hant' ), true ) ) {
			return true;
		}

		return false;
	}
}
