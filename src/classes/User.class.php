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
 * Main class which holds all info on an account
 * via ref to various other classes
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class User
{
	public $id, $username,  $handle,  $email;


   /**
	 * Account constructor, creates a member for $id
	 *
	 * @param int $id Id of the user
	 */
	function __construct($id)
	{
		global $db, $firephp;
		$this->db = &$db;
		$this->firephp = &$firephp;
	
		$this->firephp->log("Inside User");

		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}

		//does account exist? / get username/handle/email
		$this->db->query("SELECT SQL_CACHE username, handle, email FROM Users WHERE id ='".$id."' LIMIT 1");

		if($this->db->numRows == 0)
		{
			trigger_error("There is no account with this id (".$id.")", E_USER_WARNING);
			return;
		}

		$result = $this->db->getRow();

		$this->id = $id;
		$this->username = $result['username'];
		$this->handle = $result['handle'];
		$this->email = $result['email'];
$this->firephp->log("finsihed with user");

		//etc
	}

	private $activated = NULL;

	/**
	 * Finds out if the member has activated their account yet or not
	 *
	 *	@return	bool
	 */
	 function isActivated()
	 {
		 //lookup only if not done so before
		 if($this->activated != NULL)
		{
			//check activation table, if record found user isnt activated
			$this->db->query("SELECT true FROM Activation_codes WHERE user_id ='{$this->id}' LIMIT 1");
			$this->activated = ($this->db->numRows == 1);

		}

		return $this->activated;
	 }

	/**
	 * Finds out if current user is an admin
	 *
	 * @todo Staff permissions still needs adding
	 * @return bool
	 */
	function isAdmin()
	{
		return ($this->id == 1);
	}
	
	/**
	 * Allows a user to change their password. Due to security
	 * all attempts are logged and previous password needs
	 * to be given.
	 *
	 * TODO Log change attempts
	 * 
	 * @param string $oldPw
	 * @param string $newPw
	 * @return bool On success
	 */
	function changePassword($oldPw, $newPw)
	{
		//Escape any naughty sql
		$oldPw = $this->db->escape($oldPw);
		$newPw = $this->db->escape($newPw);
		
		$sql = "SELECT changePassword({$this->id},'{$oldPw}','{$newPw}') AS changeSuc";
		$this->db->query($sql);
		$result = $this->db->getRow();
		return $result['changeSuc'];
		
		return false;
	}

	/**
	 * Returns an array of Mail objects
	 *
	 * @return Mail[]
	 */
	 function getMail($returnType = "int")
	 {
		return Mail::getMail($this->id);
	 }


	 private $profile = NULL;

	 /**
	 * Fetchs the Profile class for the current user. If this has not been
	 * called yet, then a call is made to make a new Profile object and
	 * then returns the newly created reference.
	 * If this has already been called, then the reference to the prior
	 * call is returned.
	 *
	 * @returns	Profile
	 */
	function getProfile()
	{
		//check to see if already fetched
		if($this->profile == null)
		{
			//get profile data
			$this->profile = new Profile($this->id);
		}

		return $this->profile;
	}


	private $inGames = NULL;
	private $inGameIds = NULL;

	/**
	 * Gets a list of games which the user is in
	 * paired with player id, array(game_id => player_id)
	 *
	 * @param String $returnType
	 * @return	array(game_id => player_id)
	 */
	 function getGames($returnType = "int")
	 {
		 //get array of games user is in
		$query = $this->db->fetch_all_array("SELECT game_id,id FROM Players WHERE user_id = '{$this->id}' ");
		$returnArray = array();

		if($returnType == 'int')
		{
			//make array of game ids
			foreach($query AS $result)
			{
				$returnArray[$result['game_id']] = $result['id'];
			}
			
			$this->inGameIds = $returnArray;
		}
		/* 
		else
		{
			//make array of Game objects
			foreach($query AS $result)
			{
				$returnArray[] = new Game($result['game_id']);
			}

			$this->inGames = $returnArray;
		}
		*/
		return $returnArray;
	 }

	 /**
	  *  Gets a list of medals which the user has
	  * 
	  * @return int[]/Medals[]
	  */
	 function getMedals($returnType = "int")
	 {
		 //get array of games user is in
		$query = $this->db->fetch_all_array("SELECT medal_id FROM User_has_medal WHERE user_id = '{$this->id}' ");
		$returnArray = array();

		 if($returnType == 'int')
		 {
			 //make array of medal ids
			foreach($query AS $result)
			{
				$returnArray[] = $result['medal_id'];
			}
		 }
		else
		{
			//make array of Medal objects
			foreach($query AS $result)
			{
				$returnArray[] = new Medal($result['medal_id']);
			}
		}

		return $returnArray;
	 }

	/**
	 * Gets a list of logins for user
	 *
	 * @param int $num Number of records to get
	 * @return array()
	 */
	function getLogins($num = 1)
	{
		if(!Validate::isInt($num) || $num < 1)
		{
			return -1;
		}
		
		$sql = "SELECT login_timestamp, ip FROM Logins WHERE "
			."user_id='{$this->id}' ORDER BY login_timestamp DESC LIMIT ".$num;
		$result = $this->db->fetch_all_array($sql);
		
		$return = array();
		foreach($result AS $record)
		{
			$return[] = array('timestamp'=>$record['login_timestamp'],
							  'ip'=>$record['ip']);
		}
		
		return $return;
	}

	 /**
	 * Does all the various checks for login and returns id on login
	 * but on failure returns the error str
	 *
	 * @param String $username
	 * @param String $password
	 * @return int/String
	 */
	 static function login($username, $password)
	 {
		global $db;

		//check username is valid
		if(!User::isValidUsername($username))
		{
			throw new Exception("Invalid username");
		}
		//make sure password is not null
		if(empty($password))
		{
			throw new Exception("You need to enter a password");
		}

		//check username & password correct
		$password = $db->escape($password);
		$db->query("SELECT userLogin('{$username}', '{$password}') AS userid");
		$result = $db->getRow();


		if($result['userid'] == -1)
		{
			#Account not found
			//return "WRONG_UN_PW";
			throw new Exception("Username/password not found");
		}

		$_SESSION['wwclogin'] = $result['userid'];

		#Add into login records
		$ip = $_SERVER['REMOTE_ADDR'];
		//Extract browser name
		//
		include(CLASS_ROOT_DIR."/BrowserDetection.class.php");
		global $firephp;
		$browserDetect = browser_detection('full_assoc');
		$firephp->log($browserDetect);
		
		$browser = $db->escape($browserDetect['browser_name']);
		$version = $db->escape($browserDetect['browser_math_number']);
		$os = $db->escape($browserDetect['os']."::".$browserDetect['os_number']);
		$userAgent = $db->escape($_SERVER['HTTP_USER_AGENT']);

		$sql = "INSERT INTO Logins (`user_id`, `login_timestamp`, `ip`, `browser_name`, `browser_ver`, `os`, `user_agent`)".
			"VALUES ('".$result['userid']."', '".time()."', '".$ip."', '".$browser."', '".$version."', '".$os."', '".$userAgent."')";
		$db->query($sql);

		return $result['userid'];
	 }


	
	
	/**
	 * Makes a new user, returns: String on error, 0 on failed mysql,
	 * else id of new game
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int User Id
	 */
	static function add(array $insertArray)
	{
		//check username
		if(!array_key_exists('username', $insertArray) ||
			!User::isValidUsername($insertArray['username']))
		{
			return "INVALID_USER";
		}
		//check email
		if(!array_key_exists('email', $insertArray) ||
			!User::isValidEmail($insertArray['email']))
		{
			return "INVALID_EMAIL";
		}
		//check handle
		if(!array_key_exists('handle', $insertArray) ||
			!User::isValidHandle($insertArray['handle']))
		{
			return "INVALID_HANDLE";
		}
		//check password
		if(!array_key_exists('password', $insertArray) ||
			empty($insertArray['password']))
		{
			return "INVALID_PASSWORD";
		}
		
		
		//make sure some one already has user/handle/email
		if(User::usernameExists($insertArray['username']))
		{
			return "USER_IN_USE";
		}
		if(User::handleExists($insertArray['handle']))
		{
			return "HANDLE_IN_USE";
		}
		if(User::emailExists($insertArray['email']))
		{
			return "EMAIL_IN_USE";
		}
		
		//dont care what password is, still escape it
		global $db;
		$insertArray['password'] = $db->escape($insertArray['password']);
		
		//insert user
		$db->query("SELECT makeUser('{$insertArray['username']}', "
			."'{$insertArray['password']}', '{$insertArray['email']}', "
			."'{$insertArray['handle']}') AS userid");
		$result = $db->getRow();
		
		return $result['userid'];
	}

	/**
	 * Get a user's handle from $id
	 *
	 * @param int $id User to lookup
	 * @return string User's handle
	 */
	static function getHandle($id)
	{
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		global $db;
		$sql = "SELECT handle FROM Users WHERE id='{$id}' LIMIT 1";
		$db->query($sql);
		$result = $db->getRow();
		
		return $result['handle'];
	}

	/**
	 * Checks to see if passed username is valid
	 * eg is alphanumeric and between 3 and 25 char long
	 *
	 * @param String $tochk
	 * @return bool
	 */
	static function isValidUsername($tochk)
	{
		return preg_match("/^[a-zA-Z0-9]{3,25}$/", $tochk);
	}
	static function usernameExists($tochk)
	{
		return false;
	}

	/**
	 * Checks to see if passed handle is valid
	 * eg is alphanumeric and between 3 and 25 char long
	 *
	 * @param String $tochk
	 * @return bool
	 */
	static function isValidHandle($tochk)
	{
		return preg_match("/^[a-zA-Z0-9]{3,25}$/", $tochk);
	}
	static function handleExists($tochk)
	{
		return false;
	}
	
	/**
	 * Checks to see if passed email is valid
	 *
	 * @param String $tochk
	 * @return bool
	 */
	static function isValidEmail($tochk)
	{
		return true;
	}
	static function emailExists($tochk)
	{
		return false;
	}
	
	/**
	 * Fetches an array of all users of site
	 *
	 * @static
	 * @return User[]
	 */
	static function getAll()
	{
		global $db;
		$sql = "SELECT id FROM Users";
		$users = array();
		
		foreach($db->fetch_all_array($sql) AS $r)
		{
			$users[$r['id']] = new User($r['id']);
		}
		
		return $users;
	}

	function __wakeup()
	{
		//refresh db connection
		global $db;
		$this->db = $db;
	}
}
?>
