<?php

require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, "..", "src", "CLITaskRunner.php"]));

use \Hambrook\CLITaskRunner\CLITaskRunner as CLITaskRunner;

/**
 * Tests for PHPUnit
 *
 * @author     Rick Hambrook <rick@rickhambrook.com>
 * @copyright  2015 Rick Hambrook
 * @license    https://www.gnu.org/licenses/gpl.txt  GNU General Public License v3
 */
class globalCallbackValidationTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testOnCompleteCallbackValid($object) {
		// Valid closure
		$result = $object->onComplete(function($v){});
		$this->assertEquals(true, $result);
		// Valid function name
		$result = $object->onComplete("strlen");
		$this->assertEquals(true, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnCompleteCallbackInvalid($object) {
		// Invalid callback (echo is a PHP reserved word)
		$result = $object->onComplete("echo");
		$this->assertEquals(false, $result);
		// Invalid callback
		$result = $object->onComplete("bad_function_name");
		$this->assertEquals(false, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnSuccessCallbackValid($object) {
		// Valid closure
		$result = $object->onSuccess(function($v){});
		$this->assertEquals(true, $result);
		// Valid function name
		$result = $object->onSuccess("strlen");
		$this->assertEquals(true, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnSuccessCallbackInvalid($object) {
		// Invalid callback (echo is a PHP reserved word)
		$result = $object->onSuccess("echo");
		$this->assertEquals(false, $result);
		// Invalid callback
		$result = $object->onSuccess("bad_function_name");
		$this->assertEquals(false, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnFailureCallbackValid($object) {
		// Valid closure
		$result = $object->onFailure(function($v){});
		$this->assertEquals(true, $result);
		// Valid function name
		$result = $object->onFailure("strlen");
		$this->assertEquals(true, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnFailureCallbackInvalid($object) {
		// Invalid callback (echo is a PHP reserved word)
		$result = $object->onFailure("echo");
		$this->assertEquals(false, $result);
		// Invalid callback
		$result = $object->onFailure("bad_function_name");
		$this->assertEquals(false, $result);
	}

}