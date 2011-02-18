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
 * Description of class
 * 
 * List of classes used by this class
 * 
 * 
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */

class FacilityCollection extends Base
{
	function __construct()
	{
		parent::__construct();
	}
	
	public $id = NULL, $player = NULL, $country = NULL; 
	
	/**
	 * Gets a collection of facilities as an array, depending
	 * on what values are set to $id/$player/$country
	 *
	 * @return Facility[]
	 */
	function get()
	{
		$sql = "SELECT * FROM Player_has_facility WHERE ";
		
		$where = " ";
		if(Validate::isInt($this->id))
		{
			$where .= "facility_id = {$this->id} AND ";
		}
		if(Validate::isInt($this->player))
		{
			$where .= "player_id = {$this->player} AND ";
		}
		if(Validate::isInt($this->country))
		{
			$where .= "country_id = {$this->country} AND ";
		}
		
		$sql .= $where ."true";
		
		$return = array();
		$result = $this->db->fetch_all_array($sql);
		foreach($result AS $f)
		{
			$return[$f['country_id']] = new Facility($f['facility_id'], $f['facility_qty']);
		}
		
		return $return;
	}
	
	/**
	 *
	 * @param Player/int $player Player to look up facilities
	 * @param Facility/int $facility If set, look up facilities of only this type
	 * @return Facility[]
	 */
	static function getAllPlayerFacilities($player, $facility = NULL)
	{
		global $db;
		
		if(($playerid = Player::id($player)) == -1)
		{
			return -1;
		}
		
		if($facility != NULL && ($facilityid = Facility::id($facility)) != -1)
		{
			$facilityWhere = " AND facility_id = '{$facilityid}'";
		}
		else
		{
			$facilityWhere = "";
		}
		
		$sql = "SELECT country_id, facility_id, facility_qty FROM ".
			"Player_has_facility WHERE player_id='{$playerid}'".$facilityWhere;
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $f)
		{
			$return[$f['country_id']] = new Facility($f['facility_id'], $f['facility_qty']);
		}
		
		return $return;
	}
	
	static function countPlayerFacilities(Player $player, $facility = NULL)
	{
		global $db;
		
		if(($playerid = Player::id($player)) == -1)
		{
			return -1;
		}
		
		//count only for this facility
		if($facility != NULL && ($facilityid = Facility::id($facility)) != -1)
		{
			$sql = "SELECT SUM(facility_qty) as total_qty, facility_id FROM Player_has_facility ".
				"WHERE player_id='{$playerid}' AND facility_id='{$facilityid}' ".
				"GROUP BY player_id LIMIT 1";
		}
		//count for all?
		else
		{
			return 0;
			$sql = "SELECT SUM(facility_qty) as total_qty, facility_id FROM Player_has_facility ".
				"WHERE player_id='{$playerid}' GROUP BY player_id, facility_id LIMIT 1";
		}
		
		global $firephp;
		
		//$sql = "SELECT country_id, facility_id, facility_qty FROM Player_has_facility WHERE player_id='{$playerid}'".$facilityWhere;
		$db->query($sql);
		
		//check result, if no rows returned then player has 0, else the returned value
		if($db->numRows == 1)
		{
			$result = $db->getRow();
			$qty = $result['total_qty'];
		}
		else
			$qty = 0;
		
		$firephp->log($qty, "Facility Qty");
		
		return $qty;
	}
	
	/**
	 * Get a list of all facilities in a game
	 *
	 * @param GameInstance $game
	 * @return Facility[]
	 */
	static function getGameSet(GameInstance $game)
	{
		global $db;
		$sql = "SELECT facility_id FROM Setting_has_facility WHERE ".
			"setting_id ='{$game->getSettingsId()}'";
			
		$result = $db->fetch_all_array($sql);
		
		
		$return = array();
		foreach($result AS $f)
		{
			$return[] = new Facility($f['facility_id']);
		}
		
		return $return;
	}
}
?>