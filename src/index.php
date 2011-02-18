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
 * This is the main script that is loaded when people visit the site
 * All it does is call the user interface template really. All main
 * processing is done in lobby.php which is the AJAX inbetween script
 * for calling pages
 * 
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */
 
//Does session really need to be started here? see lobby.php
session_start();

require_once("./tiggerConfig.php");

try
{
	//Does this really need to be called here? see lobby.php
    $db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $db->connect();
       
    //Include UI Template
    require("ui/template.php");
    
    //
    unset($_SESSION['track']);
    $db->close();
}
catch (Exception $e)
{
	// TODO need more elegant error to user
	
    print "Exception:" .$e->getMessage();
}

?>

