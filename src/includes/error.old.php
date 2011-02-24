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
		case 'A':
			$errorMsg = "";
			break;
		case 'B':
			$errorMsg = "";
			break;
		default:
			$errorMsg = "Undefined error code '{$args['code']}'";
	}
}
?>

<table border="2px">
	<tr>
		<th style="background-color:red;">
			Loading Error
		</th>
	</tr>
	<tr>
		<td><?=$errorMsg?></td>
	</tr>
	<tr>
		<td>
			<pre><?php
			print_r(debug_backtrace());
			print"\n\nREQUEST\n";
			print_r($_REQUEST);
			print"\n\ntest\n";
			print_r($args);
			?>
			</pre>
		</td>
	</tr>
</table>