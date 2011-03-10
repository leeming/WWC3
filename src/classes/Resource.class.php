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
 * Class to handle seperate resources
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Resource extends Base
{
	public $id;
	public $name = NULL;
	public $qty = 0;
	
	/**
	 * Constructor for resource which loads infor from db
	 * 
	 * @param int $id Id of the resouce
	 */
	function __construct($id, $qty = 0)
	{
		parent::__construct();
		
		//check that id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		////check that qty is int
		if(!Validate::isInt($qty))
		{
			trigger_error("(int)\$qty expected, (".gettype($qty).") passed", E_USER_ERROR);
			return;
		}
		
		
		$this->db->query("SELECT name FROM Resources WHERE id ='{$id}' LIMIT 1");
		
		if($this->db->numRows == 0)
		{
			trigger_error("There is no resource with this id (".$id.")", E_USER_WARNING);
			return;
		}
		
		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->qty = $qty;
		$this->name = $result['name'];
	}
	
	/**
	 * Gets the small icon to display next to the resource
	 *
	 * @return String 
	 */
	function getIcon()
	{
		return "[".$this->name."]";
	}
	
	/**
	 * Gets the default resource qty for current game set (from Base class)
	 *
	 * @deprecated This should be moved to game instance
	 * @return int
	 */
	/*
	function getDefault()
	{
		$db->query("SELECT resource_qty FROM Default_resources WHERE resource_id ='{$this->id}' AND game_id ='{$this->getGameId()}' LIMIT 1");
		
		//no results so return 0 resources
		if($this->db->numRows == 0)
		{
			return 0;
		}
		//return result
		else
		{
			$result = $this->db->getRow();
			return $result['resource_qty'];
		}
	}
	



	/**
	 * Makes a new Resource, returns: -1 on error, 0 on failed mysql,
	 * else id of new resource
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Resource ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Resources", $insertArray);
		}

		return -1;
	}

	function equals(Resource &$cmp)
	{
		return ($this->id == $cmp->id);
	}

	function __toString()
	{
		return $this->getIcon()." ".$this->qty;
	}
}
?>