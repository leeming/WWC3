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
 * Class which determines all battles between player and milita
 * This class is VERY experimental and open for modification at 
 * any time. Feel free to give suggestions on how this class
 * will work.
 * 
 * TODO Have a serious think/recode of this class
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Attack extends Base
{	
	/**
	 * Constructor for Attack class
	 *
	 * @param Battalion $bat Battalion to attack with
	 * @param CountryInstance $loc Country to attack
	 */
	function __construct(Battalion $bat, CountryInstance $loc)
	{
		parent::__construct();
		
		$this->bat = &$bat;
		$this->loc = &$loc;
		$this->player = &$bat->player;
		
		$this->rounds = 10;
	}
	
	/**
	 * Finds out if battalion is able to attack location.
	 * Reasons not been able to attack may include
	 *  - Not at border
	 *  - 0 units in battalion
	 *  - no turns to attack with
	 *
	 * @return bool
	 */
	function canAttack()
	{
		//check to see if player has any turns
		$resources = $this->player->getResources(true);
		if($resources[RESOURCE_TURNS]->qty < 1)
		{
			$this->firephp->log("Attack failed: no turns");
			return false;
		}
		
		//check to see if battalion has any units
		if($this->bat->isEmpty())
		{
			$this->firephp->log("Attack failed: no units");
			return false;
		}
		
		//is battalion location border and attackable
		$attackable = $this->bat->location->getAttackBorders($this->player->team);
		if(!in_array($this->loc->id, $attackable))
		{
			$this->firephp->log($attackable,"Attack failed: non attackable border");
			return false;
		}
		
		//if here then can attack
		return true;
	}
	
	/**
	 * Find out if there are any battalions currently
	 * in this country. If so then an array of battalions
	 * is returned, else empty array
	 *
	 * @return Battalion[]
	 */
	function getEnemies()
	{
		return $this->loc->armiesPresent($this->player->game);
	}
	
	/**
	 * Lazy method to count the getEnemies array to
	 * check if any enemies are in the country or if
	 * it is just the milita
	 *
	 * @return bool
	 */
	function hasEnemies()
	{
		//apparently empty() can not have a function as a param
		return count($this->getEnemies()) > 0;
	}
	
	/**
	 * Calculates how many resources are needed to
	 * do a attack
	 *
	 * @return Resource[]
	 */
	function resourcesNeeded()
	{
		$resources = array();
		
		//Needs a turn
		$resources[RESOURCE_TURNS] = new Resource(RESOURCE_TURNS, 1);
		
		$resources = array_merge($resources,$this->bat->resourcesNeededToAttack());
		
		return $resources;
	}
	
	
	/**
	 * Gets the strength of the Battalion or Country
	 * passed. If a battalion is passed then unit str
	 * are all summed together and used with the battalion
	 * level modifier. Else if a country is passed then
	 * the milita str is calculated from the milita size
	 * and resistance
	 *
	 * @param Battalion/Country $strOf
	 * @return int
	 */
	private function getStr($strOf)
	{
		if(get_class($strOf) == "Battalion")
			return $strOf->totalStr();
		else if(get_class($strOf) == "CountryInstance")
			return $strOf->getMilita();
		//else not Battalion/Country
		else
		{
			trigger_error("Battalion/Country expected but ".get_class($strOf)." passed", E_USER_ERROR);
			exit();
		}
	}
	
	/**
	 * Set only inside doAttack method
	 * Some methods only give valid output
	 * if the battle has taken place (such as unitsLost())
	 *
	 * @var bool $attDone
	 */
	private $attDone;
	
	/**
	 * The main method which does all the battle calculations
	 * All post battle vars are set inside here
	 *
	 * If there is currently enemies in this country then
	 * the Battalion object must be passed, if no battalion
	 * is passed then (null) attacking milita is assumed.
	 *
	 * @param Battalion $attBat Battalion to attack
	 * @return bool If you win
	 */
	function doAttack(Battalion $attBat)
	{
		if(!$this->canAttack())
			return false;
		
		//if $attBat is null then attacking country milita
		if($attBat == NULL)
		{
			//check that nobody is here
			if(count($this->loc->armiesPresent()) == 0)
				//$this->doMilitaAttack();
				$attMilita = true;
			//tried to attack milita but armies present
			else
			{
				$this->firephp->warn("Failed to attack milita. Armies present");
				return false;
			}
		}
		else
		{
			$attMilita = false;
		}
		
		
		
		if($attMilita)
		{
			$this->firephp->group($this->loc, "Doing Attack on milita");
			
			$this->calcPowerDiff($this->bat, $this->loc);
			$this->firephp->log($this->getStr($this->bat), "Power p1");
			$this->firephp->log($this->getStr($this->loc), "Power p2 (milita)");
			$this->firephp->log($this->diff, "Power difference");
		}
		else
		{
			$this->firephp->group($attBat, "Doing Attack on Battalion");
			
			$this->calcPowerDiff($this->bat, $attBat);
			$this->firephp->log($this->getStr($this->bat), "Power p1");
			$this->firephp->log($this->getStr($attBat), "Power p2");
			$this->firephp->log($this->diff, "Power difference");			
		}
		
		
		if($this->diff > 0)
		{
			$this->battleMessage = "You win!";
			
			if($attMilita)
			{
				//update country owner
				$this->loc->setOwner($this->player->getTeamId());
				
				//move battalion to new country
				$this->bat->move($this->loc);
			}
		}
		else
		{
			$this->battleMessage = "You lost!";
		}
		
		$this->attDone = true;
		
		$this->firephp->groupEnd();
	}
	/**
	 * Same as doAttack but for milita
	 *
	 * @see doAttack
	 */
	function doMilitaAttack()
	{
		$this->firephp->group("Doing Attack on milita");
		
		$this->calcPowerDiff($this->bat, $this->loc);
		$this->firephp->log($this->getStr($this->bat), "Power p1");
		$this->firephp->log($this->getStr($this->loc), "Power p2 (milita)");
		$this->firephp->log($this->diff, "Power difference");
		
		if($this->diff > 0)
		{
			$this->battleMessage = "You win!";
			
			//update country owner
			$this->loc->setOwner($this->player->getTeamId());
			
			//move battalion to new country
			$this->bat->move($this->loc);
		}
		else
		{
			$this->battleMessage = "You lost!";
		}
		
		$this->attDone = true;
		
		$this->firephp->groupEnd();
	}
	
	/**
	 * Get power ratings from both armies and
	 * calculate winner. If result is -ve then p2
	 * is stronger, likewise +ve p1 is stronger.
	 *
	 * @param Battalion/Country $p1
	 * @param Battalion/Country $p2
	 * @return void
	 */
	private $diff;
	private $winfactor;
	private function calcPowerDiff($p1, $p2)
	{
		//get power from both armies
		$pow1 = $this->getStr($p1);
		$pow2 = $this->getStr($p2);
		
		//calc slight modifiers
		$pow1 *= 1 +(rand(0,1000)/100000);
		$pow2 *= 1 +(rand(0,1000)/100000);
		
		$this->diff = $pow1 - $pow2;
		
		#set the win factor
		//Won by lots - p1
		if($this->diff > 0 && $this->diff > $pow2)
		{	$this->winfactor = 0.9;	}
		//Won by lots - p2
		else if($this->diff < 0 && abs($this->diff) > $pow1)
		{	$this->winfactor = 0.1;	}
		//Won by a bit - p1
		else if($this->diff > 0 && $this->diff > $pow2/2)
		{	$this->winfactor = 0.65;	}
		//Won by a bit - p2
		else if($this->diff < 0 && abs($this->diff) > $pow1/2)
		{	$this->winfactor = 0.35;	}
		//close fight
		else
		{	$this->winfactor = 0.5;	}
	}
	
	
	####################################
	####################################
	###  All methods below can only  ###
	###  be called AFTER doAttack()  ###
	####################################
	####################################
	
	/**
	 * Gets the default winning/lost message after
	 * a battle. This includes the number of resources
	 * used in the attack and the number of units lost
	 * WARNING: This can not be called before a battle!
	 *
	 *
	 * @todo Unsure if enemy unit count should be shown to user?
	 *
	 * @return String
	 */
	function battleMsg()
	{
		/**
		 * Should not be able to call this method
		 * before doAttack()
		 */
		if(!$this->attDone)
		{
			$this->firephp->log("Attack->battleMsg() failed: Attack not done!");
			trigger_error("Battle methods called in wrong order",E_USER_ERROR);
			return;
		}
		
		return $this->battleMessage;
	}
	
	/**
	 * Gets an array of the user's resources used in the battle
	 * WARNING: This can not be called before a battle!
	 *
	 * @return Resource[]
	 */
	function resorcesUsed()
	{
		/**
		 * Should not be able to call this method
		 * before doAttack()
		 */
		if(!$this->attDone)
		{
			$this->firephp->log("Attack->battleMsg() failed: Attack not done!");
			trigger_error("Battle methods called in wrong order",E_USER_ERROR);
			return;
		}
	}
	
	/**
	 * Get an array of units lost in battle
	 * WARNING: This can not be called before a battle!
	 *
	 * @return Unit[]
	 */
	function unitsLost()
	{
		/**
		 * Should not be able to call this method
		 * before doAttack()
		 */
		if(!$this->attDone)
		{
			$this->firephp->log("Attack->battleMsg() failed: Attack not done!");
			trigger_error("Battle methods called in wrong order",E_USER_ERROR);
			return;
		}
	}
}

?>