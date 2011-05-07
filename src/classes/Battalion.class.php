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
 * A battalion is a collection of a user's (land based) troops
 * This class includes the methods for moving, managing and
 * attacking, as they are all troop related.
 * 
 * TODO Needs reviewing & refactoring
 * 
 * @uses Base
 * @uses Resource	Resources are needed for moving and attacking
 * @uses Research	For stat modification (should this be in this class?)
 * @uses Unit		Detailed information about units in a battalion
 * @uses CountryInstance	Moving & Attacking purposes
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */

class Battalion extends Base
{
	public $id, $name, $location, $commander, $exp;

	/**
	 * Constructor for the Battalion Class
	 * Loads all data from database
	 *
	 * @param int $id Id of the battalion
	 */
	function __construct($id)
	{
		parent::__construct();

		$this->firephp->log("New battalion ({$id})"); 

		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT b.player_id, b.name, b.country_id, c.name AS commander, c.exp "
			."FROM Battalion b INNER JOIN Battalion_commanders c ON(b.id=c.battalion_id)"
			." WHERE b.id = {$id} LIMIT 1");

		$result = $this->db->getRow();

		$this->id = $id;
		$this->name = $result['name'];
		$this->commander = $result['commander'];
		$this->exp = $result['exp'];
		$this->firephp->log("recursion???"); 
		$this->player = Player::getInstance($result['player_id']);
		$this->location = CountryInstance::getInstance($this->player->getGameId(), $result['country_id']);
		
	}

	/**
	 * Gets the location of the battalion, if the battalion has moved then
	 * the location value is updated and returned
	 *
	 * @return CountryInstance
	 */
	function getLocation()
	{
		$sql="SELECT country_id FROM Battalion WHERE id='{$this->id}' LIMIT 1";
		$this->db->query($sql);
		$result = $this->db->getRow();
		
		//check if different
		if($result['country_id'] != $this->location->id)
		{
			//update location
			$this->firephp->log("Country changed, to ".$result['country_id']);
			$this->location = CountryInstance::getInstance($this->player->id,$result['country_id']);
		}
		
		return $this->location;
	}

	private $units = NULL;
	
	/**
	 * Get a list of units within a battalion
	 *
	 * @todo How to manage results where qty == 0 ?? dont fetch/delete from db?
	 * @param bool $recache
	 * @return Unit[]
	 */
	function getUnits($recache = false)
	{
		if($recache || $this->units == NULL)
		{
			$this->units = array();
			
			$sql = "SELECT unit_id, unit_qty FROM Player_has_unit"
				." WHERE battalion_id = '{$this->id}' ";
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $r)
			{
				$this->units[] = new Unit($r['unit_id'], $r['unit_qty']);
			}
		}
		
		return $this->units;
	}

	/**
	 * A more specific getUnits() which gets only units
	 * which are of troop type. It actually calls getUnits
	 * and filters the array
	 *
	 * @return Unit[]
	 */
	function getTroops($recache = false)
	{
		$units = $this->getUnits($recache);
		$troops= array();
		foreach($units AS $u)
		{
			if($u->isTroop())
			{
				$troops[] = &$u;
			}
		}
		return $troops;
	}
	
	/**
	 * A more specific getUnits() which gets only units
	 * which are of tank type. It actually calls getUnits
	 * and filters the array
	 *
	 * @return Unit[]
	 */
	function getTanks($recache = false)
	{
		$units = $this->getUnits($recache);
		$tanks= array();
		foreach($units AS $u)
		{
			if($u->isTank())
			{
				$tanks[] = &$u;
			}
		}
		return $tanks;
	}
	
	/**
	 * Find out battalion lvl, generated dynamically from exp
	 *
	 * @return int Level
	 */
	function getLevel()
	{
		return (int)($this->exp/100)+1;
	}
	
	/**
	 * Calculate exp needed for next level
	 *
	 * @return int
	 */
	function nextLevelExp()
	{
		return $this->getLevel()*100;
	}

	function getImg()
	{
		return "img/general.jpg";
	}

	/**
	 * Calculate the attack strength of battalion
	 * using player researches/battalion level/units
	 *
	 * TODO Should this be here?
	 *
	 * @return int
	 */
	function totalStr()
	{
		$str = 0;
		
		$this->getUnits(true);
		foreach($this->units AS $unit)
		{
			$stats = $unit->getStats();
			$str += $unit->qty*$stats['ATTACK'];
		}
		
		return $str * (($this->getLevel()/10)+1);
	}

	function attack(Country $attack)
	{
		
	}
	
	/**
	 * Move a battalion to desired country. Returns false
	 * if player doesnt have any turns to move (or other resources)
	 * and also if country is not a moveable border.
	 *
	 * @todo Should battalion apply row locks, to stop simutaneous attack/move?
	 * @param CountryInstance $move Country to move to
	 * @return bool
	 */
	function move(CountryInstance $move)
	{
		$this->firephp->group("Entering battalion->move");
		$player= &$this->player;
		
		$resources = $player->getResources(true);
		if($resources[RESOURCE_TURNS]->qty < 1)
		{
			$this->firephp->warn("Move failed: no turns");
			$this->firephp->groupEnd();
			return false;
		}
		
		//is battalion location border and moveable
		$moveable = $this->location->getMoveBorders($player->team);
		if(!in_array($move->id, $moveable))
		{
			$this->firephp->warn("Move failed: non moveable border");
			$this->firephp->groupEnd();
			return false;
		}
		
		#ok to move
		//move battalion
		$sqls[] = "UPDATE Battalion SET country_id='{$move->id}' WHERE ".
			"id='{$this->id}' LIMIT 1";
		
		//take resources for moving away from person
		$sqls[] = "UPDATE Player_has_resource SET resource_qty=`resource_qty`-1".
			" WHERE player_id='{$this->player->id}' AND resource_id='".RESOURCE_TURNS."' LIMIT 1";
			
		foreach($sqls AS $sql)
		{
			$this->db->query($sql);
			
			//check if failed (why should it?)
			if($this->db->affected_rows == 0)
			{
				$this->firephp->warn("Battalion Move failed:Update ",$sql);
				$this->firephp->warn("Mysql error is", $this->db->error);
				$this->firephp->groupEnd();
				return false;
			}
		}
		
		$this->firephp->log("Battalion moved from ".$this->location->id." ->".$move->id);
		
		//update internal class values
		$this->location = $move;
		
		//if here then all went well and moved
		$this->firephp->groupEnd();
		return true;
	}

	/**
	 * Check to see if battalion is empty or has troops
	 * If no troops returns false, else true
	 *
	 * @return bool
	 */
	function isEmpty()
	{
		$units =$this->getUnits(true);
		
		//Simple if empty then no units
		if(empty($units))
			return true;
		
		//some units could be stored but 0 qty
		foreach($units AS $unit)
		{
			//if any unit qty > 0 then battalion not empty
			if($unit->qty > 0)
				return false;
		}
		
		//if here all units checked and all have 0 qty
		return true;
	}
	
	/**
	 * Get the total number of resources expected to be used
	 * in moving a battalion
	 *
	 * @return Resource[]
	 */
	function resourcesNeededToMove()
	{
		$units = $this->getUnits();
		return array();
	}

	/**
	 * Get the total number of resources expected to be used
	 * while attacking
	 *
	 * @return Resource[]
	 */
	function resourcesNeededToAttack()
	{
		$units = $this->getUnits();
		$need= array();
		foreach($units AS $unit)
		{
			foreach($unit->resourceToAttack() AS $r)
			{
				//Add accumlative resources
				if(array_key_exists($r->id,$need))
					$need[$r->id]->qty += $unit->qty * $r->qty;
				else
				{
					$r->qty *= $unit->qty;
					$need[$r->id] = $r;
				}
			}
		}
		
		return $need;
	}

	/**
	 * Makes a new battalion, returns: -1 on error, 0 on failed mysql,
	 * else id of new battalion
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Facility ID
	 */
	static function add(array &$insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name'])
		   && array_key_exists('player_id', $insertArray)
		   && Vallidate::isInt($insertArray['player_id'])
		   && array_key_exists('country_id', $insertArray)
		   && Vallidate::isInt($insertArray['country_id']))
		
		{
			global $db;

			$bat = array('name' 	 => $insertArray['name'],
						 'player_id' => $insertArray['player_id'],
						 'country_id'=> $insertArray['country_id']);
			$battId = $db->query_insert("Battalion",$bat);
			
			$com = array('battalion_id' => $battId,
						 'name' => $insertArray['commander'],
						 'exp' => '0');
			
			
			return $db->query_insert("Battalion_commander", $com);
		}

		return -1;
	}


	function equals(Battalion $cmp)
	{
		return ($this->id == $cmp->id);
	}

	function __toString()
	{
		return "(Battalion){$this->name}";
	}

}
?>
