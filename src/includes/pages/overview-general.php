<table>
	<tr>
		<th>Game</th>
	</tr>
	<tr>
		<td><?=$player->game->getName()?></td>
	</tr>
	<tr>
		<td>Cycle #<?=$player->game->cycleNumber()?></td>
	</tr>
	<tr>
		<td><?=$player->team->getName()?></td>
	</tr>
	
	
	<tr>
		<th>Resources</th>
	</tr>
	<?php
	foreach($player->getResources(true) AS $res)
	{
		?>
		<tr>
			<td><?=$res->qty?> <?=$res->name?></td>
		</tr>
		<?php
	}
	if(count($player->getResources()) == 0)
	    print"<tr><td><i>No Resources???<br>You should have some</i></td></tr>";
	?>
	
	<tr>
		<th>Battalion(s)</th>
	</tr>
	<tr>
		<td>
		<?php
		$sel = $player->getSelectedBattalion();
		print $sel->name." lvl".$sel->getLevel()." (".$sel->location->name.")";
		?>
		</td>
	</tr>
	<tr>
		<th>Research</th>
	</tr>
	<?php
	
	$queue = new ResearchQueue($player);
	$activeResearch = $queue->get(0);
	
	//no research?
	if(empty($activeResearch))
	{
		?>
		<tr>
			<td>No research</td>
		</tr>
		<?php
	}
	else
	{
		$res = new Research($activeResearch['research']);
		?>
		<tr>
			<td><?=$res->name?> - <?=$res->getPercentDone($activeResearch['ticksDone'])?>%</td>
		</tr>
		<tr>
			<td> [Percent bar here]</td>
		</tr>
		<?php
	}
	?>
</table>
