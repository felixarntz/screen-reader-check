<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class SRC_UnitTest_Factory_For_Check extends WP_UnitTest_Factory_For_Thing {
	public function __construct( $factory = null, $default_generation_definitions = array() ) {
		if ( empty( $default_generation_definitions ) ) {
			$basic_html_document = '<!DOCTYPE html>
<html lang="en-US">
<head>
	<meta charset="utf-8">
	<title>Basic HTML Document</title>
</head>
<body>
	<div class="page">
		<h1>Basic HTML Document</h1>
		<p>A paragraph inside the basic HTML document.</p>
	</div>
</body>
</html>';

			$default_generation_definitions = array(
				'html'          => $basic_html_document,
			);
		}
		parent::__construct( $factory, $default_generation_definitions );
	}

	public function create_object( $args ) {
		$check = src_get()->checks->create( $args );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		return $check->get_id();
	}

	public function update_object( $id, $args ) {
		$check = src_get()->checks->get( $id );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		$field_mappings = array(
			'url'  => 'src_url',
			'html' => 'src_html',
		);

		foreach ( $args as $key => $value ) {
			if ( ! isset( $field_mappings[ $key ] ) ) {
				continue;
			}

			update_post_meta( $id, $field_mappings[ $key ], $value );
		}

		return true;
	}

	public function get_object_by_id( $id ) {
		return src_get()->checks->get( $id );
	}
}
