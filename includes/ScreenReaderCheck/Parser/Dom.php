<?php
/**
 * Parser DOM class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Parser;

use DOMDocument;
use DOMXPath;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a DOM.
 *
 * @since 1.0.0
 */
class Dom extends Node {
	/**
	 * Parses HTML code into a DOM object.
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @param string $html The HTML code to parse.
	 * @return ScreenReaderCheck\Parser\Dom|null The DOM object, or null on parse error.
	 */
	public static function parse( $html ) {
		$doc = new DOMDocument();

		$status = $doc->loadHTML( $html );
		if ( ! $status ) {
			return null;
		}

		return new Dom( $doc );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param DOMDocument $domDocument The DOM document object.
	 * @param DOMXPath    $domXPath    Optional. The DOM XPath object. By default it is created from the DOM document.
	 */
	public function __construct( $domDocument, $domXPath = null ) {
		if ( ! $domXPath ) {
			$domXPath = new DOMXPath( $domDocument );
		}

		parent::__construct( $domDocument, $domXPath );
	}

	/**
	 * Runs a query-selector based search and returns the results.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $selector    The query selector.
	 * @param bool   $includeText Optional. Whether to include text nodes. Default false.
	 * @param bool   $single      Optional. Whether to only return a single result. Default false.
	 * @return array|Node|null Array of results, or a single node (or null if nothing found) depending on $single.
	 */
	public function find( $selector, $includeText = false, $single = false ) {
		$nodes = $this->parseNodes( $this->domXPath->evaluate( $this->parseSelector( $selector ) ), $includeText );

		if ( $single ) {
			if ( ! isset( $nodes[0] ) ) {
				return null;
			}
			return $nodes[0];
		}

		return $nodes;
	}

	/**
	 * Returns the document type for this DOM.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The document type.
	 */
	public function getDocumentType() {
		if ( 'html' === strtolower( $this->domNode->doctype->name ) ) {
			$id = $this->domNode->doctype->systemId;

			$standards = array(
				'xhtml11',
				'xhtml1',
				'xhtml',
				'html4',
				'html3',
			);

			foreach ( $standards as $standard ) {
				if ( false !== stripos( $id, $standard ) ) {
					return $standard;
				}
			}

			return 'html5';
		}

		return 'unknown';
	}

	/**
	 * Returns the outer HTML representation of this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The HTML output.
	 */
	public function outerHtml() {
		return $this->domNode->saveHTML( $this->domNode );
	}

	/**
	 * Returns the inner HTML of this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The HTML output.
	 */
	public function innerHtml() {
		$html = '';

		foreach ( $this->domNode->childNodes as $node ) {
			$html .= $this->domNode->saveHTML( $node );
		}

		return $html;
	}

	/**
	 * Returns the previous node.
	 *
	 * This overrides the regular Node method since a DOM cannot have a previous node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The previous DOM node, or null if there is no previous node.
	 */
	public function getPrevious() {
		return null;
	}

	/**
	 * Returns the next node.
	 *
	 * This overrides the regular Node method since a DOM cannot have a next node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The next DOM node, or null if there is no next node.
	 */
	public function getNext() {
		return null;
	}

	/**
	 * Returns the parent node.
	 *
	 * This overrides the regular Node method since a DOM cannot have a parent node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The parent DOM node, or null if there is no parent node.
	 */
	public function getParent() {
		return null;
	}

	/**
	 * Checks whether this node has a parent node.
	 *
	 * This overrides the regular Node method since a DOM cannot have a parent node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if a parent node exists, false otherwise.
	 */
	public function hasParent() {
		return false;
	}
}
