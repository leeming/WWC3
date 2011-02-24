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
 * Need to check if this is still used as the <tt>Database</tt>
 * object SHOULD be used instead?
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @deprecated <tt>Database</tt> object should do this
 */

#Connect to DB
if($con = @mysql_connect(DB_HOST, DB_USER, DB_PASS))
{ 
	if(!$db = @mysql_select_db(DB_NAME, $con))
	{
		#Failed connection to DB

		//Trigger error and return false
		//trigger_error('Database connection failed', E_USER_WARNING);
		die("ERROR_SQL_DB");
	}		
}
else
{
	//trigger_error('Server connection failed', E_USER_WARNING);
	die("ERROR_SQL_CONNECT");
}

?>
