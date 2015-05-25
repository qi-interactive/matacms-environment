<?php

// run by  codecept run in matacms-environment dir

class EnvironmentBehaviorTest extends \Codeception\TestCase\Test {

	public function testDummy() {
		$this->assertTrue(1+1==2);
	}
}