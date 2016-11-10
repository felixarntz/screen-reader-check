<?php
/**
 * HelpfulLinkTexts test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the HelpfulLinkTexts test.
 *
 * @since 1.0.0
 */
class HelpfulLinkTexts extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'helpful_link_texts';
		$this->title            = __( 'Helpful Link Texts', 'screen-reader-check' );
		$this->description      = __( 'The purpose of all links should be obvious from the link text or direct context of the link. When leading to non-HTML content, links should inform about the file type of the target.', 'screen-reader-check' );
		$this->guideline_title  = __( '2.4.4 Link Purpose', 'screen-reader-check' );
		$this->guideline_anchor = 'navigation-mechanisms-refs';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H30',
			'title'  => __( 'Providing link text that describes the purpose of a link for anchor elements', 'screen-reader-check' ),
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
		$links = $dom->find( 'a' );

		if ( count( $links ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no links in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$linktexts = array();

		foreach ( $links as $link ) {
			$linktext = '';

			$children = $link->getChildren( true );
			foreach ( $children as $child ) {
				if ( $child->isTextNode() ) {
					$linktext .= $child->text();
				} else {
					$text_content = $child->text();
					if ( $text_content ) {
						$linktext .= $text_content;
					} else {
						$alt = $child->getAttribute( 'alt' );
						if ( $alt ) {
							$linktext .= $alt;
						}
					}
				}
			}

			if ( empty( $linktext ) ) {
				$result['message_codes'][] = 'missing_link_text';
				$result['messages'][] = $this->wrap_message( __( 'The following link is missing a link text:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
				$has_errors = true;
			} else {
				$href = $link->getAttribute( 'href' );
				if ( $href ) {
					if ( false !== ( $other_href = array_search( $linktext, $linktexts, true ) ) && $other_href !== $href ) {
						$result['message_codes'][] = 'duplicate_link_text';
						$result['messages'][] = $this->wrap_message( __( 'The link text of the following link is already used for another link with a different target:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
						$has_errors = true;
					} else {
						$linktexts[ $href ] = $linktext;

						$linktext_words = preg_replace( '/[^ \w]+/', '', $linktext );
						$blacklist = array( 'continue reading', 'read more', 'more', 'continue' );
						if ( in_array( trim( strtolower( $linktext_words ) ), $blacklist ) ) {
							$result['message_codes'][] = 'non_descriptive_link_text';
							$result['messages'][] = $this->wrap_message( __( 'The link text of the following link does not properly describe its target:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
							$has_errors = true;
						} else {
							$non_html_content = $this->check_non_html_content( $href );
							if ( $non_html_content && ! preg_match( '/(' . implode( '|', $non_html_content ) . ')/i', $linktext ) ) {
								$result['message_codes'][] = 'missing_non_html_content_link_text';
								$result['messages'][] = $this->wrap_message( __( 'The link text of the following link does not properly describe the target file type although it is non-HTML content:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $link->outerHtml() ), $link->getLineNo() );
								$has_errors = true;
							}
						}
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All links in the HTML code have valid link texts provided.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Checks whether the href attribute is external content.
	 *
	 * If it is, the method returns an array of tokens to check for in the link text.
	 * Otherwise it returns false.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $href The href attribute.
	 * @return array|false Array of tokens if external content, false otherwise.
	 */
	protected function check_non_html_content( $href ) {
		if ( 0 === strpos( $href, 'mailto:' ) ) {
			return array( 'email', 'mail' );
		}

		if ( 0 === strpos( $href, 'tel:' ) ) {
			return array( 'telephone', 'phone', 'call' );
		}

		$list = array(
			'jpg|jpeg|jpe|gif|png|bmp|tiff|tif' => array( 'image', 'picture', 'graphic' ),
			'wmv|avi|divx|flv|mov|mpeg|mpg|mpe|ogv|webm|mkv' => array( 'video', 'motion picture', 'film', 'sequence' ),
			'txt|csv|css|js|rtx|rtf' => array( 'text', 'document' ),
			'mp3|m4a|wav|ogg|wma' => array( 'audio', 'song', 'track' ),
			'zip|rar|tar|gz|gzip|7z' => array( 'archive' ),
			'doc|docx|dotx|dotm' => array( 'document', 'word', 'office', 'microsoft' ),
			'xla|xls|xlt|xlw|xlsx|xlsm|xlsb|xltx|xltm|xlam' => array( 'spreadsheet', 'excel', 'office', 'microsoft' ),
			'pot|pps|ppt|pptx|pptm|ppsx|ppsm|potx|potm|ppam' => array( 'presentation', 'slides', 'powerpoint', 'office', 'microsoft' ),
			'odt' => array( 'document', 'openoffice' ),
			'ods' => array( 'spreadsheet', 'openoffice' ),
			'odp' => array( 'presentation', 'slides', 'openoffice' ),
			'pages' => array( 'document', 'pages', 'apple' ),
			'numbers' => array( 'spreadsheet', 'numbers', 'apple' ),
			'key' => array( 'presentation', 'slides', 'keynote', 'apple' ),
		);

		foreach ( $list as $extensions => $tokens ) {
			$matches = array();
			if ( preg_match( '/\.(' . $extensions . ')$/i', $href, $matches ) ) {
				return array_merge( array( $matches[1] ), $tokens );
			}
		}

		return false;
	}
}
