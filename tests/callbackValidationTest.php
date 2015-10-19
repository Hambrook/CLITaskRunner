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
class callbackValidationTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testOnLineCallbackValid($object) {
		// Valid closure
		$result = $object->onLine("stdOut", function($v){});
		$this->assertEquals(true, $result);
		// Valid function name
		$result = $object->onLine("stdOut", "strlen");
		$this->assertEquals(true, $result);
		// Valid pattern
		$result = $object->onLine("stdOut", "strlen", "/^.*$/");
		$this->assertEquals(true, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnLineCallbackInvalid($object) {
		// Invalid callback (echo is a PHP reserved word)
		$result = $object->onLine("stdOut", "echo");
		$this->assertEquals(false, $result);
		// Invalid callback
		$result = $object->onLine("stdOut", "bad_function_name");
		$this->assertEquals(false, $result);
		// Valid callable, invalid pattern
		$result = $object->onLine("stdOut", "strlen", "/bad");
		$this->assertEquals(false, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnBufferCallbackValid($object) {
		// Valid
		$result = $object->onBuffer("stdOut", function($v){});
		$this->assertEquals(true, $result);
		// Valid
		$result = $object->onBuffer("stdOut", "strlen");
		$this->assertEquals(true, $result);
	}

	/**
	 * @depends testCreate
	 */
	public function testOnBufferCallbackInvalid($object) {
		// Invalid callback (echo is a PHP reserved word)
		$result = $object->onBuffer("stdOut", "echo");
		$this->assertEquals(false, $result);
		// Invalid callback
		$result = $object->onBuffer("stdOut", "bad_function_name");
		$this->assertEquals(false, $result);
	}

}