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
 * Description of the file
 *
 * @uses GameInstance	Links to the game instance this belongs to
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */

class CountryInstance extends Country
{
	/**
	 * Constructor for CountryInstance class
	 * This can not be directly called, must use
	 * CountryInstance::getInstance() method
	 *
	 * @param int $game GameInstance Id
	 * @param int $country Country Id
	 */
	public function __construct($game, $country, $invalid = true)
	{
		parent::__construct($country);
		
		if($invalid)
			trigger_error("CountryInstance cant not be called directly! use getInstance", E_USER_ERROR);
		
		$this->game = GameInstance::getInstance($game);		
	}
	
	/**
	 * Gets a singleton instance for a (game,contry) pair
	 * note: Country.id == CountryInstance.id
	 * 
	 * @param int $game Game instance id
	 * @param int $country Country id
	 * @return CountryInstance
	 */
	static function getInstance($game, $country)
	{
		if(!Validate::isInt($game))
		{
			trigger_error("(int)\$game expected, (".gettype($game).") passed", E_USER_ERROR);
			return;
		}
		if(!Validate::isInt($country))
		{
			trigger_error("(int)\$country expected, (".gettype($country).") passed", E_USER_ERROR);
			return;
		}
		global $firephp;
		//Check if instance exists
		if(isset($_SESSION['cache'][__CLASS__][$country]))
		{
			//$firephp->log("Country Instance(cache)");
			return $_SESSION['cache'][__CLASS__][$country];
		}
		elseif(isset($_SESSION['singletons'][__CLASS__][$country]))
		{
			//$firephp->log("Country Instance(singleton) saving to cache");
			$_SESSION['cache'][__CLASS__][$country] = unserialize($_SESSION['singletons'][__CLASS__][$country]);
			return $_SESSION['cache'][__CLASS__][$country];
		}
		else
		{
			//$firephp->log("Country Instance(new) saving to cache");
			$_SESSION['cache'][__CLASS__][$country] = new CountryInstance($game, $country, false);
			return $_SESSION['cache'][__CLASS__][$country];
		}
	}
	
	/**
	 * Class destructor, saves object to session so that
	 * it can be recovered on next page load
	 */
	function __destruct()
	{
		//save to singleton array
		@$_SESSION['singletons'][__CLASS__][$this->id] = serialize($this);
		//get rid of cache for this page
		unset($_SESSION['cache'][__CLASS__][$this->id]);
	}
	
	function getId(){	return $this->id; }
	function getName(){	return $this->name; }
	
	
	public $milita = NULL;

	/**
	 * Get the number of milita in the country
	 * This includes the turn by turn population growths as well
	 * as milita strength increases from multiple change of control
	 *
	 * @todo Currently change of control multiplier not done
	 * @return int
	 */
	function getMilita()
	{		
		$sql = "SELECT milita_qty FROM Milita WHERE country_id = '{$this->getId()}' AND game_id = '{$this->game->id}' LIMIT 1";		
		$this->db->query($sql);
		$this->firephp->log($sql,"Get game country milita");
		$result = $this->db->getRow();
		
		$this->milita = $result['milita_qty'];
		
		//$this->firephp->log("Milita is ", $this->milita);
		return $this->milita;
	}
	
	
	function getResources()
	{
		$sql = "SELECT resource_id, resource_qty FROM Country_has_resource".
			" WHERE country_id='{$this->id}'";
		$return = array();
		
		//$this->db->query($sql);
		$result = $this->db->fetch_all_array($sql);
		
		//$result = array();
		
		foreach($result AS $r)
		{ 
			$return[$r['resource_id']] = new Resource($r['resource_id'], $r['resource_qty']);
		}
		
		return $return;
	}
	
	private $owner = NULL;

	/**
	 * Find the ownership of country, this returns 0 for milita
	 * or teaminstance
	 *
	 * @param bool $recache
	 * @return int Team id of who owns country
	 */
	function getOwner($recache = FALSE)
	{
		if($this->owner == NULL || $recache)
		{
			$this->db->query("SELECT team_id FROM Team_has_country WHERE ".
				"country_id = '{$this->getId()}' AND game_id = '{$this->game->id}'".
				"LIMIT 1");
			$result = $this->db->getRow();
			
			$this->owner = $result['team_id'];
		}
		
		return $this->owner;
	}


	/**
	 * Set the owner of the country
	 *
	 * @param int $id Id of the team to set owner as
	 */
	function setOwner($id)
	{
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		//are both country & team instances in same game?
		$team = new TeamInstance($id);
		if(!$team->game == $this->game->id)
		{
			trigger_error("Can not assign team ownership to country. ".
				"Game instance mismatch", E_USER_ERROR);
			return;
		}
		
		$this->firephp->log("Setting country #{$this->id} new owner #{$id}");
		//Delete previous owner and insert new owner
		//if milita owned, no problem delete still done, just nothing is deleted
		$this->db->query("DELETE FROM Team_has_country WHERE team_id = '{$id}' ".
			"AND country_id = '{$this->getId()}' LIMIT 1");
		$this->db->query("INSERT INTO Team_has_country (`team_id`,`country_id`) ".
			"VALUES ('{$id}', '{$this->getId()}')");
		
		//update class
		$this->owner = $id;
	}
	
	
	/**
	 * Gets a collection of countries which are border
	 * to this one. This actually asks the Country class
	 * for this information and passes it on
	 *
	 * @return int[]
	 */
	//function getBorders()
	//{ return $this->country->getBorders(); }
	
