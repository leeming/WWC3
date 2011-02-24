<?php

//get list of user's games
$gamelist = $user->getGames();


foreach($gamelist AS $game => $playerId)
{
	$gameObj = GameInstance::getInstance($game);
	?>
	<a class="" onclick="pickgame(<?=$game?>)"><?=$gameObj->getName()?> #<?=$game?></a>
	<?php
}
if(count($gamelist) == 0)
{
	?>
	<a onclick="loadWindow('games','', 'list=notmy')">You are in no games</a>
	<?php
}
else
{
	//show separator if user has any games
	?>
	<a rel="separator"> </a>
	<?php
}
?>
  <a class="" onclick="loadWindow('games','', 'list=all')">Game List</a>
  <a class="" onclick="loadWindow('makeGame')">Create New Game</a>
