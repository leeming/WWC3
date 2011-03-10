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
 * Collection of news articles, useful for paging & catergorising news
 * 
 * TODO Not really done much with this, ideal for new developer to pick up
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class NewsCollection extends Base
{
	public $page;
	public $perPage;

	function __construct()
	{
		parent::__construct();
		
		$this->page = 1;
		$this->perPage = 5;
	}
	
	function fetch($page = NULL)
	{
		if($page == NULL)
			$page = $this->page;
		elseif(!Validate::isInt($page))
		{
			trigger_error("(int)\$page expected, (".gettype($page).") passed", E_USER_ERROR);
			return;
		}
		
		$sql = "SELECT id FROM News ORDER BY post_timestamp DESC LIMIT ".
			"".(($this->page-1)*$this->perPage)." , {$this->perPage}";
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $id)
		{
			$return[] = new News($id['id']);
		}
		
		return $return;
	}
	
	
	
	
	/**
	 * Get all news articles
	 *
	 * @param bool $newFirst True[default]:Newst first, False:Oldest
	 * @return News[]
	 */
	static function all($newFirst = true)
	{
		global $db;
		$sql = "SELECT id FROM News ORDER BY post_timestamp ".($newFirst?"DESC":"ASC");
		$result = $db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $id)
		{
			$return[] = new News($id['id']);
		}
		
		return $return;
	}
}
?>