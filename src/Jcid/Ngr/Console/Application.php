<?php

namespace Jcid\Ngr\Console;

use Jcid\Framework\ConsoleOutput;
use Jcid\Ngr\Command\SyncToCkanCommand;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;

class Application extends BaseApplication
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var
	 */
	private $dirbase;

	/**
	 * @param string $dirbase
	 */
	public function __construct($dirbase)
	{
		parent::__construct();

		$this->dirbase = $dirbase;
	}

	/**
	 * Runs the current application
	 */
	public function run(InputInterface $input = null, OutputInterface $output = null)
	{
		if (null === $output) {
			$output = new ConsoleOutput();
		}

		$container = $this->getContainer();
		$container->set("console.input", $input);
		$container->set("console.output", $output);

		return parent::run($input, $output);
	}

	/**
	 * Container ophalen
	 */
	public function getContainer()
	{
		if ($this->container) {
			return $this->container;
		}

		// Basic container
		$containerBuilder = new ContainerBuilder();
		$containerBuilder->setParameter("dir.base", $this->dirbase);

		// Load services
		$loader	= new XmlFileLoader($containerBuilder, new FileLocator(__DIR__."/../Resources/config/"));

		// Config opzoeken
		$finder		= new Finder();
		$iterator	= $finder
			->files()
			->name("*.xml")
			->in(__DIR__."/../Resources/config/");
		foreach ($iterator as $file) {
			$loader->load($file->getFilename());
		}

		// Config inladen
		$configData = file_get_contents($this->dirbase."/config.json");
		$config = json_decode($configData, true);
		foreach ($config as $key => $value) {
			$containerBuilder->setParameter(sprintf("config.%s", $key), $value);
		}

		// Compilen is nodig voor compiler pass
		$containerBuilder->compile();

		return $this->container = $containerBuilder;
	}

	/**
	 * Initializes all the commands
	 */
	protected function getDefaultCommands()
	{
		return array_merge(
			parent::getDefaultCommands(),
			array(
				new SyncToCkanCommand(),
			)
		);
	}

}
