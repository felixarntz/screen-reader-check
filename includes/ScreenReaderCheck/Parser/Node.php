<?php
/**
 * Parser node class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Parser;

use DOMXPath;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents a node in the DOM.
 *
 * @since 1.0.0
 */
class Node {
	protected $domNode;

	protected $domXPath;

	public function __construct( $domNode, $domXPath = null ) {
		if ( ! $domXPath ) {
			$domXPath = new DOMXPath( $domNode->ownerDocument );
		}

		$this->domNode = $domNode;
		$this->domXPath = $domXPath;
	}

	public function find( $selector, $single = false ) {
		$nodes = $this->parseNodes( $this->domXPath->evaluate( $this->parseSelector( $selector ), $this->domNode ) );

		if ( $single ) {
			if ( ! isset( $nodes[0] ) ) {
				return null;
			}
			return $nodes[0];
		}

		return $nodes;
	}

	public function outerHtml() {
		return $this->domNode->ownerDocument->saveHTML( $this->domNode );
	}

	public function innerHtml() {
		$html = '';

		foreach ( $this->domNode->childNodes as $node ) {
			$html .= $this->domNode->ownerDocument->saveHTML( $node );
		}

		return $html;
	}

	public function text() {
		return $this->domNode->textContent;
	}

	public function getAttributes() {
		$attributes = array();

		for ( $i = 0; $i < $this->domNode->attributes->length; $i++ ) {
			$item = $this->domNode->attributes->item( $i );
			$attributes[ $item->name ] = $item->value;
		}

		return $attributes;
	}

	public function getAttribute( $name ) {
		$item = $this->domNode->attributes->getNamedItem( $name );
		if ( ! $item ) {
			return null;
		}

		return $item->value;
	}

	public function hasAttribute( $name ) {
		return (bool) $this->domNode->attributes->getNamedItem( $name );
	}

	public function getPrevious() {
		if ( ! $this->domNode->previousSibling ) {
			return null;
		}

		return $this->parseNode( $this->domNode->previousSibling );
	}

	public function getNext() {
		if ( ! $this->domNode->nextSibling ) {
			return null;
		}

		return $this->parseNode( $this->domNode->nextSibling );
	}

	public function getParent() {
		if ( ! $this->domNode->parentNode ) {
			return null;
		}

		return $this->parseNode( $this->domNode->parentNode );
	}

	public function hasParent() {
		return (bool) $this->domNode->parentNode;
	}

	public function getChildren() {
		return $this->parseNodes( $this->domNode->childNodes );
	}

	public function hasChildren() {
		return (bool) $this->getChildren();
	}

	public function __call( $method, $args ) {
		switch ( $method ) {
			case 'getLineNo':
			case 'getNodePath':
			case 'hasAttributes':
			case 'isSameNode':
				return call_user_func_array( array( $this->domNode, $method ), $args );
		}
	}

