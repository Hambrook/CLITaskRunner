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
class streamStdErrLineTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo one two 1>&2; echo three 1>&2; echo four 1>&2;");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testOnLineMock($object) {
		$mock = $this->getMock("stdClass", ["validateOne", "validateOne2", "validateThree", "validateFour"]);
		$mock->expects($this->once())
			->method("validateOne")
			->with($this->equalTo(["one two", "one"]));
		$mock->expects($this->once())
			->method("validateOne2")
			->with($this->equalTo(["one two", "two"]));
		$mock->expects($this->once())
			->method("validateThree")
			->with($this->equalTo(["three"]));
		$mock->expects($this->once())
			->method("validateFour")
			->with($this->equalTo(["four"]));

		$object->onLine("stdErr", [$mock, "validateOne"], "/(one).*/");
		$object->onLine("stdErr", [$mock, "validateThree"], "/three/");
		$object->onLine("stdErr", [$mock, "validateFour"], "/four/");
		$object->onLine("stdErr", [$mock, "validateOne2"], "/.*(two)/");

		$object->process();
	}

	/**
	 * @depends testCreate
	 */
	public function testOnLineLocal($object) {
		$object->onLine("stdErr", [$this, "validateOne"], "/(one).*/");
		$object->onLine("stdErr", [$this, "validateThree"], "/three/");
		$object->onLine("stdErr", [$this, "validateFour"], "/four/");
		$object->onLine("stdErr", [$this, "validateOne2"], "/.*(two)/");

		$object->process();
	}
	public function validateOne($v) {
		$this->assertEquals(["one two", "one"], $v);
	}
	public function validateThree($v) {
		$this->assertEquals(["three"], $v);
	}
	public function validateFour($v) {
		$this->assertEquals(["four"], $v);
	}
	public function validateOne2($v) {
		$this->assertEquals(["one two", "two"], $v);
	}

}