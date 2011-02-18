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
 * Holds all information about a country, includes all production values
 * and also current state of country (armies present/milita/owner etc)
 *
 * TODO Shouldnt there be a CountryInstance class which contains all game
 * 		related	functions??? (extends)
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Country extends MapArea
{
	public $id;
	
	/**
	 * Constructor for Country class
	 *
	 * @param int $id
	 */
	function __construct($id)
	{
		parent::__construct();

		//check that id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->id = $id;
		
		//get country details
		$this->db->query("SELECT name FROM Countries WHERE id ='{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		
		$this->name = $result['name'];
	}

	/**
	 * Gets the id of the country
	 *
	 * @return	int
	 */
	function getId()
	{	return $this->id;	}

	/**
	 * Gets the name of the country
	 *
	 * @return string
	 */
	function getName()
	{	return $this->name;	}



	public $milita = NULL;

	/**
	 * Get the number of milita in the country
	 * This includes the turn by turn population growths as well
	 * as milita strength increases from multiple change of control
	 *
	 * @todo Currently change of control multiplier not done
	 * @deprecated Moved to CountryInstance
	 * @return int
	 */
	/*
	function getMilita()
	{		
		$sql = "SELECT milita_qty FROM Milita WHERE country_id = '{$this->id}' AND game_id = '{$this->gameId}' LIMIT 1";		
		$this->db->query($sql);
		$this->firephp->log($sql);
		$result = $this->db->getRow();
		
		$this->milita = $result['milita_qty'];
		
		
		$this->firephp->log("Milita is ", $this->milita);
		return $this->milita;
	}
	*/

	private $owner = NULL;

	/**
	 * Find the ownership of country, returned cached value unless $recache is set
	 *
	 * @deprecated Moved to countryinstance
	 *
	 * @param bool $recache
	 * @return int Team id of who owns country
	 */
	/*
	function getOwner($recache = FALSE)
	{
		if($this->owner == NULL || $recache)
		{
			$this->db->query("SELECT team_id FROM Team_has_country WHERE ".
				"country_id = '{$this->id}' AND game_id = '{$this->gameId}' LIMIT 1");
			$result = $this->db->getRow();
			
			$this->owner = $result['team_id'];
		}
		
		return $this->owner;
	}
	*/


	/**
	 * Set the owner of the country
	 *
	 * @deprecated Moved to countryinstance
	 * 
	 * @param int $id Id of the team to set owner as
	 */
	/*
	function setOwner($id)
	{
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		//Not sure if REPLACE would work as team_id is also primary key?
		$this->db->query("DELETE FROM Team_has_country WHERE team_id = '{$id}' ".
			"AND country_id = '{$this->id}' AND game_id = '{$this->gameId}' LIMIT 1");
		$this->db->query("INSERT INTO Team_has_country (`team_id`,`country_id`,`game_id`) ".
			"VALUES ('{$id}', '{$this->id}', '{$this->gameId}')");
		$this->owner = $id;
	}
	*/


	private $borders = array();
	private $bordersId = array();

	/**
	 * Get an array of country id who border this country
	 *
	 * @param string $returnType Type of array to return
	 * @param bool $recache
	 * @return	int[]/Country[]
	 */
	function getBorders($returnType = "int" ,$recache = FALSE)
	{
		$sql="SELECT border_country FROM Country_has_border WHERE country_id = '{$this->id}'";
		
		//Return array of border ids
		if($returnType == "int" && (empty($this->bordersId) || $recache) )
		{
			$results = $this->db->fetch_all_array($sql);
			
			$this->bordersId = array();
			foreach($results AS $row)
			{
				$this->bordersId[] = $row['border_country'];
			}
		}
		//Return array of Countries
		else if(empty($this->borders) || $recache)
		{
			$results = $this->db->fetch_all_array($sql);
			
			$this->borders = array();
			foreach($results AS $row)
			{
				$this->borders[] = new Country($row['border_country'], $this->gameId);
			}
		}
		
		if($returnType == "int")
			return $this->bordersId;
		else
			return $this->borders;
	}

	/**
	 * @deprecated Moved to country instance
	 * @return int[]
	 */
	/*
	function getAttackBorders(TeamInstance $team)
	{
		$teamCountries = $team->countriesOwn('int');
		$borders = $this->getBorders();
		

		$return = array();
		foreach($borders AS $border)
		{
			//does team own this country?
			if(!in_array($border, $teamCountries))
				$return[] = $border;
		}
		
		$this->firephp->log($return,"Attackable borders from {$this->id}");
		return $return;
	}
	
	function getMoveBorders(TeamInstance $team)
	{
		$teamCountries = $team->countriesOwn('int');
		$borders = $this->getBorders();
		
		$return = array();
		foreach($borders AS $border)
		{
			//does team own this country?
			if(in_array($border, $teamCountries))
				$return[] = $border;
		}
		
		return $return;
	}
*/
	/**
	 * Check if this country (a) is a boarder of country (b) where the border goes a->b
	 *
	 * @param int/Country $country
	 * @return bool
	 */
	function isBoarderOf($country)
	{
		if(Validate::isInt($country))
			return in_array($countryId, $this->getBorders());
		else
		{
			foreach($this->getBorders('object') AS $country)
			{
				if($this->equals($country))
				{
					return true;
				}
			}
			
			return false;
		}
	}
	
	/**
	 * Add a country as a border to this one
	 *
	 * @param Country $toAdd Country to add as a border
	 * @param bool $twoWay Add border a->b & b->a
	 */
	function addBorder(Country $toAdd, $twoWay = true)
	{
		//check to see if this already a border
		foreach($this->getBorders('object', true) AS $borders)
		{
			if($borders->equals($toAdd))
				return;
		}
		
		//not a border, so add
		$insertArray = array("country_id" => $this->id,
							 "border_country" => $toAdd->id);
		
		$this->db->query_insert("Country_has_border",$insertArray);
		
		//if twoWay is set, make sure border goes the other way
		//as some borders you can only go a->b and NOT b->a
		if($twoWay)
		{
			$toAdd->addBorder($this, false);
		}
	}

	
	private $coordSet = array();
	
	/**
	 * Get set of coords of country as array per segment, ie islands can have multiple parts so count() > 1
	 *
	 * @return string[] Format is x,x,x,x where x is an int
	 */
	function getCoords($recache = true)
	{
		if($recache || empty($this->coordSet))
		{			
			$result = $this->db->fetch_all_array("SELECT coord_set FROM Country_coords WHERE country_id ='{$this->id}'");
			
			foreach($result AS $coordSet)
			{
				$this->coordSet[] = $coordSet['coord_set'];
			}
		}
		
		return $this->coordSet;
	}
	
	/**
	 * Add a set of coordinates for a country
	 *
	 * @param string $toAdd
	 */
	function addCoords($toAdd)
	{
		$insertArray = array('coord_set' => $toAdd,
							 'country_id' => $this->id);
		
		$this->db->query_insert("Country_coords", $insertArray);
	}
	
	/**
	 * Gets an array of resources for the country
	 *
	 * @return Resource[]
	 */
	function getBonus()
	{
		$result = $this->db->fetch_all_array("SELECT resource_id, resource_qty FROM Country_has_resource WHERE country_id = '{$this->id}'");
		
		$this->bonus = array();
		foreach($result AS $resource)
		{
			$this->bonus[] = new Resource($resource['resource_id'], $resource['resource_qty']);
		}
		
		return $this->bonus;
	}
	
	/**
	 * Add a resource that the country gives as a bonus
	 *
	 * @param Resource $toAdd Resource to add as bonus
	 */
	function addBonus(Resource $toAdd)
	{
		//check to see if this already a bonus
		foreach($this->getBonus('object', true) AS $bonus)
		{
			if($bonus->equals($toAdd))
				return;
		}
		
		//not a bonus, so add
		$insertArray = array("country_id" => $this->id,
							 "resource_id" => $toAdd->id,
							 "resource_qty" => $toAdd->qty);
		
		$this->db->query_insert("Country_has_resource",$insertArray);
	}
	
	
	private $regions = NULL;
	
	/**
	 * Lists all the regions which this country is in
	 *
	 * @param bool $recache
	 * @return Regions[]
	 */
	function regionsIn($recache = false)
	{
		$sql = "SELECT region_id FROM Country_in_region WHERE country_id ='{$this->id}'";
		
		if($recache || $this->regions == NUL)
		{
			$this->regions = array();
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $region)
			{
				$this->regions[] = new Region($region['region_id']);
			}
		}
		
		return $this->regions;
	}
	
	
	/**
	 * Lists all the ids of the armies currently in this country
	 *
	 * @deprecated Moved to country instance
	 * @return Battalion[]
	 */
	/*
	function armiesPresent(Game $game)
	{
		$return = array();
		
		$sql = "SELECT b.id FROM Battalion b LEFT JOIN ".
			"Players p ON(b.player_id = p.id) WHERE p.game_id = '{$game->id}' AND".
			" b.country_id = '{$this->id}'";
		$query = $this->db->fetch_all_array($sql);
		
		$this->firephp->log('Armies present SQL', $sql);
		
		
		foreach($query AS $result)
		{
			$return[] = new Battalion($result['id']);
		}

		$this->firephp->log($return, "Armies present");

		return $return;
	}
	*/

	/**
	 * Attack a country's milita with a battalion. No enemies can
	 * be here if milita can be attacked
	 *
	 * @deprecated Moved to country instance
	 * @param Battalion $attWith Battalion to attack with
	 * @return 
	 */
	/*
	function attackMilita(Battalion $attackWith)
	{
		$att = new Attack($attackWith, $this);
		if(!$att->canAttack())
		{
			return "Can not attack country";
		}
		else if($att->hasEnemies())
		{
			return "Must clear enemy troops before attacking";
		}
		else
		{
			$att->doMilitaAttack();
			return $att;
		}
		
	}
	*/
	
	/**
	 * Gets the flag url for country
	 *
	 * @return string
	 */
	function getFlag()
	{}
	
	private $resources = NULL;
	function getResources()
	{
		if($this->resources == NULL)
		{
			$this->resources = array();
			$sql = "SELECT * FROM Country_has_resource WHERE country_id='{$this->id}'";
			$result = $this->db->fetch_all_array($sql);
			
			foreach($result AS $r)
			{
				$this->resources[$r['resource_id']] = new Resource($r['resource_id'], $r['resource_qty']);
			}
		}
		
		$this->firephp->info($this->resources, "Country has resource");
		return $this->resources;
	}
	
	/**
	 * Get name of country without creating full object
	 */
	static function name($id)
	{
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		global $db;
		$sql = "SELECT name FROM Countries WHERE id='{$id}' LIMIT 1";
		$db->query($sql);
		$result = $db->getRow();
		
		return $result['name'];		
	}
	
	/**
	 * Makes a new country, returns: -1 on error, 0 on failed mysql,
	 * else id of new country
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Country ID
	 */
	static function add(array &$insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name'])
		   && array_key_exists('default_milita', $insertArray)
		   && $insertArray['default_milita'] >= 0)
		{
			global $db;

			return $db->query_insert("Countries", $insertArray);
		}

		return -1;
	}
	
	function equals(Country $obj)
	{
		return ($this->id == $obj->id);
	}
	
	function __toString()
	{
		return "(Country){$this->getName()}({$this->id})";
	}
}


?>
