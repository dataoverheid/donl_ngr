<?php

namespace Jcid\Ngr\Cache;

class Cache
{
	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var array
	 */
	private $ids;

	/**
	 * @var array
	 */
	private $urls;

	/**
	 * @var bool
	 */
	private $retrieved;

	/**
	 * @param string $path
	 */
	public function __construct($path)
	{
		$this->path = $path;
		$this->clear();
	}

	/**
	 * @param string $ngrId
	 * @param string $ckanId
	 */
	public function addIdMatch($ngrId, $ckanId)
	{
		$this->retrieve();
		$this->ids[$ngrId] = $ckanId;
	}

	/**
	 * @param string $ngrId
	 * @return string
	 */
	public function getCkanId($ngrId)
	{
		$this->retrieve();
		return isset($this->ids[$ngrId]) ? $this->ids[$ngrId] : null;
	}

	/**
	 * @param string $ckanId
	 * @param string $url
	 */
	public function addUrl($ckanId, $url)
	{
		$this->retrieve();
		$this->urls[$ckanId] = $url;
	}

	/**
	 * @param string $url
	 * @return bool
	 */
	public function getCkanIdByUrl($url)
	{
		$this->retrieve();
		return array_search($url, $this->urls);
	}

	/**
	 * @param string $ckanId
	 * @return string
	 */
	public function getCkanUrlById($ckanId)
	{
		$this->retrieve();
		return isset($this->urls[$ckanId]) ? $this->urls[$ckanId] : null;
	}

	/**
	 * @return bool
	 */
	public function hasItems()
	{
		$this->retrieve();

		return count($this->ids) > 0 && count($this->urls) > 0;
	}

	/**
	 * Opruimen cache
	 */
	public function clear()
	{
		file_put_contents($this->path."/ids.json", "");
		file_put_contents($this->path."/urls.json", "");

		$this->ids = [];
		$this->urls = [];
	}

	/**
	 * Ophalen flush
	 */
	public function flush()
	{
		$idData = json_encode($this->ids, JSON_PRETTY_PRINT);
		file_put_contents($this->path."/ids.json", $idData);

		$urlData = json_encode($this->urls, JSON_PRETTY_PRINT);
		file_put_contents($this->path."/urls.json", $urlData);
	}

	/**
	 * Ophalen cache
	 */
	private function retrieve()
	{
		if ($this->retrieved) {
			return;
		}

		if (file_exists($this->path."/ids.json")) {
			$idData = file_get_contents($this->path."/ids.json");
			$this->ids = json_decode($idData, true);
		}

		if (file_exists($this->path."/urls.json")) {
			$urlData = file_get_contents($this->path."/urls.json");
			$this->urls = json_decode($urlData, true);
		}

		$this->retrieved = true;
	}
}
