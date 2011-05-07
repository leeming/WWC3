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
 * This is the main code that acts as a gateway to served paged.
 * It checks requested pages to see if they are valid and if added,
 * else a custom error page is returned to notify the user the page
 * was expected but not found. This code also manages calling
 * the DB connection and session handling stuff
 *
 * TODO Need to add session hijacking code
 * TODO RFC on way pages are loaded, white listing seems a bit of a pain now
 * 	    that	     there	     are many pages and code is public anyway (no
 *      need 	 to /hidemap to directory locations)
 * 
 * TODO Clean up code and properly document? partition code?
 * 
 *
 * @author Leeming <a_p_leeming@hotmail.com>
 * @version 1.0
 * @copyright Copyright &copy; 2011, Leeming
 */

session_start();


//get config file & connection
require_once("./tiggerConfig.php");

/*
//Basic secure session handler
$ss = new SecureSession();
$ss->check_browser = true;
$ss->check_ip_blocks = 2;
$ss->secure_word = 'WWC_';
$ss->regenerate_id = true; 

//Check valid session
if(!$ss->Check())
{
	//Session is faulty or hijacked
	//TODO What is the best thing to do here? error page/anything else?
	$_SESSION = array();
	$_REQUEST['page'] = "error";
	$args['code'] = "NOT_LOGGED_IN";
	
}
*/
$startTime = microtime(true);



//Set up debugger (FirePHP)
$firephp = FirePHP::getInstance(true);
//$firephp->setOptions(array('maxObjectDepth'=>3,'maxArrayDepth'=>4, 'includeLineNumbers'=>true));

ob_start();
header('Cache-Control: no-cache');
header('Pragma: no-cache');


$firephp->info(memory_get_usage());

