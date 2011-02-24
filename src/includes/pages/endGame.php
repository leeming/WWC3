<?php
$game = new Game($args['id']);

if($args['restart'])
{
	print"Restarting....";
	$game->restart();
}
else
{
	print"Ending....";
	$game->end();
}

print mysql_error();
print"<br>Game (hopefully) restarted<br><br>";
?>