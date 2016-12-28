<?php

namespace Jcid\Framework;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput as BaseConsoleOutput;

class ConsoleOutput extends BaseConsoleOutput
{
	/**
	 * @param array $messages
	 * @param int   $type
	 * @param int   $verbosity
	 */
	public function writelnInfo($messages, $type = self::OUTPUT_NORMAL, $verbosity = self::VERBOSITY_NORMAL)
	{
		$this->writeInfo($messages, true, $type, $verbosity);
	}

	/**
	 * @param array $messages
	 * @param bool  $newline
	 * @param int   $type
	 * @param int   $verbosity
	 */
	public function writeInfo($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL, $verbosity = self::VERBOSITY_NORMAL)
	{
		if ($this->getVerbosity() < $verbosity) {
			return;
		}

		$messages = (array) $messages;

		foreach ($messages as $key => $message) {
			$messages[$key] = $this->getInfo().$message;
		}

		parent::write($messages, $newline, $type);
	}

	/**
	 * @return string
	 */
	public function getInfo()
	{
	  date_default_timezone_set("Europe/Amsterdam");
		return sprintf("%-20s", "[".date("H:i:s")." ".$this->getMemory()."]");
	}

	/**
	 * @return string
	 */
	private function getMemory()
	{
		$size = memory_get_usage(true);
		$unit = array("b","kb","mb","gb","tb","pb");
		return @round($size/pow(1024,($i=floor(log($size,1024)))))." ".$unit[$i];
	}
}
