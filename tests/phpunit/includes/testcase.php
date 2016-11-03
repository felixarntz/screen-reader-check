<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class SRC_UnitTestCase extends WP_UnitTestCase {
	protected $basic_html_document = '';

	public static function factory() {
		static $factory = null;
		if ( ! $factory || ! $factory instanceof SRC_UnitTest_Factory ) {
			$factory = new SRC_UnitTest_Factory();
		}
		return $factory;
	}

	function setUp() {
		parent::setUp();

		$this->basic_html_document = '<!DOCTYPE html>
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
	}

	function assertCheck( $actual, $message = '' ) {
		$this->assertInstanceOf( 'ScreenReaderCheck\Check', $actual, $message );
	}

	function assertTest( $actual, $message = '' ) {
		$this->assertInstanceOf( 'ScreenReaderCheck\Test', $actual, $message );
	}
}
