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
class bufferSizeTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testGet($object) {
		$this->assertTrue(is_int($object->bufferSize()));
	}

	/**
	 * @depends testCreate
	 */
	public function testSetValid($object) {
		$object->bufferSize(20);
		$this->assertEquals(20, $object->bufferSize());
		$object->bufferSize(30);
		$this->assertEquals(30, $object->bufferSize());
	}

}