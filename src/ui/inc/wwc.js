//Remember the original title of the page
defaultTitle = document.title;

$(function(){
  $(".rootVoices").buildMenu(
  {
    template:"menuVoices.html",
    additionalData:"pippo=1",
    menuWidth:150,
    openOnRight:false,
    menuSelector: ".menuContainer",
    iconPath:"ui/ico/",
    hasImages:false,
    fadeInTime:100,
    fadeOutTime:300,
    adjustLeft:2,
    minZindex:"auto",
    adjustTop:10,
    opacity:.95,
    shadow:true,
    shadowColor:"#ccc",
    hoverIntent:0,
    openOnClick:false,
    closeOnMouseOut:true,
    closeAfter:100000
  });
  
  if (self.location.href == top.location.href){
    $("body").css({font:"normal 13px/16px 'trebuchet MS', verdana, sans-serif"});
  }

  //not used any more?
  $(".containerPlus").buildContainers({
    containment:"document",
    elementsPath:"ui/elements/"
  });
  ////

});

function isset( variable )
{
  return( typeof( variable ) != 'undefined' );
}

//this function get the id of the element that fires the context menu.
function testForContextMenu(el){
  if (!el) el= $.mbMenu.lastContextMenuEl;
  alert("the ID of the element is:   "+$(el).attr("id"));
}

/**
 * This function is the onload function which is called on load
 * Mainly handles loading of menus/hiding depending on login, and
 * also of loading sub windows
 */
function loadScript()
{
    //should move these down to style for each menu really
    document.getElementById('menuAccount').style.display = 'none';
    document.getElementById('menuNav').style.display = 'none';
    document.getElementById('menuGames').style.display = 'none';
    document.getElementById('menuDock').style.display = 'none';
    document.getElementById('menuMail').style.display = 'none';
    
    document.getElementById('menuAdmin').style.display = 'none';
    
    //Load main window
    var mainwin = makeWindow({id: 'bxContex'});
    
    //define new window
    mainwin.setSize(800,500);
    mainwin.setPos(250,400);
    mainwin.make();
    
    //set content and show
    mainwin.setBodyAjax('news');
    mainwin.setTitle('News');
    mainwin.show();
    
    
    //Check to see if user is logged in or not
    $.get("lobby.php", { page: "get", get: 'checkLogin' }, function(data){
      //user is already logged in
      if(data == "1")
      {
        logonView();
        
        //find out what game user was in
        $.get("lobby.php", { page: "get", get: 'whatGame'}, function(data2){
          //If user is in a game, enter that game by default
          //thus if user did a page refresh reenter game, but if 1st load no game
          if(data2 > 0)
            pickgame(data2);
        });//end whatGame
        
        //refresh last page
        /*$.get("lobby.php", { page: "get", get: 'lastPage'}, function(data2){
          splitter = data2.split(" ");
          loadWindow(splitter[0],splitter[1]);
        });
        */
      }
      //user not logged in
      else
      {
        //Load login window
        var loginwin = makeWindow({id: 'bxLogin2', buttons: ''});
        //define new window
        loginwin.setSize(300,200);
        loginwin.setPos(10,50);
        loginwin.make();
        
        //set content and show
        loginwin.setBodyAjax('login');
        loginwin.setTitle('Login');
        loginwin.show();
        
        
        //open login page
        //loadWindow("login","","bxLogin");
        //var loginwin = makeWindow({title: 'Login2'});
        //loginwin.setPos(250,400);
        //loginwin.setSize(200,300);
      }
      
      //open news
      loadWindow("news");
      
      
    });//end checkLogin
    
}

function whatWindow(e)
{
  var targ;
  if (!e)
  {
    e=window.event;
  }

  if (e.target)
  {
    targ=e.target;
  }
  else if (e.srcElement)
  {
    targ=e.srcElement;
  }
  if (targ.nodeType==3) // defeat Safari bug
  {
    targ = targ.parentNode;
  }
  var tname;
  tname=targ.tagName;

  return $(targ).closest('.mbcontainercontent').prev().text();
}
//shortcut function for whatWindow() to find which window a link was pressed inside
function ww(e)
{ return whatWindow(e); }


