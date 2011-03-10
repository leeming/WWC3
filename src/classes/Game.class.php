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
 * Holds abstract top level information about a game such as it's name, default
 * settings and map info. For information on the current state of a game in play
 * then see <tt>GameInstance</tt>
 * 
 * @see GameInstance
 *
 * @uses Settings	This holds all the settings relating to this game
 * @uses Validate	Validate class for easier/shortcut validation
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Game extends Base
{
	public $name;
	
	/**
	 * Constructor for game
	 *
	 * @param int $id Id of the game
	 */
	function __construct($id)
	{
		parent::__construct();
		
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->id = $id;
		
		//get game info
		$this->db->query("SELECT * FROM Games WHERE id ='{$id}' LIMIT 1");
		
		//no game found
		if($this->db->numRows == 0)
		{
			$this->firephp->error("Game #{$id} not found");
			trigger_error("Game not found", E_USER_ERROR);
			return -1;
		}
		
		$result = $this->db->getRow();
		$this->name = $result['name'];
		
		$this->settings = new Settings($result['setting_id']);
		$this->desc = $result['desc'];
		
	}
	
	/**
	 * Gets the array of team id which are used in the game
	 *
	 * @return int[]
	 */
	function getTeamSet()
	{	return $this->settings->getTeams();	}
	
	/**
	 * Gets the name of the settings been used for this game
	 * @return String
	 */
	function getSettingsName()
	{	return $this->settings->name;	}
	/**
	 * Gets the id of the settings been used for this game
	 * @return int
	 */
	function getSettingsId()
	{	return $this->settings->id;	}

	
	/**
	 * Get the list of defaulta for game of $type
	 *
	 * @param string $type Type of default to get
	 * @return $type[]
	 */
	function getDefaults($type)
	{
		if(strtolower($type) == "research")
		{
			$result = $this->db->fetch_all_array("SELECT research_id FROM "
				."Default_researches WHERE game_id = '{$this->id}'");
			
			$return = array();
			foreach($result AS $default)
			{
				$return[] = new Research($default['research_id']);
			}
			return $return;
		}
		else if(strtolower($type) == "resource")
		{
			$result = $this->db->fetch_all_array("SELECT resource_id, resource_qty".
				" FROM  Default_resources WHERE game_id = '{$this->id}'");
			
			$return = array();
			foreach($result AS $default)
			{
				$return[] = new Resource($default['resource_id'], $default['resource_qty']);
			}
			return $return;
		}
		else if(strtolower($type) == "facility")
		{
			$result = $this->db->fetch_all_array("SELECT facility_id, facility_qty".
				" FROM  Default_facilities WHERE game_id = '{$this->id}'");
			
			$return = array();
			foreach($result AS $default)
			{
				$return[] = new Facility($default['facility_id'], $default['facility_qty']);
			}
			return $return;
		}
		else if(strtolower($type) == "unit")
		{
			$result = $this->db->fetch_all_array("SELECT unit_id, unit_qty".
				" FROM  Default_units WHERE game_id = '{$this->id}'");
			
			$return = array();
			foreach($result AS $default)
			{
				$return[] = new Unit($default['unit_id'], $default['unit_qty']);
			}
			return $return;
		}
		
		return array();
	}
	
	/**
	 * Add a default to one of the default tables for the game,
	 * these include: Research/Resource/Facility/Unit
	 *
	 * @param $toAdd Research/Resource/Facility/Unit Type to add
	 * @return int 1:success, 0:already default, -1:wrong arg/error
	 */
	function addDefault($toAdd)
	{
		if(get_class($toAdd) == "Research")
		{
			//add only if not already there
			$this->db->query("INSERT IGNORE INTO Default_researches
				(`research_id`,`game_id`) VALUES
				({$toAdd->id},{$this->id})");
			return $this->db->affected_rows;
		}
		else if(get_class($toAdd) == "Resource")
		{
			//add only if not already there
			$this->db->query("INSERT IGNORE INTO Default_resources
				(`resource_id`,`resource_qty`,`game_id`) VALUES
				({$toAdd->id},{$toAdd->qty},{$this->id})");
			return $this->db->affected_rows;
		}
		else if(get_class($toAdd) == "Facility")
		{
			//add only if not already there
			$this->db->query("INSERT IGNORE INTO Default_facilities
				(`facility_id`,`facility_qty`,`game_id`) VALUES
				({$toAdd->id},{$toAdd->qty},{$this->id})");
			return $this->db->affected_rows;
		}
		else if(get_class($toAdd) == "Unit")
		{
			//add only if not already there
			$this->db->query("INSERT IGNORE INTO Default_units
				(`unit_id`,`unit_qty`,`game_id`) VALUES
				({$toAdd->id},{$toAdd->qty},{$this->id})");
			return $this->db->affected_rows;
		}
		else
		{
			return -1;
		}
	}
	
	##########
	
	/**
	 * Makes a new Game, returns: -1 on error, 0 on failed mysql,
	 * else id of new game
	 *
	 * @todo Need to move some create stuff to game instance class
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Game Id
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']) &&
		   array_key_exists('setting_id', $insertArray) && Validate::isInt($insertArray['setting_id']))
		{
			global $db;
			
			$gameId = $db->query_insert("Games", $insertArray);
			
			
			//make countries
			$result = $db->fetch_all_array("SELECT c.id, c.default_milita FROM Countries c "
				."LEFT JOIN Setting_has_country s ON (s.country_id = c.id) WHERE "
				."s.setting_id = '{$insertArray['setting_id']}'");
			foreach($result AS $c)
			{
				$insert = array('country_id' => $c['id'],
								'game_id' => $gameId,
								'milita_qty' => $c['default_milita']);
				//insert starting milita
				$db->query_insert("Milita", $insert);
			}
			
			//make teams
			$result = $db->fetch_all_array("SELECT t.id, t.capital FROM Teams t LEFT JOIN Setting_has_team s "
				."ON (s.team_id = t.id) WHERE s.setting_id = '{$insertArray['setting_id']}'");
			foreach($result AS $t)
			{
				$insert = array('game_id' => $gameId,
								'team_id' => $t['id']);
				//insert game team
				$teamId = $db->query_insert("Game_teams", $insert);
				
				//make team capital
				$db->query_insert("Team_has_country",array(
					'country_id' => $t['capital'] ,
					'team_id' => $teamId
				));
			}
			
			return $gameId;
		}
		return -1;
	}
}
?>
