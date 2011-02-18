//Array of all the windows existing
var windows = new Array();
var windowMappings = new Array();

function WWCWindow(id)
{
    this.id = id;
    
    //defaults
    this.title = "";
    this.browserTitle = false;
    this.x = 50;
    this.y = 80;
    this.width = 500;
    this.height = 300;
    this.history = new Array();
    //this.history2 = new ArrayList();
    this.historyIndex = 0;
    this.windowIndex = 0;
    this.buttons = 'm,i,r';
    
    //methods
    this.make = make;
    this.setTitle = setTitle;
    this.setSize = setSize;
    this.setPos = setPos;
    this.show = show;
    this.setBodyText = setBodyText;
    this.setBodyAjax = setBodyAjax;
    this.historyForward = historyForward;
    this.historyBack = historyBack;
    this.historyRefresh = historyRefresh;
    this.rebuild = rebuild;
    this.destroy = destroy;
}

function make()
{
    var newdiv = document.createElement('div');
    
    newdiv.setAttribute('id', this.id);
    newdiv.setAttribute('class', "containerPlus draggable resizable {buttons:'"+this.buttons+"', skin:'black', height:'"+this.height+"', width:'"+this.width+"',closed:'false', winindex:"+windows.length+", rememberMe:true}");
    newdiv.setAttribute('style', "top:"+this.y+"px;left:"+this.x+"px");
    newdiv.innerHTML = "<div class=\"no\"><div class=\"ne\"><div class=\"n\"><a id=\""+this.id+"Title\">"+this.title+"</a></div></div>"+
          "<div class=\"o\"><div class=\"e\"><div class=\"c\">"+
            "<div class=\"winidtext\">"+this.id+"</div>"+
            "<div class=\"mbcontainercontent\" id=\""+this.id+"Content\">No content loaded</div>"+
          "</div></div></div>"+
          "<div >"+
            "<div class=\"so\"><div class=\"se\"><div class=\"s\"> </div></div></div>"+
          "</div>"+
        "</div>";
    
    //document.body.appendChild(newdiv);
    var par = document.getElementById('dockwrapper');
    par.appendChild(newdiv);//   add(document.getElementById(this.id));
    
    
    $(".containerPlus").buildContainers({
        containment:"document",
        elementsPath:"ui/elements/"
    });
    
    //Setup refresh button and bind to this object's history
    var par = this;
    
    $(".containerPlus").find(".reloadContainer:first").bind("click", function(){
        //Get current page and reload it
        var p = par.history[par.historyIndex-1];
        par.setBodyAjax(p[0],p[1]);
    });
}

function rebuild()
{
    $(".containerPlus").buildContainers({
        containment:"document",
        elementsPath:"ui/elements/"
    });
}

function setTitle(title, updateBrowser)
{
    this.title = title;
    this.browserTitle = updateBrowser;
    
    if(this.browserTitle)
      document.title = title+" - WWC3";
      
    document.getElementById(this.id+"Title").innerHTML = this.title;
}

function setBodyText(text)
{
    $('#'+this.id+"Content").html(text);
}

function setBodyAjax(page, args, halthistory)
{
    args = (!isset(args) || args == '')?'':'&'+args;
    
    $('#'+this.id).innerHTML = "LOADING...";
    $('#'+this.id).mb_changeContainerContent('lobby.php', 'page='+page+args);
    
    //Do history
    if(typeof (halthistory) == "undefined")
    {
        //check to see if currently on 'last' page of history
        if(this.history.length == this.historyIndex)
        {
            this.history[this.historyIndex++] = new Array(page,args,this.title);
            
            //this.history = {page,args,this.title};
        }
        //else not last so after current index clear it
        else
        {}
    }
}

function setPos(x,y)
{
    this.x = x;
    this.y = y;
}

function setSize(width, height)
{
    this.width = width;
    this.height = height;
}

function show()
{
    //show window now
    if($('#'+this.id).mb_getState('closed'))
        $('#'+this.id).mb_open();
    if($('#'+this.id).mb_getState('iconized'))
        $('#'+this.id).mb_iconize();
        
        
    //raise window to focus
    $('#'+this.id).mb_BringToFront();
}

function historyForward()
{}
function historyBack()
{}
function historyRefresh()
{ 
    //Get current page and reload it
    var p = this.history[this.historyIndex-1];
    this.setBodyAjax(p[0],p[1], true);
}

function destroy()
{
    windows[this.id] = null;
    //$('#'+this.id).mb_close();
    var thisel = document.getElementById(this.id);
    thisel.parentNode.removeChild(thisel);
}

WWCWindow.prototype.make = make;
WWCWindow.prototype.setTitle = setTitle;
WWCWindow.prototype.setSize = setSize;
WWCWindow.prototype.setPos = setPos;
WWCWindow.prototype.show = show;
WWCWindow.prototype.setBodyText = setBodyText;
WWCWindow.prototype.setBodyAjax = setBodyAjax;
WWCWindow.prototype.historyForward = historyForward;
WWCWindow.prototype.historyBack = historyBack;
WWCWindow.prototype.historyRefresh = historyRefresh;
WWCWindow.prototype.rebuild = rebuild;
WWCWindow.prototype.destroy = destroy;



function makeWindow(params)
{
    //Check if id is set, if not use default one
    if(params && typeof( params.id ) != "undefined" )
        id = params.id;
    else
        id = "bx"+windows.length;
    
    var w = new WWCWindow(id);
    
    //check additional params
    if(isset(params))
    {
        //Set window title
        if(params && typeof( params.title ) != "undefined" )
        {
            //update global browser title
            if( typeof( params.browserTitle ) != "undefined" && params.browserTitle == true )
                w.setTitle(params.title, true);
            else
                w.setTitle(params.title);
        }
        
        //position (x,y)
        if(isset(params.x) && isset(params.y))
            w.setPos(params.x, params.y);
        //dimentions
        if(isset(params.width) && isset(params.height))
            w.setSize(params.width, params.height);
        
        
        //Set what buttons are set
        if(isset(params) && isset(params.buttons))
        {
            w.buttons = params.buttons;
        }
    }
    
    
    //windows.push(w);
    windows[id] = w;
    return w;
}

function testwindow()
{
    /*
    var w = new WWCWindow('testid');
    w.setTitle("New window title",true);
    w.make();
    w.setBodyAjax("news");
    */
    var w2 = makeWindow({title: 'title', browserTitle: true});
    
    
    w2.setBodyAjax("news");
}