function logonView()
{
      //activate menus
  document.getElementById('menuAccount').style.display = '';
  document.getElementById('menuMail').style.display = '';
  document.getElementById('menuGames').style.display = '';


  
  //open welcome window  
  //loadWindow('welcome',' ','bxPopup');
  //Dont reload, this page loads x2 if loged in on refresh
  //loadWindow('news', 'bxContex' ,null, {title:'News'});
  
  //load user info on accounts menu
  $.get("lobby.php", { page: "get", get: 'userInfo'}, function(data2){
    document.getElementById('userInfo').innerHTML = data2;
  });
  
  //load user's games
  $.get("lobby.php", { page: "get", get: 'userGames'}, function(data2){
    document.getElementById('menu_games').innerHTML = data2;
  });
}

function pickgame(gameid)
{
    //show menus
    document.getElementById('menuNav').style.display = '';
    document.getElementById('menuDock').style.display = '';	
    
    //load game welcome page
    loadWindow('play','', 'id='+gameid);
    
    //load resource window
    if(isset(windows['bxOverview']))
        window.setTimeout("loadWindow('overview','bxOverview','view=general')", 1000);
    else
    {
        var w = makeWindow({id:'bxOverview', x:50, y:50, width:250, height:300});
        w.make();
        
        w.setBodyAjax('overview');
        w.setTitle('Overview');

        w.show();
    }
    
    //load facilities on submenu
    //updateFacilityMenu();
}

var windowHistory = new Object;

function loadWindow(page, winid, args, extraparams)
{ 
  page = (typeof (page) == "undefined")?'error':page;
  winid = (typeof (winid) == "undefined" || winid == '')?'bxContex':winid;

  //fetch window object
  var thiswindow = windows[winid];
  
  //check to see if this window was found
  if(!isset(thiswindow))
  {
 	 console.log('Window not found, creating...');
	 thiswindow = makeWindow({id:winid, x:50, y:50, width:250, height:300});
	 thiswindow.make();

  }

  //Sort out if there is any extra params to deal with
  if(isset(extraparams))
  {
    //Set title
    if(isset(extraparams.title))
    {
      if(isset(extraparams.browserTitle))
        thiswindow.setTitle(extraparams.title, extraparams.browserTitle);
      else
        thiswindow.setTitle(extraparams.title);
    }
    
  } //end of extra params
  
  
  thiswindow.setBodyAjax(page, args);
  thiswindow.show();
}

function getPageName(page)
{
  var title;
  switch(page)
  {
    case 'news': 	title='News'; break;
    case 'contact':	title="Contact Us"; break;	
    default: 		title=page;
  }
  
  return title;
}

function reloadOverview(view)
{
    //set body
    $('#bxPopup').mb_changeContainerContent('lobby.php', 'page=overview&view='+view);
}

function setHistory(winid, page)
{
  //dont save pages that submitted forms
  $('#'+winid).setHistory(page);
}

function submitLogin()
{
  //get username
  username = document.getElementById('uname').value;
  //get password
  password = document.getElementById('pword').value;
  
  $.get("lobby.php", { page: "login", uname: username, pword: password, submit: 'true' }, function(data){
    //login successful
    if(data == "1")
    { console.log(windows);
      //close login box
      windows['bxLogin2'].destroy();
      
      //refresh main window
      windows['bxContex'].historyRefresh();
      
      console.log(windows);
      logonView();
    }
    //problem with log in
    else
    {
      errorWindow(data);
    }
  });
}

function errorWindow(data)
{	
    //load resource window
    if(isset(windows['bxError']))
    {
        windows['bxError'].setBodyText(data);
        windows['bxError'].show();
    }
    else
    {
        var w = makeWindow({id:'bxError', x:100, y:100, width:250, height:300});
        w.make();
        

        w.setTitle('Error');

        w.show();
        w.setBodyText(data);
    }
//alert(data);
  
}

