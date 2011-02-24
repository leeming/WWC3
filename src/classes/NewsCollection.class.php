<?php

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