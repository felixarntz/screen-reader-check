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
	public static function parse( $html ) {
		$doc = new DOMDocument();

		$status = $doc->loadHTML( $html );
		if ( ! $status ) {
			return null;
		}

		return new Dom( $doc );
	}

	public function __construct( $domDocument, $domXPath = null ) {
		if ( ! $domXPath ) {
			$domXPath = new DOMXPath( $domDocument );
		}

		parent::__construct( $domDocument, $domXPath );
	}

	public function find( $selector, $single = false ) {
		$nodes = $this->parseNodes( $this->domXPath->evaluate( $this->parseSelector( $selector ) ) );

		if ( $single ) {
			if ( ! isset( $nodes[0] ) ) {
				return null;
			}
			return $nodes[0];
		}

		return $nodes;
	}

	public function getDocumentType() {
		return $this->domNode->doctype->name;
	}

	public function outerHtml() {
		return $this->domNode->saveHTML( $this->domNode );
	}

	public function innerHtml() {
		$html = '';

		foreach ( $this->domNode->childNodes as $node ) {
			$html .= $this->domNode->saveHTML( $node );
		}

		return $html;
	}

	public function getPrevious() {
		return null;
	}

	public function getNext() {
		return null;
	}

	public function getParent() {
		return null;
	}

	public function hasParent() {
		return false;
	}
}
