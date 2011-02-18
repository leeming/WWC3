<?php
/**
 * Class to get a collection of Mail such as getting a list of mail
 * for a user that is unread
 *
 * @todo Not sure about the future of the Mail classes, may get external code...
 * 
 * @todo Add class to allow formatting of body text, test & add any unexpected collections
 */
class MailCollection
{
	/**
	 * MailCollection constructor, doesnt do much apart
	 * from reset vars to default
	 */
	function __construct()
	{
		global $db;
		$this->db = &$db;
		
		//set vars
		$this->clear();
	}
	
	private $sender;
	private $owner;
	private $read;
	private $deleted;
	private $page;
	private $pageSize;
	
	/**
	 * Resets vars back to defaults
	 */
	function clear()
	{
		$this->sender = NULL;
		$this->owner = NULL;
		$this->read = NULL;
		$this->deleted = 0;
		$this->page = 1;
		$this->pageSize = NULL;
	}
	
	/**
	 * Set $owner var
	 *
	 * @param int/User $user
	 */
	function setOwner($user)
	{
		//check to see if $user is object or int
		if(Validate::inInt($user))
		{
			$this->owner = $user;
		}
		elseif(gettype($user) == "User")
		{
			$this->owner = $user->id;
		}
		else
		{
			trigger_error("(int/User)\$user expected, (".gettype($user).") passed", E_USER_ERROR);
			return;
		}
	}
	
	/**
	 * Set $sender var
	 *
	 * @param int/User $user
	 */
	function setSender($user)
	{
		//check to see if $user is object or int
		if(Validate::inInt($user))
		{
			$this->sender = $user;
		}
		elseif(gettype($user) == "User")
		{
			$this->sender = $user->id;
		}
		else
		{
			trigger_error("(int/User)\$user expected, (".gettype($user).") passed", E_USER_ERROR);
			return;
		}
	}
	
	/**
	 * Set $read var to true/false
	 *
	 * @param bool $read
	 */
	function setRead($read)
	{
		$this->read = ($read > 0);
	}
	
	/**
	 * Set $deleted var to true/false
	 *
	 * @param bool $read
	 */
	function setDeleted($deleted)
	{
		$this->deleted = ($deleted > 0);
	}
	
	/**
	 * Set what page of mail to get
	 *
	 * @param int $page Page number
	 */
	function setPage($page)
	{
		if(Validate::isInt($page))
			$this->page = $page;
	}
	
	/**
	 * Set how many mail to have per page
	 *
	 * @param int $size Number of mail per page
	 */
	function setPageSize($size)
	{
		if(Validate::isInt($size))
			$this->pageSize = $size;
	}
	
	/**
	 * The main method of the class, runs the database query that gets
	 * the mail depending on what vars have been set.
	 *
	 * @return Mail[]
	 */
	function getMail()
	{
		//Construct WHERE clause
		$whereClause = "WHERE true ";
		if($this->owner != NULL)
			$whereClause .= " AND owner_id = '{$this->owner}'";
		if($this->sender != NULL)
			$whereClause .= " AND sender_id = '{$this->sender}'";
		if($this->read != NULL)
			$whereClause .= " AND read = '{$this->read}'";
		if($this->deleted != NULL)
			$whereClause .= " AND deleted = '{$this->deleted}'";
		
		//Construct LIMIT
		$limit = "";
		if($this->pageSize != NULL)
			$limit = " LIMIT ".(($this->page-1)*$this->pageSize).",".$this->pageSize;
		
		
		$sql = "SELECT id FROM Mail WHERE owner_id = '{$id}' AND deleted = '0' ORDER BY sent_timestamp DESC {$limit}";
		
		$result = $this->db->fetch_all_array($sql);
		
		$this->resultSet = array();
		foreach($result AS $mailId)
		{
			$userMail[] = new Mail($mailId);
		}
		
		return $userMail;
	}
	
	static function numUnread($w)
	{
		//Check to see if array is passed as $w
		if(is_array($w))
		{
			$count = 0;
			foreach($w AS $mail)
			{
				//Make sure its a mail object
				if(get_class($mail) != "Mail")
				{
					throw new Exception("Array of Mail objects expected");
				}
				
				$count += (int)(!$mail->read);
			}
			return $count;
		}
		//if int is passed then its a userid
		else if(Validate::isInt($w))
		{
			global $db;
			$db->query("SELECT COUNT(`read`) AS unread FROM Mail WHERE `read`=0".
				" AND deleted=0 AND owner_id='{$w}'");
			$result = $db->fetch_array();
			
			return $result['unread'];
		}
		else
		{
			throw new Exception("Userid or array of Mail objects expected");
		}
	}
}