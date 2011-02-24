<?php
/**
 * Holds information about type of units
 *
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