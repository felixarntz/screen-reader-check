<?php
/**
 * ValidHTML test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the ValidHTML test.
 *
 * @since 1.0.0
 */
class ValidHTML extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'valid_html';
		$this->title            = __( 'Valid HTML', 'screen-reader-check' );
		$this->description      = __( 'HTML markup must be used correctly.', 'screen-reader-check' );
		$this->guideline_title  = __( '4.1.1 Parsing', 'screen-reader-check' );
		$this->guideline_anchor = 'ensure-compat-parses';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H74',
			'title'  => __( 'Ensuring that opening and closing tags are used according to specification', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H75',
			'title'  => __( 'Ensuring that Web pages are well-formed', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H88',
			'title'  => __( 'Using HTML according to spec', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H93',
			'title'  => __( 'Ensuring that <code>id</code> attributes are unique on a Web page', 'screen-reader-check' ),
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
		$doctype = $dom->getDocumentType();
		if ( 'unknown' === $doctype ) {
			$result['type'] = 'error';
			$result['message_codes'][] = 'error_missing_doctype';
			$result['messages'][] = __( 'No doctype has been declared for the page.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$issues = $this->w3c_validate( $dom->outerHtml() );
		if ( ! is_wp_error( $issues ) ) {
			foreach ( $issues as $issue ) {
				$skip = false;
				$skip_patterns = array(
					// Redundant roles should not be covered according to BITV.
					' role is unnecessary for element',
					' does not need a “role” attribute',
					// Alternative texts are covered in other tests.
					' must have an “alt” attribute',
					' is missing required attribute “alt”',
					// Document language is covered in another test.
					' document appears to be written in',
				);
				foreach ( $skip_patterns as $skip_pattern ) {
					if ( false !== strpos( $issue['message'], $skip_pattern ) ) {
						$skip = true;
						break;
					}
				}
				if ( $skip ) {
					continue;
				}

				if ( 'info' === $issue['type'] && ! empty( $issue['subType'] ) && 'warning' === $issue['subType'] ) {
					$has_warnings = true;
					$result['message_codes'][] = 'warning_' . str_replace( '-', '_', sanitize_title( $issue['message'] ) );
					$result['messages'][] = $this->wrap_message( $issue['message'] . '<br>' . $this->wrap_code( $issue['extract'] ), $issue['lastLine'] );
				} elseif ( 'error' === $issue['type'] ) {
					$has_errors = true;
					$result['message_codes'][] = 'error_' . str_replace( '-', '_', sanitize_title( $issue['message'] ) );
					$result['messages'][] = $this->wrap_message( $issue['message'] . '<br>' . $this->wrap_code( $issue['extract'] ), $issue['lastLine'] );
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'No invalid usage of HTML code was detected.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Makes a remote request to the W3C validator to validate HTML code.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $html HTML code to validate.
	 * @return array|WP_Error Array of issues on success, error object otherwise.
	 */
	protected function w3c_validate( $html ) {
		$postdata = array(
			'out' => 'json',
		);

		$boundary = wp_generate_password( 24 );

		$payload = '';
		foreach ( $postdata as $key => $value ) {
			$payload .= '--' . $boundary . "\r\n";
			$payload .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n\r\n";
			$payload .= $value . "\r\n";
		}

		$payload .= '--' . $boundary . "\r\n";
		$payload .= 'Content-Disposition: form-data; name="content"; filename="index.html"' . "\r\n";
		$payload .= "\r\n";
		$payload .= $html;
		$payload .= "\r\n";

		$payload .= '--' . $boundary . '--';

		$response = wp_remote_post( 'https://validator.w3.org/nu/', array(
			'headers' => array(
				'content-type' => 'multipart/form-data; boundary=' . $boundary,
			),
			'body'    => $payload,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! isset( $response['messages'] ) ) {
			return new WP_Error( 'invalid_response', __( 'Invalid response.', 'screen-reader-check' ) );
		}

		return $response['messages'];
	}
}
