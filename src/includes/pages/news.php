<?php
//Show single news article
if(isset($args['view']) && Validate::isInt($args['view']))
{
	require("news-view.php");
}
//Show 'all' (paged) news
else
{
	require("news-all.php");
}
?>