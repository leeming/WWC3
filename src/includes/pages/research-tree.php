<?php
$collection = new ResearchCollection($player->game);

//sub tree
if(isset($args['id']) && Validate::isInt($args['id']))
{
	?>
	<p>[<a href="#" onclick="loadWindow('research',ww(event), 'view=tree')">Back to full tree</a>]</p>
	<?php
	$tree = $collection->getSubTree($args['id']);
}
//full tree
else
{
	$tree = $collection->get();
	$firephp->log($tree);
}
?>
<table border="1">
	<tr>
		<th>Name</th>
		<th>Description</th>
		<th>Dependendencies</th>
	</tr>
	
	<?php
	$have = $player->getResearches('int');
	$queued = new ResearchQueue($player);
	
	$colHave = "background-color: #555;";
	$colQue = "background-color: #F00;";
	$colNext = "background-color: #0F0;";
	$colNon = "";
	
	foreach($tree AS $item)
	{
		if(in_array($item->id, $have))
			$style = $colHave;
		elseif($queued->in_array($item->id))
			$style = $colQue;
		elseif(in_array($item->id, $collection->getResearchable($player)))
			$style = $colNext;
		else
			$style = $colNon;
		
		?>
		<tr>
			<td style="<?=$style?>"><a href="#" onclick="loadWindow('research',ww(event), 'view=tree&id=<?=$item->id?>')">
				<?=$item->name?></a>
			</td>
			<td style="<?=$style?>"><?=$item->desc?></td>
			<td style="<?=$style?>">
				<?php
				$depend = $item->getPreReq("obj");
				foreach($depend AS $dep)
				{
					print $dep->name."<br>";
				}
				if(count($depend) == 0)
				{
					print "None";
				}
				?>
			</td>
		</tr>	
		<?php
	}
	?>
</table>

<table>
	<tr>
		<td style="<?=$colHave?>">Done</td>
		<td style="<?=$colQue?>">Queued</td>
		<td style="<?=$colNext?>">Researchable</td>
		
	</tr>
</table>
