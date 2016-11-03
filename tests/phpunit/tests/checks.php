<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class Test_Checks extends SRC_UnitTestCase {
	public function test_create_check_with_url() {
		$check = src_get()->checks->create( array(
			'url' => 'http://example.com',
		) );

		$this->assertCheck( $check );
		$this->assertEquals( 'http://example.com', $check->get_url() );
	}

	public function test_create_check_with_url_invalid() {
		$check = src_get()->checks->create( array(
			'url' => 'http://dhausidgazdus.dgz',
		) );

		$this->assertWPError( $check );
	}

	public function test_create_check_with_html() {
		$check = src_get()->checks->create( array(
			'html' => $this->basic_html_document,
		) );

		$this->assertCheck( $check );
		$this->assertEquals( $this->basic_html_document, $check->get_html() );
	}

	public function test_create_check_without_args() {
		$check = src_get()->checks->create();

		$this->assertWPError( $check );
	}

	public function test_get_check() {
		$post_id = self::factory()->post->create( array(
			'post_type' => 'src_check',
		) );

		$check = src_get()->checks->get( $post_id );

		$this->assertCheck( $check );
		$this->assertEquals( $post_id, $check->get_id() );
	}

	public function test_get_check_invalid() {
		$post_id = self::factory()->post->create();

		$check = src_get()->checks->get( $post_id );

		$this->assertWPError( $check );
	}

	public function test_delete_check() {
		$post_id = self::factory()->post->create( array(
			'post_type' => 'src_check',
		) );

		$result = src_get()->checks->delete( $post_id );

		$this->assertTrue( $result );
	}

	public function test_delete_check_invalid() {
		$post_id = self::factory()->post->create();

		$result = src_get()->checks->delete( $post_id );

		$this->assertWPError( $result );
	}
}
