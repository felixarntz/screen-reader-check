<?php
/**
 * Stats class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

defined( 'ABSPATH' ) || exit;

/**
 * This class provides analytics and handles stats.
 *
 * @since 1.0.0
 */
class Stats {
	/**
	 * The checks class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Checks
	 */
	private $checks;

	/**
	 * The tests class instance.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var ScreenReaderCheck\Tests
	 */
	private $tests;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\Checks $checks The checks class instance.
	 * @param ScreenReaderCheck\Tests  $tests  The tests class instance.
	 */
	public function __construct( $checks, $tests ) {
		$this->checks = $checks;
		$this->tests  = $tests;

		$this->tests->set_stats( $this );
	}

	/**
	 * Logs a test result for easy stats retrieval.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param ScreenReaderCheck\TestResult|array $result Test result object or array.
	 */
	public function log_result( $result ) {
		if ( is_a( $result, 'ScreenReaderCheck\TestResult' ) ) {
			$result = $result->to_array();
		}

		$check_stats = get_post_meta( $result['check_id'], 'src_stats', true );
		if ( ! is_array( $check_stats ) ) {
			$check_stats = array(
				'total' => array(
					'error_count'   => 0,
					'warning_count' => 0,
				),
				'tests' => array(),
			);
		}

		if ( ! isset( $check_stats['tests'][ $result['test_slug'] ] ) ) {
			$check_stats['tests'][ $result['test_slug'] ] = array(
				'error_count'   => 0,
				'warning_count' => 0,
				'request_count' => 0,
				'skipped'       => false,
			);
		}

		if ( ! empty( $result['request_data'] ) ) {
			$check_stats['tests'][ $result['test_slug'] ]['request_count'] += count( $result['request_data'] );
		} elseif ( 'skipped' === $result['type'] ) {
			$check_stats['tests'][ $result['test_slug'] ]['skipped'] = true;
		} elseif ( in_array( $result['type'], array( 'warning', 'error' ), true ) ) {
			foreach ( $result['message_codes'] as $message_code ) {
				if ( 0 === strpos( $message_code, 'error_' ) ) {
					$check_stats['tests'][ $result['test_slug'] ]['error_count']++;
					$check_stats['total']['error_count']++;
				} elseif ( 0 === strpos( $message_code, 'warning_' ) ) {
					$check_stats['tests'][ $result['test_slug'] ]['warning_count']++;
					$check_stats['total']['warning_count']++;
				}
			}
		}

		update_post_meta( $result['check_id'], 'src_stats', $check_stats );
	}

	public function register_menu_item() {
		add_submenu_page( 'edit.php?post_type=src_check', __( 'Stats', 'screen-reader-check' ), __( 'Stats', 'screen-reader-check' ), 'edit_posts', 'src_stats', array( $this, 'render_page' ) );
	}

