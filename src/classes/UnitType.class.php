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
 * Holds information about type of units
 *
 * TODO Needs reviewing and refactoring
 *
 * @extends Base
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
 class UnitType extends Base
{
	public $id, $name;
	
	function __construct($id)
	{
		parent::__construct();
		
		//check that id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->id = $id;
		$this->db->query("SELECT name FROM Unit_types WHERE id='{$this->id}' LIMIT 1");
		$result = $this->db->getRow();
		
		$this->name = $result['name'];
	}
	
	function equals(UnitType $cmp)
	{
		return ($cmp->id == $this->id);
	}
	
	/**
	 * Makes a new UnitType, returns: -1 on error, 0 on failed mysql,
	 * else id of new unit type
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int UnitType ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Unit_types", $insertArray);
		}

		return -1;
	}
}
?>