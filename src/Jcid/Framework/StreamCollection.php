<?php

namespace Jcid\Framework;

class StreamCollection implements \Iterator
{
	/**
	 * @var callable
	 */
	private $callable;

	/**
	 * @var integer
	 */
	private $size;

	/**
	 * @var integer
	 */
	private $position;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $initialized;

	/**
	 * @param callable $callable
	 * @param integer  $size
	 */
	public function __construct(\Closure $callable, $size)
	{
		$this->callable		= $callable;
		$this->size			= $size;
		$this->position		= 1;
		$this->data			= array();
		$this->initialized	= array();
	}

	/**
	 * Rewind the array
	 */
	public function rewind()
	{
		$this->initialize();
		$this->position = 1;
	}

	/**
	 * Get the current object
	 *
	 * @return mixed
	 */
	public function current()
	{
		$this->initialize();
		return $this->data[$this->position];
	}

	/**
	 * Get the current key
	 *
	 * @return int
	 */
	public function key()
	{
		$this->initialize();
		return $this->position;
	}

	/**
	 * Goto next item
	 */
	public function next()
	{
		$this->initialize();
		++$this->position;
	}

	/**
	 * Validate item exists
	 *
	 * @return bool
	 */
	public function valid()
	{
		$this->initialize();
		return isset($this->data[$this->position]);
	}

	/**
	 * Initialize the collection
	 */
	private function initialize()
	{
		// Elke x page opnieuw ophalen (mits niet al gedraaid is
		$page = (int) floor($this->position / $this->size) + 1;
		if ($this->position % $this->size === 1 && !isset($this->initialized[$page])) {
			$this->initialized[$page] = true;

			// Ophalen en migreren van data
			$newData	= call_user_func($this->callable, $page, $this->position);
			if ($newData) {
				$size		= count($newData);
				$keys		= range($this->position, $this->position + $size - 1);
				$this->data	= array_combine($keys, $newData);
			}
		}
	}
}
