<?php
/**
 * Class for handling collection of games. Usefull
 * when wanting a full list of games ('core' games
 * or instances). Mainly used in browsing for
 * games to join
 */

class GameCollection extends Base
{
	static function getAllGames()
	{
		global $db;
		$sql = "SELECT id FROM Games";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $r)
			$return[$r['id']] = new Game($r['id']);
			
		return $return;
	}
	static function getAllGameInstances()
	{
		global $db;
		$sql = "SELECT id FROM Game_in_play ORDER BY start_timestamp";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $r)
			$return[$r['id']] = GameInstance::getInstance($r['id']);
			
		return $return;
	}
	
	static function getRunningGameInstances()
	{
		global $db;
		$sql = "SELECT id FROM Game_in_play WHERE finish_timestamp = 0 ORDER BY start_timestamp";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $r)
			$return[$r['id']] = GameInstance::getInstance($r['id']);
			
		return $return;
	}
	
	/**
	 * Get a collection of game instances
	 * which are all spawned from $game
	 *
	 * @param int/Game Core Game id
	 * @return Game[]
	 */
	static function getInstancesOf($game)
	{
		//if $game is of type Game then get id
		if(get_class($game) == "Game")
			$game = $game->id;
		
		//should be int here	
		if(!Validate::isInt($game))
		{
			trigger_error("Int expected",E_USER_ERROR);
			return;
		}
		
		global $db;
		
		$sql = "SELECT id FROM Game_in_play WHERE game_id ='{$game}' ".
			"ORDER BY start_timestamp";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $r)
			$return[$r['id']] = GameInstance::getInstance($r['id']);
			
		return $return;
	}
	static function getRunningInstancesOf($game)
	{
		$insts = self::getInstancesOf($game);
		//delete finished games
		foreach($insts AS $id => $obj)
		{
			if($obj->finished != 0)
			{
				unset($insts[$id]);
			}
		}
		
		return $insts;
	}
}
?>