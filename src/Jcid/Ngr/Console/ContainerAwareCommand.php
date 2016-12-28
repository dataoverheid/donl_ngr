<?php

namespace Jcid\Ngr\Console;

use Symfony\Component\Console\Command\Command;

abstract class ContainerAwareCommand extends Command
{
	/**
	 * Gets a service by id.
	 *
	 * @param  string $id The service id
	 * @return object The service
	 */
	public function get($id)
	{
		return $this->getApplication()->getContainer()->get($id);
	}
}
