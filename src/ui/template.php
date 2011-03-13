<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?=SITE_TITLE?> - Lets go to War!</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

  <!-- Javascript Libs -->
  <script type="text/javascript" src="ui/inc/jquery.min.js"></script>
  <script type="text/javascript" src="ui/inc/jquery-ui.min.js"></script>
  <script type="text/javascript" src="ui/inc/jquery.metadata.js"></script>
  <script type="text/javascript" src="ui/inc/mbContainer.js"></script>
  <script type="text/javascript" src="ui/inc/mbMenu.js"></script>
  <script type="text/javascript" src="ui/inc/jquery.hoverIntent.js"></script>
  
  <!-- WWC Libs -->
  <script type="text/javascript" src="ui/inc/wwcWindow.js"></script>
  <script type="text/javascript" src="ui/inc/wwc.js"></script>
  
  <link rel="stylesheet" type="text/css" href="ui/css/main.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="ui/css/menu2.css" media="screen" />
  <link rel="stylesheet" type="text/css" href="ui/css/mbContainer.css" title="style"  media="screen"/>
</head>

<body bgcolor="#ffffff" onload="loadScript()">

<div id="dockwrapper" class="wrapper">
<div class="menustrip">

	<div class="rootVoices" style="float:right">
		<div class="rootVoice {menu: 'menu_feedback', disabled:true}">Feedback</div>
	</div>
	<div class="rootVoices">
		<div id="menuAccount" class="rootVoice {menu: 'menu_account', disabled:true}"  disabled="true">Account</div>
		<div id="menuGames" class="rootVoice {menu: 'menu_games'}">Games</div>
		<div id="menuNav" class="rootVoice {menu: 'menu_nav'}" >Nav</div>
		<div id="menuDock" class="rootVoice {menu: 'menu_dockable'}" >Dockables</div>
		<div id="menuMail" class="rootVoice {menu: 'menu_mail'}" >Mail</div>
    	<div id="menuSite" class="rootVoice {menu: 'menu_main'}">About</div>
		<div id="menuHelp" class="rootVoice {menu: 'menu_help'}" >Help</div>
		<div id="menuAdmin" class="rootVoice {menu: 'menu_admin', disabled:true}" >Admin</div>
	</div>
</div>
<div id="dock" style="display:none;"> </div> 
</div>


<!-- menus -->
<div id="menu_main" class="mbmenu">
  <a class="" onclick="loadWindow('news','','','true')">News</a>
  <a class="" onclick="loadWindow('contact')">Contact</a>
  <a class="" onclick="loadWindow('about')">About</a>
  <a class="" onclick="loadWindow('staff')">Staff</a>
  <a class="" onclick="testwindow()">test</a>
</div>

<div id="menu_account" class="mbmenu">
  <a rel="text" id="userInfo"></a> 
  <a rel="separator"> </a> 
  
  <a class="" style="text-decoration:line-through" onclick="loadWindow('profile')">Profile</a>
  <a class="" onclick="loadWindow('password')">Change Password</a>
  <a class="" style="text-decoration:line-through" onclick="loadWindow('premium')">Premium</a>
  <a class="" style="text-decoration:line-through" onclick="loadWindow('invite')">Invites</a>
  <a class="" style="text-decoration:line-through" onclick="loadWindow('email')">Email Details</a>
  <a class="" style="text-decoration:line-through" onclick="loadWindow('alerts')">Alerts + Windows</a>

  
  <a rel="separator"> </a> <!-- menuvoice separator-->
  <a class="" onclick="logout();" href="index.php">Logout</a>
</div>

<div id="menu_games" class="mbmenu">
  <a class="{menu: 'submenu_mygames', img: 'icon_14.png'}">My Games</a>
  <a rel="separator"> </a>
  <a class="" onclick="loadWindow('games','','list=all')">Game List</a>
  <a class="" onclick="loadWindow('makeGame')">Create New Game</a>
</div>

<div id="submenu_mygames" class="mbmenu">
</div>


