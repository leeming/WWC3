<?php
/**
 * Class containing information about a team which belongs
 * to the game/setting.
 *
 * NOTE :TeamInstance is the class which
 * holds the information about the current team in a game
 *
 * @todo Implement capitals in a DB table to allow multiple countries
 * 		as capitals. At start of game random capitals can then be picked
 */
class Team extends Base
{
	/**
	 * Team constructor
	 *
	 * @param int $id Id of team
	 */
	function __construct($id)
	{
		parent::__construct();
		
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT * FROM Teams WHERE id ='{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		$this->id = $id;		
		$this->name = $result['name'];
		$this->colour = $result['colour'];
		$this->capital = $result['capital'];
	}
	
	/**
	 * Gets an array of countries owned by team
	 *
	 * @deprecated Moved to TeamInstance
	 * @param string $returnType
	 * @return int[]/Country[]
	 */
	/*
	function countriesOwned($returnType = "int")
	{
		$sql = "SELECT country_id FROM Team_has_country WHERE team_id='{$this->id}'";
		$result = $this->db->fetch_all_array($sql);
		
		$return = array();
		
		foreach($result AS $res)
		{
			$return[] = ($returnType=="int")?$res['country_id']:
							new Country($res['country_id']);
		}
		
		return $return;
	}
	*/
	
	/**
	 * Returns a set of countries which are allowed to
	 * be team capitals
	 *
	 * @todo Implement
	 */
	function capitalSet()
	{}
	
	/**
	 * Makes a new Team, returns: -1 on error, 0 on failed mysql,
	 * else id of new team
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Team ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;
			
			return $db->query_insert("Teams", $insertArray);
		}
		
		return -1;
	}
}
?>