<?php
/**
 * This is a base class which is used for both countries and regions (continents)
 *
 * @see Country
 * @see Region
 */
abstract class MapArea extends Base
{
	public $name = NULL;
	private $bonus = array();
	private $requirements = array();

	function  __construct()
	{
		parent::__construct();
	}

	/**
	 * Gets the name of the region/country
	 *
	 * @return String name Name of area
	 */
	function getName()
	{	return $this->name;	}

	/**
	 * Gets an array of resources that the area gives eg countries give gdp/supplies etc per turn, but regions give greater bonuses
	 *
	 * @return Resource[]
	 */
	function getBonus()
	{	return $this->bonus;	}

	/**
	 * Gets an array of requirements for the bonus, note this will be empty for countries
	 *
	 * @return Country[]
	 */
	function getBonusRequirements()
	{	return $this->requirements;	}

}


?>
