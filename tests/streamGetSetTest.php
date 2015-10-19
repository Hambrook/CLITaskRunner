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
class streamGetSetTest extends PHPUnit_Framework_TestCase {

	private $initialStreams = [
		"stdIn"  => ["pipe","r"],
		"stdOut" => ["pipe","w"],
		"stdErr" => ["pipe","w"]
	];

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testGetDefault($object) {
		$this->assertEquals($this->initialStreams, $object->streams());
	}

	/**
	 * @depends testCreate
	 */
	public function testSetNewValid($object) {
		$tmp = $this->initialStreams;
		unset($tmp["stdOut"]);
		$object->streams($tmp);
		$this->assertEquals($tmp, $object->streams());
	}

	/**
	 * @depends testCreate
	 */
	public function testSetNewInvalid($object) {
		$tmp = $this->initialStreams;
		unset($tmp["stdOut"][1]);
		// Reset to initial streams
		$tmp = $object->streams($this->initialStreams);
		$tmp = $object->streams($tmp);
		$this->assertEquals(false, $tmp);
		$this->assertEquals($this->initialStreams, $object->streams());
	}

}