<div id="menu_nav" class="mbmenu">
	<a href="#" class="{menu: 'submenu_buildings'}" onclick="loadWindow('build')">Buildings</a>
	<a class="{menu: 'submenu_overviews'}" onclick="loadWindow('overview')">Overview</a>
	<a class="{menu: 'submenu_comms'}" onclick="loadWindow('comms')">Communication</a>
	<a onclick="loadWindow('actions')">Actions</a>
	<a class="{menu: 'submenu_research'}" onclick="loadWindow('research')">Research</a>
	<a class="{menu: 'submenu_team'}" onclick="loadWindow('team')">Team</a>
	<a onclick="loadWindow('market')">Market</a>
	<a onclick="loadWindow('bank')">Bank</a>
</div>


<div id="submenu_buildings" class="mbmenu">

</div>

<div id="submenu_overviews" class="mbmenu">
	<a>General</a>
	<a style="text-decoration: line-through;">Battle</a>
	<a style="text-decoration: line-through;">Troop</a>
	<a style="text-decoration: line-through;">Team</a>
	<a style="text-decoration: line-through;">Production</a>
</div>

<div id="submenu_comms" class="mbmenu">
	<a  style="text-decoration: line-through;">Forum</a>
	<a style="text-decoration: line-through;">Chat</a>
</div>

<div id="submenu_actions" class="mbmenu">
	<a>Move</a>
	<a>Attack</a>
	<a>Air Raid</a>
	<a>Spy</a>
</div>

<div id="submenu_research" class="mbmenu">
	<a href="#" onclick="loadWindow('research','','view=tree')">Tree</a>
	<a href="#" onclick="loadWindow('research','','view=queue')">Queue</a>
</div>

<div id="submenu_team" class="mbmenu">
	<a style="text-decoration: line-through;">Forum</a>
	<a style="text-decoration: line-through;">Orders</a>
	<a style="text-decoration: line-through;">Generals</a>
	<a style="text-decoration: line-through;">Set Play Style</a>
	<a style="text-decoration: line-through;">Treaties</a>
</div>

<div id="menu_dockable" class="mbmenu">
	<a rel="text">dododo</a>
	<a href='#' onclick="$('#demoContainer').mb_iconize(); alert(' is iconized? '+$('#demoContainer').mb_getState('iconized')+'\n is collapsed? '+$('#demoContainer').mb_getState('collapsed')+'\n is closed? '+$('#demoContainer').mb_getState('closed'));">This</a>

	<a href='#' onclick="$('#demoContainer').mb_open(); alert(' is iconized? '+$('#demoContainer').mb_getState('iconized')+'\n is collapsed? '+$('#demoContainer').mb_getState('collapsed')+'\n is closed? '+$('#demoContainer').mb_getState('closed'));">That</a>
</div>

<div id="menu_mail" class="mbmenu">
	<a onclick="loadWindow('mail','','read=all')">Read</a>
	<a onclick="loadWindow('mail','','send=new')">Send</a>
	<a onclick="loadWindow('list','','type=friend')">Friend List</a>
	<a onclick="loadWindow('list','','type=ignore')">Ignore List</a>
</div>

<div id="menu_admin" class="mbmenu">
	<a onclick="loadWindow('settings')">Make Settings</a>
	<a onclick="loadWindow('createGame')">Set Up New Game</a>
	<a onclick="loadWindow('manageUsers')">Manage Users</a>
	<a onclick="loadWindow('manageGames')">Manage Current Game</a>
</div>


<div id="menu_help" class="mbmenu">
  <a rel="text" >
	Please remember to look over the FAQs before using the help service
  </a>
  <a onclick="loadWindow('faq')">FAQ</a>
  <a onclick="loadWindow('guides')">Game Guides</a>
  <a onclick="loadWindow('helpcentre')">Help Centre</a>
</div>

<div id="menu_feedback" class="mbmenu">
  <a rel="text" >
	Feel free to give us some feedback. If you want to do this anonymously you can.<br>The more you tell us, the more we can improve it!
  </a>
  <a onclick="loadWindow('ideas')">Ideas</a>
  <a onclick="loadWindow('bugs')">Bugs</a>
  <a onclick="loadWindow('question')">Ask A Question</a>
  <a onclick="loadWindow('praise')">Thank the Staff</a>
</div>
<!-- end menues -->


</body>
</html>
