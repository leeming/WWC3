<?php
//look to see if valid id given
if(!isset($args['id']) || !Validate::isInt($args['id']))
{
	$firephp->warn($args['id'], "Not a valid game id");
	print"Invalid Game ID";
	return;
}

//check to see if user in game
$gameList = $user->getGames();
if(!array_key_exists($args['id'],$gameList))
{
	$firephp->warn($args['id'], "Not in game");
	print"User not in game";
	return;
}

//empty out game cache
unset($_SESSION['singletons']);

//output some player data
$player = Player::getInstance($gameList[$args['id']]);
$_SESSION['curGame'] = $args['id'];
$_SESSION['curPlayer'] = $gameList[$args['id']];

?>
Next cycle you will get...

<?php
//foreach($player->getResourcesPerCycle() AS $r)
//	print $r->name ." x".$r->qty."<br>";

if($player->game->isCycle(time()))
	print "Should do cycle";
else
	print $player->game->timeTillNextCycle();
?>
<!--
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

<p>
	##########<br>
	## Facilities  ##<br>
	##########<br>
	<?php
	$collection = new FacilityCollection();
	$collection->player = $player->id;
	
	$playerFac =  $collection->get();
	foreach($playerFac AS $country => $fac)
	{
		$c = new Country($country);
		print $fac." in {$c->name}<br>";
	}
	
	/*
	$playerFac = $player->getFacilities();
	foreach($playerFac AS $country => $fac)
	{
		$c = new Country($country);
		print $fac."({$c->name})<br>";
	}
	*/
	if(empty($playerFac))
	{
		print "No facilities";
	}
	?>
</p>

<p>
	##########<br>
	## Research  ##<br>
	##########<br>
	
	<?php
	$research =  $player->getResearches('obj');
	foreach($research AS $r)
	{
		print $r->name."<br>";
	}
	
	if(empty($research))
	{
		print "No researches";
	}	
	?>
</p>

<p>
	###########<br>
	## Battalions  ##<br>
	###########<br>
	
	<?php
	$batts = $player->getBattalions();
	foreach($batts AS $b)
	{
		print $b->name."->".$b->commander."(".$b->exp.")<br>";
		print "Contains: <ul>";
		
		foreach($b->getUnits() AS $unit)
		{
			print"<li>".$unit->qty." ".$unit->name."</li>";
		}
		
		print"</ul><br>";
	}
	?>
	
</p>
-->