<?php
/*
* License: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or (at your
* option) any later version. This program is distributed in the hope that it
* will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
* of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
* Public License for more details.
*/

/**
 * This class holds all the information about a game settings
 *
 * @todo Need to add methods to be able to edit current setting and also
 * 		 to be able to add records into Setting_has_xxxx tables in batch
 * 		 mode, ie import the Setting_has_country set only instead of copying
 * 		 the whole setting. Add a copy setting method to clone the setting
 * 		 in the database. Add a method to check if there is any problems
 * 		 with the setting config, such as including a facility that is
 * 		 required by a research which isnt included, thus never able to build
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Settings
{
	/**
	 * Settings constructor, gets the settings for $id
	 *
	 * @param	(int)$id	- id of the settings
	 */
	function __construct($id)
	{
        global $db;
        $this->db = &$db;
        
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}	
		
		$query = $db->query("SELECT SQL_CACHE * FROM Settings WHERE id = '{$id}' LIMIT 1");
		//check if settings exist
		if($db->numRows == 0)
		{
			trigger_error("There is no settings with this id (".$id.")", E_USER_ERROR);
		}
		
		//get row
		$result = $db->getRow();

		$this->id = $id;
		$this->maxPlayers = $result['max_players'];
		$this->generals = $result['has_generals'];
		$this->teamGovernments = $result['team_governments'];
		$this->playerGovernments = $result['player_governments'];
		$this->treaties = $result['allow_team_treaties'];
		$this->naturalDisasters = $result['natural_disasters'];
		$this->militaGrowth = $result['milita_growth'];
		$this->lateEntryTime = $result['late_entry_time'];
		$this->cycleTime = $result['cycle_time'];
		$this->assimRate = $result['assim_rate'];
		$this->name = $result['name'];
		$this->desc = $result['desc'];
	}
	
	/**
	 * Find out what the time scale of cycles are (in seconds)
	 *
	 *
	 * @return int
	 */
	function getCycleSpeed()
	{	return $this->cycleTime; }
	/*
	function getCycleTime()
	{	return $this->getCycleSpeed();	}
	*/
	
	private $regionList = NULL;
	private $regionIdList = NULL;
	
	/**
	 * Find out what regions are in this setting
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Region[]
	 */
	function getRegions($returnType = 'int', $recache = false)
	{
		$sql = "SELECT region_id FROM Setting_has_region WHERE setting_id ='{$this->id}'";
		
		if($returnType == 'int')
		{
			if($recache || $this->regionIdList == NULL)
			{
				$this->regionIdList = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $region)
				{
					$this->regionIdList[] = $region['region_id'];
				}
			}
			
			return $this->regionIdList;
		}
		else
		{
			if($recache || $this->regionList == NULL)
			{
				$this->regionList = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $region)
				{
					$this->regionList[] = new Region($region['region_id']);
				}
			}
			
			return $this->regionList;
		}
	}
	
	
	/**
	 * Add a region to setting
	 *
	 * @param Region/int $toAdd Region to add
	 */
	function addRegion($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Region")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getRegions('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'region_id' => $id);
			
			$this->db->query_insert("Setting_has_region",$values);
		}
	}
	
	private $countryList = NULL;
	private $countryIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Country[]
	 */
	function getCountries($returnType = "int", $recache = false)
	{
		$sql = "SELECT country_id FROM Setting_has_country WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->countryIdList == NULL)
			{
				$this->countryIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $country)
				{
					$this->countryIdList[] = $country['country_id'];
				}
			}
			
			return $this->countryIdList;
		}
		else
		{
			if($recache || $this->countryList == NULL)
			{
				$this->countryList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $country)
				{
					$this->countryList[] = new Country($country['country_id']);
				}
			}
			
			return $this->countryList;
		}
	}
	
		
	/**
	 * Add a country to setting
	 *
	 * @param Country/int $toAdd Country to add
	 */
	function addCountry($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Country")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getCountries('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'country_id' => $id);
			
			$this->db->query_insert("Setting_has_country",$values);
		}
	}
	
	private $resourceList = NULL;
	private $resourceIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Resource[]
	 */
	function getResources($returnType = "int", $recache = false)
	{
		$sql = "SELECT resource_id FROM Setting_has_resource WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->resourceIdList == NULL)
			{
				$this->resourceIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $resource)
				{
					$this->resourceIdList[] = $resource['resource_id'];
				}
			}
			
			return $this->resourceIdList;
		}
		else
		{
			if($recache || $this->resourceList == NULL)
			{
				$this->resourceList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $resource)
				{
					$this->resourceList[] = new Resource($resource['resource_id']);
				}
			}
			
			return $this->resourceList;
		}		
	}
	
		
	/**
	 * Add a resource to setting
	 *
	 * @param Resource/int $toAdd Resource to add
	 */
	function addResource($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Resource")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getResources('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'resource_id' => $id);
			
			$this->db->query_insert("Setting_has_resource",$values);
		}
	}
	
	private $teamList = NULL;
	private $teamIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Team[]
	 */
	function getTeams($returnType = "int", $recache = false)
	{
		$sql = "SELECT team_id FROM Setting_has_team WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->teamIdList == NULL)
			{
				$this->teamIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $team)
				{
					$this->teamIdList[] = $team['team_id'];
				}
			}
			
			return $this->teamIdList;
		}
		else
		{
			if($recache || $this->teamList == NULL)
			{
				$this->teamList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $team)
				{
					$this->teamList[] = new Team($team['team_id']);
				}
			}
			
			return $this->teamList;
		}		
		
	}
	
	
		
	/**
	 * Add a team to setting
	 *
	 * @param Team/int $toAdd Team to add
	 */
	function addTeam($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Team")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getTeams('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'team_id' => $id);
			
			$this->db->query_insert("Setting_has_team",$values);
		}
	}
	
	private $researchList = NULL;
	private $researchIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Research[]
	 */
	function getResearches($returnType = "int", $recache = false)
	{
		$sql = "SELECT research_id FROM Setting_has_research WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->researchIdList == NULL)
			{
				$this->researchIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $research)
				{
					$this->researchIdList[] = $research['research_id'];
				}
			}
			
			return $this->researchIdList;
		}
		else
		{
			if($recache || $this->researchList == NULL)
			{
				$this->researchList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $research)
				{
					$this->researchList[] = new Research($research['research_id']);
				}
			}
			
			return $this->researchList;
		}
	}
	
		
	/**
	 * Add a research to setting
	 *
	 * @param Research/int $toAdd Research to add
	 */
	function addResearch($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Research")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getResearches('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'research_id' => $id);
			
			$this->db->query_insert("Setting_has_research",$values);
		}
	} 
	
	private $unitList = NULL;
	private $unitIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Unit[]
	 */
	function getUnits($returnType = "int", $recache = false)
	{
		$sql = "SELECT unit_id FROM Setting_has_unit WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->unitIdList == NULL)
			{
				$this->unitIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $unit)
				{
					$this->unitIdList[] = $unit['unit_id'];
				}
			}
			
			return $this->unitIdList;
		}
		else
		{
			if($recache || $this->unitList == NULL)
			{
				$this->unitList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $unit)
				{
					$this->unitList[] = new Unit($unit['unit_id']);
				}
			}
			
			return $this->unitList;
		}
	}
	
	
		
	/**
	 * Add a unit to setting
	 *
	 * @param Team/int $toAdd Unit to add
	 */
	function addUnit($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Unit")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getUnits('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'unit_id' => $id);
			
			$this->db->query_insert("Setting_has_unit",$values);
		}
	}
	
	private $facilityList = NULL;
	private $facilityIdList = NULL;
	
	/**
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Facility[]
	 */
	function getFacilities($returnType = "int", $recache = false)
	{
		
		$sql = "SELECT facility_id FROM Setting_has_facility WHERE setting_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->facilityIdList == NULL)
			{
				$this->facilityIdList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $facility)
				{
					$this->facilityIdList[] = $facility['facility_id'];
				}
			}
			
			return $this->facilityIdList;
		}
		else
		{
			if($recache || $this->facilityList == NULL)
			{
				$this->facilityList = array();
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $facility)
				{
					$this->facilityList[] = new Facility($facility['facility_id']);
				}
			}
			
			return $this->facilityList;
		}		
	}

	/**
	 * Add a facility to setting
	 *
	 * @param Facility/int $toAdd Facility to add
	 */
	function addFacility($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$id = $toAdd;
		}
		else if(get_class($toAdd) == "Facility")
		{
			$id = $toAdd->id;
		}
		
		//check to see if currently in setting
		if(!in_array($id, $this->getFacilities('int', true)))
		{
			//not in list so add
			$values = array('setting_id' => $this->id,
							'facility_id' => $id);
			
			$this->db->query_insert("Setting_has_facility",$values);
		}
	}
	
	/**
	 * Makes a new Setting, returns: -1 on error, 0 on failed mysql,
	 * else id of new setting
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Setting ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']) &&
				array_key_exists('desc', $insertArray)&& !empty($insertArray['desc']))
		{
			global $db;
			
			return $db->query_insert("Settings", $insertArray);
		}

		return -1;
	}
	
	
	function equals(Settings &$cmp)
	{
		return ($this->id == $cmp->id);
	}
}
?>