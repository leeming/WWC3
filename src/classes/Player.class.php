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
 * Class containing all information about players
 * within a game. Players are children of Users that are assoc with only 1
 * GameInstance
 *
 * TODO Needs reviewing and refactoring
 *
 * @todo Ongoing game interaction methods need to be added
 * @todo Max battalion needs doing, depends on game settings
 * 		and also if player is premium player
 *
 * @uses GameInstance
 * @uses User
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Player extends Base
{
	private $selectedBattalion;
	
	function __construct($id)
	{
		parent::__construct();
		
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;			
		}
		
		if(isset($_SESSION['cache'][__CLASS__][$id]))
		{
			trigger_error("Player instance already exists. ALWAYS use getInstance()", E_USER_ERROR);
			return;
		}
		
		$_SESSION['cache'][__CLASS__][$id] = &$this;
		
		$this->db->query("SELECT game_id, user_id, team_id FROM Players WHERE id='{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->game = GameInstance::getInstance($result['game_id']);
		$this->user = new User($result['user_id']);
		
		$this->team = new TeamInstance($result['team_id']);
		
		//select primary battalion
		$this->getBattalions();//sets internal value
		$this->selectedBattalion = $this->battalions[0];
		
		$this->firephp->info("end of player");
	}
	
	static function getInstance($id)
	{
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;			
		}
		
		global $firephp;
		
		//Check if instance exists
		if(isset($_SESSION['cache'][__CLASS__][$id]))
		{
			//$firephp->log("Player(cache)");
			return $_SESSION['cache'][__CLASS__][$id];
		}
		elseif(isset($_SESSION['singletons'][__CLASS__][$id]))
		{
			//$firephp->log("Player(singleton) saving to cache");
			$_SESSION['cache'][__CLASS__][$id] = unserialize($_SESSION['singletons'][__CLASS__][$id]);
			return $_SESSION['cache'][__CLASS__][$id];
		}
		else
		{
			//$firephp->log("Player(new) saving to cache");
			$_SESSION['cache'][__CLASS__][$id] = new Player($id);
			return $_SESSION['cache'][__CLASS__][$id];
		}
	}
		
	/**
	 * Class destructor, saves object to session so that
	 * it can be recovered on next page load
	 */
	function __destruct()
	{
		@$_SESSION['singletons'][__CLASS__][$this->id] = serialize($this);
		//get rid of cache for this page
		unset($_SESSION['cache'][__CLASS__][$this->id]);
	}
	
	function getHandle()
	{
		return $this->user->handle;
	}
	
	private $resources = NULL;
	
	/**
	 * Get a list of resources the player owned
	 *
	 * @return Resource[]
	 */
	function getResources()
	{
			$this->resources = array();
			
			$sql = "SELECT resource_id, resource_qty FROM Player_has_resource"
				." WHERE player_id = '{$this->id}' ";
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $r)
			{
				$this->resources[$r['resource_id']] = new Resource($r['resource_id'], $r['resource_qty']);
			}
		
		return $this->resources;
	}
	
	private $facilities = NULL;
	
	/**
	 * Get a list of facilities the player owned
	 *
	 * @return Facility[]
	 */
	function getFacilities()
	{
		$this->facilities = array();
		
		$sql = "SELECT facility_id, SUM(facility_qty) AS qty FROM Player_has_facility"
			." WHERE player_id = '{$this->id}' GROUP BY facility_id";
		$result = $this->db->fetch_all_array($sql);
		
		foreach($result AS $r)
		{
			$this->facilities[] = new Facility($r['facility_id'], $r['qty']);
		}
			
		return $this->facilities;
	}
	
	/**
	 * Finds out where all a specific player's facilities are
	 * The returned array is a pair of (CountryInstance, Facility)
	 *
	 * @param int $fac Facility Id
	 * @return mixed[]
	 */
	function findFacilities($fac)
	{
		if(!Validate::isInt($fac))
			throw new InvalidArgumentException("int expected for \$fac");
		
		$find = array();
		$sql = "SELECT country_id, facility_qty FROM Player_has_facility"
				." WHERE player_id = '{$this->id}' AND facility_id='{$fac}'";
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $r)
			{
				$find[] = array(
					'country' => CountryInstance::getInstance($this->getGameId(),$r['country_id']),
					'facility' => new Facility($fac, $r['facility_qty']));
			}
			
		return $find;
	}
	
	private $battalions = NULL;
	
	/**
	 * Get a list of battalions the player owned
	 *
	 * @return Facility[]
	 */
	function getBattalions()
	{
			$this->battalions = array();
			
			$sql = "SELECT id FROM Battalion WHERE player_id = '{$this->id}' ";
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $r)
			{
				$this->battalions[] = new Battalion($r['id']);
			}
			
		return $this->battalions;
	}
	
	
	/**
	 * Fetch the battalion which is currently active,
	 * player can only directly control 1 battalion
	 * one at a time.
	 *
	 * @return Battalion
	 */
	function getSelectedBattalion()
	{
		return $this->selectedBattalion;
	}
	function setSelectedBattalion(Battalion $bat)
	{	$this->selectedBattalion = &$bat;	}
	
	function maxBattalions()
	{
		return 3;
	}
	
	function getNumBattalions()
	{
		return count($this->getBattalions());
	}
	
	
	private $researches = NULL;
	private $researchIds = NULL;
	
	/**
	 * Get a list of researches the player has
	 *
	 * @param ENUM $returnType Set the return to be int or object 
	 * @return int[]/Research[]
	 */
	function getResearches($returnType="int")
	{
		if($returnType == "int")
		{
				$this->researchIds = array();
				
				$sql = "SELECT research_id FROM Player_has_research"
					." WHERE player_id = '{$this->id}' ";
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $r)
				{
					$this->researchIds[] = $r['research_id'];
				}
			
			
			return $this->researchIds;
		}
		else
		{
			
				$this->researches = array();
				
				$sql = "SELECT research_id FROM Player_has_research"
					." WHERE player_id = '{$this->id}' ";
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $r)
				{
					$this->researches[] = new Research($r['research_id']);
				}
			
			
			return $this->researches;	
		}
	}
	
	/**
	 * Allow player to build facilities
	 *
	 * @param Facility $fac Facility to build
	 * @param int $location Country id of where to build it
	 * @param int $qty How many
	 * @return bool
	 */
	function build(Facility $fac, $location, $qty)
	{
		//check location and qty are valid
		if(!Validate::isInt($location) || !Validate::isInt($qty))
		{
			$this->firephp->log('Invalid location/qty, not int');
			return "Invalid input, make sure you selected a country and set a qty";
		}
		
		//check if player can afford
		$faccost = $fac->getCost();
		$this->getResources(true);
		foreach($faccost AS $c)
		{
			if(!array_key_exists($c->id, $this->resources))
				$this->resources[$c->id] = new Resource($c->id,0);
			
			
			$this->firephp->log("Have ".$this->resources[$c->id]->qty.
				" but needed ".$c->qty*$qty);
			
			//player doesnt have enough resources
			if($this->resources[$c->id]->qty < $c->qty*$qty)
			{
				return "You need more resources to build this";
			}
		}
		
		//check if player owns location
		if(!in_array($location, $this->team->countriesOwn()))
		{
			$this->firephp->log("Team does not own this country");
			return "Team does not own this country";
		}
		
		//check if research is done for building
		foreach($fac->getPreReq('int') AS $chk)
		{
			if(!in_array($chk, $this->getResearches("int",true)))
				return "You do not have the research to build this";
		}
		
		
		###Ok to build
		$sql = "INSERT INTO Player_has_facility (`facility_id`,`player_id`,".
			" `country_id`, `facility_qty`) VALUES('{$fac->id}','{$this->id}',".
			" '{$location}','{$qty}') ON DUPLICATE KEY UPDATE ".
			"facility_qty=`facility_qty`+{$qty}";
		$this->firephp->log($sql,"Adding new facilities::SQL");
		$this->db->query($sql);
		
		
		//Take away each resource from user (cost of qty * facility)
		foreach($faccost AS $c)
		{
			//$c is type Resource
			$sql = "UPDATE Player_has_resource SET resource_qty=`resource_qty`-".
				($qty*$c->qty)." WHERE player_id='{$this->id}' AND ".
				"resource_id='{$c->id}' LIMIT 1";
			$this->firephp->log($sql,"Updating resource::SQL");
			$this->db->query($sql);	
				
			//update resources in player object (this one)
			$this->resources[$c->id]->qty - $c->qty*$qty;
		}
		
		return true;
	}


	/**
	 * Lazy way to get the user id of this player
	 * as player id's relate to games not accounts
	 *
	 * @return int
	 */
	function getUserId()
	{	return $this->user->id;	}
	
	function getTeamId()
	{	return $this->team->getId(); }
	
	/**
	 * Short hand sort method to Get id of player passed
	 *
	 * @param int/Player $find Player to find id for
	 * @return int Id of player or -1 for invalid
	 */
	static function id($find)
	{
		if(Validate::isInt($find))
			return $find;
		else if(get_class($find) == "Player")
			return $find->id;
		else
			return -1;
	}
	
	
	/**
	 * Calculates how many resources a player will get per
	 * cycle.
	 *
	 * @return Resource[]
	 */
	function getResourcesPerCycle()
	{
		//$this->firephp->group("Resources Per Cycle");
		//$this->firephp->log("Adding base +1 turn");
		
		//Players always only get 1 turn per cycle
		$resources = array();	
		$resources[RESOURCE_TURNS] = new Resource(RESOURCE_TURNS, 1);
		
		//get facilities
		$facs = $this->getFacilities();
		
		foreach($facs AS $f)
		{
			//$this->firephp->group("Checking Facilities");
			//$this->firephp->log($f->name, "This facility");
			
			$produce = $f->producesResource();
			foreach($produce AS $id => $prod)
			{
				//$this->firephp->group("Checking Resources");
				//$this->firephp->log($prod,"Produces");
				
				
				//Resource already in array so add to it
				if(key_exists($id, $resources))
				{
					//$r = $resources[$id];
					//$r->qty += $prod->qty;
					$resources[$id]->qty += $prod->qty;
				}
				//add resource to array
				else
					$resources[$id] = &$prod;
					
				//$this->firephp->groupEnd();
			}
			//$this->firephp->groupEnd();
		}
		
		foreach($this->team->getCountryResources() AS $r)
		{ 
			if(array_key_exists($r->id,$resources))
				$resources[$r->id]->qty += $r->qty;
			else
				$resources[$r->id] = &$r;
		}
		
		foreach($this->team->getRegionResources() AS $r)
		{
			if(array_key_exists($r->id,$resources))
				$resources[$r->id]->qty += $r->qty;
			else
				$resources[$r->id] = &$r;
		}
		
		//$this->firephp->log($resources, "Final resources");
		//$this->firephp->groupEnd();
		//$this->firephp->groupEnd();
		
		return $resources;
	}
	
	
	/**
	 * Calculates how many units a player will get per
	 * cycle.
	 *
	 * @return Unit[]
	 */
	function getUnitsPerCycle()
	{
		$this->firephp->group("Units Per Cycle");
	
		$units = array();
		
		//get facilities
		$facs = $this->getFacilities();
		
		foreach($facs AS $f)
		{
			$this->firephp->group("Checking Facilities");
			$this->firephp->log($f->name, "Fac");
			
			$produce = $f->producesUnit();
			foreach($produce AS $id => $unit)
			{
				$this->firephp->group("Checking Units");
				$this->firephp->log($unit,"unit");
				
				
				//Resource already in array so add to it
				if(key_exists($id, $units))
				{
					$units[$id]->qty += $unit->qty;
				}
				//add resource to array
				else
					$units[$id] = $unit;
					
				$this->firephp->groupEnd();//end unit group
			}
			$this->firephp->groupEnd();//end facility group
		}
		
		$this->firephp->log($units, "Final units");
		$this->firephp->groupEnd();//end units per cycle group
		
		return $units;
	}
	
	function getGameId()
	{ return $this->game->id; }
	
}
?>
