<?php

namespace Jcid\Ngr\Transformer;

use Jcid\Framework\CleanArray;
use Jcid\Ngr\Model\Package;
use Jcid\Ngr\Model\Resource;
use Jcid\Ngr\Model\Source;

class NgrToCkanTransformer
{
	/**
	 * @var \DOMXPath
	 */
	private $xpath;

	/**
	 * @param  \DOMDocument $document
	 * @return Package[]
	 */
	public function transform(\DOMDocument $document)
	{
		$this->xpath	= new \DOMXPath($document);
		foreach ($this->getNamespaces() as $key => $value) {
			$this->xpath->registerNamespace($key, $value);
		}
		$items = $this->xpath->query("/csw:GetRecordsResponse/csw:SearchResults/gmd:MD_Metadata");

		$data = [];
		foreach ($items as $item) {
			$data[] = $this->transformItem($item);
		}

		return $data;
	}

	/**
	 * @param  \DOMElement $item
	 * @return Package  
	 */
	private function transformItem(\DOMElement $item)
	{
		$package = new Package();

		// Id ophalen (Bart  { en } uit Trim gehaald ivm Assembla issue 181)
		$ngrID = trim( $this->querySingleValue("gmd:fileIdentifier/gco:CharacterString", $item), " " );
		$package->setNgrId( $ngrID );
        
        
		// Title ophalen
		$package->setTitle($this->querySingleValue("gmd:identificationInfo/*/gmd:citation/*/gmd:title/gco:CharacterString", $item));

		// Licenties
		$package->setLicenses($this->queryMultipleValues("gmd:identificationInfo/*/gmd:resourceConstraints/*/gmd:otherConstraints/gco:CharacterString", $item));

		
		// LanguageCode
		$package->setLanguage($this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language/gco:CharacterString", $item));
		
		if ($package->getLanguage() == null) {
			$package->setLanguage($this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:language/gmd:LanguageCode/@codeListValue", $item));
		}
		
		
		// Spatial
		$package->setSpatial($this->querySingleValue("gmd:identificationInfo/*/*/*/gmd:geographicElement/*/gmd:geographicIdentifier/*/gmd:code/gco:CharacterString", $item));
		
		// Modified
		$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='revision'][last()]/gmd:CI_Date/gmd:date/gco:Date", $item);
		if (empty($modified)) {
			$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='revision'][last()]/gmd:CI_Date/gmd:date/gco:DateTime", $item);
		}
		//file_put_contents("logs/modified.log", sprintf("Debug: Modified for NGR %s = %s)  \n", $package->getNgrId(), $modified), FILE_APPEND);
		if (!empty($modified)) {
		  if (strlen($modified)==4 && is_numeric($modified)) {
		    $package->setModified("01-01-".$modified);
		  } else {
			$parsed = strtotime($modified);
			$package->setModified(date('d-m-Y', $parsed));
		  }
		}
		else {
			$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='creation'][last()]/gmd:CI_Date/gmd:date/gco:Date", $item);
			if (empty($modified)) {
				$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='creation'][last()]/gmd:CI_Date/gmd:date/gco:DateTime", $item);
			}
			if (!empty($modified)) {
				if (strlen($modified)==4 && is_numeric($modified)) {
					$package->setModified("01-01-".$modified);
				} else {
					$parsed = strtotime($modified);
					$package->setModified(date('d-m-Y', $parsed));
				}
			}
			else {
				$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='publication'][last()]/gmd:CI_Date/gmd:date/gco:Date", $item);
				if (empty($modified)) {
					$modified = $this->querySingleValue("gmd:identificationInfo/gmd:MD_DataIdentification/gmd:citation/gmd:CI_Citation/*[./gmd:CI_Date/gmd:dateType/*/@codeListValue='publication'][last()]/gmd:CI_Date/gmd:date/gco:DateTime", $item);
				}
				if (!empty($modified)) {
					if (strlen($modified)==4 && is_numeric($modified)) {
						$package->setModified("01-01-".$modified);
					} else {
						$parsed = strtotime($modified);
						$package->setModified(date('d-m-Y', $parsed));
					}
				}
				else {
					$package->setModified((date('d-m-Y')));
					//file_put_contents("logs/wrong_mappings.log", sprintf("Warning: Modified for NGR %s not equals revisiondate, modified equals creationdate or currentdate (modified = %s)  \n", $package->getNgrId(), $package->getModified()), FILE_APPEND);
				}
			}
		}
		
		// Summary
		$package->setNotes($this->querySingleValue("gmd:identificationInfo/*/gmd:abstract/gco:CharacterString", $item));

		// Tags
		$tags	= [];
		//$tags	= array_merge($tags, $this->queryMultipleValues("gmd:identificationInfo/*/gmd:topicCategory/gmd:MD_TopicCategoryCode", $item));
		$tags	= array_merge($tags, $this->queryMultipleValues("gmd:identificationInfo/*/gmd:descriptiveKeywords/*/gmd:keyword/gco:CharacterString", $item));
		$tags	= CleanArray::format($tags);
		$package->setTags($tags);

		// Ophalen protocol
		$package->setProtocol(CleanArray::format($this->queryMultipleValues("gmd:distributionInfo/*/gmd:transferOptions/*/gmd:onLine/*/gmd:protocol/gco:CharacterString", $item)));

		// Resources ophalen
		$resources				= $this->xpath->query("gmd:distributionInfo/*/gmd:transferOptions/*/gmd:onLine", $item);
		foreach ($resources as $resourceData) {
			$resource = (new Resource())
				->setProtocol($this->querySingleValue("*/gmd:protocol/gco:CharacterString", $resourceData))
				->setUrl($this->querySingleValue("*/gmd:linkage/gmd:URL", $resourceData))
				->setName($this->querySingleValue("*/gmd:name /gco:CharacterString", $resourceData));
			$package->addResource($resource);
		}
		
		
		//Maintainer ophalen
		$package->setMaintainer($this->querySingleValue("gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:pointOfContact[1]/gmd:CI_ResponsibleParty[1]/gmd:organisationName[1]/gco:CharacterString", $item));
		$package->setMaintainer_email($this->querySingleValue("gmd:identificationInfo[1]/gmd:MD_DataIdentification[1]/gmd:pointOfContact[1]/gmd:CI_ResponsibleParty[1]/gmd:contactInfo[1]/*/gmd:address/*/gmd:electronicMailAddress/gco:CharacterString", $item));

		// Source ophalen
		$roles	= CleanArray::format($this->queryMultipleValues("gmd:identificationInfo/*/gmd:pointOfContact/*/gmd:role/gmd:CI_RoleCode/@codeListValue", $item));
		$names	= CleanArray::format($this->queryMultipleValues("gmd:identificationInfo/*/gmd:pointOfContact/*/gmd:organisationName/gco:CharacterString", $item));
		$emails	= CleanArray::format($this->queryMultipleValues("gmd:identificationInfo/*/gmd:pointOfContact/*/gmd:contactInfo/*/gmd:address/*/gmd:electronicMailAddress/gco:CharacterString", $item));
		$source	= (new Source())
			->setRole(isset($roles[0])?$roles[0]:null)
			->setName(isset($names[0])?$names[0]:null)
			->setEmail(isset($emails[0])?$emails[0]:null);
		$package->setSource($source);

		// Landingpage
		$landingpage = utf8_encode(htmlentities($this->querySingleValue("gmd:identificationInfo/*/gmd:supplementalInformation/gco:CharacterString", $item)));
		if (preg_match("/http.+$/", $landingpage, $matches) == 1) $landingpage = $matches[0];
		$package->setLandingpage($landingpage);
		
		//Temporal (startdate)
		$start = $this->querySingleValue("gmd:identificationInfo/*/*/*/gmd:temporalElement/*/gmd:extent/*/gml:beginPosition", $item);
		if (isset($start)) {
		  if (strlen($start)==4 && is_numeric($start)) {
		    $package->setStartdate("01-01-".$start);
		  } else {
  		  $parsed = strtotime($start);
  		  $package->setStartdate(date('d-m-Y', $parsed));
  		}
		}
		else {
			$start = $this->querySingleValue("gmd:identificationInfo/*/*/*/gmd:temporalElement/*/gmd:extent/*/gml:begin/*/gml:timePosition", $item);
			if (isset($start)) {
			  if (strlen($start)==4 && is_numeric($start)) {
				$package->setStartdate("01-01-".$start);
			  } else {
			  $parsed = strtotime($start);
			  $package->setStartdate(date('d-m-Y', $parsed));
			  }
			}
		}
		
		//MD_Metadata.identificationInfo>MD_DataIdentification.extent>EX_Extent.temporalElement>EX_TemporalExtent.extent
		//$package->setStartdate("2014-12-01");
		//Temporal (enddate)
		$end = $this->querySingleValue("gmd:identificationInfo/*/*/*/gmd:temporalElement/*/gmd:extent/*/gml:endPosition", $item);
		if (isset($end)) {
		  if (strlen($end)==4 && is_numeric($end)) {
		    $package->setEnddate("01-01-".$end);
		  } else {
  		  $parsed = strtotime($end);
  		  $package->setEnddate(date('d-m-Y', $parsed));
  		}
		}
		else {
			$end = $this->querySingleValue("gmd:identificationInfo/*/*/*/gmd:temporalElement/*/gmd:extent/*/gml:end/*/gml:timePosition", $item);
			if (isset($end)) {
			  if (strlen($end)==4 && is_numeric($end)) {
				$package->setEnddate("31-12-".$end);
			  } else {
			  $parsed = strtotime($end);
			  $package->setEnddate(date('d-m-Y', $parsed));
			  }
			}
		}

  	//if (isset($start) || isset($end)) file_put_contents("logs/temporal.log", sprintf("Title %s | start: %s end: %s | ps: %s pe: %s\n", $package->getTitle(), $start, $end, $package->getStartdate(), $package->getEnddate()), FILE_APPEND);

		
		// accrual_periodicity
		$frequency = $this->querySingleValue("gmd:identificationInfo/*/gmd:resourceMaintenance/*/gmd:maintenanceAndUpdateFrequency/gmd:MD_MaintenanceFrequencyCode/@codeListValue", $item);
		$package->setAccrualPeriodicity(isset($frequency)?$frequency:null);
		//Tags
		$tags	= [];
		$tags	= array_merge($tags, $this->queryMultipleValues("gmd:identificationInfo/*/gmd:descriptiveKeywords/*/gmd:keyword/gco:CharacterString", $item));
		$tags	= CleanArray::format($tags);
		$package->setTags($tags);
		
		
		//Themes
		$themes	= [];
		$themes	= array_merge($themes, $this->queryMultipleValues("gmd:identificationInfo/*/gmd:topicCategory/gmd:MD_TopicCategoryCode", $item));
		$themes	= CleanArray::format($themes);
		$package->setThemes($themes);

		
		return $package;
	}

	/**
	 * @param  string $query
	 * @param  null   $node
	 * @return string
	 */
	private function querySingleValue($query, $node = null)
	{
		$items = $this->xpath->query($query, $node);
		if ($items->length === 1) {
			return $items->item(0)->nodeValue;
		}
	}

	/**
	 * @param  string $query
	 * @param  null   $node
	 * @return array
	 */
	private function queryMultipleValues($query, $node = null)
	{
		$items = $this->xpath->query($query, $node);
		$data = [];
		foreach ($items as $item) {
			$data[] = $item->nodeValue;
		}

		return $data;
	}

	/**
	 * @return array
	 */
	private function getNamespaces()
	{
		return array(
			'csw' => 'http://www.opengis.net/cat/csw/2.0.2',
			'xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
			'geonet' => 'http://www.fao.org/geonetwork',
			'gmd' => 'http://www.isotc211.org/2005/gmd',
			'srv' => 'http://www.isotc211.org/2005/srv',
			'gml' => 'http://www.opengis.net/gml',
			'gco' => 'http://www.isotc211.org/2005/gco',
		);
	}
}
