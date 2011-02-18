<?php
/**
 * Get a collection of researches such as research queues,
 * sub trees etc.
 *
 * @deprecated research queue moved to researchQueue class
 * 
 * @author	Leeming
 */
class ResearchCollection extends Base
{
	/**
	 * Constructor for the ResearchCollection Class
	 *
	 * @param GameInstance $game Reference object to which game to look at 
	 */
	function __construct(GameInstance $game)
	{
		parent::__construct();
		
		$this->game = &$game;
	}
	
	private $finalNode = NULL;
	
	/**
	 * Set finalNode value for when looking up a subtree
	 * of all researches needed before (and including) $base
	 *
	 * @param int $base Research id of research subtree
	 */
	function setBase($base)
	{
		if(Validate::isInt($base))
			$this->finalNode = $base;
	}
	
	/**
	 * Gets the full collection of researches included in
	 * the required tree.
	 *
	 * @return Research[]
	 */
	function get()
	{
		//check to see if getting full tree or sub
		
		if($this->finalNode != NULL) //subtree
		{
			$this->firephp->log($this->finalNode, "Getting research subtree");
			//Get list of dependancies
			$base = new Research($this->finalNode);
			
			//low indexes should now be basic researches, more adv at end
			$return = array_reverse($base->getAllDependencies('obj'));
			
			$return[] = $base;			
			return $return;
		}
		else	//full tree
		{
			$this->firephp->log("Get full research tree");
			
			$sql = "SELECT research_id FROM Setting_has_research WHERE".
				" setting_id='{$this->game->getSettingsId()}'";
			$result = $this->db->fetch_all_array($sql);
			
			$return = array();
			foreach($result AS $res)
			{
				$return[] = new Research($res['research_id']);
			}
			
			return $return;
		}
		
	}
	
	/**
	 * Wrapper method for getting a sub tree of a research
	 * same as setting the base node and then getting the
	 * collection
	 *
	 * @param int $base Research id of research subtree
	 */
	function getSubtree($base)
	{
		$this->setBase($base);
		return $this->get();
	}

	/**
	 * Find out what researches a player can add to their queue.
	 * Looks at what researches are already done and also currently
	 * in the player's queue
	 *
	 * @param Player $player Player ref to check what can be researched
	 * @return int[] Array of Research Ids 
	 */
	function getResearchable(Player $player)
	{
		//get researches done
		$done = $player->getResearches('int');
		
		$queue = array();
		$rqueue = new ResearchQueue($player);
		foreach($rqueue->queue AS $res)
		{
			$queue[] = $res['research'];
		}
		
		$playerHave = array_merge( $done,$queue);
		
		$next = array();
		
		//check all researches in full tree and check dependances
		$this->base = NULL;
		$getAll = $this->get();
		foreach($getAll AS $r)
		{
			//skip if already has
			if(in_array($r->id, $playerHave))
				continue;
			
			//flag to start each research
			$gotDeps = true;
			
			foreach($r->getPreReq('int') AS $deps)
			{
				if(!in_array($deps, $playerHave))
				{
					//player doens have dependency
					$gotDeps = false;
					break;
				}
			}
			
			//add to array if $gotDeps still set
			if($gotDeps)
				$next[] = $r->id;
		}
		
		return $next;
	}
}
?>
