<a href="?page=games&list=all">All</a> | <a href="?page=games&list=my">My</a> |
<a href="?page=games&list=notmy">Not In</a>

<p>
<?php
require("../game_maker/functions.php");
require_once("../classes/Validate.class.php");
require_once("../classes/Game.class.php");
require_once("../classes/Convert.class.php");

$gameList = getGames();

if($args['list'] == "my")
{
	
}
else if($args['list'] == "notmy")
{
	
} 
else if($args['list'] == "all") 
{
	foreach($gameList AS $game)
	{
		print $game->name."(#".$game->id.") <a href='?page=games&view={$game->id}'>View</a><br>";
	}
}
else if(Validate::isInt($args['view']))
{
	$game = new Game($args['view']);
	
	if($game->canJoinGame($user))
	{
		?>
		<a href="?page=join&id=<?=$game->id?>">Join Game</a>
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
				print $player->name;
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
