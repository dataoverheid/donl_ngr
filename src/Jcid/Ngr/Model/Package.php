<?php

namespace Jcid\Ngr\Model;

use Ferrandini\Urlizer;

class Package
{
	/**
	 * @var string
	 */
	private $ngrid;

	/**
	 * @var string
	 */
	private $title;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $licenses;

	/**
	 * @var string
	 */
	private $language;

	/**
	 * @var string
	 */
	private $notes;

	/**
	 * @var array
	 */
	private $tags;

	/**
	 * @var array
	 */
	private $protocol;

	/**
	 * @var \Jcid\Ngr\Model\Resource[]
	 */
	public $resources;

	/**
	 * @var Source
	 */
	private $source;

	/**
	 * @var landingpage
	 */
	private $landingpage;
	
	
	/**
	 * @var spatial
	 */
	private $spatial;
	
	/**
	 * @var startdate
	 */
	private $startdate;
	
	/**
	 * @var enddate
	 */
	private $enddate;
	
	/**
	 * @var accrualPeriodicity
	 */
	private $accrualPeriodicity;
	/**
	 * @var array
	 */
	private $themes;
	/**
	 * @var string
	 */
	private $modified;
	
	/**
	 * @var string
	 */
	private $maintainer;
	private $maintainer_email;
	private $status;
	private $accessibility;
	private $high_value_dataset;
	private $owner_org;
	private $id;
		
	
	/**
	 *
	 */
	public function __construct()
	{
		$this->resources = [];
	}

	/**
	 * @param  string  $ngrid
	 * @return Package
	 */
	public function setNgrId($ngrid)
	{
		$this->ngrid = $ngrid;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNgrId()
	{
		return $this->ngrid;
	}
	
	
	/**
	 * @param  string  $ngrid
	 * @return Package
	 */
	public function setOwner_org($owner_org)
	{
		$this->owner_org = $owner_org;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOwner_org()
	{
		return $this->owner_org;
	}
	
	/**
	 * @param  string  $ngrid
	 * @return Package
	 */
	public function setID($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getID()
	{
		return $this->id;
	}
	
	/**
	 * @param  string  $title
	 * @return Package
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		$this->name = substr(Urlizer::urlize($this->title), 0, 100);

		return $this;
	}

	/**
	 * @param  string  $name
	 * @return Package
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
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param  array   $licenses
	 * @return Package
	 */
	public function setLicenses($licenses)
	{
		$this->licenses = $licenses;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getLicenses()
	{
		return $this->licenses;
	}

	/**
	 * @param  string  $language
	 * @return Package
	 */
	public function setLanguage($language)
	{
		$this->language = $language;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * @param  string  $notes
	 * @return Package
	 */
	public function setNotes($notes)
	{
		$this->notes = $notes;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getNotes()
	{
		return $this->notes;
	}

	/**
	 * @param  array   $tags
	 * @return Package
	 */
	public function setTags($tags)
	{
		$this->tags = $tags;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * @param  array   $protocol
	 * @return Package
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}

	/**
	 * @param  \Jcid\Ngr\Model\Resource[] $resources
	 * @return Package
	 */
	public function setResources($resources)
	{
		$this->resources = $resources;

		return $this;
	}
    
	/**
	 * @param  \Jcid\Ngr\Model\Resource $resource
	 * @return Package
	 */
	public function addResource(Resource $resource)
	{
		// Negeren als er geen url is bij een website
		if (empty($resource->getUrl())) {
			return $this;
		}

		$this->resources[] = $resource;

		return $this;
	}
    
    /**
	 * @param none
	 * @return $this
	 */
	// public function copyWithoutResources()
	// {
	// 	$rtn = [];
	// 	
	// 	foreach ($this as $key => $value) {
    //         if( $key != 'resources' ) { 
    //             $rtn[$key] = $value;
    //         }
    //         //echo "$key\n";
    //     }
    //     
	// 	return $rtn;
	// }
				
				
	/**
	 * @return \Jcid\Ngr\Model\Resource[]
	 */
	public function getResources()
	{
		return $this->resources;
	}

	/**
	 * @param  Source  $source
	 * @return Package
	 */
	public function setSource($source)
	{
		$this->source = $source;

		return $this;
	}

	/**
	 * @return Source
	 */
	public function getSource()
	{
		return $this->source;
	}
	
	/**
	 * @param  Landingpage  $landingpage
	 * @return Package
	 */
	public function setLandingpage($landingpage)
	{
		$this->landingpage = $landingpage;

		return $this;
	}

	/**
	 * @return Landingpage
	 */
	public function getLandingpage()
	{
		return $this->landingpage;
	}
	
	public function setSpatial($spatial)
	{
		$this->spatial = $spatial;

		return $this;
	}

	public function getSpatial()
	{
		return $this->spatial;
	}
	
	public function setStartdate($startdate)
	{
		$this->startdate = $startdate;

		return $this;
	}

	public function getStartdate()
	{
		return $this->startdate;
	}
	
	public function setEnddate($enddate)
	{
		$this->enddate = $enddate;

		return $this;
	}

	public function getEnddate()
	{
		return $this->enddate;
	}
	
	
	public function setAccrualPeriodicity($accrualPeriodicity)
	{
		$this->accrualPeriodicity = $accrualPeriodicity;

		return $this;
	}

	public function getAccrualPeriodicity()
	{
		return $this->accrualPeriodicity;
	}
	
	/**
	 * @param  array   $tags
	 * @return Package
	 */
	public function setThemes($themes)
	{
		$this->themes = $themes;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getThemes()
	{
		return $this->themes;
	}
	
	public function setModified($modified)
	{
		$this->modified = $modified;

		return $this;
	}

	public function getModified()
	{
		return $this->modified;
	}
	
	public function setMaintainer($maintainer)
	{
		$this->maintainer = $maintainer;

		return $this;
	}

	public function getMaintainer()
	{
		return $this->maintainer;
	}
	
	public function setMaintainer_email($maintainer_email)
	{
		$this->maintainer_email = $maintainer_email;

		return $this;
	}

	public function getMaintainer_email()
	{
		return $this->maintainer_email;
	}

	public function setstatus($status)
	{
		$this->status = $status;

		return $this;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setAccessibility($accessibility)
	{
		$this->accessibility = $accessibility;

		return $this;
	}

	public function getAccessibility()
	{
		return $this->accessibility;
	}

	public function setHigh_value_dataset($high_value_dataset)
	{
		$this->high_value_dataset = $high_value_dataset;

		return $this;
	}

	public function getHigh_value_dataset()
	{
		return $this->high_value_dataset;
	}

}
