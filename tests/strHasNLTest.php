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
class strHasNLTest extends PHPUnit_Framework_TestCase {

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
		$method = $this->getMethod($object, "strHasNL");
		$this->assertTrue($method->invoke($object, "this string has\nnew lines"));
		$this->assertFalse($method->invoke($object, "this string has no new lines"));
	}

	/**
	 * @depends testCreate
	 */
	public function testCompat($object) {
		$data = [
			"line one",
			"line two"
		];
		$method = $this->getMethod($object, "strHasNL");
		$object->compatMode(true);
		$this->assertTrue($method->invoke($object, implode("\r", $data)));
		$this->assertTrue($method->invoke($object, implode("\r\n", $data)));
		$this->assertTrue($method->invoke($object, implode("\n", $data)));
		$this->assertFalse($method->invoke($object, "no split"));
	}

}