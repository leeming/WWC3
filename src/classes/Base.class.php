<?php
/**
 * Base class for many other classes which automatically sets
 * the database connection ($db) and the gameId, but only if
 * GAMEID const is set. Nevertheless game id can still be set
 * via setGame($gameId)
 *
 * @see setGame()
 */
abstract class Base
{
	public $db;
	public $firephp;
	
	function __construct()
	{
		global $db;
		global $firephp;
		
		$this->db = &$db;
		$this->firephp = &$firephp;
		$this->firephp->setObjectFilter(get_class($this), array('firephp','db'));
		//$this->firephp->log(get_class($this), "Obj filtered");
		$this->setGame();
	}
	
	public $gameId = NULL;
	
	/**
	 * Set the default game id to use
	 *
	 * @param int $gameId
	 */
	function setGame($gameId = NULL)
	{
		if($gameId == NULL && defined("GAMEID"))
		{
			$this->gameId = GAMEID;
		}
		
		if(Validate::isInt($gameId))
		{
			$this->gameId = $gameId;
		}
		else if($gameId != NULL)
		{
			trigger_error("(int)\$gameId expected, (".gettype($gameId).") passed", E_USER_ERROR);
			return;
		}
	}
	
	/**
	 * Get the game id currently in use
	 *
	 * @return int
	 */
	function getGameId()
	{
		//get default from GAMEID
		if(defined('GAMEID'))
		{
			$this->setGame(GAMEID);
		}
		
		//check if game id has been set
		if($this->gameId == NULL)
		{
			trigger_error("gameId needs to be set before it can be fetched!", E_USER_ERROR);
			return;
		}
		
		return $this->gameId;
	}
}

?>