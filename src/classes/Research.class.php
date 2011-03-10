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
 * This class lets you manage research for users, but also
 * allows you to look up research details on the fly, aswell
 * as constructing the research tree to full or restricted 
 * levels. eg. Show the research tree up to only Jets
 *
 * @todo removePreReq()
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class Research extends Base
{
	public $id, $name, $desc;
	
	/**
	 * Constructor for the Research Class
	 * Loads all data from database 
	 *
	 * @param 	int	$id	- Id of the research
	 */
	function __construct($id)
	{
		parent::__construct();
		
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT * FROM Research WHERE id = '".$id."' LIMIT 1");
		
		$result = $this->db->getRow();
		
		$this->id = $id;
		$this->name = $result['name'];
		$this->desc = $result['desc'];
		$this->cost = $result['cost'];
	}
	
	private $preReq = NULL;
	private $preReqIds = NULL;
	
	/**
	 * Gets the array of research which are needed prior to researching
	 * this current research
	 *
	 * @param string $returnType Type to return
	 * @param bool $recache
	 * @return int[]/Research[]
	 */
	function getPreReq($returnType = "int", $recache = false)
	{
		$sql = "SELECT required_research FROM Research_requires_research WHERE ".
			"research_id ='{$this->id}'";
		
		if($returnType == "int")
		{
			if($this->preReqIds == NULL || $recache)
			{
				$this->preReqIds = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $require)
				{
					$this->preReqIds[] = $require['required_research'];
				}
			}
			
			return $this->preReqIds;
		}
		else
		{
			if($this->preReq == NULL || $recache)
			{
				$this->preReq = array();
				
				$result = $this->db->fetch_all_array($sql);
				
				foreach($result AS $require)
				{
					$this->preReq[] = new Research($require['required_research']);
				}
			}
			
			return $this->preReq;
		}
	}
	
	/**
	 * Add a pre requisite for a research
	 * 
	 * @param int/Research $toAdd
	 * @return int[] New preReqs or -1 if not inserted
	 */
	function addPreReq($toAdd)
	{
		//figgure out id
		if(Validate::isInt($toAdd))
		{
			$newId = $toAdd;
		}
		else if(gettype($toAdd) == "Research")
		{
			$newId = $toAdd->id;
		}

		//check to see if currently in req list
		if(!in_array($newId, $this->getPreReq()))
		{
			//not in list so add
			$values = array('research_id' => $this->id,
							'required_research' => $newId);

			$this->db->query_insert("Research_requires_research",$values);

			return $this->getPreReq('int', true);
		}

		return -1;
	}

	function removePreReq($toRemove)
	{}
	
	/**
	 * Gets a collection of all recursive dependencies of this
	 * current research. Most direct dependencies for current
	 * research are located at front of array, and more 'basic'
	 * researches are ordered last in the array. Duplitates are
	 * handled.
	 * WARNING: Take care when creating researches to make sure
	 * that there is no loops of dependancies, eg A -> B and B->A
	 * as all symmetric dependancies will cause an infinite loop.
	 *
	 * @param string $returnType Type of return int or object
	 * @return int[]/Research[]
	 */
	function getAllDependencies($returnType = "int")
	{
		//Get direct dependencies for this research
		$result = $this->db->fetch_all_array("SELECT required_research FROM ".
			"Research_requires_research WHERE research_id = '{$this->id}'");
		$return = array();
		
		//repeat this process for each set of dependencies
		foreach($result AS $researchid)
		{
			$r = new Research($researchid['required_research']);
			
			if($returnType == "int")
				$return[$researchid['required_research']] = $researchid['required_research'];
			else
				$return[$researchid['required_research']] = $r;

			$return = $return + $r->getAllDependencies($returnType);
			
		}
		
		return $return;
	}


	/**
	 * Find out in percentage how much of the research is complete
	 *
	 * @param int $ticksDone Number of ticks already done
	 * @return float Percentage
	 */
	function getPercentDone($ticksDone)
	{
		if(Validate::isInt($ticksDone))
			return min(floor(($ticksDone/$this->cost)*100),100);
		else
			return 0;
	}
	
	/**
	 * Find out how many cycles are left for a research
	 *
	 * @param int $ticksDone Number of ticks already done
	 * @return int
	 */
	function getTicksLeft($ticksDone)
	{
		return $this->cost - $ticksDone;
	}


	/**
	 * Makes a new Research, returns: -1 on error, 0 on failed mysql,
	 * else id of new research
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Research ID
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('name', $insertArray) && !empty($insertArray['name']))
		{
			global $db;

			return $db->query_insert("Research", $insertArray);
		}

		return -1;
	}

	function equals(Research $cmp)
	{
		return ($this->id == $cmp->id);
	}
}
?>
