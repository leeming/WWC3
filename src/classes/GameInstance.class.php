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
 * Singleton (per user) class to hold the current known state of a game
 *
 * TODO Needs reviewing and refactoring
 *
 * @uses Instance	Interface class
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class GameInstance implements Instance
{
	
	/**
	 * Constructor for GameInstance class
	 * This can not be directly called, must use
	 * GameInstance::getInstance() method
	 *
	 * @param int $id GameInstance Id
	 */
	private function __construct($id)
	{
		global $db, $firephp;
		$this->db = &$db;
		$this->firephp = &$firephp;
		
		$sql = "SELECT * FROM Game_in_play WHERE id='{$id}' LIMIT 1";
		$query = $this->db->query($sql);
		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->start = $result['start_timestamp'];
		$this->finished = $result['finish_timestamp'];
		
		$this->game = new Game($result['game_id']);
		
	}
	
	
	/**
	 * Gets a singleton instance of GameInstance
	 * 
	 * @param int $game GameInstance id
	 * @return GameInstance
	 */
	static public function getInstance($game)
	{
		global $firephp;
		if(!Validate::isInt($game))
		{
			trigger_error("(int)\$game expected, (".gettype($game).") passed", E_USER_ERROR);
			return;
		}
			
		//Check if instance exists
		if(isset($_SESSION['cache'][__CLASS__][$game]))
		{
			//$firephp->log("Game Instance(cache)");
			return $_SESSION['cache'][__CLASS__][$game];
		}
		elseif(isset($_SESSION['singletons'][__CLASS__][$game]))
		{
			//$firephp->log("Game Instance(singleton) saving to cache");
			$_SESSION['cache'][__CLASS__][$game] = unserialize($_SESSION['singletons'][__CLASS__][$game]);
			return $_SESSION['cache'][__CLASS__][$game];
		}
		else
		{
			//$firephp->log("Game Instance(new) saving to cache");
			$_SESSION['cache'][__CLASS__][$game] = new GameInstance($game);
			return $_SESSION['cache'][__CLASS__][$game];
		}
	}
	
	
	/**
	 * Class destructor, saves object to session so that
	 * it can be recovered on next page load
	 */
	function __destruct()
	{
		//$this->firephp->log("Destructing game instance and storing into singleton");
		//save to singleton array
		@$_SESSION['singletons'][__CLASS__][$this->id] = serialize($this);
		//get rid of cache for this page
		unset($_SESSION['cache'][__CLASS__][$this->id]);
	}
	
	
	private $private = NULL;
	
	/**
	 * Find out if a game is private or not
	 *
	 * @return bool
	 */
	function isPrivate()
	{
		if($this->private == NULL)
		{
			//$this->db->query("SELECT owner_id FROM Private_games WHERE ".
			//	"game_id ='{$this->id}' LIMIT 1");
			//$this->private = ($this->db->numRows > 0);
			return 0;
		}
		
		return $this->private;
	}
	
	private $ownerId = NULL;
	private $owner = NULL;
	
	/**
	 * Find out the owner of a game if private
	 *
	 * @param string $returnType Type of return
	 * @return int/User
	 */
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

	private $invitedPlayersId = NULL;
	private $invitedPlayers = NULL;
	
	/**
	 * Get a list of users invited to a private game
	 *
	 * @param string $returnType Type of return
	 * @return int[]/User[]
	 */
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


	/**
	 * Check if a user is on invite list - if private
	 *
	 * @param int/User $user User to check if on list
	 * @return bool
	 */
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
	
	/**
	 * Check to see if user can join game
	 *
	 * @param int/User $user User to check if can join
	 * @return bool
	 */
	function canJoinGame(&$user)
	{
		$this->firephp->warn($this->db);
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
			$this->firephp->error("Invalid datatype passed");
			return -1;
		}
		$this->firephp->log("Pre test");
		//Check if game is private and user not invited
		if($this->isPrivate() && !in_array($userId, $this->getInvitedPlayers()))
		{
			$this->firephp->log("Private game and you are not invited!");
			return 0;
		}
		$this->firephp->log("Post test");
		//check to see user not currently in game
		if(in_array($userId, $this->getPlayerList()))
		{
			$this->firephp->log("Already in game");
			return 0;
		}
		
		//check if already started and has limit
		if(time()-$this->start >$this->game->settings->lateEntryTime)
		{
			$this->firephp->log("Game started and has late entry limit");
			return 0;
		}
		
		//check if there is a limit on players
		if($this->game->settings->maxPlayers < count($this->getPlayerList())
		   && $this->game->settings->maxPlayers != 0)
		{
			$this->firephp->log("Game has max number of players");
			return 0;
		}
		
		return 1;
	}
	

	private $playerList = NULL;
	private $playerIdList = NULL;
	
	/**
	 * Get a list of players in game
	 *
	 * @return Player[]
	 */
	function getPlayerList($returnType = "int", $recache = false)
	{
		if($returnType == "int")
		{

			$result = $this->db->fetch_all_array("SELECT id FROM Players ".
				"WHERE game_id ='{$this->id}'");
			
			
			$this->playerIdList = array();
			foreach($result AS $players)
			{
				$this->playerIdList[] = $players['id'];
			}

			
			return $this->playerIdList;
		}
		else
		{
			$result = $this->db->fetch_all_array("SELECT id FROM Players ".
				"WHERE game_id ='{$this->id}'");
			
			
			$this->playerList = array();
			foreach($result AS $players)
			{
				$this->playerList[] = Player::getInstance($players['id']);
			}
			
			
			return $this->playerList;
		}
		
	}
	/**
	 * Gets list of players in game
	 * @return Player[]
	 * @see getPlayerList
	 */
	function getPlayers()
	{	return $this->getPlayerList('obj');	}
	
	/**
	 * Gets the current cycle the game is on, this is
	 * calculated from the start time and the cycle speed
	 * in the game settings
	 *
	 * @return int Cycle number
	 */
	function cycleNumber()
	{
		//has game started yet?
		if(time() < $this->start)
			return 0;
		else
			return floor((time()-$this->start)/$this->game->settings->cycleTime);
	}
	
	/**
	 * Gets the time when the game started
	 * @return int
	 */
	function getStartTime()
	{	return $this->start;	}
	/**
	 * Checks to see if the game has started yet
	 * @return bool
	 */
	function hasStarted()
	{ return ($this->start <= time());	}
	/**
	 * Gives number of seconds until game starts.
	 * @return int
	 */
	function timeUntilStart()
	{	return $this->start - time();	}
	
	
	/**
	 * Gets the array of team id which are used in the game
	 *
	 * @return int[]
	 */
	function getTeamSet()
	{	return $this->game->getTeamSet();	}
	
	/**
	 * Gets game name
	 * @return String
	 */
	function getName()
	{	return $this->game->name; }
	/**
	 * Gets game desc
	 * @return String
	 */
	function getDesc()
	{	return $this->game->desc; }
	/**
	 * Gets game settings name
	 * @return String
	 */
	function getSettingsName()
	{	return $this->game->getSettingsName(); }
	/**
	 * Gets game settings id
	 * @return int
	 */
	function getSettingsId()
	{	return $this->game->getSettingsId(); }
	/**
	 * Gets the Game object from this instance
	 * @return Game
	 */
	function getGame()
	{	return $this->game;}
	
	private $teams = NULL;
	/**
	 * Get an array of TeamInstances which are in this gameInstance
	 *
	 * @return TeamInstance
	 */
	function getTeams()
	{
		
			$this->teams = array();
			$sql = "SELECT id FROM Game_teams WHERE game_id='{$this->id}'";
			
			$result = $this->db->fetch_all_array($sql);
			foreach($result AS $id)
			{
				$this->teams[$id['id']] = new TeamInstance($id['id']);
			}
		
		
		//$this->firephp->log($this->teams, "Teams in game instance");
		return $this->teams;
	}
	
	/**
	 * Checks to see if is time for a cycle update
	 * Pass a time to allow cron job to iterate over
	 * all game instances
	 *
	 * @param int $time
	 * @return bool
	 */
	function isCycle($time)
	{
		//game not started yet
		if($this->start > time())
			return false;
		
		$intoGame = $time - $this->start;
		$intoCycle = $intoGame%$this->game->settings->cycleTime;
		
		$this->firephp->log($this->game->settings->cycleTime-$intoCycle,"Seconds untill next cycle");
		//allow for a few seconds either way
		$leway = 5;
		if($intoCycle + $leway >= $this->game->settings->cycleTime
		   || $intoCycle - $leway <= 0)
			return true;
		else
			return false;
	}
	
	function timeTillNextCycle()
	{ return (time()-$this->start)%$this->game->settings->cycleTime; }
	
	/**
	 * Does all the stuff needed for updating cycles
	 *
	 * @todo Add resource % modifiers for team countries/regions
	 * @todo Unit output modifiers?
	 * @todo Research not ticked over
	 * @todo Unit output to battalions
	 * @todo Army upkeep
	 */
	function doCycle()
	{
		$this->firephp->log("doCycle called");
		
		$this->firephp->group("Doing game cycle for #{$this->id}");
		$sql = array();
		
		
		//Add a turn to all players in game
		/*$sql[] = "UPDATE Player_has_resource SET resource_qty=`resource_qty`+1".
			" WHERE player_id IN (SELECT id FROM Players WHERE game_id='{$this->id}')".
			" AND resource_id='".RESOURCE_TURNS."'";
		*/
		//Update country assims
		$sql[] = "UPDATE Milita SET assim=`assim`+".$this->game->settings->assimRate.
			" WHERE game_id='{$this->id}'";
		
		
		$teams = $this->getTeams();
		
		//Check each player
		$list = $this->getPlayerList('obj', true);
		//$this->firephp->log($list, "Players in game");
		$this->firephp->group("Players in Game", array('Collapsed' => true));
		foreach($list AS $player)
		{
			$this->firephp->group($player->getHandle(), array('Collapsed' => true));
			
			#calc resources
			$resources = $player->getResourcesPerCycle();
			
			/*
			//add on resources from team to player
			$team = $teams[$player->team->id];
			
			foreach($team->getCountryResources() AS $r)
			{ 
				if(array_key_exists($r->id,$resources))
					$resources[$r->id] += $r->qty;
				else
					$resources[$r->id] = &$r;
			}
			
			foreach($teams[$player->team->id]->getRegionResources() AS $r)
			{
				if(array_key_exists($r->id,$resources))
					$resources[$r->id] += $r->qty;
				else
					$resources[$r->id] = &$r;
			}
			*/
			//add % modifiers
			
			//create sql
			foreach($resources AS $r)
			{
				$sql[] = "INSERT INTO Player_has_resource VALUES ({$r->id},".
					" {$player->id}, {$r->qty}) ON DUPLICATE KEY UPDATE ".
					"resource_qty=`resource_qty`+{$r->qty}";
			}
			
			#calc units
			
			$units = $player->getUnitsPerCycle();
			//modifiers? not yet
			
			//battalion distributions not done - use primary bat
			$bat = $player->getSelectedBattalion();
			
			//create sql
			foreach($units AS $u)
			{
				$sql[] = "INSERT INTO Player_has_unit VALUES ({$u->id}, ".
					"{$bat->id}, {$u->qty}) ON DUPLICATE KEY UPDATE ".
					"unit_qty=`unit_qty`+{$u->qty}";
			}
			
			
			#Update research
			//do research later
			
			$this->firephp->groupEnd();
		}
		
		//clean up any over 100 assim countries
		$sql[] = "UPDATE Milita SET assim=100 WHERE assim>100";
		$this->firephp->groupEnd();
		
		$this->firephp->group("SQL", array('Collapsed' => true));
		
		//do all sql queries now
		foreach($sql AS $s)
		{
			$this->firephp->log($s);
			$this->db->query($s);
		}
		
		$this->firephp->groupEnd();	//end sql group
		$this->firephp->groupEnd(); //end turn group
	}
	
	/**
	 * Allow a user to join this game
	 *
	 * @param User $user User to add to game
	 * @return int Player id or -1 on fail
	 */
	function join(User &$user)
	{
		$this->firephp->group("Joining game #{$this->id}");
		
		//make sure that user can join game
		if(!$this->canJoinGame($user))
			return -1;	
		
		$this->firephp->log("Able to join");
		
		//Get team with least players
		$sql = "SELECT `Game_teams`.`id` ,`Game_teams`.`team_id` FROM `Game_teams` "
			."LEFT JOIN `Players` ON (`Game_teams`.`id` = `Players`.`team_id`) "
			."WHERE `Game_teams`.`game_id` = '{$this->id}' "
			."GROUP BY `Game_teams`.`id` ORDER BY COUNT(`Players`.`id`) ASC LIMIT 1";	
		$this->db->query($sql);
		$team = $this->db->getRow();
		$this->firephp->log($sql);
		$this->firephp->log("Joining team #{$team['id']}");

		//insert into player table & get player id
		$playerId = $this->db->query_insert("Players", array(
			'user_id' => $user->id,
			'game_id' => $this->id,
			'team_id' => $team['id']
		));
		
		$this->firephp->log($playerId, "Player id");
		
		//find team capital - in the future this should be calc'd with capital set
		$sql = "SELECT `capital` FROM `Teams` WHERE `id` = {$team['team_id']} LIMIT 1";
		$this->db->query($sql);
		$r = $this->db->getRow();
		$capital = $r['capital'];

		$this->firephp->log($capital, "Team capital");

		//create battalion
		$battalionId = $this->db->query_insert("Battalion", array(
			'player_id' => $playerId,
			'country_id'=> $capital
		));
		$this->db->query_insert("Battalion_commanders", array(
			'battalion_id' => $battalionId
		));
		
		$this->firephp->log($battalionId, "Battalion added to capital");

		//Add resources
		$this->db->query("INSERT INTO `Player_has_resource` (`resource_id`, "
			."`player_id`, `resource_qty`) SELECT `resource_id`, {$playerId}, "
			."`resource_qty` FROM `Default_resources` WHERE `game_id` = {$this->game->id}");
		$this->firephp->log("Default resources added");
		
		//add units
		$this->db->query("INSERT INTO `Player_has_unit` (`unit_id`, `battalion_id`, "
			."`unit_qty`) SELECT `unit_id`, {$battalionId}, `unit_qty` FROM "
			."`Default_units` WHERE `game_id` = {$this->game->id}");
		$this->firephp->log("Default units added");
		
		//add facilities
		$this->db->query("INSERT INTO `Player_has_facility` (`facility_id`, "
			."`player_id`, `country_id`, `facility_qty`) SELECT `facility_id`, "
			."{$playerId}, {$capital} , `facility_qty` FROM `Default_facilities` "
			."WHERE `game_id` = {$this->game->id}");
		$this->firephp->log("Default resources added");
		
		//add researches
		$this->db->query("INSERT INTO `Player_has_research` (`player_id`, "
			."`research_id`) SELECT {$playerId}, `research_id` FROM `Default_researches` "
			."WHERE `game_id` = {$this->id}");
		$this->firephp->log("Default researches added");
		
		
		$this->firephp->log("Complete!");
		$this->firephp->groupEnd();
		return $playerId;
	}
	
	
	/**
	 * Ends a game instance, recording the winner and cleaning
	 * up the database of game entries
	 *
	 * @todo Doesnt record winner etc yet
	 */
	function end()
	{
		$this->firephp->group("Ending game instance");
		$this->firephp->log("Attempting to end #{$this->id}");
		
		#List of queries to run to clean up database
		//clear invites
		$sql[] = "DELETE FROM Game_invites WHERE game_id='{$this->id}'";
		
		//clear current milita records
		$sql[] = "DELETE FROM Milita WHERE game_id='{$this->id}'";

		//Clear teams
		/*
		$teams = $this->db->fetch_all_array("SELECT id FROM Game_teams WHERE ".
			"game_id='{$this->id}'");
		
		foreach($teams AS $t)	//both these shouldnt need to be done, cascade in db
		{
			//clear team orders
			$sql[] = "DELETE FROM Team_orders WHERE team_id='{$t['id']}'";
			
			//clear team owned countries
			$sql[] = "DELETE FROM Team_has_country WHERE team_id='{$t['id']}'";
		}*/
		
		//clear teams
		$sql[] = "DELETE FROM Game_teams WHERE game_id='{$this->id}'";
		//clear from private games list
		$sql[] = "DELETE FROM Private_games WHERE game_id='{$this->id}'";
		
		#Clean up all tables relating to players
		/* Again shouldnt need this as db cacades
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
		*/
		$sql[] = "DELETE FROM Players WHERE game_id='{$this->id}' LIMIT 1";
		
		//finally set game as finished
		$sql[] = "UPDATE Game_in_play SET finish_timestamp = '".time()."' ".
			"WHERE id='{$this->id}' LIMIT 1";
		
		
		//execute queries
		foreach($sql AS $query)
		{
			$this->firephp->log($query,"SQL");
			$this->db->query($query);
			//error
			if($this->db->errno != 0)
				$this->firephp->error($query, "SQL failed");
		}
		$this->firephp->log("End of end game function");
		$this->firephp->groupEnd();
	}
	
	/**
	 * Finish current game and start a new on at $time
	 *
	 * @param int $time Time (in sec) for game to start
	 * @return Game Game object for new game
	 */
	function restart($time = 0)
	{ die('Needs reimplementing.. blahhhhhh');
		//save some tables
		/*
		$reshs = $this->db->fetch_all_array("SELECT * FROM Default_researches WHERE game_id='{$this->id}'");
		$units = $this->db->fetch_all_array("SELECT * FROM Default_units WHERE game_id='{$this->id}'");
		$facs = $this->db->fetch_all_array("SELECT * FROM Default_facilities WHERE game_id='{$this->id}'");
		$resrs = $this->db->fetch_all_array("SELECT * FROM Default_resources WHERE game_id='{$this->id}'");
		*/
		
		//end current game
		$this->end();
		
		
		//add new game
		$array = array(
			'name' => $this->name,
			'setting_id' => $this->game->settings->id,
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
	
	/**
	 * Create a new game instance
	 */
	static function add(array $insertArray)
	{
		global $db, $firephp;
		$firephp->group("Adding Game Instance");
		
		if(array_key_exists('game_id', $insertArray) && Validate::isInt($insertArray['game_id']))
		{
			//start time is optional, if not passed then use time()
			if(array_key_exists('start', $insertArray) && Validate::isInt($insertArray['start']))
				$start = $insertArray['start'];
			else
				$start = time();
				
			//Check that start time starts on the 0th sec of the min
			$start -= $start%60;
				
			$firephp->log("Attempting to add game instance from Game #".$insertArray['game_id']);	
				
			//Add game instance into db
			$id = $db->query_insert('Game_in_play', array('game_id'=>$insertArray['game_id'],
				'start_timestamp' => $start, 'finish_timestamp' =>0));
			
			$firephp->log("Instance #$id added");
			
			
			$tmp = self::getInstance($id);
			$firephp->log($tmp,"Instance is:");
			//setup country militas
			CountryCollection::setUpNewGameCountries($tmp);
			
			//set up game teams
			TeamInstance::makeGameTeams($tmp);
			
			//deal with private game stuff
			$firephp->warn("Private game info not checked");
			
			
			$firephp->info($insertArray, "Success! Added game instance #$id");
			$firephp->groupEnd();
			return $id;
		}
		else
		{
			$firephp->error($insertArray, "Invalid parameters : Expecting game_id(int)");
			$firephp->groupEnd();
			return -1;
		}
	}
	
	// Prevent users to clone the instance
    public function __clone()
    {
        trigger_error('Clone is not allowed.', E_USER_ERROR);
    }
	
	public function __wakeup()
	{
		global $db;
		global $firephp;
		
		$this->db = &$db;
		$this->firephp = &$firephp;
	}
}
?>