	public function render_page() {
		$date_start    = ! empty( $_REQUEST['date_start'] ) ? wp_unslash( $_REQUEST['date_start'] ) : '';
		$date_end      = ! empty( $_REQUEST['date_end'] ) ? wp_unslash( $_REQUEST['date_end'] ) : '';
		$site_category = ! empty( $_REQUEST['site_category'] ) ? wp_unslash( $_REQUEST['site_category'] ) : '';
		$max_items     = ! empty( $_REQUEST['max_items'] ) ? absint( $_REQUEST['max_items'] ) : 20;
		$table_format  = ! empty( $_REQUEST['table_format'] ) ? wp_unslash( $_REQUEST['table_format'] ) : 'complex';

		if ( 'compact' === $table_format ) {
			$errors_text = __( 'Err.', 'screen-reader-check' );
			$warnings_text = __( 'Warn.', 'screen-reader-check' );
			$additional_text = __( 'More', 'screen-reader-check' );
		} else {
			$errors_text = __( 'Errors', 'screen-reader-check' );
			$warnings_text = __( 'Warnings', 'screen-reader-check' );
			$additional_text = __( 'More', 'screen-reader-check' );
		}

		$check_ids = array();

		$tests = $this->tests->get_tests();

		$available_categories = $this->tests->get_site_categories();

		if ( ! empty( $date_start ) && ! empty( $date_end ) ) {
			$query_args = array(
				'posts_per_page' => ( $max_items > 0 ? $max_items : -1 ),
				'post_type'      => 'src_check',
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'ASC',
				'date_query'     => array(
					array(
						'after'          => $date_start,
						'before'         => $date_end,
						'inclusive'      => true,
					),
				),
				'meta_query'     => array(
					'relation'       => 'AND',
					array(
						'key'            => 'src_url',
						'compare'        => 'EXISTS',
					),
					array(
						'key'            => 'src_url',
						'value'          => '',
						'compare'        => '!=',
					),
					array(
						'key'            => 'src_stats',
						'compare'        => 'EXISTS',
					),
				),
			);

			/*if ( ! empty( $site_category ) ) {
				$query_args['meta_query'][] = array(
					'key'     => 'src_options',
					'value'   => '"global_site_category";s:' . strlen( $site_category ) . ':"' . $site_category . '"',
					'compare' => 'LIKE',
				);
			}*/

			$check_ids = get_posts( $query_args );
		}

		$total_stats = array(
			'total' => array(
				'error_count'   => 0,
				'warning_count' => 0,
			),
			'tests' => array(),
		);

		?>
		<style type="text/css">
			.tablenav .alignleft {
				margin-left: 10px;
			}

			.tablenav .alignleft:first-child {
				margin-left: 0;
			}

			.tablenav .alignleft input,
			.tablenav .alignleft select {
				max-width: 100px;
			}

			.tablenav .alignleft .button {
				max-width: none;
			}

			.table-overflow-wrap {
				overflow-x: scroll;
			}

			.widefat .column-check_category,
			.widefat .column-results_total,
			.widefat .column-results_total_warnings,
			.widefat .column-accumulated_results {
				border-right: 2px solid #e1e1e1;
			}

			.widefat .column-accumulated_results {
				text-align: right;
			}
		</style>
		<div class="wrap">
			<h1><?php _e( 'Stats', 'screen-reader-check' ); ?></h1>

			<form method="get">
				<input type="hidden" name="post_type" value="src_check" />
				<input type="hidden" name="page" value="src_stats" />
				<div class="tablenav">
					<div class="alignleft">
						<label for="date-start"><?php _e( 'Start Date', 'screen-reader-check' ); ?></label>
						<input type="text" id="date-start" name="date_start" value="<?php echo esc_attr( $date_start ); ?>" placeholder="2000-01-01" required="required" />
					</div>
					<div class="alignleft">
						<label for="date-end"><?php _e( 'End Date', 'screen-reader-check' ); ?></label>
						<input type="text" id="date-end" name="date_end" value="<?php echo esc_attr( $date_end ); ?>" placeholder="2000-12-31" required="required" />
					</div>
					<!--<div class="alignleft">
						<label for="site-category"><?php _e( 'Website Category', 'screen-reader-check' ); ?></label>
						<select id="site-category" name="site_category">
							<option value=""><?php _e( 'Any', 'screen-reader-check' ); ?></option>
							<?php foreach ( $available_categories as $slug => $name ) : ?>
								<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $site_category, $slug ); ?>><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>-->
					<div class="alignleft">
						<label for="max-items"><?php _e( 'Maximum Results', 'screen-reader-check' ); ?></label>
						<input type="number" id="max-items" name="max_items" value="<?php echo esc_attr( $max_items ); ?>" min="0" step="1" required="required" />
					</div>
					<div class="alignleft">
						<label for="table-format"><?php _e( 'Table Format', 'screen-reader-check' ); ?></label>
						<select id="table-format" name="table_format">
							<option value="complex" <?php selected( $table_format, 'complex' ); ?>><?php _e( 'Complex', 'screen-reader-check' ); ?></option>
							<option value="compact" <?php selected( $table_format, 'compact' ); ?>><?php _e( 'Compact', 'screen-reader-check' ); ?></option>
						</select>
					</div>
					<div class="alignleft">
						<input type="submit" name="show_results" class="button" value="<?php _e( 'Show Results', 'screen-reader-check' ); ?>" />
					</div>
				</div>
			</form>

			<div class="table-overflow-wrap">
				<table class="widefat striped">
					<thead>
						<tr>
							<th id="check_id" class="column-check_id" rowspan="2"><?php _e( 'ID', 'screen-reader-check' ); ?></th>
							<th id="check_url" class="column-check_url" rowspan="2"><?php _e( 'URL', 'screen-reader-check' ); ?></th>
							<th id="check_category" class="column-check_category" rowspan="2"><?php _e( 'Category', 'screen-reader-check' ); ?></th>
							<th id="results_total" class="column-results_total" colspan="2"><?php _e( 'Total', 'screen-reader-check' ); ?></th>
							<?php
							$i = 0;
							foreach ( $tests as $test_slug => $test ) :
								$i++;

								$total_stats['tests'][ $test_slug ] = array(
									'error_count'   => 0,
									'warning_count' => 0,
									'request_count' => 0,
									'skipped'       => false,
								);

