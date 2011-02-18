<?php
/**
 * Class containing information about a current team in a game
 *
 * @todo Still needs loads done, orders needs adding, team leader, governments
 */
class TeamInstance extends Base
{
	/**
	 * TeamInstance constructor
	 *
	 * @param int $id Id of game team
	 */
	function __construct($id)
	{
		parent::__construct();
		
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT team_id, game_id FROM Game_teams WHERE id ='{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->game = $result['game_id'];
		$this->team = new Team($result['team_id']);
	}
	
	private $countryList = NULL;
	private $countryIdList = NULL;
	
	/**
	 * Get a list of countries team owns
	 *
	 * @param string $returnType Type of array to return
	 * @return int[]/Country[]
	 */
	function countriesOwn($returnType = 'int', $recache = false)
	{
		$sql = "SELECT country_id FROM Team_has_country WHERE team_id ='{$this->id}'";
		
		if($returnType == 'int')
		{
			if($this->countryIdList == NULL || $recache)
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
			if($this->countryList == NULL || $recache)
			{
				$this->countryList = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $country)
				{
					//$this->countryList[] = new Country($country['country_id']);
					$this->countryList[] = CountryInstance::getInstance($this->game,$country['country_id']);
				}
			}
			
			return $this->countryList;
		}
	}
	
	/**
	 * Accessor method to get name of team to avoid $obj->team->team->name
	 *
	 * @return string Team name
	 */
	function getName()
	{
		return $this->team->name;
	}
	
	function getColour()
	{
		return $this->team->colour;
	}	
	function getCapital()
	{
		return $this->team->capital;
	}
	
	function getId()
	{	return $this->id; }
	
	/**
	 * Gets all the players which are in this team
	 *
	 * @return Player[]
	 */
	function getPlayers()
	{
		$sql = "SELECT id FROM Players WHERE team_id='{$this->id}'";
		$return = array();
		
		foreach($this->db->fetch_all_array($sql) AS $row)
		{
			$return[] = Player::getInstance($row['id']);
		}
		
		return $return;
	}
	
	
	static function makeGameTeams(GameInstance $game)
	{
		global $db, $firephp;
		$firephp->group("Adding teams to game");
		
		$firephp->log("Getting team set for game");
		$teams = $game->getTeamSet();
		$firephp->log($teams, "Team set got");
		
		foreach($teams AS $team)
		{
			$firephp->log("Creating team instance from #$team");
			self::add(array('team_id'=>$team, 'game_id'=>$game->id));
		}
		
		$firephp->log("Teams made");
		$firephp->groupEnd();
	}
	
	static function add(array $insertArray)
	{
		global $db, $firephp;
		$firephp->group("Creating team instances");
		
		if(array_key_exists('team_id', $insertArray) &&
		   Validate::isInt($insertArray['team_id']) &&
		   array_key_exists('game_id', $insertArray) &&
		   Validate::isInt($insertArray['game_id']))
		{
			$id = $db->query_insert('Game_teams', array(
				'team_id' => $insertArray['team_id'],
				'game_id' => $insertArray['game_id']
			));
			
			//check if something went wrong
			if($id == 0)
			{
				$firephp->warn("SQL insert error: ".$db->error);
			}
			else
			{
				$firephp->info("Team instance #$id created from ".
					"({$insertArray['team_id']},{$insertArray['game_id']})");
				
				//get capital
				$tmp = new Team($insertArray['team_id']);
				$db->query_insert("Team_has_country", array(
					'country_id' => $tmp->capital,
					'team_id' => $id));
				$firephp->info("Capital added to team");
			}
		}
		else
		{
			$firephp->log("Invalid parameters : Expecting team_id(int), game_id(int)");
			$id = -1;
		}
		
		
		$firephp->groupEnd();
		return $id;
	}
	
	private $countryResources = NULL;
	/**
	 * Gets an array of resources gained from holding a country
	 * @return Resource[]
	 */
	function getCountryResources()
	{
		$this->firephp->group("Getting Team's Country Resources");
			$this->countryResources = array();
	
			//get team owned countries
			$owned = $this->countriesOwn('obj');
			$this->firephp->log($owned,"team owns these countries");
			//foreach country owned by country
			foreach($owned AS $c)
			{
				//get the resources gained from country
				foreach($c->getResources() AS $res)
				{
					//$this->firephp->log($res->qty."x ".$res->name);
					
					//resource already added? - yes sum qty
					if(array_key_exists($res->id, $this->countryResources))
						$this->countryResources[$res->id]->qty += $res->qty;
					//no - add to array
					else
						$this->countryResources[$res->id] = $res;
				}
			}
		
		//$this->firephp->info($this->countryResources,"Total resources from country");
		$this->firephp->groupEnd();
		return $this->countryResources;
	}
	
	/**
	 * Gets an array of bonus percentages gained from holding a country
	 * These are then used to * resources
	 *
	 * @todo Implement - does db have this info?
	 * @return float[]
	 */
	function getCountryBonuses()
	{ return array(); }
	
	/**
	 *
	 * @todo Implement - does db have this info?
	 * @return Resource[]
	 */
	function getRegionResources()
	{ return array(); }
	
	/**
	 *
	 * @todo Implement - does db have this info?
	 * @return float[]
	 */
	function getRegionBonuses()
	{ return array(); }
}
?>