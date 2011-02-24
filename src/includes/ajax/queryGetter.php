<?php
//private pages
if(isset($_SESSION['user']))
{
	switch($args['get'])
	{
		case 'userInfo':
			include("userInfo.php");
			return;
		case 'userGames':
			include("menuGamelist.php");
			return;
		case 'loadFacilityMenu':
			include("makeFacilityMenu.php");
			return;
		case 'whatGame':
			if(isset($player))
				print $player->game->id;
			else
				print 0;
			return;
		
		//Allow player to set active battalion
		case 'activeBat':
			include("activeBat.php");
			return;
		//gets building info for building page
		case 'buildInfo':
			include("buildInfo.php");
			return;

	}
}//end if logged in

//public
switch($args['get'])
{
	case 'lastPage':
		//$last = array_pop($_SESSION['track']);
		for($i=count($_SESSION['track'])-1; $i>=0; $i--)
		{ print $i.":";
			if($_SESSION['track'][$i]['page'] != "get")
			{
				$last = $_SESSION['track'][$i];
				return;
			}
		}
		
		print $last['request']['page']." ";
		foreach($last['request'] AS $index => $value)
		{
			//dont redo 'page' and dont allow resubmit of forms
			if($index != "page" && $index != "submit")
				print $index."=".$value."&";
		}	
		return;

	//Expand news items
	case 'expandNews':
		include("getNews.php");
		return;
	
	//Basic check to see if user is logged in or not
	case 'checkLogin':
		print isset($_SESSION['user'])?"1":"0";
		return;
	
	default:
		print "Unknown get";
		return;
}
?>