// Persistant or not?
$db = new Database(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$db->connect();


if(key_exists('user', $_SESSION))
	$user = unserialize($_SESSION['user']);
if(key_exists('curPlayer', $_SESSION))
	$player = Player::getInstance($_SESSION['curPlayer']);
   
############################
### Lobby pages go below ###
############################

//List all pages allowed to view in the format 'pageName' => 'fileLocation'
$publicPages = array(//'About' pages
					  'news' => "pages/news.php",
					  'about' => "pages/about.php",
					  'contact' => "pages/contact.php",
					  'staff' => "pages/staff.php",
					  
					  //'Help' pages
					  'faq' => "pages/faq.php",
					  'guides' => "pages/guides.php",
					  'helpcentre' => "pages/help.php",
					  
					  //'Feedback' pages
					  'bugs' => "pages/bugs.php",
					  'ideas' => "pages/ideas.php",
					  'praise' => "pages/praise.php",
					  'question' => "pages/questions.php",
					  
					  //Misc pages
					  'error' => "pages/error.php",
					  'debug' => "pages/debug.php",
					  'login' => "pages/login.php",
					  'register' => "pages/signup.php",
                      					  
					  //AJAX
					  'get'	=> "ajax/queryGetter.php"
);
$lobbyPages = array(  //Account pages
					  'profile' => "pages/profile.php",
					  'password' => "pages/changePassword.php",
					  'premium' => "pages/premium.php",
					  'invite' => "pages/inviteFriend.php",
					  'email' => "pages/manageEmail.php",
					  'alerts' => "pages/manageAlerts.php",
					  'logout' => "pages/logout.php",
						
					  //Games pages	
					  'games' => "pages/listGames.php",
					  'play'=> "pages/play.php",
					  'end'=> "pages/endGame.php",
					  'makeGame'=> "pages/makeGame.php",
					  
					  //Mail pages
					  'mail' => "pages/mail.php",
					  'list' => "pages/buddyLists.php",
					  
					  //Misc
					  'welcome' => "pages/welcome.php"

);
$gamePages = array(  //Game Navigation pages
					  'build' => "pages/buildings.php",
					  'overview' => "pages/overviews.php",
					  'forum' => "pages/forum.php",
					  'chat' => "pages/chat.php",
					  'actions' => "pages/militaryActions.php",
					  'battalions' => "pages/battalion.php",
					  'research' => "pages/research.php",
					  'team' => "pages/team.php",
					  'market' => "pages/market.php",
					  'bank' => "pages/bank.php"
	
);
$adminPages = array( //Admin pages
                      'settings' => "pages/makeSettings.php",
                      'createGame' => "pages/gameCreation.php",
                      'manageUsers' => "pages/manageUser.php",
                      'managePlayer' => "pages/managePlayer.php",
                      'manageTeam' => "pages/manageTeam.php",
                      'manageGames' => "pages/manageGames.php"
);

//if no page is given, show this default
if(!isset($_REQUEST['page']) || $_REQUEST['page'] == "")
{
	//Default page
	$firephp->log("Using default page");
	$page = "news";
}
else
{
	$page = $_REQUEST['page'];
}

#check page is allowed
//logged in pages
if(key_exists($page, $lobbyPages) || key_exists($page, $gamePages) || key_exists($page, $adminPages))
{
	//Check to see session data is set, if not error
	if(!key_exists('user', $_SESSION))
	{
		$firephp->warn($page,"Not logged in");
		$_SESSION['_debug'][] = array('timestamp' => time(),
									 'text' => "Redirect:: Not logged in",
									 'request' => $_REQUEST);
		$page ="error";
		$args['code'] = "NOT_LOGGED_IN";
	}
    //if page is game, check game id is set
    else if(key_exists($page, $gamePages))
    {
        if(isset($player))
        {
            //$player = unserialize($_SESSION['player']);
            $pagePath = $gamePages[$page];
        }
        else
        {
            //throw error for now because not added any game pages;
            $firephp->warn($page, "Game not set");
            $page = "error";
            $args['code'] = "GAME_NOT_SET";
        }
    }
    //if page is admin
    else if(key_exists($page,$adminPages))
    {
        if($user->isAdmin())
            $pagePath = $adminPages[$page];
        else
        {
            //user isnt an admin
            $firephp->warn($page, "User is not an admin");
            $page = "error";
            $args['code'] = "ADMIN_ONLY";            
        }
    }
    //else page should be lobby page
    else 
    {
        $pagePath = $lobbyPages[$page];
    }

}
//else public page
else if(!key_exists($page, $publicPages))
{
	$firephp->log($page, "Illegal page request");
	$page="error";
	$args['code'] = "PAGE_NOT_ALLOWED";
}
//else page is public page
else
{
	$pagePath = $publicPages[$page];
}

//catch error page and set pagePath
if($page == "error")
	$pagePath = $publicPages['error'];

$absoluteDirPath = SITE_ROOT."/includes/";

//check to see if page exists (on the filestore)
if(!file_exists($absoluteDirPath.$pagePath))
{
	$page="error";
	$pagePath = $publicPages['error'];
	$args['code'] = "PAGE_NOT_ADDED";
}

//save all request data into the $args var
foreach(array_merge($_GET,$_POST) AS $index => $value)
{
	$args[$index] = $value;
}

$firephp->log($args,"Main Request query");

//log page requests
$_SESSION['_track'][] = array('timestamp' =>time(),
							 'page' => $page,
							 'pageReq' => isset($_REQUEST['page'])?$_REQUEST['page']:"",
							 'request' => $args);

//load page here
try
{
    require($absoluteDirPath.$pagePath);
}
catch(Exception $e)
{
	/*
	 * Clear buffer of any previous content from required file and print out
	 * error on its own 
	 * TODO Headers removed? Confirm as nice to debug with firebug
	 */
	 ob_end_clean();
	
    print "Unexpected error: ".$e->getMessage();
}

##########################
### End of lobby pages ###
##########################


//Send buffer contents
ob_end_flush();
	
	
#Clean up at end

if(isset($_SESSION['cache']))
    unset($_SESSION['cache']);

if(isset($user))
	$_SESSION['user'] = serialize($user);
//if(isset($player))
//	$_SESSION['player'] = serialize($player);
	
$db->close();
//$firephp->info(microtime(true)-$startTime,"Page fully loaded");
?>
