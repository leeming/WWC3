<?php
/**
 * List of classes used by this class
 */
/*  now in include_path
require_once("MapArea.class.php");
require_once("Country.class.php");
*/

/**
 * Holds all information about a region inc bonuses and included countries
 * Regions are basically continents and give extra bonuses.
 */
class Region extends MapArea
{
	public $id;
	
	/**
	 * Constructor for Region class
	 *
	 * @param int $id Region Id
	 * @param int $game Game id
	 */
	function __construct($id, $game)
	{
		parent::__construct();
		
		//check that id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		//check that game is int
		if(!Validate::isInt($game))
		{
			trigger_error("(int)\$game expected, (".gettype($game).") passed", E_USER_ERROR);
			return;
		}
		
		
		$this->id = $id;
		$this->gameId = $game;
		
		//get region details
		$this->db->query("SELECT name FROM Regions WHERE id ='{$id}' LIMIT 1");
		$result = $this->db->getRow();
		
		
		$this->name = $result['name'];
	}

	/**
	 * Gets the id of the region
	 *
	 * @return	int
	 */
	function getId()
	{	return $this->id;	}

	/**
	 * Gets the name of the region
	 *
	 * @return	String
	 */
	function getName()
	{	return $this->name;	}
	
	private $bonus = NULL;
	/**
	 * Gets an array of resources for the country
	 *
	 * @todo Need to check if resource is in setting, if so dont add
	 * @return Resource[]
	 */
	function getBonus()
	{
		if($this->bonus == NULL)
		{
			$result = $this->db->fetch_all_array("SELECT resource_id, resource_qty FROM Region_has_resource WHERE region_id = '{$this->id}'");
			
			$this->bonus = array();
			foreach($result AS $resource)
			{
				$this->bonus[] = new Resource($resource['resource_id'], $resource['resource_qty']);
			}
		}
		
		return $this->bonus;
	}
	
	/**
	 * Add a resource that the region gives as a bonus
	 *
	 * @param Resource $toAdd Resource to add as bonus
	 */
	function addBonus(Resource $toAdd)
	{
		//check to see if this already a bonus
		foreach($this->getBonus('object', true) AS $bonus)
		{
			if($bonus->equals($toAdd))
				return;
		}
		
		//not a bonus, so add
		$insertArray = array("region_id" => $this->id,
							 "resource_id" => $toAdd->id,
							 "resource_qty" => $toAdd->qty);
		//print_r($insertArray);
		$this->db->query_insert("Region_has_resource",$insertArray);
	}
	
	
	private $containsId = NULL;
	private $containsCountry = NULL;
	
	/**
	 * Gets list of countries that are in a region
	 *
	 * @todo need to consult Setting_has_resion table
	 * 
	 * @param string $returnType
	 * @return int[]/Country[]
	 */
	function contains($returnType = "int", $recache = false)
	{
		$sql = "SELECT country_id FROM Country_in_region WHERE region_id = '{$this->id}'";
		
		if($returnType == "int")
		{
			if($recache || $this->containsId == NULL)
			{
				$this->containsId = array();
				
				$result = $this->db->fetch_all_array($sql);
				foreach($result AS $countryId)
				{
					$this->containsId[] = $countryId['country_id'];
				}
			}
			
			return $this->containsId;
		}
		else
		{
			if($recache || $this->containsCountry == NULL)
			{
				$this->containsCountry = array();
				
				$result = $this->db->fetch_all_array($sql);
				foreach($result AS $countryId)
				{
					$this->containsCountry[] = new Country($countryId['country_id'], $this->gameId);
				}
			}
			
			return $this->containsCountry;
		}
	}
	
	/**
	 * Add a country to be contained within a region
	 *
	 * @param int/Country $toAdd County to add
	 */
	function addCountry($toAdd)
	{
		if(Validate::isInt($toAdd))
		{
			//check to see if this already a bonus
			foreach($this->contains('int', true) AS $country)
			{
				if($country == $toAdd)
					return;
			}
			
			$id = $toAdd;
		}
		else
		{
			//check to see if this already a bonus
			foreach($this->contains('object', true) AS $country)
			{
				if($country->equals($toAdd))
					return;
			}
			
			$id = $toAdd->id;
		}
		
		//not a contained country, so add
		$insertArray = array("region_id" => $this->id,
							 "country_id" => $id);
		
		$this->db->query_insert("Country_in_region",$insertArray);
	}

	
	/**
	 * Makes a new region, returns: -1 on error, 0 on failed mysql,
	 * else id of new region
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Region ID
	 */
	static function add(array &$insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Regions", $insertArray);
		}

		return -1;
	}	
	
	
	function equals(Region $obj)
	{
		return ($this->id == $obj->id);
	}
	
	function __toString()
	{
		return "(Region){$this->getName()}({$this->id})";
	}
}


?>
