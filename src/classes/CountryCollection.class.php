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
 * Collection of countries... stil used?
 * 
 * TODO Verify if this class is needed
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */

class CountryCollection extends Base
{
	function __construct()
	{
		parent::__construct();
	}
	
	static function getAllGameCountries(Game $game)
	{
		global $db, $firephp;		
		
		$sql = "SELECT country_id FROM Setting_has_country WHERE ".
			"setting_id='{$game->settings->id}'";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $r)
		{
			$return[] = $r['country_id'];
		}
		
		return $return;
	}
	
	static function setUpNewGameCountries(GameInstance $game)
	{
		global $db, $firephp;		

		$sql = "INSERT INTO Milita (`country_id`,`game_id`,`milita_qty`) ".
			"SELECT s.country_id, {$game->id} ,c.default_milita AS milita_qty ".
			"FROM Setting_has_country s LEFT JOIN Countries c ON ".
			"(s.country_id = c.id) WHERE s.setting_id='{$game->game->settings->id}'";
		$firephp->log($sql,"Add militas");
		$db->query($sql);
		
		unset($tmp);
	}
}
?>