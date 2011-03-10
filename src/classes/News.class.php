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
 * Class for posting news & updates on a site. Also allows
 * users to: edit & delete news, post comments
 *
 * TODO Needs reviewing and refactoring
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
class News extends Base
{
   /**
	 * News constructor, loads news article with id $id
	 * 
	 * @param int $id Id of news article
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
		
		$this->db->query("SELECT * FROM News WHERE id ='".$id."' LIMIT 1");

		if($this->db->numRows == 0)
		{
			trigger_error("There is no news with this id (".$id.")", E_USER_WARNING);
			return;
		}

		$result = $this->db->getRow();

		$this->id = $id;
		$this->authorId = $result['author_id'];
		$this->title = stripslashes($result['title']);
		$this->timestamp = $result['post_timestamp'];
		$this->body = stripslashes($result['body']);
	}
	
	private $comments = NULL;
	
	/**
	 * Get list of comments for the news article
	 */
	function getComments($recache = false)
	{
		if($recache || $this->comments == NULL)
		{
			$sql ="SELECT id FROM News_comments WHERE news_id='{$this->id}' "
				."ORDER BY comment_timestamp";
			$results = $this->db->fetch_all_array($sql);
			
			$this->comments = array();
			foreach($results AS $row)
			{
				$this->comments[] = new NewsComment($row['id']);
			}
		}
		
		return $this->comments;
	}
	
	/**
	 * Get number of comments
	 *
	 * @return int Number of comments
	 */
	function countComments()
	{
		$sql ="SELECT COUNT(id) AS `count` FROM News_comments WHERE news_id='{$this->id}'";
		$this->db->query($sql);
		
		$result = $this->db->getRow();
		return $result['count'];
	}
	
	/**
	 * Makes a new comment
	 *
	 * @param String $body Body of the comment
	 * @return int Comment Id (or -1 on error, 0 mysql error)
	 */
	function postComment($body)
	{
		//make sure user exists
		if(key_exists("user",$GLOBALS))
			global $user;
		else
			return -1;
		
		return NewsComment::add(array(
			'author_id' => $user->id,
			'news_id' => $this->id,
			'body' => $body
		));
	}
	
	
	
	/**
	 * Update current news with values set inside
	 * of the object
	 */
	function update()
	{
		//validate all fields
		//
		$author = $this->authorId;
		$title = $this->title;
		$timestamp = $this->timestamp;
		$body = $this->body;
		
		//assuming all validation is done
		$sql = "UPDATE News SET author_id ='{$author}', title='{$title}',".
			"post_timestamp='{$timestamp}', body='{$body}' WHERE ".
			"id='{$this->id}' LIMIT 1";
			
		$this->db->query($sql);	
	}
	
	/**
	 * Makes a new `news article`, returns: -1 on error, 0 on failed mysql,
	 * else id of new `news`
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int News Id
	 * @todo do real text formatting/validation
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('title', $insertArray) && !empty($insertArray['title'])
		   && array_key_exists('author_id', $insertArray)
		   && Validate::isInt($insertArray['author_id'])
		   && array_key_exists('body', $insertArray) && !empty($insertArray['body']))
		{
			global $db;
			
			
			//clean array
			$clean = array();
			$clean['title'] = $db->escape($insertArray['title']);
			$clean['body'] = $db->escape($insertArray['body']);
			$clean['post_timestamp'] = time();
			$clean['author_id'] = $insertArray['author_id'];
			
			return $db->query_insert("News", $clean);
		}

		return -1;
	}
	
	
	/**
	 * Delete a news article
	 *
	 * @param int $id Id of the news article
	 * @return bool true:deleted, false:didnt
	 */
	static function delete($id)
	{
		if(!Validate::isInt($id))
			return false;
		
		global $db;
		$sql = "DELETE FROM News WHERE id='{$id}' LIMIT 1";
		$db->query($sql);
		
		return $db->affected_rows;
	}
}
?>
