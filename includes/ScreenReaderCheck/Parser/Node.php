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
	/**
	 * The DOM node object.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var DOMNode
	 */
	protected $domNode;

	/**
	 * The DOM XPath object.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var DOMXPath
	 */
	protected $domXPath;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param DOMNode  $domNode  The DOM node object.
	 * @param DOMXPath $domXPath Optional. The DOM XPath object. By default it is created from the DOM node.
	 */
	public function __construct( $domNode, $domXPath = null ) {
		if ( ! $domXPath ) {
			$domXPath = new DOMXPath( $domNode->ownerDocument );
		}

		$this->domNode = $domNode;
		$this->domXPath = $domXPath;
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
		$nodes = $this->parseNodes( $this->domXPath->evaluate( $this->parseSelector( $selector ), $this->domNode ), $includeText );

		if ( $single ) {
			if ( ! isset( $nodes[0] ) ) {
				return null;
			}
			return $nodes[0];
		}

		return $nodes;
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
		return $this->domNode->ownerDocument->saveHTML( $this->domNode );
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
			$html .= $this->domNode->ownerDocument->saveHTML( $node );
		}

		return $html;
	}

	/**
	 * Returns the inner text of this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string The text output.
	 */
	public function text() {
		return $this->domNode->textContent;
	}

	/**
	 * Returns the tag name of this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Tag name.
	 */
	public function getTagName() {
		return strtolower( $this->domNode->tagName );
	}

	/**
	 * Returns all attributes of this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Array of attributes, as $name => $value pairs.
	 */
	public function getAttributes() {
		$attributes = array();

		for ( $i = 0; $i < $this->domNode->attributes->length; $i++ ) {
			$item = $this->domNode->attributes->item( $i );
			$attributes[ $item->name ] = $item->value;
		}

		return $attributes;
	}

	/**
	 * Returns a single attribute value.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name Name of the attribute to return.
	 * @return string|null Attribute value, or null if attribute does not exist.
	 */
	public function getAttribute( $name ) {
		$item = $this->domNode->attributes->getNamedItem( $name );
		if ( ! $item ) {
			return null;
		}

		return $item->value;
	}

	/**
	 * Checks whether a specific attribute exists on this node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $name Name of the attribute to check for.
	 * @return bool True if the attribute exists, false otherwise.
	 */
	public function hasAttribute( $name ) {
		return (bool) $this->domNode->attributes->getNamedItem( $name );
	}

	/**
	 * Returns the previous node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The previous DOM node, or null if there is no previous node.
	 */
	public function getPrevious() {
		if ( ! $this->domNode->previousSibling ) {
			return null;
		}

		return $this->parseNode( $this->domNode->previousSibling );
	}

	/**
	 * Returns the next node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The next DOM node, or null if there is no next node.
	 */
	public function getNext() {
		if ( ! $this->domNode->nextSibling ) {
			return null;
		}

		return $this->parseNode( $this->domNode->nextSibling );
	}

	/**
	 * Returns the parent node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return ScreenReaderCheck\Parser\Node|null The parent DOM node, or null if there is no parent node.
	 */
	public function getParent() {
		if ( ! $this->domNode->parentNode ) {
			return null;
		}

		return $this->parseNode( $this->domNode->parentNode );
	}

	/**
	 * Checks whether this node has a parent node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if a parent node exists, false otherwise.
	 */
	public function hasParent() {
		return (bool) $this->domNode->parentNode;
	}

	/**
	 * Returns the child nodes.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $includeText Optional. Whether to include text nodes. Default false.
	 * @return array Array of ScreenReaderCheck\Parser\Node objects.
	 */
	public function getChildren( $includeText = false ) {
		return $this->parseNodes( $this->domNode->childNodes, $includeText );
	}

	/**
	 * Checks whether this node has any child nodes.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $includeText Optional. Whether to include text nodes. Default false.
	 * @return bool True if child nodes exist, false otherwise.
	 */
	public function hasChildren( $includeText = false ) {
		return (bool) $this->getChildren( $includeText );
	}

	/**
	 * Checks whether this node is a text node.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return bool True if it is a text node, false otherwise.
	 */
	public function isTextNode() {
		return $this->domNode->nodeType !== XML_ELEMENT_NODE;
	}

	/**
	 * Magic caller.
	 *
	 * Exposes some methods of the internal DOMNode object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method Method name.
	 * @param array  $args   Method arguments.
	 * @return mixed Method result.
	 */
	public function __call( $method, $args ) {
		switch ( $method ) {
			case 'getLineNo':
			case 'getNodePath':
			case 'hasAttributes':
			case 'isSameNode':
				return call_user_func_array( array( $this->domNode, $method ), $args );
		}
	}

	/**
	 * Parses a node list into an array of Node objects.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param DOMNodeList $domNodeList Node list object.
	 * @param bool        $includeText Optional. Whether to include text nodes. Default false.
	 * @return array Array of ScreenReaderCheck\Parser\Node objects.
	 */
	protected function parseNodes( $domNodeList, $includeText = false ) {
		if ( ! is_a( $domNodeList, 'DOMNodeList' ) ) {
			return array();
		}

		$nodes = array();

		for ( $i = 0; $i < $domNodeList->length; $i++ ) {
			$item = $domNodeList->item( $i );
			if ( ! $includeText && $item->nodeType !== XML_ELEMENT_NODE ) {
				continue;
			}

			$nodes[] = $this->parseNode( $item );
		}

		return $nodes;
	}

	/**
	 * Parses an internal node object into a Node object.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param DOMNode $domNode The internal node object to parse.
	 * @return ScreenReaderCheck\Parser\Node The resulting DOM node.
	 */
	protected function parseNode( $domNode ) {
		return new Node( $domNode, $this->domXPath );
	}

	/**
	 * Parses a query selector into an XPath query string.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $selector Query selector string.
	 * @return string XPath query string.
	 */
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
