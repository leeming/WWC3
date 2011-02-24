<?php
if(!isset($user) || !$user->isAdmin())
{
    print 'Admin only. Didnt this go thru lobby?';
    return;
}

if(!isset($player))
{
    print "You are not currently in a game. Please join one so that you can manage it";
    return;
}

if(!isset($args['id']) || !Validate::isInt($args['id']))
{
    print "Invalid Team ID passed";
    return;
}

$team = new TeamInstance($args['id']);

if(isset($args['forceTurn']))
{

}

?>
Name: <?=$team->getName()?><br>
Colour: <?=$team->getColour()?><br>
Capital: <?=Country::name($team->getCapital())?><br><br>

Players in Team<br>

<?php
foreach($team->getPlayers() AS $p)
{
    print $p->getHandle()."<br>";
}
?>


<br>Countries Owned<br>

<?php
foreach($team->countriesOwn() AS $cid)
{
    print Country::name($cid)."<br>";
}
?>
