<?php
$bat = $player->getSelectedBattalion();
$loc = new Country($bat->location);
?>

<p>
	You can move to:
	<ul>
		<?php
		//get all borders to this country but only link moveable
		$borders = $loc->getBorders('obj',true);
		$firephp->log($borders, "Country Borders");
		
		$teamCountries = $player->team->countriesOwn('int');
		$firephp->log($teamCountries, "Team Countries");
		
		foreach($borders AS $border)
		{
			//does team own this country?
			if(in_array($border->id, $teamCountries))
			{
				?>
				<li><a href="#" onclick=""><?=$border->name?></a></li>
				<?php
			}
			else
			{
				?>
				<li><?=$border->name?></li>				
				<?php
			}
			
		}
		?>
	</ul>		
</p>