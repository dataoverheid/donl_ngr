<?php

namespace Jcid\Ngr\Connector;

use GuzzleHttp\Client;
use Jcid\Framework\ConsoleOutput;
use Jcid\Framework\StreamCollection;
use Jcid\Ngr\Model\Package;
use Jcid\Ngr\Transformer\NgrToCkanTransformer;
use Symfony\Component\Console\Output\OutputInterface;

class Ngr
{
	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var ConsoleOutput
	 */
	private $output;

	/**
	 * @var NgrToCkanTransformer
	 */
	private $transformer;

	/**
	 * @param ConsoleOutput        $output
	 * @param NgrToCkanTransformer $transformer
	 * @param array                $config
	 */
	public function __construct(ConsoleOutput $output, NgrToCkanTransformer $transformer, array $config)
	{
		$this->client = new Client($config);
		$this->transformer = $transformer;
		$this->output = $output;
	}

	/**
	 * @param  integer                    $limit
	 * @return StreamCollection|Package[]
	 */
	public function getStream($limit)
	{
		return new StreamCollection(function ($page) use ($limit) {
			// Gegevens opvragen vanuit NGR
			$offset = (($page - 1) * $limit) + 1;
			$this->output->writelnInfo(sprintf("Ophalen gegeven uit het NGR offset: %s", $offset), OutputInterface::OUTPUT_NORMAL, OutputInterface::VERBOSITY_VERY_VERBOSE);
			$postData = file_get_contents(__DIR__ . "/../Resources/ngr/request.xml");
			$postData = sprintf($postData, $offset, $limit);
			
			//response = $this->client->post("/geonetwork/srv/dut/csw", ["body" => $postData])->getBody();
			//Nieuwe manier om
			$url = 'http://nationaalgeoregister.nl/geonetwork/srv/dut/csw';

			$stream_options = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => "Content-type: application/xml\r\n",
					'content' => $postData,
				),
			);

			$context  = stream_context_create($stream_options);
			$response = file_get_contents($url, null, $context);

			//
			// Gegevens inladen in DOMDocument
			$this->output->writelnInfo(" - Gegevens inladen in DOMDocument", OutputInterface::OUTPUT_NORMAL, OutputInterface::VERBOSITY_DEBUG);
			$document = new \DOMDocument();
			$document->loadXML($response);
			//file_put_contents('log_xml.txt', $document->saveXML(), FILE_APPEND);
			// Gegevens transformeren naar objecten
			$this->output->writelnInfo(" - Gegevens transformeren naar objecten", OutputInterface::OUTPUT_NORMAL, OutputInterface::VERBOSITY_DEBUG);

			return $this->transformer->transform($document);
		}, $limit);
	}
}
