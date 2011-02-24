<?php
/**
 * Class to get user mail
 *
 * @todo Test class - Otherwise functionally complete
 */

class Mail extends Base
{
	public $id, $owner, $sender, $title, $body, $read, $deleted;

	/**
	 * Constructor for Mail class, gets mail from $id
	 *
	 * @param int $id Id of the mail
	 */
	function __construct($id)
	{
		parent::__contruct();

		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}

		//get mail details
		$this->db->query("SELECT * FROM Mail WHERE id='{$id}' LIMIT 1");
		$result = $this->db->getRow();

		$this->id = $id;
		$this->owner = $result['owner_id'];
		$this->sender = $result['sender_id'];
		$this->title = htmlspecialchars($result['title']);
		$this->body = htmlspecialchars($result['body']);

		$this->read = $result['read'];
		$this->deleted = $result['deleted'];
	}

	/**
	 * Check to see if user is allowed to view this mail.
	 * Restrictions are if deleted or not owner of mail and
	 * not an admin
	 *
	 * @param User $user User to check readability for
	 * @return bool
	 */
	function canRead(User &$user)
	{
		//Check ownership
		if($this->owner == $user->id || $user->isAdmin())
		{
			//check if flagged as deleted, only admins can view deleted mail
			if($this->deleted && $user->isAdmin())
			{
				return true;
			}
		}
		//else
		return false;
	}

	/**
	 * Set mail as read
	 */
	function setRead()
	{
		$this->db->query("UPDATE Mail SET read = '1' WHERE id = '{$this->id}' LIMIT 1");
		$this->read = 1;
	}

	/**
	 * Set mail as unread
	 */
	function setUnread()
	{
		
		$this->db->query("UPDATE Mail SET read = '0' WHERE id = '{$this->id}' LIMIT 1");
		$this->read = 0;
	}

	/**
	 * Set delete flag for mail
	 */
	function setDelete()
	{
		$this->db->query("UPDATE Mail SET deleted = '1' WHERE id = '{$this->id}' LIMIT 1");
		$this->deleted = 1;		
	}

	
	/**
	 * Send new mail
	 *
	 * @param int/User $to Mail to send to
	 * @param int/User $from Mail sent from
	 * @param string $title Title of the mail
	 * @param string $body Main body of mail
	 * @return bool
	 */
	static function send($to, $from, $title, $body)
	{
		//Validate $to
		if(!Validate::isInt($to))
		{	
			if(gettype($to) == "User")
			{
				$toId = $to->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$toId = $to;
		}
		
		//Validate $from
		if(!Validate::isInt($from))
		{	
			if(gettype($from) == "User")
			{
				$fromId = $from->id;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$fromId = $from;
		}
		
		//Prepare statement
		$title = $this->db->escape($title);
		$body = $this->db->escape($body);
		$sql = "INSERT INTO Mail (`owner_id`, `sender_id`, `sent_timestamp`, `title`, `body`)".
			" VALUES({$toId}, {$fromId}, {time()}, {$title}, {$body})";
			
		$this->db->query($sql);
		return true;
		
	}
	
}
?>