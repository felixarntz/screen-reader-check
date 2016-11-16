<?php
/**
 * DynamicallyInsertedContent test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the DynamicallyInsertedContent test.
 *
 * @since 1.0.0
 */
class DynamicallyInsertedContent extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'dynamically_inserted_content';
		$this->title            = __( 'Dynamically inserted content', 'screen-reader-check' );
		$this->description      = __( 'Content that is dynamically added (for example through AJAX) should appear at a relevant position in the page.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.2 Meaningful Sequence', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-sequence';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/SCR21',
			'title'  => __( 'Using functions of the Document Object Model (DOM) to add content to a page', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/SCR26',
			'title'  => __( 'Inserting dynamic content into the Document Object Model immediately following its trigger element', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/SCR37',
			'title'  => __( 'Creating Custom Dialogs in a Device Independent Way', 'screen-reader-check' ),
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
		$buttons = $dom->find( 'button' );

		if ( count( $buttons ) === 0 ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'No dynamic content was detected in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$found = false;

		$has_errors = false;
		$has_warnings = false;

		foreach ( $buttons as $button ) {
			$type = $button->getAttribute( 'type' );
			if ( $type && 'submit' === $type ) {
				continue;
			}

			$element_ids = array();
			$aria_controls = $button->getAttribute( 'aria-controls' );
			if ( $aria_controls ) {
				$element_ids = array_map( 'trim', explode( ' ', $aria_controls ) );
			} else {
				$elements = array();
				$data_target = $button->getAttribute( 'data-target' );
				if ( $data_target ) {
					$elements = array( $data_target );
				} else {
					$identifier = $this->get_button_identifier( $button );
					$button_controlled_elements = $this->get_option( 'button_controlled_elements_' . $identifier );
					if ( $button_controlled_elements ) {
						if ( 'NONE' !== trim( $button_controlled_elements ) ) {
							$elements = array_filter( array_map( 'trim', explode( ' ', $button_controlled_elements ) ) );
						}
					} else {
						$result['request_data'][] = array(
							'slug'          => 'button_controlled_elements_' . $identifier,
							'type'          => 'text',
							'label'         => __( 'Dynamic Content Control', 'screen-reader-check' ),
							'description'   => sprintf( __( 'If the button in line %s controls one or more specific elements, provide the element IDs, separated by a space. If the controlled elements do not have IDs, provide CSS class selectors (class name prefixed with a dot), that applies to them if possible. If the button is used for something else, enter &quot;NONE&quot;.', 'screen-reader-check' ), $button->getLineNo() ),
							'default'       => 'NONE',
						);
					}
				}

				if ( ! empty( $elements ) ) {
					$found = true;

					foreach ( $elements as $element ) {
						if ( '.' === substr( $element, 0, 1 ) ) {
							$result['message_codes'][] = 'warning_missing_aria_controls_with_id';
							$result['messages'][] = $this->wrap_message( sprintf( __( 'The element with the selector %s must receive a unique ID, and then the following button should have an <code>aria-controls</code> attribute with that ID to indicate that their interactive relation:', 'screen-reader-check' ), $element ) . '<br>' . $this->wrap_code( $button->outerHtml() ), $button->getLineNo() );
							$has_warnings = true;
						} else {
							if ( '#' === substr( $element, 0, 1 ) ) {
								$element = substr( $element, 1 );
							}
							$element_ids[] = $element;

							$result['message_codes'][] = 'warning_missing_aria_controls';
							$result['messages'][] = $this->wrap_message( sprintf( __( 'The following button should have an <code>aria-controls</code> attribute with the ID of the element %s to indicate that their interactive relation:', 'screen-reader-check' ), '#' . $element ) . '<br>' . $this->wrap_code( $button->outerHtml() ), $button->getLineNo() );
							$has_warnings = true;
						}
					}
				}
			}

			if ( empty( $element_ids ) ) {
				continue;
			}

			$found = true;

			foreach ( $element_ids as $element_id ) {
				$element = $dom->find( '#' . $element_id, false, true );
				if ( ! $element ) {
					$result['message_codes'][] = 'error_dynamic_element_not_exist';
					$result['messages'][] = $this->wrap_message( sprintf( __( 'The element with the ID %s does not exist although it is controlled by the following button:', 'screen-reader-check' ), $element_id ) . '<br>' . $this->wrap_code( $button->outerHtml() ), $button->getLineNo() );
					$has_errors = true;
				} else {
					if ( ! $this->is_element_following( $button, $element_id ) ) {
						$identifier = $this->get_button_identifier( $button );

						$valid_focus_change = $this->get_option( 'valid_focus_change_' . $identifier . '_for_id_' . $element_id );
						if ( $valid_focus_change ) {
							if ( 'no' === $valid_focus_change ) {
								$result['message_codes'][] = 'error_dynamic_element_focus_not_adjusted';
								$result['messages'][] = $this->wrap_message( sprintf( __( 'The focus for the element with ID %s is not adjusted accordingly when it is toggled by the following button:', 'screen-reader-check' ), $element_id ) . '<br>' . $this->wrap_code( $button->outerHtml() ), $button->getLineNo() );
								$has_errors = true;
							}
						} else {
							$result['request_data'][] = array(
								'slug'          => 'valid_focus_change_' . $identifier . '_for_id_' . $element_id,
								'type'          => 'select',
								'label'         => __( 'Focus Change', 'screen-reader-check' ),
								'description'   => sprintf( __( 'Is the focus for the element with ID %1$s adjusted accordingly when it is toggled by the button in line %2$s?', 'screen-reader-check' ), $element_id, $button->getLineNo() ),
								'options'       => array(
									array(
										'value'   => 'yes',
										'label'   => __( 'Yes', 'screen-reader-check' ),
									),
									array(
										'value'   => 'no',
										'label'   => __( 'No', 'screen-reader-check' ),
									),
								),
								'default'       => 'no',
							);
						}
					}
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'skipped';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'No dynamic content was detected in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All detected dynamic content is added properly.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Parses a button into a string.
	 *
	 * The string is supposed to uniquely identify the button in the best way possible.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param ScreenReaderCheck\Parser\Node $button The button element.
	 * @return string The sanitize button identifier.
	 */
	protected function get_button_identifier( $button ) {
		$id = $button->getAttribute( 'id' );
		if ( $id ) {
			return 'id_' . $id;
		}

		$name = $button->getAttribute( 'name' );
		if ( $name ) {
			return 'name_' . $name;
		}

		return 'line_' . $button->getLineNo();
	}

	/**
	 * Checks whether an element with a given ID is "close" to the provided button element.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param ScreenReaderCheck\Parser\Node $button     The button element.
	 * @param string                        $element_id The element's unique ID.
	 * @return bool True if the element is close following, false otherwise.
	 */
	protected function is_element_following( $button, $element_id ) {
		$next = $button->getNext();
		if ( $next && ! $next->isTextNode() ) {
			if ( $element_id === $next->getAttribute( 'id' ) ) {
				return true;
			}

			$child_of_next = $next->find( '#' . $element_id, false, true );
			if ( $child_of_next ) {
				return true;
			}
		} else {
			$parent = $button->getParent();
			$next = $parent->getNext();

			if ( $next && ! $next->isTextNode() ) {
				if ( $element_id === $next->getAttribute( 'id' ) ) {
					return true;
				}

				$child_of_next = $next->find( '#' . $element_id, false, true );
				if ( $child_of_next ) {
					return true;
				}
			}
		}

		return false;
	}
}