function submitReg()
{
  //get form data
  username = document.getElementById('uname').value;
  handle = document.getElementById('handle').value;
  email = document.getElementById('email').value;
  password = document.getElementById('pword').value;
  password2 = document.getElementById('pword2').value;
  
  if(password != password2)
  {
    document.getElementById('regError').innerHTML = "Passwords are not the same";
    document.getElementById('regError').style.display = "";
    return;
  }
  
  $.get("lobby.php", { page: "register", uname: username, pword: password,
        'handle': handle, 'email': email, submit: 'true' }, function(data){
    //reg successful
    if(data == "1")
    {
      loadWindow("register","done=true");
    }
    //reg failed
    else
    {
      document.getElementById('regError').innerHTML = data;
      document.getElementById('regError').style.display = "";
    }
  });
}

function logout()
{
  document.getElementById('menuAccount').style.display = 'none';
  document.getElementById('menuMail').style.display = 'none';
  document.getElementById('menuGames').style.display = 'none';	
  document.getElementById('menuNav').style.display = 'none';
  document.getElementById('menuDock').style.display = 'none';
  
  //loadWindow('logout');
  $.get("lobby.php", {page: "logout"});
}

function joingame(gameid)
{
  $.get("lobby.php", { page: "games", join: gameid}, function(data){
    //Joined game
    if(data == "1")
    {
      //show a notification window
      msg = "You have joined the game. This should appear in a nice window but im working on that.\nYour games list should have been refreshed. Join the game by going to the 'Games' menu and clicking on the game";
	  alert(msg);
      //document.getElementById('bxPopupContent').innerHTML = msg;
      //document.getElementById('bxPopupTitle').innerHTML = "Joined Game";
      
      
      //show window now
      //if($('#bxPopup').mb_getState('closed'))
      //    $('#bxPopup').mb_open();
      //if($('#bxPopup').mb_getState('iconized'))
      //    $('#bxPopup').mb_iconize();
      
      //reload games list on menu
      $.get("lobby.php", { page: "get", get: 'userGames'}, function(data2){
        document.getElementById('menu_games').innerHTML = data2;
      });
    }
    //failed
    else
    {
      errorWindow(data);
    }
  });
  
  //load user's games
  $.get("lobby.php", { page: "get", get: 'userGames'}, function(data){
    document.getElementById('submenu_mygames').innerHTML = data;
  });
}

function updateFacilityMenu()
{
  $.get("lobby.php", { page: "get", get: 'loadFacilityMenu'}, function(data){
    document.getElementById('submenu_buildings').innerHTML = data;
  });
}

function submitForm(fieldNames, args, winid, callback)
{
console.log('fields',fieldNames);
  //get fields
  fieldNames = (typeof (fieldNames) == "undefined")?'[]':fieldNames;
    
  //construct the args string to pass in request
  var argsStr = (typeof (args) == "undefined")?'':args+"&";  

  //argsStr += "submit=true";
  for(var i in fieldNames)
  {
    argsStr += "&"+fieldNames[i] + "=" +document.getElementById(fieldNames[i]).value;
    
    el = document.getElementById(fieldNames[i]);
    //get value from awkward elements
    if(el.tagName == "SELECT")	//<select>
    {
      if(el.selectedIndex < el.options.length && el.selectedIndex >= 0)
        args[fieldNames[i]] = el.options[el.selectedIndex].id;
    }
    //normal input field
    else
    {
      args[fieldNames[i]] =document.getElementById(fieldNames[i]).value;
    }
    document.getElementById(fieldNames[i]).value = "";
  }
  
  argsStr += "&submit=true";

  console.log("Args being posted are:",argsStr);  

  $.get("lobby.php", argsStr, function(data){
    if(data == "1")
    {
      //If a callback is set do callback function
	  if(callback && typeof(callback) === "function")
	  {
		//no params?
		callback();
	  }
	  else
	  {
      	alert('posted :-)'); 
	  }
    }
    else
    {
      errorWindow(data);
    }
    //document.getElementById('').innerHTML = data;
  });
}

function testwindows()
{}
