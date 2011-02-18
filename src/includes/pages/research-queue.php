<?php
$queue = new ResearchQueue($player);


if(isset($args['submit']))
{
	print (int)$queue->add(@$args['addQ']);
	die();
}
if(isset($args['remove']))
{
	$queue->remove($args['remove']);
}

$collection = new ResearchCollection($player->game);

if($queue->getSize() > 0)
{
$r = $queue->get(0);
$firstResearch = new Research($r['research']);

?>

<table border="1">
	<tr>
		<th>Current Research</th>
	</tr>
	
	<!-- Show position [0] on its own, rest later -->
	<tr>
		<td>
			Currently researching: <?=$firstResearch->name?>
		</td>
	</tr>
	<tr>
		<td>
			<?=$firstResearch->desc?>
		</td>
	</tr>
	<tr>
		<td>
			<?php
			$cyclesLeft = $firstResearch->getTicksLeft($r['ticksDone']);
			$percentCom = $firstResearch->getPercentDone($r['ticksDone']);
			?>
			Cycles Left to Complete: <?=$cyclesLeft?> (<?=$percentCom?>%)
		</td>
	</tr>
	
	<!-- Show player queue -->
	<tr>
		<td>
			<table border="1">
				<tr>
					<th>Position</th>
					<th>Research</th>
					<th>Percent Done</th>
					<th>Delete</th>
				</tr>
				
				<?php
				//Get player queue and remove first pos
				$r = $queue->get();
				unset($r[0]);
				
				foreach($r AS $res)
				{
					$obj = new Research($res['research']);
					
					?>
					<tr>
						<td><?=$res['pos']?></td>
						<td><?=$obj->name?></td>
						<td><?=$obj->getPercentDone($res['ticksDone'])?>%</td>
						<td>[<a href="#" onclick="loadWindow('research','view=queue&remove=<?=$obj->id?>');">Remove</a>]</td>
					</tr>	
					<?php
				}
				if(count($r) == 0)
				{
					print"<tr><td colspan='4'>You have no research in your queue</td></tr>";
				}
				?>
			</table>
		</td>
	</tr>
</table>

<?php
}
else
{
	print "No researches here";
}

if($queue->canResearch())
{ ?>
<form onsubmit="return false;">
<table border="1">
	<tr>
		<th>Add Research to Queue</th>
	</tr>
	<tr>
		<td>
			<select id="addQ">
				<?php
				$nextRes = $collection->getResearchable($player);
				foreach($nextRes AS $show)
				{
					$r = new Research($show);
					print"<option id='".$r->id."'>".$r->name."</option>";
				}
				?>
			</select>
			<input type="submit" value="Queue"  onclick="submitForm(['addQ'], {page: 'research', view: 'queue'}); " />
		</td>
	</tr>
	
</table>
</form>
<?php
}
?>