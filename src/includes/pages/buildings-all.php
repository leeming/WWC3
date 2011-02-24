<?php
if(isset($args['submit']))
{
	$firephp->log($args);
	
	$response = $player->build(new Facility($args['id']), $args['loc'], $args['qty']);
	if($response === true)
	{
		print "1";
	}
	else if($response === false)
	{
		print"Facility could not be built";
	}
	else
	{
		print $response;
	}
	die();
}
?>

<script type="text/javascript">
function loadBuilding(id)
{
  //load user's games
  $.get("lobby.php", { page: "get", get: 'buildInfo', id: id}, function(data){
    document.getElementById('buildingInnerContent').innerHTML = data;
  });	
}
</script>

<style>
body
{
    font-family: Arial, Arial, Helvetica, sans-serif;
}

#menuContainer
{
    width: 150px;
    background-color:#111;
	border: 1px ridge #fff;
    float:left;
	padding-bottom: 10px;
}
#contentContainer
{
    width: 100%;
    
}
#buildingInnerContent
{
    margin-left:160px;
    
}

.buildingCell
{
    height: 30px;
}
div.buildingCell:hover
{
    color: #777;
    text-decoration: underline;
    cursor: pointer;
}

.buildingCell img
{
    position: absolute;
    left: 25px;
}

.buildingCell h1
{
    font-size: 1em;
    margin-left:45px;
    padding-top: 8px;
}

.buildingCell span
{
}
</style>



<div id="menuContainer">
<?php
$tmp = &$player->game;
$collection = FacilityCollection::getGameSet($tmp);

foreach($collection AS $b)
{
    ?>
    <div class="buildingCell" onclick="loadBuilding(<?=$b->id?>)">
        <img alt="[build]" title="<?=ucfirst($b->name)?>" src="ui/images/build.gif" width="35px" height="35px" /> 
        <h1><?=ucfirst($b->name)?></h1>
    </div>
    
    <?php
}
?>
</div>
<div id="contentContainer">
    <div id="buildingInnerContent">
    &lt;- Click the building on the building on the left to see more info
    </div>
</div>
