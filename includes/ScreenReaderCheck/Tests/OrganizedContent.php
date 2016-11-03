<?php
/**
 * OrganizedContent test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the OrganizedContent test.
 *
 * @since 1.0.0
 */
class OrganizedContent extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'organized_content';
		$this->title            = __( 'Organized Content', 'screen-reader-check' );
		$this->description      = __( 'Paragraphs and groups of form controls must be marked by appropriate structural HTML elements. To highlight parts of text, <code>strong</code> or <code>em</code> must be used.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H49',
			'title'  => __( 'Using semantic markup to mark emphasized or special text', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H71',
			'title'  => __( 'Providing a description for groups of form controls using fieldset and legend elements', 'screen-reader-check' ),
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
		$has_errors = false;
		$has_warnings = false;

		$double_breaks = $dom->find( 'br' );
		foreach ( $double_breaks as $last_break ) {
			$first_break = $last_break->getPrevious();
			if ( 'br' !== $first_break->getTagName() ) {
				continue;
			}

			$result['message_codes'][] = 'misuse_of_br_tag';
			$result['messages'][] = $this->wrap_message( __( 'Actual paragraph markup must be used instead of the following occurrence of two <code>br</code> tags:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $first_break->outerHtml() . $last_break->outerHtml() ), $first_break->getLineNo() );
			$has_errors = true;
		}

		$bolds = $dom->find( 'b' );
		foreach ( $bolds as $bold ) {
			$result['message_codes'][] = 'misuse_of_b_tag';
			$result['messages'][] = $this->wrap_message( __( 'The following content is highlighted using the old <code>b</code> tag and thus should use <code>strong</code> instead:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $bold->outerHtml() ), $bold->getLineNo() );
			$has_errors = true;
		}

		$italics = $dom->find( 'i' );
		foreach ( $italics as $italic ) {
			// Skip if using aria-hidden (probably an icon then).
			if ( $italic->hasAttribute( 'aria-hidden' ) ) {
				continue;
			}

			// Skip if no content (probably an icon then).
			if ( ! $italic->text() ) {
				continue;
			}

			// Skip if using an iconfont class.
			$iconfont_classes = $this->get_global_option( 'iconfont' );
			if ( $iconfont_classes ) {
				$classes = $italic->getAttribute( 'class' );
				if ( $classes ) {
					$iconfont_classes = explode( ' ', $iconfont_classes );
					$is_icon = false;
					foreach ( $iconfont_classes as $iconfont_class ) {
						if ( false !== strpos( $classes, $iconfont_class ) ) {
							$is_icon = true;
							break;
						}
					}
					if ( $is_icon ) {
						continue;
					}
				}
			}
			$result['message_codes'][] = 'misuse_of_i_tag';
			$result['messages'][] = $this->wrap_message( __( 'The following content is highlighted using the old <code>i</code> tag and thus should use <code>em</code> instead:', 'screen-reader-check' ) . '<br>' . $this->wrap_code( $italic->outerHtml() ), $italic->getLineNo() );
			$has_errors = true;
		}

		$forms = $dom->find( 'form' );
		foreach ( $forms as $form ) {
			$fieldsets = $form->find( 'fieldset' );
			if ( count( $fieldsets ) === 0 ) {
				$radios = $form->find( 'input[type="radio"]' );
				if ( count( $radios ) > 0 ) {
					$radio_groups = array();
					foreach ( $radios as $radio ) {
						$parent = $radio->getParent();
						if ( 'label' === $parent->getTagName() ) {
							$parent = $parent->getParent();
						}

						$group = $parent->getNodePath();
						if ( ! isset( $radio_groups[ $group ] ) ) {
							$radio_groups[ $group ] = array();
						}
						$radio_groups[ $group ][] = $radio;
					}

					foreach ( $radio_groups as $radio_group ) {
						if ( count( $radio_group ) <= 1 ) {
							continue;
						}

						$code = array();
						$lineno = $radio_group[0]->getLineNo();
						foreach ( $radio_group as $radio_group_item ) {
							$code[] = $this->wrap_code( $radio_group_item->outerHtml() );
						}
						$code = implode( '<br>', $code );

						$result['message_codes'][] = 'missing_fieldset_for_radio_group';
						$result['messages'][] = $this->wrap_message( __( 'The following set of radio buttons should be properly grouped using <code>fieldset</code>:', 'screen-reader-check' ) . '<br>' . $code, $lineno );
						$has_errors = true;
					}
				}

				$checkboxes = $form->find( 'input[type="checkbox"]' );
				if ( count( $checkboxes ) > 0 ) {
					$checkbox_groups = array();
					foreach ( $checkboxes as $checkbox ) {
						$parent = $checkbox->getParent();
						if ( 'label' === $parent->getTagName() ) {
							$parent = $parent->getParent();
						}

						$group = $parent->getNodePath();
						if ( ! isset( $checkbox_groups[ $group ] ) ) {
							$checkbox_groups[ $group ] = array();
						}
						$checkbox_groups[ $group ][] = $checkbox;
					}

					foreach ( $checkbox_groups as $checkbox_group ) {
						if ( count( $checkbox_group ) <= 1 ) {
							continue;
						}

						$code = array();
						$lineno = $checkbox_group[0]->getLineNo();
						foreach ( $checkbox_group as $checkbox_group_item ) {
							$code[] = $this->wrap_code( $checkbox_group_item->outerHtml() );
						}
						$code = implode( '<br>', $code );

						$result['message_codes'][] = 'missing_fieldset_for_checkbox_group';
						$result['messages'][] = $this->wrap_message( __( 'The following set of checkboxes should be properly grouped using <code>fieldset</code>:', 'screen-reader-check' ) . '<br>' . $code, $lineno );
						$has_errors = true;
					}
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'No invalid usages of tags or lack of structure have been found.', 'screen-reader-check' );
		}

		return $result;
	}
}
