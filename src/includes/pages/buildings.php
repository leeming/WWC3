<?php
//manage buildings
if(isset($args['production']))
{
	require("buildings-production.php");
}
//view single building
else if(isset($args['view']) && Validate::isInt($args['view']))
{
	print"view building...";
	require("buildings-view.php");
}
//else show all buildings
else
{
	require("buildings-all.php");
}
?>