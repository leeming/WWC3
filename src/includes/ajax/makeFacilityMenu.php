<a onclick="loadWindow('build','')">Build</a>
<a onclick="loadWindow('build','production=true')">Production</a>
<a rel="separator"> </a>
<?php

$facilities = new FacilityCollection();

//$userGames = $user->getGames();
//$facilities->player = $userGames[$_SESSION['game']];

$facilities = FacilityCollection::getGameSet($player->game);

foreach($facilities AS $facility)
{
	if(FacilityCollection::countPlayerFacilities($player,$facility->id) > 0)
	{
		?>
		<a onclick="loadwindow('build','id=<?=$facility->id?>')"><?=$facility->name?></a>
		<?php
	}
}
?>