<script type="text/javascript">
function setActiveBattalion(id)
{
	//Set players active battalion to this one
	$.get("lobby.php", { page: "get", get: 'activeBat', batid: id}, function(data2){
		//reload window
		loadWindow('overview','view=army','bxPopup');
	});
}
function expandTroops(id)
{ alert(document.getElementById('expandTr'+id).innerHTML);
document.getElementById('expandTr'+id).innerHTML = "-";

	//document.getElementById('expandTr'+id).style.display = 'none';
	//document.getElementById('hideTa'+id).style.display = '';
}
function expandTanks(id)
{

	document.getElementById('expandTa'+id).style.display = 'none';
	document.getElementById('hideTa'+id).style.display = '';
}
function hideTroops(id)
{}
function hideTanks(id)
{}
</script>

<style>
.battalionContainer
{
	border: 1px solid #ddd;
	padding: 2px;
	margin-top:10px;
}

.activeBat
{
	border 2px solid #ddd;
	background-color: #d11;
}

.battalionContainer span
{
	margin-left: 5px;
	display:block;
}

.battalionContainer span.bname
{
	size: 1.0em;
	font-weight: bold;
}
.battalionContainer img
{
	display:inline;	
	float:left;
	margin-right: 5px;
}

a.showmore
{
	text-decoration: none;
}

a.showless
{
	text-decoration: none;
	display: none;
}

div.activebat
{
	width:10px;
	height:10px;
	float: right;
	background-color: #DDD;
	-moz-border-radius:5px;
}
</style>

<?php
$b = $player->getBattalions();

foreach($b AS $bat)
{
	$troops = $bat->getTroops(true);
	$tanks = $bat->getTanks();
	
	$troopCount = 0;
	foreach($troops AS $t)
		$troopCount += $t->qty;
	$tankCount = 0;
	foreach($tanks AS $t)
		$tankCount += $t->qty;
	
	?>
	<div class="battalionContainer<?=($bat->equals($player->getSelectedBattalion())?" activeBat":"")?>">
		<img alt="[Commander]" src="<?=$bat->getImg()?>" width="85px" height="85px" />
		<div class="activebat" onclick="setActiveBattalion(<?=$bat->id?>)" title="Click to select this battalion"></div>
		<span class="bname"><?=$bat->name?></span>
		<span>Location: <?=$bat->location->name?></span>
		<span>Lvl: <?=$bat->getLevel()?></span>
		<span>EXP: <?=$bat->exp?>/<?=$bat->nextLevelExp()?></span>
		<span>
			Troops: <?=$troopCount?>
			<a id="expandTr<?=$bat->id?>" class="showmore" href="#" onclick="expandTroops(<?=$bat->id?>); return false;">[+]</a>
			<a id="hideTr<?=$bat->id?>" class="showless" href="#" onclick="hideTroops(<?=$bat->id?>); return false;">[-]</a>
		</span>
		<span>
			Tanks: <?=$tankCount?>
			<a id="expandTa<?=$bat->id?>" class="showmore" href="" onclick="expandTanks(<?=$bat->id?>); return false;">[+]</a>
			<a id="hideTa<?=$bat->id?>" class="showless" href="" onclick="hideTanks(<?=$bat->id?>); return false;">[-]</a>
		</span>
	</div>
	<?php
}

?>