	/**
	 * Gets a collection of countries which are border
	 * to this one AND which the team passed, are able
	 * to attack. This basically means countries
	 * someone can attack
	 *
	 * If an int is passed, this is assumed to be the
	 * team instance id, this is then used to fetch the
	 * teamInstance object
	 *
	 * @todo I think this should implement CountryCollection??
	 * @see getBorders()
	 * @see getMoveBorders()
	 *
	 * @param int/TeamInstance $team
	 * @return int[]
	 */
	function getAttackBorders($team)
	{
		if(Validate::isInt($team))
			$team = TeamInstance::getInstance($team);
			
		//$team should now be TeamInstance, do sanity check
		if(get_class($team) != "TeamInstance")
		{
			trigger_error("(int/TeamInstance)\$team expected, (".gettype($team).") passed", E_USER_ERROR);
			return;
		}
		
		//get team owned countries and this country's borders
		$teamCountries = $team->countriesOwn('int', true);
		$borders = $this->getBorders();
		
		//create an array of countries which are bordered but not owned
		$return = array();
		foreach($borders AS $border)
		{
			//does team own this country?
			if(!in_array($border, $teamCountries))
				$return[] = $border;
		}
		
		$this->firephp->log($return,"Attackable borders from {$this->getId()}");
		return $return;
	}
	
	/**
	 * Gets a collection of countries which are border
	 * to this one AND which the team passed, are able
	 * to move to. 
	 *
	 * If an int is passed, this is assumed to be the
	 * team instance id, this is then used to fetch the
	 * teamInstance object
	 *
	 * @todo I think this should implement CountryCollection??
	 * @see getBorders()
	 * @see getAttackBorders()
	 *
	 * @param int/TeamInstance $team
	 * @return int[]
	 */
	function getMoveBorders($team)
	{
		if(Validate::isInt($team))
			$team = TeamInstance::getInstance($team);
			
		//$team should now be TeamInstance, do sanity check
		if(get_class($team) != "TeamInstance")
		{
			trigger_error("(int/TeamInstance)\$team expected, (".gettype($team).") passed", E_USER_ERROR);
			return;
		}
		
		//get team owned countries and this country's borders
		$teamCountries = $team->countriesOwn('int', true);
		$borders = $this->getBorders();
		
		//create an array of countries which are bordered and owned
		$return = array();
		foreach($borders AS $border)
		{
			//does team own this country?
			if(in_array($border, $teamCountries))
				$return[] = $border;
		}
		
		$this->firephp->log($return,"Moveable borders from {$this->getId()}");
		return $return;
	}

	/**
	 * Lists all the battalion ids of the armies currently in this country
	 *
	 * @return Battalion[]
	 */
	function armiesPresent()
	{
		$return = array();
		
		$sql = "SELECT id FROM Battalion WHERE country_id='{$this->getId()}'";
		$query = $this->db->fetch_all_array($sql);
		
		$this->firephp->log($sql, 'Armies present SQL');
		
		
		foreach($query AS $result)
		{
			$return[] = new Battalion($result['id']);
		}
		
		$this->firephp->log($return, "Armies present");
		
		return $return;
	}

	/**
	 * Attack a country's milita with a battalion. No enemies can
	 * be here if milita can be attacked
	 *
	 * @param Battalion $attWith Battalion to attack with
	 * @return 
	 */
	function attackMilita(Battalion $attackWith)
	{
		$this->firephp->group("Attacking milita");
		$this->firephp->log($this->id, "Country id");
		
		$att = new Attack($attackWith, $this);
		if(!$att->canAttack())
		{
			$this->firephp->groupEnd();
			return "Can not attack country";
		}
		else if($att->hasEnemies())
		{
			$this->firephp->groupEnd();
			return "Must clear enemy troops before attacking";
		}
		else
		{
			$this->firephp->groupEnd();
			$att->doMilitaAttack();
			return $att;
		}
		
	}
	




	/**
	 * !! No cloning of singleton instances
	 */
	private function __clone() {}
	
	/**
	 * Implemented for the sake of consistency but this should
	 * never return true because its a singleton!
	 *
	 * @todo Error caused, maybe something to do with php's poor overriding & type hinting?
	 */
	/*
	function equals(CountryInstance $cmp)
	{
		if($cmp->getId() == $this->getId() &&
		   $cmp->game->getId() == $this->game->getId())
			return true;
		else
			return false;
	}*/
	
	function __wakeup()
	{
		//refresh db connection
		global $db;
		$this->db = $db;
	}
}
?>