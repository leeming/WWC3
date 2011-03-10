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
 * Holds information about mility units
 *
 * @todo May need to revise once unit handling is added with
 * 		 battalions and attacking. Add a unit collection for
 *		 when facilities output and players own xxx units???
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Unit extends Base
{
	public $name;
	public $id;
	public $qty = 0;
	
	/**
	 * Unit constructor
	 *
	 * @param int $id Id of the unit
	 */
	function __construct($id, $setQty = 0)
	{
		parent::__construct();
		
		//check that id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		//check that setQty is int
		if(!Validate::isInt($setQty))
		{
			trigger_error("(int)\$setQty expected, (".gettype($setQty).") passed", E_USER_ERROR);
			return;
		}
		
		$this->id = $id;
		$this->qty = $setQty;
		
		$this->db->query("SELECT unit_type_id, name FROM Units WHERE id = '{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		$this->name = $result['name'];
		$this->type = new UnitType($result['unit_type_id']);
	}
	
	private $stats = NULL;
	
	/**
	 * Array of unit stats
	 *
	 * @param bool $recache
	 * @return array(string $statName => int $qty)
	 */
	function getStats($recache = FALSE)
	{
		if($this->stats == NULL || $recache)
		{
			//$result = $this->db->fetch_all_array("SELECT s.name, u.stat_qty FROM Unit_stats s".
			//	"LEFT JOIN Unit_has_stats u ON (s.id = u.stat_id) WHERE u.unit_id = '{$this->id}'");
			
			$sql = "SELECT stat_qty, stat_id, name FROM `Unit_has_stats` LEFT JOIN Unit_stats ON (id = stat_id) WHERE unit_id='{$this->id}'";
			$result = $this->db->fetch_all_array($sql);
			$this->firephp->log("SQL:",$sql);
			
			
			$this->stats = array();
			
			foreach($result AS $stat)
			{
				//$this->stats[] = array($stat['name'] => $stat['stat_qty']);
				$this->stats[$stat['name']] = new Stat($stat['stat_id'], $stat['stat_qty']);
			}
		}
		
		return $this->stats;
	}
	
	
	private $researchIdList = NULL;
	private $researchList = NULL;
	
	/**
	 * List or research required for unit
	 *
	 * @param string $returnType
	 * @return int[]/Research[]
	 */
	function requiredResearch($returnType = 'int')
	{
		if(($returnType == "int" && $this->researchIdList == NULL) ||
			($returnType != "int" && $this->researchList == NULL))
		{
			$result = $this->db->fetch_all_array("SELECT research_id FROM Unit_requires_research WHERE unit_id = '{$this->id}'");
			
			if($returnType == "int")
			{
				$this->researchIdList = array();
			}
			else
			{
				$this->researchList = array();
			}
			
			foreach($result AS $requirement)
			{
				if($returnType == "int")
				{
					$this->researchIdList[] = $requirement['research_id'];
				}
				else
				{
					$this->researchList[] = new Research($reqirement['research_id']);
				}
			}
		}
		
		if($returnType == "int")
			return $this->researchIdList;
		else
			return $this->researchList;
	}
	
	
	private $preReq = NULL;
	private $preReqIds = NULL;

	/**
	 * Gets the array of researches which are needed prior to making unit
	 *
	 * @param string $returnType Type to return
	 * @param bool $recache
	 * @return int[]/Research[]
	 */
	function getPreReq($returnType = "int", $recache = false)
	{
		
		$sql = "SELECT research_id FROM Unit_requires_research WHERE unit_id ='{$this->id}'";
		
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
	 * Add a pre requisite for a unit
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
			$values = array('unit_id' => $this->id,
							'research_id' => $newId);

			$this->db->query_insert("Unit_requires_research",$values);

			return $this->getPreReq('int', true);
		}

		return -1;
	}

	function removePreReq($toRemove)
	{}


	function isTroop()
	{ return ($this->type->name == "TROOP"); }
	function isTank()
	{ return ($this->type->id == UNIT_TYPE_TANK); }
	
	function resourceToMove()
	{
		//static $1 per unit to move
		$return = array();
		$return[RESOURCE_MONEY] = new Resource(RESOURCE_MONEY,1);
		
		//if troops use 1 supply, if tank 1 oil
		if($this->isTroop())
			$return[RESOURCE_SUPPLIES] = new Resource(RESOURCE_SUPPLIES, 1);
		else
			$return[RESOURCE_OIL] = new Resource(RESOURCE_OIL, 1);
			
		return $return;
	}
	function resourceToAttack()
	{
		//static $3 per unit to att
		$return = array();
		$return[RESOURCE_MONEY] = new Resource(RESOURCE_MONEY,3);
		
		//if troops use 2 supply, if tank 2 oil
		if($this->isTroop())
			$return[RESOURCE_SUPPLIES] = new Resource(RESOURCE_SUPPLIES, 2);
		else
			$return[RESOURCE_OIL] = new Resource(RESOURCE_OIL, 2);
			
		return $return;		
	}

	/**
	 * Add a stat to a unit
	 *
	 * @param Stat $toAdd Stat to add
	 */
	function addStat(Stat $toAdd)
	{
		//check to see if unit already has stat
		foreach($this->getStats(true) AS $stat)
		{
			if($toAdd->equals($stat))
				return;
		}
		
		//unit doesnt have stat, so add
		$insertArray = array('unit_id' => $this->id,
							 'stat_id' => $toAdd->id,
							 'stat_qty'=> $toAdd->qty);
		
		$this->db->query_insert("Unit_has_stats", $insertArray);
	}
	

	function equals(Unit &$cmp)
	{
		return ($this->id == $cmp->id);
	}
	
	/**
	 * Makes a new Unit, returns: -1 on error, 0 on failed mysql,
	 * else id of new unit
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Unit ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']) &&
			array_key_exists('unit_type_id', $insertArray) && Validate::isInt($insertArray['unit_type_id']))
		{
			global $db;

			return $db->query_insert("Units", $insertArray);
		}

		return -1;
	}
}

?>