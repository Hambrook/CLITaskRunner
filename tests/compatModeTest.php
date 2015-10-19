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
class compatModeTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$tmp = new CLITaskRunner("echo");
		$this->assertInstanceOf("\Hambrook\CLITaskRunner\CLITaskRunner", $tmp);
		return $tmp;
	}

	/**
	 * @depends testCreate
	 */
	public function testGet($object) {
		$this->assertTrue(is_bool($object->compatMode()));
	}

	/**
	 * @depends testCreate
	 */
	public function testSetValid($object) {
		$tmpOld = $object->compatMode();
		$this->assertTrue($object->compatMode(!$tmpOld));  // Setting should always return true
		$tmpNew = $object->compatMode();
		$this->assertTrue(is_bool($tmpNew));
		$this->assertEquals($tmpOld, !$tmpNew);
	}

}