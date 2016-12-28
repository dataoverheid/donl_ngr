<?php

namespace Jcid\Framework;

class CleanArray
{
	/**
	 * @param array $array
	 * @return array
	 */
	public static function format(array $array)
	{
		// Trim alle items
		array_map(function($item){
			return trim($item);
		}, $array);

		// Lege items filteren
		$array = array_filter($array);

		// Alleen unique items
		$array = array_unique($array);

		// Opnieuw keys bereken
		$array = array_values($array);

		return $array;
	}
}
