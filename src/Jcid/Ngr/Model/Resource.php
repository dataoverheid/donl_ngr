<?php

namespace Jcid\Ngr\Model;

class Resource implements \JsonSerializable
{
	/**
	 * @var string
	 */
	private $protocol;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @param  string                   $protocol
	 * @return \Jcid\Ngr\Model\Resource
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}

	/**
	 * @param  string                   $url
	 * @return \Jcid\Ngr\Model\Resource
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param  string                   $name
	 * @return \Jcid\Ngr\Model\Resource
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize()
	{
		return [
			"protocol"	=> $this->protocol,
			"format" => $this->protocol,
			"url"		=> $this->url,
			"name"		=> $this->name,
		];
	}
}
