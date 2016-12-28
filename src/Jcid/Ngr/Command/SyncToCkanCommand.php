<?php

namespace Jcid\Ngr\Command;

use Jcid\Framework\ConsoleOutput;
use Jcid\Ngr\Console\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SyncToCkanCommand extends ContainerAwareCommand
{
	/**
	 *
	 */
	protected function configure()
	{
		$this->setName("ngr:to:ckan")
			->setDescription("Sync datasets from NGR to CKAN")
			->addOption("update-cache", null, InputOption::VALUE_NONE, "Cache moet opnieuw worden opgebouwd")
			->addOption("delete-orphans", null, InputOption::VALUE_NONE, "Verwijder wezen (orphans)")
			->addOption("skip-updates", null, InputOption::VALUE_NONE, "Overslaan van updates");
	}

	/**
	 * @param  InputInterface $input
	 * @param  ConsoleOutput  $output
	 * @return int|void
	 */
	protected function execute(InputInterface $input, ConsoleOutput $output)
	{
		$start	= microtime(true);

		// Services
		$ngr	= $this->get("ngr.connector.ngr");
		$ckan	= $this->get("ngr.connector.ckan");
		$cache	= $this->get("ngr.cache");

		// JK 20150122 ALWAYS BUILD CACHE
		// Cache controleren
		//if ($input->getOption("update-cache") || !$cache->hasItems()) {

			// Vragen wat te doen
			//$helper		= $this->getHelper("question");
			//$question	= new ConfirmationQuestion("We hebben geen cache items wil je de cache opbouwen? (Y/n)", true);

			// Cache builden waar nodig
			//if ($input->getOption("update-cache") || $helper->ask($input, $output, $question)) {
				$cache->clear();
				$ckan->buildCache();
			//}
		//}

		// Ophalen en verwerken items
		$ckan->sendStream($ngr->getStream(100), $input->getOption("skip-updates"));

		// Verwijderen items uit CKAN die niet (meer) in NGR staan
		if ($input->getOption("delete-orphans"))
			$ckan->deleteOrphans();

		$output->writelnInfo(sprintf("Proces afgerond in %s seconden", microtime(true) - $start));
	}
}
