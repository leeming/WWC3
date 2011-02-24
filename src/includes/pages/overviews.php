<div class="tabs">
<a href="" onclick="loadWindow('overview',ww(event), 'view=general'); return false;">General</a> |
<a href="" onclick="loadWindow('overview',ww(event),'view=resources'); return false;">Resources</a> |
<a href="" onclick="loadWindow('overview',ww(event),'view=army'); return false;">Army</a>
</div>
<?php
if(isset($args['view']) && $args['view'] == "general")
	require("overview-general.php");
else if(isset($args['view']) && $args['view'] == "resources")
	require("overview-resources.php");
else if(isset($args['view']) && $args['view'] == "army")
	require("overview-army.php");

?>
