<?php
//look to see if valid id given
if(!Validate::isInt($args['id']))
{
	print"Invalid Game ID";
	return;
}

//check to see if user in game
$gameList = $user->getGames();
if(!array_key_exists($args['id'],$gameList))
{
	print"User not in game";
	return;
}

//output some player data
$player = new Player($gameList[$args['id']]);
?>
<p>
	############<br>
	## Player Data ##<br>
	############<br>
	User : <?=$user->username?> (<?=$user->id?>)<br>
	Handle : <?=$user->handle?> (<?=$player->id?>)<br>
</p>

<p>
	###########<br>
	## Resources ##<br>
	###########<br>
	<?php
	$playerResources = $player->getResources();
	foreach($playerResources AS $r)
	{
		print $r->qty ." ".$r->name."<br>";
	}
	if(empty($playerResources))
	{
		print "No resources";
	}
	?>
</p>
