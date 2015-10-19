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
class strSplitByNLTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	protected static function getMethod($object, $name) {
		$method = new ReflectionMethod($object, $name);
		$method->setAccessible(true);
		return $method;
	}

	/**
	 * @depends testCreate
	 */
	public function testValid($object) {
		$data = [
			"line one",
			"line two"
		];
		$method = $this->getMethod($object, "strSplitByNL");
		$this->assertEquals($data, $method->invoke($object, implode(PHP_EOL, $data)));
		$this->assertEquals(["no split"], $method->invoke($object, "no split"));
	}

	/**
	 * @depends testCreate
	 */
	public function testInvalid($object) {
		$method = $this->getMethod($object, "strSplitByNL");
		$this->assertEquals([], $method->invoke($object, new stdClass));
	}

	/**
	 * @depends testCreate
	 */
	public function testCompat($object) {
		$data = [
			"line one",
			"line two"
		];
		$method = $this->getMethod($object, "strSplitByNL");
		$object->compatMode(true);
		$this->assertEquals($data, $method->invoke($object, implode("\r", $data)));
		$this->assertEquals($data, $method->invoke($object, implode("\r\n", $data)));
		$this->assertEquals($data, $method->invoke($object, implode("\n", $data)));
		$this->assertEquals(["no split"], $method->invoke($object, "no split"));
	}

}