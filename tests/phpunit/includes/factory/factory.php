<?php
/**
 * @package ScreenReaderCheck
 * @subpackage Tests
 */

class SRC_UnitTest_Factory extends WP_UnitTest_Factory {
	public $check;

	public function __construct() {
		parent::__construct();

		$this->check = new SRC_UnitTest_Factory_For_Check( $this );
	}
}
