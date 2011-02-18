<?php
/**
 * List of classes used by this class
 */
/* now in include_path
require_once(CLASSPATH."base/Base.class.php");
require_once(CLASSPATH."game/Player.class.php");
require_once(CLASSPATH."site/User.class.php");
require_once("Settings.class.php");
*/
/**
 * Class containing all information about current games
 * This could include current games playing or games waiting to play
 *
 * @todo Add cycle management stuff, max player setting
 * 		 join method
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
	
	private $private = NULL;
	
	/**
	 * Find out if a game is private or not
	 *
	 * @deprecated moved to instance
	 * @return bool
	 */
	/*
	function isPrivate()
	{
		if($this->private == NULL)
		{
			$this->db->query("SELECT owner_id FROM Private_games WHERE game_id ='{$this->id}' LIMIT 1");
			$this->private = ($this->db->numRows > 0);
			
		}
		
		return $this->private;
	}
	*/
	private $ownerId = NULL;
	private $owner = NULL;
	
	/**
	 * Find out the owner of a game if private
	 *
	 * @param string $returnType Type of return
	 * @return int/User
	 */
	/*
	function getOwner($returnType = "int")
	{
		//Check to see if game is private
		if($this->isPrivate())
		{
			$sql = "SELECT owner_id FROM Private_game WHERE game_id = '{$this->id}' LIMIT 1";
			
			if($returnType == "int")
			{
				if($this->ownerId == NULL)
				{
					$this->db->query($sql);
					$result = $this->db->getRow();
					
					$this->ownerId = $result['owner_id'];
				}
				
				return $this->ownerId;
			}
			else
			{
				if($this->owner == NULL)
				{
					$this->db->query($sql);
					$result = $this->db->getRow();
					
					$this->owner = new User($result['owner_id']);
				}
				
				return $this->ownerId;
			}
		}
	}
*/
	private $invitedPlayersId = NULL;
	private $invitedPlayers = NULL;
	
	/**
	 * Get a list of users invited to a private game
	 *
	 * @param string $returnType Type of return
	 * @return int[]/User[]
	 */
	/*
	function getInvitedPlayers($returnType = "int", $recache = false)
	{
		//Check to see if game is private
		if($this->isPrivate())
		{
			$sql = "SELECT user_id FROM Game_invites WHERE game_id = '{$this->id}'";
			
			if($returnType == "int")
			{
				if($this->invitedPlayersId == NULL || $recache)
				{
					$this->db->query($sql);
					$result = $this->db->getRow();
					
					$this->invitedPlayersId = $result['user_id'];
				}
				
				return $this->invitedPlayersId;
			}
			else
			{
				if($this->invitedPlayers == NULL || $recache)
				{
					$this->db->query($sql);
					$result = $this->db->getRow();
					
					$this->invitedPlayers = new User($result['user_id']);
				}
				
				return $this->invitedPlayers;
			}
		}
		//else public
		return array();
	}
	*/
	
	

	/**
	 * Check if a user is on invite list
	 *
	 * @param int/User $user User to check if on list
	 * @return bool
	 */
	/*
	function isInvited($user)
	{
		if($this->private)
		{
			if(Validate::isInt($user))
				$userId = $user;
			else
				$userId = $user->id;
			
			return in_array($userId, $this->getInvitedPlayers('int', true));
		}
		
		return 0;
	}
*/



	/**
	 * Check to see if user can join game
	 *
	 * @param int/User $user User to check if can join
	 * @return bool
	 */
	/*
	function canJoinGame(&$user)
	{
		if(Validate::isInt($user))
		{
			$userId = $user;
		}
		else if(get_class($user) == "User")
		{
			$userId = $user->id;
		}
		//invalid $user data type
		else
		{
			return -1;
		}
		
		//Check if game is private and user not invited
		if($this->isPrivate && !in_array($userId, $this->getInvitedPlayers()))
		{
			return 0;
		}
		
		//check to see user not currently in game
		if(in_array($userId, $this->getPlayerList()))
			return 0;
		
		//check if already started and has limit
		if(time()-$this->startTimestamp >$this->settings->lateEntryTime)
			return 0;
		
		//check if there is a limit on players
		if($this->settings->maxPlayers < count($this->getPlayerList())
		   && $this->settings->maxPlayers != 0)
			return 0;
		
		return 1;
		
	}
	*/
	
	private $playerList = NULL;
	private $playerIdList = NULL;
	
	/**
	 * Get a list of players in game
	 *
	 * @return Player[]
	 */
	/*
	function getPlayerList($returnType = "int", $recache = false)
	{
		if($returnType == "int")
		{
			if($this->playerIdList == NULL || $recache)
			{
				$result = $this->db->fetch_all_array("SELECT id FROM Players WHERE game_id ='{$this->id}'");
				
				
				$this->playerIdList = array();
				foreach($result AS $players)
				{
					$this->playerIdList[] = $players['id'];
				}
			}
			
			return $this->playerIdList;
		}
		else
		{
			if($this->playerList == NULL || $recache)
			{ 
				$result = $this->db->fetch_all_array("SELECT id FROM Players WHERE game_id ='{$this->id}'");
				
				
				$this->playerList = array();
				foreach($result AS $players)
				{
					$this->playerList[] = new Player($players['id']);
				}
			}
			
			return $this->playerList;
		}
		
	}
	*/
	
	/**
	 * Gets the current cycle the game is on, this is
	 * calculated from the start time and the cycle speed
	 * in the game settings
	 *
	 * @return int Cycle number
	 */
	/*
	function cycleNumber()
	{
		//has game started yet?
		if(time() < $this->startTimestamp)
			return 0;
		else
			return floor((time()-$this->startTimestamp)/$this->settings->cycleTime);
	}
	*/
	
	
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
	 * Ends a game instance, recording the winner and cleaning
	 * up the database of game entries
	 *
	 * @todo Doesnt record winner etc yet
	 */
	/*
	function end()
	{
		#List of queries to run to clean up database
		//clear invites
		$sql[] = "DELETE FROM Game_invites WHERE game_id='{$this->id}'";
		//clear current milita records
		$sql[] = "DELETE FROM Milita WHERE game_id='{$this->id}'";

		$teams = $this->db->fetch_all_array("SELECT id FROM Game_teams WHERE game_id='{$this->id}'");
		foreach($teams AS $t)
		{
			//clear team orders
			$sql[] = "DELETE FROM Team_orders WHERE team_id='{$t['id']}'";
			
			//clear team owned countries
			$sql[] = "DELETE FROM Team_has_country WHERE team_id='{$t['id']}'";
		
		}
		
		//clear teams
		$sql[] = "DELETE FROM Game_teams WHERE game_id='{$this->id}'";
		//clear from private games list
		$sql[] = "DELETE FROM Private_games WHERE game_id='{$this->id}'";
		
		#Clean up all tables relating to players
		//get ids of all players
		$ids = $this->db->fetch_all_array("SELECT id FROM Players WHERE game_id='{$this->id}'");
		foreach($ids AS $id)
		{
			$sql[] = "DELETE FROM Player_has_facility WHERE player_id='{$id}'";
			$sql[] = "DELETE FROM Player_has_research WHERE player_id='{$id}'";
			$sql[] = "DELETE FROM Player_has_resource WHERE player_id='{$id}'";
			$sql[] = "DELETE FROM Player_has_unit WHERE player_id='{$id}'";
			$sql[] = "DELETE FROM Player_research_queue WHERE player_id='{$id}'";
			
			//battalions
			$batts = $this->db->fetch_all_array("SELECT id FROM Battalion WHERE player_id='{$id}'");
			foreach($batts AS $bat)
			{
				$sql[] = "DELETE FROM Battalion_commanders WHERE battalion_id='{$bat}' LIMIT 1";
				$sql[] = "DELETE FROM Battalion WHERE id='{$bat}' LIMIT 1";
			}
			
			
			$sql[] = "DELETE FROM Players WHERE id='{$id}' LIMIT 1";
		}
		
		$sql[] = "DELETE FROM Default_facilities WHERE game_id='{$this->id}'";
		$sql[] = "DELETE FROM Default_researches WHERE game_id='{$this->id}'";
		$sql[] = "DELETE FROM Default_resources WHERE game_id='{$this->id}'";
		$sql[] = "DELETE FROM Default_units WHERE game_id='{$this->id}'";
		
		//finally get rid of game entry
		$sql[] = "DELETE FROM Games WHERE id='{$this->id}' LIMIT 1";
		
		//print_r($sql);
		
		//execute queries
		foreach($sql AS $query)
		{
			$this->db->query($query);
		}
		
	}
	*/
	
	/**
	 * Finish current game and start a new on at $time
	 *
	 * @param int $time Time (in sec) for game to start
	 * @return Game Game object for new game
	 */
	/*
	function restart($time = 0)
	{
		//save some tables
		$reshs = $this->db->fetch_all_array("SELECT * FROM Default_researches WHERE game_id='{$this->id}'");
		$units = $this->db->fetch_all_array("SELECT * FROM Default_units WHERE game_id='{$this->id}'");
		$facs = $this->db->fetch_all_array("SELECT * FROM Default_facilities WHERE game_id='{$this->id}'");
		$resrs = $this->db->fetch_all_array("SELECT * FROM Default_resources WHERE game_id='{$this->id}'");
		
		//end current game
		$this->end();
		
		
		//add new game
		$array = array(
			'name' => $this->name,
			'setting_id' => $this->settings->id,
			'desc' => $this->desc,
			'start_timestamp' => time()+$time
		);
		
		$id = Game::add($array);
		
		$newGame = new Game((int)$id);
		
		//add back researches
		foreach($reshs AS $research)
		{
			$obj = new Research($research['research_id']);
			$newGame->addDefault($obj);
		}
		//add back units
		foreach($units AS $unit)
		{
			$obj = new Unit($unit['unit_id'], $unit['unit_qty']);
			$newGame->addDefault($obj);
		}
		//add back facilities
		foreach($facs AS $f)
		{
			$obj = new Facility($f['facility_id'],$f['facility_qty']);
			$newGame->addDefault($obj);
		}
		//add back resource
		foreach($resrs AS $f)
		{
			$obj = new Resource($f['resource_id'],$f['resource_qty']);
			$newGame->addDefault($obj);
		}
		
		
		//return new game
		return $newGame;
	}
	*/
	
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
