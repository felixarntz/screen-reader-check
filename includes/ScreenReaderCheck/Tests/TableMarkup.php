<?php
/**
 * TableMarkup test class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck\Tests;

use ScreenReaderCheck\Test;

defined( 'ABSPATH' ) || exit;

/**
 * This class represents the TableMarkup test.
 *
 * @since 1.0.0
 */
class TableMarkup extends Test {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		parent::__construct();

		$this->slug             = 'table_markup';
		$this->title            = __( 'Valid table markup', 'screen-reader-check' );
		$this->description      = __( 'Data tables must have a valid structure with marked headings and relationships between the cells. If layout tables are present, structural table markup must not be used for these.', 'screen-reader-check' );
		$this->guideline_title  = __( '1.3.1 Info and Relationships', 'screen-reader-check' );
		$this->guideline_anchor = 'content-structure-separation-programmatic';

		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H39',
			'title'  => __( 'Using caption elements to associate data table captions with data tables', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H43',
			'title'  => __( 'Using id and headers attributes to associate data cells with header cells in data tables', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H51',
			'title'  => __( 'Using table markup to present tabular information', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H63',
			'title'  => __( 'Using the scope attribute to associate header cells and data cells in data tables', 'screen-reader-check' ),
		);
		$this->links[] = array(
			'target' => 'https://www.w3.org/TR/WCAG20-TECHS/H73',
			'title'  => __( 'Using the summary attribute of the table element to give an overview of data tables', 'screen-reader-check' ),
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
		$tables = $dom->find( 'table' );

		$has_errors = false;
		$has_warnings = false;

		if ( count( $tables ) === 0 ) {
			$has_table_data = $this->get_option( 'has_table_data' );
			if ( $has_table_data ) {
				if ( 'yes' === $has_table_data ) {
					$result['message_codes'][] = 'missing_table_markup_for_tabular_data';
					$result['messages'][] = __( 'The page contains tabular data that do not use proper table markup.', 'screen-reader-check' );
					$has_errors = true;
				} else {
					$result['type'] = 'info';
					$result['message_codes'][] = 'skipped';
					$result['messages'][] = __( 'There are no tables in the HTML code provided. Therefore this test was skipped.', 'screen-reader-check' );
					return $result;
				}
			} else {
				$result['request_data'][] = array(
					'slug'          => 'has_table_data',
					'type'          => 'select',
					'label'         => __( 'Tabular data available', 'screen-reader-check' ),
					'description'   => __( 'Specify whether the page contains tabular data.', 'screen-reader-check' ),
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

		foreach ( $tables as $table ) {
			$is_datatable = false;
			$layout_table_usage = $this->get_global_option( 'layout_table_usage' );
			if ( 'no' !== $layout_table_usage ) {
				$identifier = $this->get_table_identifier( $table );

				$table_type = $this->get_option( 'table_type_' . $identifier );
				if ( $table_type ) {
					if ( 'data' === $table_type ) {
						$is_datatable = true;
					}
				} else {
					$result['request_data'][] = array(
						'slug'          => 'table_type_' . $identifier,
						'type'          => 'select',
						'label'         => __( 'Table Type', 'screen-reader-check' ),
						'description'   => sprintf( __( 'Does the table in line %s contain actual data or is it a layout table?', 'screen-reader-check' ), $table->getLineNo() ),
						'options'       => array(
							array(
								'value'   => 'data',
								'label'   => __( 'Data Table', 'screen-reader-check' ),
							),
							array(
								'value'   => 'layout',
								'label'   => __( 'Layout Table', 'screen-reader-check' ),
							),
						),
						'default'       => 'data',
					);
					continue;
				}
			} else {
				$is_datatable = true;
			}

			if ( $is_datatable ) {
				$ths = $table->find( 'th' );
				if ( count( $ths ) === 0 ) {
					$identifier = $this->get_table_identifier( $table );

					$table_headings = $this->get_option( 'table_headings_' . $identifier );
					if ( $table_headings ) {
						if ( in_array( $table_headings, array( 'columns', 'columnsrows' ), true ) ) {
							$result['message_codes'][] = 'missing_column_heading_markup';
							$result['messages'][] = sprintf( __( 'The data table in line %s is missing valid markup for its column headings.', 'screen-reader-check' ), $table->getLineNo() );
							$has_errors = true;
						}
						if ( in_array( $table_headings, array( 'rows', 'columnsrows' ), true ) ) {
							$tds_with_rowscope = $table->find( 'td[scope="row"]' );
							if ( count( $tds_with_rowscope ) === 0 ) {
								$result['message_codes'][] = 'missing_row_heading_markup';
								$result['messages'][] = sprintf( __( 'The data table in line %s is missing valid markup for its row headings.', 'screen-reader-check' ), $table->getLineNo() );
								$has_errors = true;
							}
						}
					} else {
						$result['request_data'][] = array(
							'slug'          => 'table_headings_' . $identifier,
							'type'          => 'select',
							'label'         => __( 'Table headings', 'screen-reader-check' ),
							'description'   => sprintf( __( 'Specify what kind of headings the data table in line %s uses.', 'screen-reader-check' ), $table->getLineNo() ),
							'options'       => array(
								array(
									'value'   => 'none',
									'label'   => __( 'No headings', 'screen-reader-check' ),
								),
								array(
									'value'   => 'columns',
									'label'   => __( 'Column headings', 'screen-reader-check' ),
								),
								array(
									'value'   => 'rows',
									'label'   => __( 'Row headings', 'screen-reader-check' ),
								),
								array(
									'value'   => 'columnsrows',
									'label'   => __( 'Both column and row headings', 'screen-reader-check' ),
								),
							),
							'default'       => 'columns',
						);
					}
				} else {
					if ( ! $table->find( 'thead', false, true ) && $table->find( 'tr:first-child > th', false, true ) ) {
						$result['message_codes'][] = 'missing_thead_tag';
						$result['messages'][] = sprintf( __( 'The data table in line %s should use <code>thead</code> to wrap its column headings.', 'screen-reader-check' ), $table->getLineNo() );
						$has_errors = true;
					}

					if ( ! $table->find( 'th[headers],td[headers]', false, true ) ) {
						$trs = $table->find( 'tr' );
						$th_row_count = 0;
						foreach ( $trs as $tr ) {
							$children = $tr->getChildren();
							$ths_only = true;
							foreach ( $children as $child ) {
								if ( 'th' !== $child->getTagName() ) {
									$ths_only = false;
									break;
								}
							}
							if ( $ths_only ) {
								$th_row_count++;
								if ( $th_row_count > 1 ) {
									break;
								}
							}
						}
						if ( $th_row_count > 1 ) {
							$result['message_codes'][] = 'missing_headers_and_id_attributes_complex';
							$result['messages'][] = sprintf( __( 'The data table in line %s should use <code>headers</code> and <code>id</code> attributes to mark complex relationships between its cells.', 'screen-reader-check' ), $table->getLineNo() );
							$has_errors = true;
						}
					}
				}

				if ( ! $table->find( 'tbody', false, true ) ) {
					$result['message_codes'][] = 'missing_tbody_tag';
					$result['messages'][] = sprintf( __( 'The data table in line %s is missing a <code>tbody</code> element.', 'screen-reader-check' ), $table->getLineNo() );
					$has_errors = true;
				}

				$caption = $table->find( 'caption', false, true );
				if ( $caption ) {
					$summary = $table->getAttribute( 'summary' );
					if ( $summary && $summary === $caption ) {
						$result['message_codes'][] = 'summary_equals_caption';
						$result['messages'][] = sprintf( __( 'The <code>summary</code> attribute of the data table in line %s has a similar value like its <code>caption</code> element.', 'screen-reader-check' ), $table->getLineNo() );
						$has_errors = true;
					}
				}
			} else {
				$forbidden_elements = $table->find( 'caption,th,td[headers]' );
				$summary = $table->getAttribute( 'summary' );
				if ( count( $forbidden_elements ) > 0 || $summary ) {
					$result['message_codes'][] = 'misuse_of_structural_markup_layout';
					$result['messages'][] = sprintf( __( 'The layout table in line %s uses structural markup which is only allowed for data tables.', 'screen-reader-check' ), $table->getLineNo() );
					$has_errors = true;
				}
			}
		}

		if ( ! $has_errors && $has_warnings ) {
			$result['type'] = 'warning';
		} elseif ( ! $has_errors && ! $has_warnings ) {
			$result['type'] = 'success';
			$result['message_codes'][] = 'success';
			$result['messages'][] = __( 'All tables in the HTML code use valid table markup.', 'screen-reader-check' );
		}

		return $result;
	}

	/**
	 * Parses a table into a string.
	 *
	 * The string is supposed to uniquely identify the table in the best way possible.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param ScreenReaderCheck\Parser\Node $table The table element.
	 * @return string The sanitize table identifier.
	 */
	protected function get_table_identifier( $table ) {
		$id = $table->getAttribute( 'id' );
		if ( $id ) {
			return 'id_' . $id;
		}

		$name = $table->getAttribute( 'name' );
		if ( $name ) {
			return 'name_' . $name;
		}

		return 'line_' . $table->getLineNo();
	}
}
