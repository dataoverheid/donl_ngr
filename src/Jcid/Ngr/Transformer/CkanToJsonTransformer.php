<?php

namespace Jcid\Ngr\Transformer;

use Jcid\Ngr\Model\Package;

class CkanToJsonTransformer
{
	/**
	 * @param  Package $package
	 * @return string
	 */
	public function transform(Package $package, $wrongmapping, $action)
	{
	    if( $action == "Aanmaken" ) {
             // Een op een velden mappen
    		$data = [
    			"title"			=> $package->getTitle(),
    			"name"			=> $package->getName(),
    			"md_uri"		=> sprintf("http://nationaalgeoregister.nl/geonetwork?uuid=%s", $package->getNgrId()),
    			"notes"			=> $package->getNotes(),
    			"resources"		=> $package->getResources(),
    			"identifier" 	=> $package->getNgrId(),
    			"extras"		=> [],
    			"landingpage"	=> $this->getDonlUrl($package->getLandingpage()),
                "owner_org"     =>  $package->getOwner_org()
    		];
        }
        else {
            //Zonder resources voor updata/pacth
            $data = [
    			"title"			=> $package->getTitle(),
    			"name"			=> $package->getName(),
    			"md_uri"		=> sprintf("http://nationaalgeoregister.nl/geonetwork?uuid=%s", $package->getNgrId()),
    			"notes"			=> $package->getNotes(),
    			"identifier" 	=> $package->getNgrId(),
    			"extras"		=> [],
    			"landingpage"	=> $this->getDonlUrl($package->getLandingpage()),
                "owner_org"     =>  $package->getOwner_org()
    		];
        }
        
        
        //Toevoegen van een ID voor een Patch
        if( $package->getID() != "" ) {
            $data["id"] = $package->getID();
        }
        
		$dataset_status = 'http://data.overheid.nl/status/beschikbaar';
		$data["dataset_status"] = $dataset_status;

		$accessibility = 'http://data.overheid.nl/acessrights/publiek';
		$data["accessibility"] = $accessibility;

		$high_value_dataset = false;
		$data["$high_value_dataset"] = $high_value_dataset;


		// Tags converten
		$data["tags"] = array_map(function ($tag) {
			$tag = utf8_decode($tag);
			$tag = preg_replace('/[^\da-z \-_\.]/i', '', $tag);
			$tag = substr($tag, 0, 100);

			return [
				"name" => $tag,
			];
		}, $package->getTags());
		
		// Modified
		$data["modified"] = $package->getModified();
		
		// Theme	
		$thememapping = $this->getThemeMapping();
		$themes = $package->getThemes();
		

		//Kijk eerst of thema is gevuld
		if (isset($themes[0])) {
			//Kijk vervolgens of thema 1 is gemapped naar owms uri
			if (isset($thememapping[strtolower($themes[0])])) {
				//$data["theme"] = $thememapping[$themes[0]];
				$data["theme"] = trim($thememapping[strtolower($themes[0])]);
			}
			else {
				file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping Theme0 missing for NGR %s , theme = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $themes[0]), FILE_APPEND);
				$data["theme"] = $themes[0];
			}
		}
		else {
			$data["theme"] = null;
		}
		
		//Kijk eerst of thema is gevuld
		if (isset($themes[1])) {
			//Kijk vervolgens of thema 1 is gemapped naar owms uri
			if (isset($thememapping[strtolower($themes[1])])) {
				$data["theme_secondary"] = trim($thememapping[strtolower($themes[1])]);
			}
			else {
				file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping ThemeSecondary missing for NGR %s , theme = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $themes[1]), FILE_APPEND);
				$data["theme_secondary"] = $themes[1];
			}
		}
		else {
			$data["theme_secondary"] = null;
		}
		
		//Temporal
		$data["temporal_from"] = $package->getStartdate();
		$data["temporal_to"] = $package->getEnddate();
		
		$data["spatial"] = $package->getSpatial();
		
		// Eigenaarschap
		$source	= $package->getSource();
		$role	= strtolower($source->getRole());
		
		
		$orgmapping = $this->getOrganizationMapping();
		$source_name_temp = $source->getName();
		$source_name = $source_name_temp;
		if (isset($orgmapping[strtolower($source_name_temp)])) {
				$source_name = trim($orgmapping[strtolower($source_name_temp)]);
		}
		else {
			file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping Organization missing for NGR %s , Organization = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $source_name_temp), FILE_APPEND);
		}
        
		$maintainer = null;
		$author = null;
		$cp = null;
		
		$orgmapping = $this->getOrganizationMapping();
		$maintainer_name_temp = $package->getMaintainer();
		//$maintainer = $source_name_temp;
		if (isset($orgmapping[strtolower($maintainer_name_temp)])) {
			$maintainer = trim($orgmapping[strtolower($maintainer_name_temp)]);
		}
		else {
			// Kan niet gemapt worden? Dan maintainer onbekend
			$maintainer = 'http://data.overheid.nl/organisatie/onbekend';
			file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping MaintainerOrganization missing for NGR %s , MaintainerOrganization = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $maintainer_name_temp), FILE_APPEND);
		}
		
		
		//$data["maintainer"] = $package->getMaintainer();
		$data["maintainer"] = $maintainer;
		$data["maintainer_email"] = $package->getMaintainer_email();
		//Vul maintainer=authority=registratiehouder wanneer wordt voldaan aan onderstaande rollen
		/*if (in_array($role, ["eigenaar", "owner","auteur", "author"])) {
			$data["maintainer"] = $source_name;
			$data["maintainer_email"] = $source->getEmail();
			$maintainer = $source_name;
		//Vul author=publisher=verstrekker wanneer wordt voldaan aan onderstaande rollen
		} else*/if (in_array($role, array("verstrekker", "resourceprovider", "distributeur", "distributor","uitgever","publisher"))) {
			$data["author"]	= $source_name;
			$data["author_email"] = $source->getEmail();
			$author = $source_name;
		//Vul contact_point=aanmelder wanneer wordt voldaan aan onderstaande rollen
		} elseif (in_array($role, ["beheerder", "custodian","contactpunt", "pointOfContact"])) {
			$data["contact_point"]	= $source_name;
			$cp = $source_name;
		}
		//Als maintainer niet is gevuld dan loggen met author
		//if (!isset($maintainer)) {
			//file_put_contents("logs/wrong_mappings.log", sprintf("Error: Maintainer missing for NGR %s , author = %s , cp = %s \n", $package->getNgrId(), $author, $cp), FILE_APPEND);
		//}

		
		// Vanuit NGR wordt er geen lod stars teruggegeven, daarom standaard op 0
		$data["lod_stars"] = 0;

		// Licentie bepalen
		$licensemapping = $this->getLicenseMapping();
		foreach ($this->getLicenses() as $licenseId => $licenseRegex) {
			$matches = preg_grep($licenseRegex, $package->getLicenses());
			if (!empty($matches)) {
				if (isset($licensemapping[strtolower($licenseId)])) {
					$data["license_url"] = trim($licensemapping[strtolower($licenseId)]);
				}
				else {
					file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping License missing for NGR %s , license = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $licenseId), FILE_APPEND);
					$data["license_url"] = "-";
				}
				$data["license_id"] = $licenseId;
				break;
			}
		}
		
		
		$frequencymapping = $this->getFrequencyMapping();
		if (isset($frequencymapping[strtolower($package->getAccrualPeriodicity())])) {
			$data["accrual_periodicity"] = trim($frequencymapping[strtolower($package->getAccrualPeriodicity())]);
		}
		else {
			file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping accrualPeriodicity missing for NGR %s , getAccrualPeriodicity = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), $package->getAccrualPeriodicity()), FILE_APPEND);
			$data["accrual_periodicity"] = $package->getAccrualPeriodicity();
		}
		
		//Taal is niet altijd correct gevuld vanuit NGR, daarom default op Nederlands
		$languagemapping = $this->getLanguageMapping();
		if (isset($languagemapping[strtolower($package->getLanguage())])) {
			$data["language"] = trim($languagemapping[strtolower($package->getLanguage())]);
		}
		else {
			file_put_contents($wrongmapping, sprintf("%s - Warning: Mapping Language missing for NGR %s , language = %s  \n", date("Y-m-d H:i:s"), $package->getNgrId(), "Nederlands"), FILE_APPEND);
			$data["language"] = "nl-NL";
		}
		// Map resource formats
		$formatMapping = $this->getFormatMapping();
        if( $action == "Aanmaken" ) {
    		foreach ($data["resources"] as $resource) {
    			if (isset($formatMapping[strtolower($resource->getProtocol())])) {
    				$resource->setProtocol(trim($formatMapping[strtolower($resource->getProtocol())]));
    			}
    			$resource->setUrl($this->getDonlUrl($resource->getUrl()));
    		}
        }
        
        
		// return JSON
		return json_encode($data);
	}

	/**
	 * @return array
	 */
	private function getLicenses()
	{
		return array(
			'publiek-domein'	=> '/^http:\/\/creativecommons\.org\/publicdomain\/mark\/1\.0\/deed\.nl/',
			'cc-0'	=> '/^http:\/\/creativecommons\.org\/publicdomain\/zero\/1\.0/',
			'cc-by3'	=> '/^http:\/\/creativecommons.org\/licenses\/by/',
		);
	}
	
	private function getFrequencyMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/frequency.csv");
	}
	
	private function getLanguageMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/language.csv");
	}
	
	private function getLicenseMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/license.csv");
	}
	
	private function getThemeMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/themes.csv");
	}
	
	private function getOrganizationMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/organizations.csv");
	}

	private function getFormatMapping()
	{
		return $this->getMapping("/var/data/stamdata/mappings/formats.csv");
	}
	
	private function getMapping($file)
	{
		$arAssoc = array();
		$handle = fopen($file, "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				// process the line read.
				$arTemp = explode("\t",$line);
				if (isset($arTemp[0]))
					$arAssoc[strtolower($arTemp[0])] = isset($arTemp[1]) ? $arTemp[1] : null;
			}
		} else {
			// error opening the file.
		} 
		fclose($handle);
	
		
		return $arAssoc;
	}
	
	private function getDonlUrl($url)
	{
		return $url;
		
		# if (!isset($url) || $url == "") return $url;
		# $url = trim($url);
        # 
		# // Source: https://mathiasbynens.be/demo/url-regex
		# $validUrlRegex = '_^(?:(?:https?|ftp)://)(?:\S+(?::\S*)?@)?(?:(?!10(?:\.\d{1,3}){3})(?!127(?:\.\d{1,3}){3})(?!169\.254(?:\.\d{1,3}){2})(?!192\.168(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)(?:\.(?:[a-z\x{00a1}-\x{ffff}0-9]+-?)*[a-z\x{00a1}-\x{ffff}0-9]+)*(?:\.(?:[a-z\x{00a1}-\x{ffff}]{2,})))(?::\d{2,5})?(?:/[^\s]*)?$_iu';
		# if (preg_match($validUrlRegex, $url))
		# 	return $url;
		# else
		# 	return "https://data.overheid.nl/foutievelink?link=".urlencode($url);
	}

}
