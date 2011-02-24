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
 * This class holds all information about facilities (buildings)
 * and how they operate. Methods are given to allow players
 * to build in a given game.
 *
 * TODO removePreReq() and other removes, producesUnit (qty)->unit
 * 		implementation?
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Facility extends Base
{
	public $id, $name, $desc;

	/**
	 * Constructor for the Facility Class
	 * Loads all data from database
	 *
	 * @param int $id Id of the facility
	 * @param int $qty Number of facilities
	 */
	function __construct($id, $qty =0)
	{
		parent::__construct();

		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		//Validate $qty is int
		if(!Validate::isInt($qty))
		{
			trigger_error("(int)\$qty expected, (".gettype($qty).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT `name`, `desc` FROM Facilities WHERE id = '".$id."' LIMIT 1");

		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->name = $result['name'];
		$this->desc = $result['desc'];
		$this->qty = $qty;
	}

	private $produceResource = NULL;

	/**
	 * Gets array of resources that the facility produces (if any)
	 *
	 * @param bool $recache
	 * @return Resource[]
	 */
	function producesResource($recache = false)
	{
		$sql = "SELECT resource_id, resource_qty FROM Facility_produces_resource WHERE facility_id ='{$this->id}'";
		
		if($this->produceResource == NULL || $recache)
		{
			$this->produceResource = array();
			
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $produce)
			{
				$this->produceResource[$produce['resource_id']] = new Resource($produce['resource_id'], $produce['resource_qty']*$this->qty);
			}
		}
		
		//$this->firephp->log($this->produceResource,$this->name." Produces" );
		return $this->produceResource;
	}

	private $produceUnit = NULL;

	/**
	 * Gets array of units that the facility produces (if any)
	 *
	 * @param bool $recache
	 * @return Unit[]
	 */
	function producesUnit($recache = false)
	{
		$sql = "SELECT unit_id, unit_qty FROM Facility_builds_unit WHERE facility_id ='{$this->id}'";


		if($this->produceUnit == NULL || $recache)
		{
			$this->produceUnit = array();

			$result = $this->db->fetch_all_array($sql);

			foreach($result AS $produce)
			{
				$this->produceUnit[] = new Unit($produce['unit_id'], $produce['unit_qty']*$this->qty);
			}
		}

		return $this->produceUnit;
	}

	function addProduction($addMe)
	{
		if(get_class($addMe) == "Resource")
		{
			//Check if qty is set and +ve
			if($addMe->qty > 0)
			{
				//check if not already in production list
				foreach($this->producesResource() AS $cmp)
				{
					//this resource already in list, so ignore
					if($cmp->equals($addMe))
						return 0;
				}
				
				$insertArray = array("facility_id" => $this->id,
									 "resource_id" => $addMe->id,
									 "resource_qty" => $addMe->qty);
				$this->db->query_insert("Facility_produces_resource", $insertArray);
				
				$this->producesUnit(true);
				return 1;
			}
		}
		else if(get_class($addMe) == "Unit")
		{
			//Check if qty is set and +ve
			if($addMe->qty > 0)
			{
				//check if not already in production list
				foreach($this->producesUnit() AS $cmp)
				{
					//this unit already in list, so ignore
					if($cmp->equals($addMe))
						return 0;
				}
				
				$insertArray = array("facility_id" => $this->id,
									 "unit_id" => $addMe->id,
									 "unit_qty" => $addMe->qty);
				$this->db->query_insert("Facility_builds_unit", $insertArray);
				
				$this->producesUnit(true);
				return 1;
			}
		}
		
		return 0;
	}


	private $preReq = NULL;
	private $preReqIds = NULL;

	/**
	 * Gets the array of research which are needed prior to building
	 * this current facility
	 *
	 * @param string $returnType Type to return
	 * @param bool $recache
	 * @return int[]/Research[]
	 */
	function getPreReq($returnType = "int", $recache = false)
	{
		
		$sql = "SELECT research_id FROM Facility_requires_research WHERE facility_id ='{$this->id}'";
		
		if($returnType == "int")
		{
			if($this->preReqIds == NULL || $recache)
			{
				$this->preReqIds = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $require)
				{
					$this->preReqIds[] = $require['research_id'];
				}
			}
			
			return $this->preReqIds;
		}
		else
		{
			if($this->preReq == NULL || $recache)
			{
				$this->preReq = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $require)
				{
					$this->preReq[] = new Research($require['research_id']);
				}
			}
			
			return $this->preReq;
		}
	}

	/**
	 * Add a pre requisite for a facility
	 *
	 * @param int/Research $toAdd
	 * @return int[] New preReqs array or -1 if not inserted
	 */
	function addPreReq($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$newId = $toAdd;
		}
		else if(get_class($toAdd) == "Research")
		{
			$newId = $toAdd->id;
		}

		//check to see if currently in req list
		if(!in_array($newId, $this->getPreReq()))
		{
			//not in list so add
			$values = array('facility_id' => $this->id,
							'research_id' => $newId);

			$this->db->query_insert("Facility_requires_research",$values);

			return $this->getPreReq('int', true);
		}

		return -1;
	}

	function removePreReq($toRemove)
	{}


	
	private $resourcesNeeded = NULL;

	/**
	 * Gets the array of resources and qty that are needed to build facility
	 *
	 * @param bool $recache
	 * @return Resource[]
	 */
	function getCost($recache = false)
	{
		$sql = "SELECT resource_id, resource_qty FROM Facility_requires_resource WHERE facility_id ='{$this->id}'";

		if($this->resourcesNeeded == NULL || $recache)
		{
			$this->resourcesNeeded = array();

			$result = $this->db->fetch_all_array($sql);

			foreach($result AS $require)
			{
				$r = new Resource($require['resource_id'], $require['resource_qty']);
				$this->resourcesNeeded[] = $r;
			}
		}
		
		return $this->resourcesNeeded;
	}

	/**
	 * Add resouce needed for facility
	 *
	 * @param Resource $toAdd Resource to add as a cost
	 * @return bool True if added, else false
	 */
	function addCost(Resource &$toAdd)
	{
		//check if resource is already there
		foreach($this->getCost() AS $re)
		{
			if($re->equals($toAdd))
				return false;
		}

		//add resouce needed
		$insert = array("facility_id" => $this->id,
						"resource_id"  => $toAdd->id,
						"resource_qty" => $toAdd->qty);

		$this->db->query_insert("Facility_requires_resource", $insert);
		return mysql_affected_rows();
	}

	

	/**
	 * Makes a new facility, returns: -1 on error, 0 on failed mysql,
	 * else id of new facility
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Facility ID
	 */
	static function add(array &$insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Facilities", $insertArray);
		}
		
		return -1;
	}

	/**
	 * Short hand sort method to Get id of facility passed
	 *
	 * @param int/Facility $fac Facility to find id for
	 * @return int Id of facility or -1 for invalid
	 */
	static function id($fac)
	{
		if(Validate::isInt($fac))
			return $fac;
		else if(get_class($fac) == "Facility")
			return $fac->id;
		else
			return -1;
	}

	function equals(Facility &$cmp)
	{
		return ($this->id == $cmp->id);
	}

	function __toString()
	{
		return "(Facility){$this->name}[{$this->qty}]";
	}

}
?>
