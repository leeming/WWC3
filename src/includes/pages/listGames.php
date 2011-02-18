<?php
//If user wants to join game
if(isset($args['join']))
{
	$game = GameInstance::getInstance($args['join']);
	
	//try to add user to game
	$result = $game->join($user);
	if(!Validate::isInt($result))
	{
		//failed to join game
		print $result;
	}
	else
	{
		print "1";
	}
	return;
}




if(isset($args['list']))
{
	$gameList = array();
	
	if($args['list'] == "all")
	{
		$gameList = GameCollection::getRunningGameInstances();
	}
	else if($args['list'] == "my")
	{
		$userGames = $user->getGames();
		foreach($userGames AS $gameid => $playerid)
		{
			$gameList[] = GameInstance::getInstance($gameid);
		}
	}
	else if($args['list'] == "notin")
	{
		$allGames = GameCollection::getRunningGameInstances();
		$userGames = $user->getGames();
		foreach($allGames AS $game)
		{
			//user is not in this game
			if(!array_key_exists($game->id, $playerGames))
			{
				$gameList[] = &$game;
			}
		}
	}
	
	
	?>	
	<table border="2px">
		<tr>
			<th>#</th>
			<th>Game Name</th>
			<th>Description</th>
			<th>Setting Used</th>
			<th>Current Cycle<br>Started At</th>
			<th></th>
			<th></th>
		</tr>
				
	<?php
	
	foreach($gameList AS $game)
	{
		?>
		<tr>
			<td><?=$game->id?></td>
			<td><?=$game->getName()?></td>
			<td><?=$game->getDesc()?></td>
			<td><?=$game->getSettingsName()?></td>
			<td align="center">
				Cycle <?=$game->cycleNumber()?><br>
				<?php
				if($game->hasStarted())
					print "Started @".date("d-m H:i:s",$game->getStartTime());
				else	//not started yet
					print "Starting in ".Convert::secondsToReadable($game->timeUntilStart());
				?>
			</td>
			<td><a href='#' onclick="loadWindow('games','view=<?=$game->id?>')">View</a></td>
			<td>
				<?php
				//if user is in game show "enter", else show "join"
				$userGames = $user->getGames();
				if(array_key_exists($game->id, $userGames))
				{
					?>
					<a href='#' onclick="pickgame(<?=$game->id?>)">Enter</a>
					<?php
				}
				else
				{
					?>
					<a href='#' onclick="joingame(<?=$game->id?>)">Join</a>
					<?php
				}
				?>
			</td>
		</tr>
		<?php
	}
}
else if(Validate::isInt($args['view']) && false)
{
	?>
	<a href='#' onclick="loadWindow('games','list=all')">Back</a>
	<?php
	
	$game = new Game($args['view']);
	
	if($game->canJoinGame($user))
	{
		?>
		<a href="?page=games&join=<?=$game->id?>">Join Game</a>
		<?php
	}
	?>
	<table>
		<tr>
			<th colspan="2">Game Details</th>
		</tr>
		<tr>
			<td>
				<table>
					<tr>
						<td>ID</td>
						<td><?=$game->id?></td>
					</tr>
					<tr>
						<td>Name</td>
						<td><?=$game->name?></td>
					</tr>
					<tr>
						<td>Desc</td>
						<td><?=$game->desc?></td>
					</tr>
					<tr>
						<td>Game Started</td>
						<td><?=date("d-m-Y H:i:s",$game->startTimestamp)?></td>
					</tr>
					<tr>
						<td>Can join</td>
						<td><?=Convert::boolToYesNo($game->canJoinGame($user))?></td>
					</tr>
					<tr>
						<td>Private</td>
						<td><?=Convert::boolToYesNo($game->isPrivate)?></td>
					</tr>
				</table>
			</td>
			<td>
				<table>
					<tr>
						<th colspan="2">Settings</th
					</tr>
					<tr>
						<td>ID</td>
						<td><?=$game->settings->id?></td>
					</tr>
					<tr>
						<td>Name</td>
						<td><?=$game->settings->name?></td>
					</tr>
					<tr>
						<td>Desc</td>
						<td><?=$game->settings->desc?></td>
					</tr>
					<tr>
						<td>Max players</td>
						<td><?=$game->settings->maxPlayers?></td>
					</tr>
					<tr>
						<td>Generals</td>
						<td><?=Convert::boolToYesNo($game->settings->generals)?></td>
					</tr>
					<tr>
						<td>Team Gov</td>
						<td><?=Convert::boolToYesNo($game->settings->teamGovernments)?></td>
					</tr>
					<tr>
						<td>Player Gov</td>
						<td><?=Convert::boolToYesNo($game->settings->playerGovernments)?></td>
					</tr>
					<tr>
						<td>Treaties</td>
						<td><?=Convert::boolToYesNo($game->settings->treaties)?></td>
					</tr>
					<tr>
						<td>Disasters</td>
						<td><?=Convert::boolToYesNo($game->settings->naturalDisasters)?></td>
					</tr>
					<tr>
						<td>Milta</td>
						<td><?=$game->settings->militaGrowth?></td>
					</tr>
					<tr>
						<td>Late Entry</td>
						<td><?=Convert::secondsToReadable($game->settings->lateEntryTime)?></td>
					</tr>
					<tr>
						<td>Cycle Time</td>
						<td><?=$game->settings->cycleTime?></td>
					</tr>
					<tr>
						<td>Assimilation Rate</td>
						<td><?=$game->settings->assimRate?></td>
					</tr>
				</table>				
			</td>
		</tr>
	</table>
	<table>
		<tr>
			<th>Players in Game</th>
		</tr>
		<tr>
			<td>
			<?php
			foreach($game->getPlayerList('object') AS $player)
			{
				print $player->user->handle;
				print"<br>";
			}
			?>
			</td>
		</tr>
	</table>
	<?php
}

?>
</p>
