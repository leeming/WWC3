<?php
if(isset($args['submit']))
{
	#submit stuff here
}
else
{
	//list all user's battaltions
	if($player->getNumBattalions == $player->maxBattalions())
	{
		print"<p>You are currently at the maximum number of battalions</p>";
	}
	else
	{
		print"<p>You are only using ".$player->getNumBattalions()." out of ".
			$player->maxBattalions()." battalions</p>";
	}
	?>
	<div class="battalionContainer">
		<?php
		foreach($player->getBattalions() AS $bat)
		{
			?>
			<table border="1">
				<tr>
					<th colspan="2"><?=$bat->name?></th>
				</tr>
				<tr>
					<td>Level</td>
					<td><?=$bat->getLevel()?></td>
				</tr>
				<tr>
					<td>Exp</td>
					<td><?=$bat->exp?>/<?=$bat->nextLevelExp()?></td>
				</tr>
				<tr>
					<td>Location</td>
					<td><?=Country::name($bat->location)?></td>
				</tr>
				
				<tr>
					<td>Units</td>
					<td>
						<ul>
						<?php
						foreach($bat->getUnits() AS $units)
						{
							print"<li>".number_format($units->qty)." ".$units->name."</li>";
						}
						?>
						</ul>
					</td>
				</tr>
				
				<tr>
					<td colspan="2">
						<a href="#" onclick="loadWindow('battalions','activate=<?=$bat->id?>')">Select</a>
					</td>
				</tr>
				
				
			</table>
			<?php
		}
		?>
	</div>
	<?php
	if($player->getNumBattalions < $player->maxBattalions())
	{
		?>
		<form method="post">
		<div class="form">
			<div class="row">
				<span class="tableHeader">Add Battalion</span>
			</div>
			
			<div class="row">
				<span class="label">Name</span>
				<span class="data">
					<input type="text" id="batName" />
				</span>
			</div>
			<div class="row">
				<span class="label">Location</span>
				<span class="data">
					<select id="batLoc">
						<?php
						foreach($player->team->countriesOwned() as $c)
						{
							?>
							<option value="<?=$c->id?>"><?=$c->name?></option>
							<?php
						}
						?>
					</select>
				</span>
			</div>
			<div class="row">
				<span class="wide">
					<input type="submit" value="Add" onclick="submitForm(['batName','batLoc'], {page: 'battalions'}); return false;" />
				</span>
			</div>
		</div>
		</form>
		<?php
	}
	
}
?>