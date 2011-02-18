<?php
if(isset($args['submit']))
{
	$firephp->log($args);
	
	$response = $player->build(new Facility($args['id']), $args['loc'], $args['build'.$args['id']]);
	if($response === true)
	{
		print "1";
	}
	else if($response === false)
	{
		print"Facility could not be built";
	}
	else
	{
		print $response;
	}
	die();
}

$tmp = &$player->game;
$collection = FacilityCollection::getGameSet($tmp);

?>
<table border="2">
	<tr>
		<th>Facility Name</th>
		<th>Description</th>
		<th>Research Needed</th>
		<th>Cost</th>
		<th>Currrently Have</th>
		<th>Build</th>
	</tr>

<?php
$countries = $player->team->countriesOwn('obj');
foreach($collection AS $fac)
{
	?>
	<tr>
		<td><?=$fac->name?></td>
		<td><?=$fac->desc?></td>
		<td>
			<?php
			$recs = $fac->getPreReq("obj");
			foreach($recs AS $r)
			{
				print $r->name."<br>";
			}
			if(count($recs) == 0)
				print "None";
			?>
			
		</td>
		<td>
			<?php
			$res = $fac->getCost();
			foreach($res AS $r)
			{
				print $r->qty."x".$r->name."<br>";
			}
			?>			
		</td>
		<td>
			<?php
			//$counting = FacilityCollection::getAllPlayerFacilities($player, $fac);
			print FacilityCollection::countPlayerFacilities($player, $fac);
			?>
		</td>
		<td>
			<?php
			//check if user has research needed to build
			$buildable = true;
			$has = $player->getResearches('int', true);
			$firephp->log($has, 'Player has');
			$firephp->log($fac->getPreReq('int'), 'Pre Req');
			
			
			foreach($fac->getPreReq('int') AS $chk)
			{
				
				if(!in_array($chk, $has))
					$buildable = false;
			}
			
			$firephp->log($buildable);
			if($buildable)
			{
				?>
				<form onsubmit="return false;">
				<select id="loc">
					<?php
					foreach($countries AS $option)
						print "<option id=\"".$option->id."\">".$option->name."</option>";
					?>
				</select><br>
				<input id="build<?=$fac->id?>" type="text" size="3" />
				<input type="submit" value="Build" onclick="submitForm(['build<?=$fac->id?>','loc'],{page: 'build', id: <?=$fac->id?>}); reloadOverview('general');" />
				</form>
				<?php
			}
			else
			{
				print "Locked";
			}
			?>
			<!--[<a href="#" onclick="loadWindow('build','view=<?=$fac->id?>')">View</a>]-->
		</td>
	</tr>
	<?php
}
?>
</table>