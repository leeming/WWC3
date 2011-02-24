<?php
if(isset($args['view']) && $args['view'] == "tree")
{
	require("research-tree.php");
}
else if(isset($args['view']) && $args['view'] == "queue")
{
	require("research-queue.php");
}
else
{
	?>
	<ul>
		<li><a href="#" onclick="loadWindow('research','view=tree')">View Research Tree</a></li>
		<li><a href="#" onclick="loadWindow('research','view=queue')">Queue Research</a></li>
	</ul>
	<?php
}
?>