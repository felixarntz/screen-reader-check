<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class SRC_UnitTestCase_For_Test extends SRC_UnitTestCase {
	protected $test_slug = 'images_alternative_texts';

	protected function get_test_result_for_file( $filename, $args = array(), $global_options = array() ) {
		$path = SRC_DIR_TESTDATA . '/' . $this->test_slug . '/' . $filename;

		if ( ! file_exists( $path ) ) {
			return new WP_Error();
		}

		$html = file_get_contents( $path );

		return $this->get_test_result_for_html( $html, $args, $global_options );
	}

	protected function get_test_result_for_html( $html, $args = array(), $global_options = array() ) {
		$check_id = self::factory()->check->create( array(
			'html'    => $html,
			'options' => $global_options,
		) );

		if ( is_wp_error( $check_id ) ) {
			return $check_id;
		}

		return src_get()->tests->run_test( $check_id, $this->test_slug, $args );
	}
}
