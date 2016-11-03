<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class Test_ImagesAlternativeTexts extends SRC_UnitTestCase_For_Test {
	function setUp() {
		parent::setUp();

		$this->test_slug = 'images_alternative_texts';
	}

	public function data_test_results() {
		return array(
			'valid_images' => array(
				'valid_images.html',
				array(
					'image_type_purely-decorative-image--png' => 'decorative',
				),
				array(),
				array(
					'success',
				),
			),
			'valid_images_almost' => array(
				'valid_images.html',
				array(
					'image_type_purely-decorative-image--png' => 'content',
				),
				array(),
				array(
					'empty_alt_attribute_content',
				),
			),
			'invalid_images' => array(
				'invalid_images.html',
				array(
					'image_type_purely-decorative-image--png' => 'decorative',
					'image_type_yet-another-image--jpg'       => 'content',
				),
				array(),
				array(
					'missing_alt_attribute',
					'alternative_text_too_long',
					'non_descriptive_alternative_text',
					'usage_of_title_attribute_layout',
					'empty_alt_attribute_content',
					'alt_attribute_part_of_src',
					'non_descriptive_alternative_text',
				),
			),
			'images_to_skip' => array(
				'images_to_skip.html',
				array(),
				array(),
				array(
					'skipped',
				),
			),
		);
	}

	/**
	 * @dataProvider data_test_results
	 *
	 * @param string $filename               Filename of the HTML code to test.
	 * @param array  $args                   Arguments to pass to the test.
	 * @param array  $global_options         Global options to add to the created check.
	 * @param array  $expected_message_codes Expected message codes.
	 */
	public function test_results( $filename, $args, $global_options, $expected_message_codes ) {
		$result = $this->get_test_result_for_file( $filename, $args, $global_options );
		$this->assertTestResult( $result );

		$message_codes = $result->get_message_codes();
		$this->assertEquals( $expected_message_codes, $message_codes );
	}
}
