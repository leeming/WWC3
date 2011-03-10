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
 * Class to access profile data of users
 *
 * TODO Hardly started at all
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Profile
{
	/**
	 * @param	(int)$id	- Id of the user to load profile
	 */
	function __construct($id)
	{
		global $db;
		$this->db = &$db;

		//Validate $id is int
		if(!Validate::isInt($id, true))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}

		//does profile exist?
		$db->query("SELECT SQL_CACHE * FROM `Profiles` WHERE `user_id` ='".$id."' LIMIT 1");

		if($db->numRows == 0)
		{
			trigger_error("There is no profile with this id (".$id.")", E_USER_WARNING);
			return;
		}

		$result = $this->db->getRow();

		$this->aim = $result['aim'];
		$this->msn = $result['msn'];
		$this->yahoo = $result['yahoo'];
		$this->sykpe = $result['sykpe'];
		$this->name = $result['name'];
		$this->dob = $result['dob'];
		$this->location = $result['location'];
		$this->info = $result['profile'];
		$this->sig = $result['signature'];
		$this->privateEmail = $result['private_email'];
		$this->avatar = $result['avatar'];
		$this->website = $result['website'];
	}
}