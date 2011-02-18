<?php
/**
 * Class to control a player's research queue in a game
 * 
 * @todo Premium players can have larger queue
 * @todo "Add research "with pre-requisites included
 * @todo Remove research
 * @author	Leeming
 */
class ResearchQueue extends Base
{
	public $maxQueue = 5;
	public $queue;
	
	/**
	 * Constructor for the ResearchQueue Class
	 */
	function __construct(Player $player)
	{
		parent::__construct();
		

		$this->playerId = $player->id;
		$this->gameId = $player->game->id;
		$this->player = &$player;
		
		$this->populateQueue();
	}

	function populateQueue()
	{
		$sql = "SELECT * FROM Player_research_queue WHERE player_id='{$this->player->id}'".
			"AND active = '1' ORDER BY pos ASC";
		$result = $this->db->fetch_all_array($sql);
		
		$this->queue = array();
		foreach($result AS $r)
		{
			$this->queue[] = array('pos' => $r['pos'],
								   'research' =>$r['research_id'],
								   'ticksDone'=>$r['ticks_done']);
		}
		
		//check to make sure queue has not overflowed
		if(count($this->queue) > $this->maxQueue)
		{ print count($this->queue)." > ".$this->maxQueue;
			trigger_error("Error: Player queue is bigger than max", E_USER_WARNING);
			return;
		}	
	}
	
	function in_array($search)
	{
		foreach($this->queue AS $chk)
		{
			if($chk['research'] == $search)
				return true;
		}
		
		return false;
	}
	
	/**
	 * Add a research into a research queue
	 *
	 * @param int/Research $research
	 * @return bool True if added
	 */
	function add($research)
	{
		//check if $research is valid
		if(!Validate::isInt($research) && is_object($research) && get_class($research) == "research")
		{
			$this->firephp->log('invalid param');
			return false;
		}
		
		if(is_object($research) && get_class($research) == "research")
			$research=$research->id;
		
		
		//check if current queue is maxed
		if(count($this->queue) >= $this->maxQueue)
		{
			$this->firephp->log('max queue');
			return false;
		}
		
		
		//check if already in queue
		foreach($this->queue AS $chk)
		{
			//player already has this research in queue
			if($chk['research'] == $research)
			{	
				$this->firephp->log($chk, 'already in queue');
				return false;
			}
		}
		
		

		//check pre requisites
		$toAdd = new Research($research);
		$preReq = $toAdd->getPreReq();
		$playerResearches = $this->player->getResearches();
		
		foreach($preReq AS $a)
		{ 
			//if $a is not in player researches, then dont do research
			if(!in_array($a, $playerResearches) && !$this->in_array($a))
			{
				
				$this->firephp->log('Dont have prereq');
				return false;
			}
		}
		
		
		
		#if here all should be ok, player has all pre req
		$sql = "SELECT * FROM Player_research_queue WHERE player_id='{$this->playerId}'"
			." AND research_id='{$research}'";
		$this->db->query($sql);
		
		//insert new
		if($this->db->numRows == 0)
		{
			$sql = "INSERT INTO Player_research_queue (`player_id`, `research_id`,
				`ticks_done`, `pos`) VALUES ({$this->playerId}, {$research}, '0',
				'".count($this->queue)."')";
			$this->db->query($sql);
			
			if($this->db->affected_rows != 1)
			{
				$this->firephp->log($sql, 'Insert failed');
				return false;
			}
		}
		//update
		else
		{
			$sql = "UPDATE Player_research_queue SET pos='{count($this->queue)}'"
				." ,active='1' WHERE `player_id`='{$this->playerId}' AND "
				."research_id='{$research}' LIMIT 1";
			$this->db->query($sql);
			
			if($this->db->affected_rows != 1)
			{
				$this->firephp->log($sql, 'Update failed');
				return false;
			}
		}
		

		//Get ticks done if continued
		$sql = "SELECT ticks_done FROM Player_research_queue WHERE ".
			"player_id='{$this->playerId}' AND research_id='{$research}'".
			" LIMIT 1";
		$this->db->query($sql);
		$result = $this->db->getRow();
		
		//add onto current current queue
		$this->queue[] = array('pos' => count($this->queue),	
							   'research' =>$research,
							   'ticksDone'=>$result['ticks_done']);
		
		/*
		 $insertArray = array('player_id' => $this->playerId,
							 'research_id' => $research,
							 'ticks_done' => '0',
							 'pos' => count($this->queue));
		$this->db->query_insert("Player_research_queue", $insertArray);
		*/
		return true;
	}
	
	/**
	 * Accessor for getting the research queue
	 *
	 * @param int $index Get single entry, or null for all
	 * @return array
	 */
	function get($index = NULL)
	{
		//Get n'th research from queue
		if(Validate::isInt($index) && $index < count($this->queue))
			return $this->queue[$index];
		//array out of bounds
		elseif(Validate::isInt($index))
		{
			$this->firephp->log($index,"ResearchQueue Out of bounds get");
			return null;
		}
		else
			return $this->queue;
	}
	
	/**
	 * Gets the current size of queue
	 * @return int
	 */
	function getSize()
	{ return count($this->queue); }
	
	/**
	 * Remove research from queue and any dependancies
	 *
	 * @param int $research Research to remove
	 */
	function remove($research)
	{
		//Check to see if research is in queue
		if(Validate::isInt($research) && $this->in_array($research))
		{
			$r = new Research($research);
			
			//get dependancies
			$dep = $r->getAllDependencies();
			//check each dependancies if in queue
			foreach($dep AS $d)
			{
				//remove all dependancies
				if($this->in_array($d))
				{
					$this->remove($d);
				}
			}
			
			//deactivate research
			$sql = "UPDATE Player_research_queue SET active='0' WHERE ".
				"player_id='{$this->player->id}' AND research_id='{$research}' LIMIT 1";
			$this->db->query($sql);
			//move pos down
			$sql = "UPDATE Player_research_queue SET pos=`pos`-1 WHERE ".
				"pos > '{$this->getPos($research)}'";
			$this->db->query($sql);
			
			//not efficent but much easier, just recalculate queue
			$this->populateQueue();
		}
	}
	
	/**
	 * Find the position of research in the queue
	 *
	 * @param int $research
	 * @return int
	 */
	function getPos($research)
	{
		foreach($this->queue AS $chk)
		{
			if($chk['research'] == $research)
				return $chk['pos'];
		}
	}
	
	/**
	 * Find out if a player can add more researches to their
	 * research queue. This could mean player has max queue
	 * or they do not own any research labs
	 *
	 * @return bool
	 */
	function canResearch()
	{
		if(count($this->queue) >= $this->maxQueue)
			return false;
		elseif(false /* Research labs */)
			return false;
		else
			return true;
	}
}
?>
