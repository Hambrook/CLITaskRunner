<?php

namespace Hambrook\CLITaskRunner;

/**
 * CLITaskRunner
 *
 * Run a CLI command in the background and get updates via callbacks on events
 * including buffer and line updates.
 *
 * @version    0.1.1
 *
 * @author     Rick Hambrook <rick@rickhambrook.com>
 * @copyright  2015 Rick Hambrook
 * @license    https://www.gnu.org/licenses/gpl.txt  GNU General Public License v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class CLITaskRunner {

	private $command         = "";
	private $lines           = "";

	private $bufferSize      = 10;
	private $compatMode      = false;

	private $streamsConfig   = [
		"stdIn"  => ["pipe","r"],
		"stdOut" => ["pipe","w"],
		"stdErr" => ["pipe","w"]
	];

	private $globalCallbacks = [
		"onSuccess"  => [],
		"onComplete" => [],
		"onFailure"  => []
	];

	private $streamsData     = [];

	/**
	 * _CONSTRUCT
	 *
	 * @param  string  $command  Source path
	 */
	public function __construct($command) {
		$this->command = $command;
		$this->_buildStreamsContainer();
	}

	/**
	 * STREAMS
	 *
	 * Get or set streams configuration
	 *
	 * @param   array  $streamsConfig  The streams config to import
	 *
	 * @return  bool|array             Success bool if setting, array if getting
	 */
	public function streams($streamsConfig=[]) {
		if (!func_num_args()) {
			return $this->streamsConfig;
		}

		// Each stream must be an array with at least two items (type and mode)
		if (!is_array($streamsConfig)) { return false; }
		foreach ($streamsConfig as $k => $v) {
			if (!is_array($v) || count($v) !== 2) { return false; }
			$streamsConfig[$k] = array_values($v);
		}

		$this->streamsConfig = $streamsConfig;
		$this->_buildStreamsContainer();

		return true;
	}

	/**
	 * BUFFERSIZE
	 *
	 * Get or set buffer size
	 *
	 * @param   int  $size  The new size
	 *
	 * @return  bool|int    Success bool if setting, int if getting
	 */
	public function bufferSize($size=10) {
		if (!func_num_args()) {
			return $this->bufferSize;
		}

		// Each stream must be an array with at least two items (type and mode)
		if (!is_int($bufferSize)) { return false; }

		$this->bufferSize = $bufferSize;

		return true;
	}

	/**
	 * COMPATMODE
	 *
	 * Get or set compatibility mode. Uses regex for line breaks instead of PHP_EOL
	 * to cover cases where commands may produce unsuspected output.
	 *
	 * @param   array  $compatMode  The streams config to import
	 *
	 * @return  bool                Success if setting, current value of getting
	 */
	public function compatMode($compatMode=false) {
		if (!func_num_args()) {
			return $this->compatMode;
		}

		$this->compatMode = (!!$compatMode);

		return true;
	}

	/**
	 * ON
	 *
	 * Set callback functions for when a new line is outputted to a stream
	 *
	 * @param   string    $stream    The stream to place the callback on
	 * @param   callable  $callback  The callback to call
	 * @param   string    $pattern   Optional regex pattern to match before calling
	 *
	 * @return  bool                 Valid callback or not
	 */
	public function onLine($stream, $callback, $pattern=false) {
		// Validate the stream, callback and pattern
		if (!$this->_streamIsWritable($stream)) { return false; }
		if (!is_callable($callback)) { return false; }
		if ($pattern && (@preg_match($pattern, null)) === false) { return false; }

		$this->streamsData[$stream]["callbacks"]["line"][] = [
			"callback" => $callback,
			"pattern"  => $pattern
		];

		return true;
	}

	/**
	 * ONLINE
	 *
	 * Set callback functions for when a new line is outputted to stdOut
	 *
	 * @param   callable  $callback  The callback to call
	 * @param   string    $pattern   Optional regex pattern to match before calling
	 *
	 * @return  bool                 Valid callback or not
	 */
	public function onBuffer($stream, $callback, $pattern=false) {
		if (!$this->_streamIsWritable($stream)) { return false; }
		if (!is_callable($callback)) { return false; }
		if ($pattern && (@preg_match($pattern, null)) === false) { return false; }

		$this->streamsData[$stream]["callbacks"]["buffer"][] = [
			"callback" => $callback,
			"pattern"  => $pattern
		];
		return true;
	}

	/**
	 * ONCOMPLETE
	 *
	 * Set callback function for when processing is complete
	 *
	 * @param   callable  $callback  The callback to call
	 *
	 * @return  bool                 Valid callback or not
	 */
	public function onComplete($callback) {
		if (is_callable($callback)) {
			$this->globalCallbacks["onComplete"][] = [
				"callback" => $callback,
				"pattern"  => false
			];
			return true;
		}
		return false;
	}

	/**
	 * ONFAILURE
	 *
	 * Set callback function for when processing fails
	 *
	 * @param   callable  $callback  The callback to call
	 *
	 * @return  bool                 Valid callback or not
	 */
	public function onFailure($callback) {
		if (is_callable($callback)) {
			$this->globalCallbacks["onFailure"][] = [
				"callback" => $callback,
				"pattern"  => false
			];
			return true;
		}
		return false;
	}

	/**
	 * ONSUCCESS
	 *
	 * Set callback function for when processing succeeds
	 *
	 * @param   callable  $callback  The callback to call
	 *
	 * @return  bool                 Valid callback or not
	 */
	public function onSuccess($callback) {
		if (is_callable($callback)) {
			$this->globalCallbacks["onSuccess"][] = [
				"callback" => $callback,
				"pattern"  => false
			];
			return true;
		}
		return false;
	}

	/**
	 * PROCESS
	 *
	 * Do the grunt work and process the command, slinging callbacks like a pro while we're at it
	 *
	 * @return  bool  Success or not
	 */
	public function process() {
		$handle = proc_open(
			$this->command,
			array_values($this->streamsConfig),
			$streams
		);
		if (!is_resource($handle)) {
			$this->_runGlobalCallbacks("onFailure");
			return false;
		}

		$this->_updateSubscriptionBools();

		do {
			$continue = false;
			foreach($this->streamsData as $k => $v) {
				if (!$v["watch"]) { continue; }
				$buffer = fread($streams[$v["index"]], $this->bufferSize);
				$this->streamsData[$k]["buffer"] = $buffer;
				if (!strlen($buffer)) {
					continue;
				}
				$continue = true;
				if ($v["watchBuffer"]) {
					$this->_runCallbacks($v["callbacks"]["buffer"], $buffer);
				}
				if (!$v["watchLine"]) { continue; }

				$this->streamsData[$k]["output"] .= $buffer;
				if ($this->strHasNL($this->streamsData[$k]["output"])) {
					$segments = $this->strSplitByNL($this->streamsData[$k]["output"]);
					while (count($segments) > 1) {
						$line = array_shift($segments);
						$this->_runCallbacks($v["callbacks"]["line"], $line);
					}
					$this->streamsData[$k]["output"] = array_shift($segments);
				}

			}
		} while ($continue);

		proc_close($handle);

		// Process callbacks
		$this->_runGlobalCallbacks("onSuccess");
		$this->_runGlobalCallbacks("onComplete");
		return true;
	}

	/**
	 * _UPDATESUBSCRIPTIONBOOLS
	 *
	 * Update the boolean flags for what we're watching so that we're
	 * not doing countless count() operations during processing.
	 *
	 * @return  array  Updated data
	 */
	private function _updateSubscriptionBools() {
		foreach ($this->streamsData as $k => $v) {
			$this->streamsData[$k]["watchLine"]   = (count($v["callbacks"]["line"]) > 0);
			$this->streamsData[$k]["watchBuffer"] = (count($v["callbacks"]["buffer"]) > 0);
			$this->streamsData[$k]["watch"]       = ($this->streamsData[$k]["watchLine"] || $this->streamsData[$k]["watchBuffer"]);
		}
		return $this->streamsData;
	}

	/**
	 * _RUNCALLBACKS
	 *
	 * Run a callback from a stream
	 *
	 * @param   array  $callbacks  Array of callbacks (from the streamsData array)
	 * @param   mixed  $data       Any data to send to the callback
	 *
	 * @return  mixed              Whatever is returned from the callback; false on failure, or null
	 */
	private function _runCallbacks($callbacks, $data=false) {
		if (!is_array($callbacks) || !count($callbacks)) { return false; }
		$results = [];
		foreach ($callbacks as $call) {
			$args = func_get_args();
			array_shift($args);

			if (count($args) && $call["pattern"] && ($firstArg = current($args)) && is_string($firstArg)) {
				if (preg_match($call["pattern"], $firstArg, $matches)) {
					$args[current(array_keys($args))] = $matches;
				} else {
					continue;
				}
			}

			$results[] = call_user_func_array($call["callback"], $args);
		}
		return $results;
	}

	/**
	 * _RUNGLOBALCALLBACKS
	 *
	 * Run a callback from the overall process
	 *
	 * @param   string  $callback  Name of callback
	 * @param   mixed   $data      Any data to send to the callback
	 *
	 * @return  mixed              Whatever is returned from the callback; false on failure, or null
	 */
	private function _runGlobalCallbacks($callback, $data=false) {
		if (!array_key_exists($callback, $this->globalCallbacks)) { return false; }
		foreach ($this->globalCallbacks[$callback] as $call) {
			$args = func_get_args();
			array_shift($args);

			if (count($args) && $call["pattern"] && ($firstArg = current($args)) && is_string($firstArg)) {
				if (preg_match($call["pattern"], $firstArg, $matches)) {
					$args[current(array_keys($args))] = $matches;
				} else {
					continue;
				}
			}

			return call_user_func_array($call["callback"], $args);
		}
		return;
	}

	/**
	 * _STREAMISWRITABLE
	 *
	 * Test if a stream can be written to by the command (and therefore read by us)
	 *
	 * @param   string  $stream  Name of the stream
	 *
	 * @return  bool             Writable or not
	 */
	private function _streamIsWritable($stream) {
		return (array_key_exists($stream, $this->streamsConfig) && $this->streamsConfig[$stream][1] == "w");
	}

	/**
	 * _BUILDSTREAMSCONTAINER
	 *
	 * Build an array for holding data about each stream (including callbacks etc)
	 *
	 * @param   string  $stream  Name of the stream
	 *
	 * @return  array            Array of streamsData
	 */
	private function _buildStreamsContainer() {
		$index = 0;
		foreach ($this->streamsConfig as $k => $v) {
			$this->streamsData[$k] = [
				"index"       => $index++,
				"config"      => $v,
				"buffer"      => "",
				"output"      => "",
				"callbacks"   => [
					"line"       => [],
					"buffer"     => []
				],
				"watch"       => false,
				"watchBuffer" => false,
				"watchLine"   => false
			];
		}
		return $this->streamsData;
	}

	/**
	 * _STRHASNL
	 *
	 * Detect if a string has new lines in it. If compat mode is set then the slightly
	 * slower but more compatible method is chosen.
	 *
	 * @param   string  $string  The string to search in
	 *
	 * @return  array            Array of streamsData
	 */
	private function strHasNL($string) {
		if (!$this->compatMode) {
			return (strpos($string, PHP_EOL) !== false);
		}
		return (
			(strpos($string, "\n") !== false) ||
			(strpos($string, "\r") !== false)
		);
	}

	/**
	 * _STRSPLITBYNL
	 *
	 * Split a string by new lines. If compat mode is set then the slightly slower but
	 * more compatible method is chosen.
	 *
	 * @param   string  $string  The string to split
	 *
	 * @return  array            Array of streamsData
	 */
	private function strSplitByNL($string) {
		if (!$this->compatMode) {
			return explode(PHP_EOL, $string);
		}
		// use OR in the regex instead of [\r\n]+ so that empty lines aren't stripped
		return preg_split("/\r\n|\r|\n/", $string);
	}

}