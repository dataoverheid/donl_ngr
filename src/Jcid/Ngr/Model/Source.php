<?php

namespace Jcid\Ngr\Model;

class Source
{
	/**
	 * @var string
	 */
	private $role;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @param  string $role
	 * @return Source
	 */
	public function setRole($role)
	{
		$this->role = $role;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @param  string $name
	 * @return Source
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
	 * @param  string $email
	 * @return Source
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}
}
