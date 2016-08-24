<?php
/**
 * Util class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

use ScreenReaderCheck\Parser\Dom;

defined( 'ABSPATH' ) || exit;

/**
 * This class contains static utility methods.
 *
 * @since 1.0.0
 */
class Util {
	/**
	 * Downloads the HTML code from a given URL.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $url The URL to download HTML from.
	 * @return string The HTML code on success, an empty string on failure.
	 */
	public static function fetch_html_from_url( $url ) {
		$response = wp_remote_get( $url, array() );
		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Parses a HTML string into a DOM object.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $html The HTML code to parse.
	 * @return ScreenReaderCheck\Parser\Dom|null The DOM object, or null on parse error.
	 */
	public static function parse_html( $html ) {
		libxml_use_internal_errors( true );

		return Dom::parse( $html );
	}

	/**
	 * Returns the content of the title tag in a given HTML code.
	 *
	 * If this method fails, it can be assumed that the HTML code cannot be parsed.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $html The HTML code to extract the title from.
	 * @return string The title on success, an empty string on failure.
	 */
	public static function get_html_title( $html ) {
		$dom = self::parse_html( $html );
		if ( ! $dom ) {
			return '';
		}

		$title_tag = $dom->find( 'title', true );
		if ( ! $title_tag ) {
			return '';
		}

		return trim( $title_tag->text() );
	}

	/**
	 * Transforms an array representation of a test result into an actual test result object.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param array $args Test result arguments.
	 * @return ScreenReaderCheck\TestResult The test result object.
	 */
	public static function result_args_to_result( $args ) {
		return new TestResult( $args );
	}
}
