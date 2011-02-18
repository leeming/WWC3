<?php
$battalion = $player->getSelectedBattalion();

if(isset($args['action']))
{
	if($args['action'] == "move" && Validate::isInt($args['id']))
	{
		$moveTo = CountryInstance::getInstance($player->getGameId(), $args['id']);
		if($battalion->move($moveTo))
		{
			//update location
			//moved under this if block
		}
		else
		{
			print"Failed to move";
		}
	}
	else if($args['action'] == "att")
	{
		//print"Attack not implemented yet";
		$newLoc = CountryInstance::getInstance($player->getGameId(), $args['id']);
		$present = $newLoc->armiesPresent($player->game);
		
		//check if enemies here
		if(count($present) == 0)	//no
		{
			$att = $newLoc->attackMilita($battalion);
			if(is_string($att))
				print $att;
			else
			{
				print $att->battleMsg();
			}
		}
		else	//enemies here
		{
			print "enemies";
		}
	}
	else if($args['action'] == "raid")
	{
		print"Air raid not added either";
	}
	else if($args['action'] == "spy")
	{
		print"nope no spies yet";
	}
}
$location = $battalion->getLocation();
?>
	
<p>
You are currently in <b><?=$location->name?></b>
</p>
	
	<b>Move</b>
	<ul>
		<?php
		$moveBorders = $location->getMoveBorders($player->team);
		foreach($moveBorders AS $border)
		{
			?>
			<li><a href="#" onclick="loadWindow('actions','action=move&id=<?=$border?>'); loadWindow('overview','view=general','bxPopup');"><?=Country::name($border)?></a></li>
			<?php
		}
		if(count($moveBorders) == 0)
		{
			print"<li>None</li>";
		}
		?>
	</ul>
	
	<b>Attack</b>
	<ul>
		<?php
		$attBorders = $location->getAttackBorders($player->team);
		foreach($attBorders AS $border)
		{
			?>
			<li><a href="#" onclick="loadWindow('actions','action=att&id=<?=$border?>')"><?=Country::name($border)?></a></li>
			<?php
		}
		if(count($attBorders) == 0)
		{
			print"<li>None</li>";
		}
		?>
	</ul>
	
	<p></p>
	<!--
	<ul>
		<li><a href="#" onclick="loadWindow('actions', 'action=move');">Move</a></li>
		<li><a href="#" onclick="loadWindow('actions', 'action=attack');">Attack</a></li>
		<li><a href="#" onclick="loadWindow('actions', 'action=spy');">Spy</a></li>
		<li><a href="#" onclick="loadWindow('actions', 'action=raid');">Air Raid</a></li>
	</ul> -->
