<?php
if(!isset($args))
{
	$errorMsg = "No error message set... doh!";
}
else
{
	switch($args['code'])
	{
		case 'PAGE_NOT_ALLOWED':
			$errorMsg = "You have tried to access a page that does not exist.";
			break;
		case 'PAGE_NOT_ADDED':
			$errorMsg = "You have tried to access a page Leeming forgot to add.";
			break;
		case 'NOT_LOGGED_IN':
			$errorMsg = "You need to be logged in to see this page. If you were "
				."previously logged in, then you may have been timed out.";
			break;
		case 'INVALID_SESSION':
			$errorMsg = "You login session has caused a fault, you have been logged out";
			break;
		case 'GAME_NOT_SET':
			$errorMsg = "You can not view game pages when you have not entered a game";
			break;
		case 'B':
			$errorMsg = "";
			break;
		case 'ADMIN_ONLY':
			$errorMsg = "Naughty naughty! This is a restricted area";
			break;
		default:
			$errorMsg = "Undefined error code '{$args['code']}'";
	}
}
?>

<table border="2px">
	<tr>
		<th style="background-color:red;">
			There has been a loading error...
		</th>
	</tr>
	<tr>
		<td><?=$errorMsg?></td>
	</tr>
	<tr>
		<td>
			<pre><?php
			print"BACKTRACE\n";
			print_r(debug_backtrace());
			print"\n\nREQUEST\n";
			print_r($_REQUEST);
			print"\n\nTRACKER\n";
			print_r($_SESSION['_track']);
			?>
			</pre>
		</td>
	</tr>
</table>