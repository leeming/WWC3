<table width="100%">
	<tr>
		<th>Resources</th>
	</tr>
	
	<!-- Player's resources right now-->
	<?php
	//Gets what the player gets next cycle
	$next = $player->getResourcesPerCycle();
	
	foreach($player->getResources(true) AS $res)
	{
		?>
		<tr>
			<td>
				<span class="hasResource"><?=$res?></span>
				 (<span class="nextResource" title="Next turn">+<?=isset($next[$res->id])?$next[$res->id]->qty:0?></span>)
			</td>
		</tr>
		<?php
	}
	?>
</table>