<?php
/**
 * FormControlLabels test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the FormControlLabels test.
 *
 * @since 1.0.0
 */
class FormControlLabels extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'form_control_labels';
		$this->title            = __( 'Form Control Labels', 'screen-reader-check' );
		$this->description      = __( 'Labels for form controls must be properly connected to and displayed before their respective control.', 'screen-reader-check' );
		$this->guideline_title  = __( '3.3.2 Labels or Instructions', 'screen-reader-check' );
		$this->guideline_anchor = 'minimize-error-cues';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H44',
			'title'  => __( 'Using label elements to associate text labels with form controls', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H65',
			'title'  => __( 'Using the title attribute to identify form controls when the label element cannot be used', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H71',
			'title'  => __( 'Providing a description for groups of form controls using fieldset and legend elements', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H90',
			'title'  => __( 'Indicating required form controls using label or legend', 'screen-reader-check' ),
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
		$form_controls = $dom->find( 'select,input' );

		if ( count( $form_controls ) === 0 ) {
			$result['type'] = 'info';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no form controls in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		$has_errors = false;
		$has_warnings = false;

		$found = false;

		foreach ( $form_controls as $form_control ) {
			// Skip hidden inputs.
			if ( 'input' === $form_control->getTagName() && 'hidden' === $form_control->getAttribute( 'type' ) ) {
				continue;
			}

			$found = true;

			$id = $form_control->getAttribute( 'id' );
			$label = null;
			if ( $id ) {
				$label = $dom->find( 'label[for="' . $id . '"]', false, true );
			}

			if ( ! $label ) {
				$title = $form_control->getAttribute( 'title' );
				if ( ! $title ) {
					$result['message_codes'][] = 'missing_label';
					$result['messages'][] = $this->wrap_message( __( 'The following form control neither is connected to a <code>label</code> element nor provides a <code>title</code> attribute:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $form_control->outerHtml() ), $form_control->getLineNo() );
					$has_errors = true;
				}
			} else {
				if ( 'input' !== $form_control->getTagName() || ! in_array( $form_control->getAttribute( 'type' ), array( 'radio', 'checkbox' ), true ) ) {
					if ( $label->getLineNo() > $form_control->getLineNo() ) {
						$result['message_codes'][] = 'label_position_after_control';
						$result['messages'][] = $this->wrap_message( __( 'The <code>label</code> element for the following form control is incorrectly positioned after it:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $form_control->outerHtml() ), $form_control->getLineNo() );
						$has_errors = true;
					}
				}
			}
		}

		if ( ! $found ) {
			$result['type'] = 'info';
			$result['message_codes'][] = 'skipped';
			$result['messages'][] = __( 'There are no form controls in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
			return $result;
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All form controls in the HTML code have valid labels provided.', 'screen-reader-check' );
		}

		return $result;
	}
}