	protected function parseNodes( $domNodeList ) {
		if ( ! is_a( $domNodeList, 'DOMNodeList' ) ) {
			return array();
		}

		$nodes = array();

		for ( $i = 0; $i < $domNodeList->length; $i++ ) {
			$item = $domNodeList->item( $i );
			if ( $item->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			$nodes[] = $this->parseNode( $item );
		}

		return $nodes;
	}

	protected function parseNode( $domNode ) {
		return new Node( $domNode, $this->domXPath );
	}

	protected function parseSelector( $selector ) {
		/* The following code comes from https://github.com/tj/php-selector/blob/master/selector.inc */

		$selector = preg_replace('/\s*>\s*/', '>', $selector);
		$selector = preg_replace('/\s*~\s*/', '~', $selector);
		$selector = preg_replace('/\s*\+\s*/', '+', $selector);
		$selector = preg_replace('/\s*,\s*/', ',', $selector);
		$selectors = preg_split('/\s+(?![^\[]+\])/', $selector);
		foreach ($selectors as &$selector) {
			// ,
			$selector = preg_replace('/,/', '|descendant-or-self::', $selector);
			// input:checked, :disabled, etc.
			$selector = preg_replace('/(.+)?:(checked|disabled|required|autofocus)/', '\1[@\2="\2"]', $selector);
			// input:autocomplete, :autocomplete
			$selector = preg_replace('/(.+)?:(autocomplete)/', '\1[@\2="on"]', $selector);
			// input:button, input:submit, etc.
			$selector = preg_replace('/:(text|password|checkbox|radio|button|submit|reset|file|hidden|image|datetime|datetime-local|date|month|time|week|number|range|email|url|search|tel|color)/', 'input[@type="\1"]', $selector);
			// foo[id]
			$selector = preg_replace('/(\w+)\[([_\w-]+[_\w\d-]*)\]/', '\1[@\2]', $selector);
			// [id]
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)\]/', '*[@\1]', $selector);
			// foo[id=foo]
			$selector = preg_replace('/\[([_\w-]+[_\w\d-]*)=[\'"]?(.*?)[\'"]?\]/', '[@\1="\2"]', $selector);
			// [id=foo]
			$selector = preg_replace('/^\[/', '*[', $selector);
			// div#foo
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\#([_\w-]+[_\w\d-]*)/', '\1[@id="\2"]', $selector);
			// #foo
			$selector = preg_replace('/\#([_\w-]+[_\w\d-]*)/', '*[@id="\1"]', $selector);
			// div.foo
			$selector = preg_replace('/([_\w-]+[_\w\d-]*)\.([_\w-]+[_\w\d-]*)/', '\1[contains(concat(" ",@class," ")," \2 ")]', $selector);
			// .foo
			$selector = preg_replace('/\.([_\w-]+[_\w\d-]*)/', '*[contains(concat(" ",@class," ")," \1 ")]', $selector);
			// div:first-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):first-child/', '*/\1[position()=1]', $selector);
			// div:last-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):last-child/', '*/\1[position()=last()]', $selector);
			// :first-child
			$selector = str_replace(':first-child', '*/*[position()=1]', $selector);
			// :last-child
			$selector = str_replace(':last-child', '*/*[position()=last()]', $selector);
			// :nth-last-child
			$selector = preg_replace('/:nth-last-child\((\d+)\)/', '[position()=(last() - (\1 - 1))]', $selector);
			// div:nth-child
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):nth-child\((\d+)\)/', '*/*[position()=\2 and self::\1]', $selector);
			// :nth-child
			$selector = preg_replace('/:nth-child\((\d+)\)/', '*/*[position()=\1]', $selector);
			// :contains(Foo)
			$selector = preg_replace('/([_\w-]+[_\w\d-]*):contains\((.*?)\)/', '\1[contains(string(.),"\2")]', $selector);
			// >
			$selector = preg_replace('/>/', '/', $selector);
			// ~
			$selector = preg_replace('/~/', '/following-sibling::', $selector);
			// +
			$selector = preg_replace('/\+([_\w-]+[_\w\d-]*)/', '/following-sibling::\1[position()=1]', $selector);
			$selector = str_replace(']*', ']', $selector);
			$selector = str_replace(']/*', ']', $selector);
		}
		// ' '
		$selector = implode('/descendant::', $selectors);
		$selector = 'descendant-or-self::' . $selector;
		// :scope
		$selector = preg_replace('/(((\|)?descendant-or-self::):scope)/', '.\3', $selector);
		// $element
		$sub_selectors = explode(',', $selector);
		foreach ($sub_selectors as $key => $sub_selector) {
			$parts = explode('$', $sub_selector);
			$sub_selector = array_shift($parts);
			if (count($parts) && preg_match_all('/((?:[^\/]*\/?\/?)|$)/', $parts[0], $matches)) {
				$results = $matches[0];
				$results[] = str_repeat('/..', count($results) - 2);
				$sub_selector .= implode('', $results);
			}
			$sub_selectors[$key] = $sub_selector;
		}
		$selector = implode(',', $sub_selectors);

		return $selector;
	}
}
