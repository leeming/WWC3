<?php
if($user->id != 1)
{
	print"Sorry Leeming only area...";
	return;
}

$games = GameCollection::getAllGames();

if(isset($args['new']) && Validate::isInt($args['new']))
{
	print "Making new instance";
	
	GameInstance::add(array('game_id' => $args['new'], 'start' => time()+(5*60)));
}
else if(isset($args['end']) && Validate::isInt($args['end']))
{
	print "Ending game instance";
	GameInstance::getInstance($args['end'])->end();
}
else if(isset($args['restart']) && Validate::isInt($args['restart']))
{
	print "Restart instance";
}

print"<u>Current Games</u><br>";
foreach($games AS $game)
{
	print $game->name." (".$game->id .")";
	print"[<a href='#' onclick=\"loadWindow('makeGame','new={$game->id}')\">New</a>]<br>";
	
	$instances = GameCollection::getRunningInstancesOf($game);
	foreach($instances AS $in)
	{
		print " |-- #".$in->id;
		print"[<a href='#' onclick=\"loadWindow('makeGame','end={$in->id}')\">End</a>]";
		print"[<a href='#' onclick=\"loadWindow('makeGame','restart={$in->id}')\">Restart</a>]<br>";
	}
}
?>