								$test_title = '' . $i . '. ' . $test->get_title();
								if ( 'compact' === $table_format && strlen( $test_title ) > 16 ) {
									$test_title = substr( $test_title, 0, 15 ) . '&hellip;';
								}
								?>
								<th id="results_<?php echo $test_slug; ?>" class="column-results_<?php echo $test_slug; ?>" colspan="3"><?php echo $test_title; ?></th>
							<?php endforeach; ?>
						</tr>
						<tr>
							<th id="results_total_errors" class="column-results_total_errors" headers="results_total"><?php echo $errors_text; ?></th>
							<th id="results_total_warnings" class="column-results_total_warnings" headers="results_total"><?php echo $warnings_text; ?></th>
							<?php foreach ( $tests as $test_slug => $test ) : ?>
								<th id="results_<?php echo $test_slug; ?>_errors" class="column-results_<?php echo $test_slug; ?>_errors" headers="results_<?php echo $test_slug; ?>"><?php echo $errors_text; ?></th>
								<th id="results_<?php echo $test_slug; ?>_warnings" class="column-results_<?php echo $test_slug; ?>_warnings" headers="results_<?php echo $test_slug; ?>"><?php echo $warnings_text; ?></th>
								<th id="results_<?php echo $test_slug; ?>_additional" class="column-results_<?php echo $test_slug; ?>_additional" headers="results_<?php echo $test_slug; ?>"><?php echo $additional_text; ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $check_ids as $check_id ) :
							$check = $this->checks->get( $check_id );
							$stats = get_post_meta( $check_id, 'src_stats', true );

							$site_category = $check->get_option( 'global_site_category' );

							if ( isset( $available_categories[ $site_category ] ) ) {
								$site_category = $available_categories[ $site_category ];
							} else {
								$site_category = '<span aria-hidden="true">&#8212;</span><span class="screen-reader-text">' . __( 'No website category', 'screen-reader-text' ) . '</span>';
							}

							$total_stats['total']['error_count']   += $stats['total']['error_count'];
							$total_stats['total']['warning_count'] += $stats['total']['warning_count'];
							?>
							<tr>
								<td class="column-check_id"><a href="<?php echo get_edit_post_link( $check_id ); ?>"><?php echo $check_id; ?></a></td>
								<td class="column-check_url"><a href="<?php echo esc_url( $check->get_url() ); ?>"><?php echo $check->get_url(); ?></a></td>
								<td class="column-check_category"><?php echo $site_category; ?></td>
								<td class="column-results_total_errors"><?php echo number_format_i18n( $stats['total']['error_count'] ); ?></td>
								<td class="column-results_total_warnings"><?php echo number_format_i18n( $stats['total']['warning_count'] ); ?></td>
								<?php foreach ( $tests as $test_slug => $test ) :
									$errors   = ! empty( $stats['tests'][ $test_slug ]['error_count'] ) ? $stats['tests'][ $test_slug ]['error_count'] : 0;
									$warnings = ! empty( $stats['tests'][ $test_slug ]['warning_count'] ) ? $stats['tests'][ $test_slug ]['warning_count'] : 0;
									$additional = '';

									$total_stats['tests'][ $test_slug ]['error_count']   += $errors;
									$total_stats['tests'][ $test_slug ]['warning_count'] += $warnings;

									if ( isset( $stats['tests'][ $test_slug ]['skipped'] ) && $stats['tests'][ $test_slug ]['skipped'] ) {
										$additional .= 'S';
										$total_stats['tests'][ $test_slug ]['skipped'] = true;
									}
									if ( ! empty( $stats['tests'][ $test_slug ]['request_count'] ) ) {
										$additional .= 'R';
										$total_stats['tests'][ $test_slug ]['request_count'] += $stats['tests'][ $test_slug ]['request_count'];
									}
									?>
									<td class="column-results_<?php echo $test_slug; ?>_errors"><?php echo number_format_i18n( $errors ); ?></td>
									<td class="column-results_<?php echo $test_slug; ?>_warnings"><?php echo number_format_i18n( $warnings ); ?></td>
									<td class="column-results_<?php echo $test_slug; ?>_additional"><?php echo $additional; ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<?php if ( ! empty( $check_ids ) ) : ?>
						<tfoot>
							<tr>
								<th id="accumulated_results" class="column-accumulated_results" colspan="3" scope="row"><?php _e( 'Accumulated Results', 'screen-reader-check' ); ?></th>
								<td class="column-results_total_errors"><?php echo number_format_i18n( $total_stats['total']['error_count'] ); ?></td>
								<td class="column-results_total_warnings"><?php echo number_format_i18n( $total_stats['total']['warning_count'] ); ?></td>
								<?php foreach ( $tests as $test_slug => $test ) :
									$additional = '';
									if ( $total_stats['tests'][ $test_slug ]['skipped'] ) {
										$additional .= 'S';
									}
									if ( ! empty( $total_stats['tests'][ $test_slug ]['request_count'] ) ) {
										$additional .= 'R';
									}
									?>
									<td class="column-results_<?php echo $test_slug; ?>_errors"><?php echo number_format_i18n( $total_stats['tests'][ $test_slug ]['error_count'] ); ?></td>
									<td class="column-results_<?php echo $test_slug; ?>_warnings"><?php echo number_format_i18n( $total_stats['tests'][ $test_slug ]['warning_count'] ); ?></td>
									<td class="column-results_<?php echo $test_slug; ?>_additional"><?php echo $additional; ?></td>
								<?php endforeach; ?>
							</tr>
						</tfoot>
					<?php endif; ?>
				</table>
			</div>
		</div>
		<?php
	}
}
