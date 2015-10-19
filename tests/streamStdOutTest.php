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
class streamStdOutTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo one two; echo three; echo four;");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testOnLine($object) {
		$mock = $this->getMock("stdClass", ["cb_one", "cb_three", "cb_four"]);
		$mock->expects($this->once())
			->method("cb_one")
			->with($this->equalTo(["one two", "one"]));
		$mock->expects($this->once())
			->method("cb_three")
			->with($this->equalTo(["three"]));
		$mock->expects($this->once())
			->method("cb_four")
			->with($this->equalTo(["four"]));

		$object->onLine("stdOut", [$mock, "cb_one"], "/(one).*/");
		$object->onLine("stdOut", [$mock, "cb_three"], "/three/");
		$object->onLine("stdOut", [$mock, "cb_four"], "/four/");
		$object->process();
	}

}