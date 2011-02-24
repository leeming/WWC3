<?php
/**
 * Class to allow user comments on news articles		 
 */
class NewsComment extends Base
{
	function __construct($id)
	{
		parent::__construct();
		
		
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		$this->db->query("SELECT * FROM News_comments WHERE id ='".$id."' LIMIT 1");

		if($this->db->numRows == 0)
		{
			trigger_error("There is no news comment with this id (".$id.")", E_USER_WARNING);
			return;
		}
		
		$result = $this->db->getRow();

		$this->id = $id;
		$this->newsId = $result['news_id'];
		$this->author = $result['author_id'];
		$this->body = $result['body'];
		$this->timestamp = $result['comment_timestamp'];
	}
	
	
	/**
	 * Makes a new comment, returns: -1 on error, 0 on failed mysql,
	 * else id of new comment
	 *
	 * @param array $insertArray Assoc array with fields to add
	 * @return int Comment Id
	 * @todo do real text formatting/validation
	 */
	static function add(array $insertArray)
	{
		//check required fields
		if(array_key_exists('body', $insertArray) && !empty($insertArray['body'])
		   && array_key_exists('author_id', $insertArray)
		   && Validate::isInt($insertArray['author_id'])
		   && array_key_exists('news_id', $insertArray)
		   && Validate::isInt($insertArray['news_id']))
		{
			global $db;
			
			//clean array
			$clean = array();
			$clean['news_id'] = $insertArray['news_id'];
			$clean['body'] = $insertArray['body'];
			$clean['comment_timestamp'] = time();
			$clean['author_id'] = $insertArray['author_id'];
			
			return $db->query_insert("News_comments", $clean);
		}

		return -1;
	}
	
	/**
	 * Deletes a comment
	 *
	 * @param int $id Id of the comment
	 */
	static function delete($id)
	{
		//Validate $id is int
		if(!Validate::isInt($id))
		{
			trigger_error("(int)\$id expected, (".gettype($id).") passed", E_USER_ERROR);
			return;
		}
		
		global $db;
		$sql = "DELETE FROM News_comments WHERE id='{$id}' LIMIT 1";
		$db->query($sql);
	}
	
	
}
?>