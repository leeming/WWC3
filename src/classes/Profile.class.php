<?php

/**
 * Class to access profile data of users
 *
 * @author leeming
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