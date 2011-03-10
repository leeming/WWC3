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
 * Simple Object to represent a Unit's stat(s)
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Stat extends Base
{
	public $id, $qty;

	/**
	 * Constructor for the Stat Class
	 *
	 * @param int $id Id of the stat
	 * @param int $qty Number of stat points
	 */
	function __construct($id, $qty = 0)
	{
		parent::__construct();
		
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		//Validate $qty is int
		if(!Validate::isInt($qty))
		{
			trigger_error("(int)\$qty expected, (".gettype($qty).") passed", E_USER_ERROR);
			return;
		}

		$this->db->query("SELECT `name` FROM Unit_stats WHERE id = '".$id."' LIMIT 1");

		$result = $this->db->getRow();

		$this->id = $id;
		$this->qty = $qty;
		$this->name = $result['name'];
	}
	
	
	function equals(Stat $cmp)
	{
		return ($this->id == $cmp->id);
	}
	
	/**
	 * Makes a new facility, returns: -1 on error, 0 on failed mysql,
	 * else id of new facility
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Facility ID
	 */
	static function add(array &$insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Unit_stats", $insertArray);
		}

		return -1;
	}
}

?>