<?php
if(isset($args['id']) && Validate::isInt($args['id']))
{
    $building = new Facility($args['id']);
    
    print "<h2>".ucfirst($building->name)."</h2><br>";
    print $building->desc."<br>";
    print "<hr>";
    
    print"Resources Needed<br>";
    $cost = $building->getCost();
    foreach($cost AS $c)
    {
        print $c . "<br>";
    }
    
    //List all research needed for this building
    ?>
    <hr>
    Research needed<br>
    <?php
    //check if user has research needed to build
	$buildable = true;
	
	//flag set if player has research req
	$bResearch = true;
	//flag 
	
	$has = $player->getResearches('int', true);
    
    $reqs = $building->getPreReq('obj');
    foreach($reqs AS $chk)
    {
        print $chk->name;
        //If user doesnt have this research show a cross
        if(!in_array($chk->id, $has))
        {
            print " <img src='img/cross-icon.gif' alt='[N]' width='10px' height='10px' title=\"You don't have this research\" />";
            $buildable = false;
        }
        else
        {
            print " <img src='img/tick-icon.png' alt='[Y]' width='10px' height='10px' title='You have this research' />";
        }
        print"<br>";
    }
    if(empty($reqs))
    {
        print "None";
    }
    ?>
    <hr>
    <?php

    //Show all of this building that you own
    $facs =$player->findFacilities($args['id']);
    $countries = $player->team->countriesOwn('obj');
    
    if(!empty($facs))
    {
        print"You have<br>";
        ?>
        <table>
            <tr>
                <th>Country</th>
                <th>Qty</th>
                <th></th>
            </tr>
        <?php
        foreach($facs AS $p)
        {
            ?>
            <tr>
                <td><?=$p['country']->name?></td>
                <td><?=$p['facility']->qty?></td>
                <td>
                    <?php
                    //Check if player owns country, if so show quick add
                    if(in_array($p['country'],$countries))
                    {    
                        ?>
                        [<a onclick="return false;" title="Build 1 More" href="#">+</a>]
                        [<a onclick="return false;" title="Build 5 More" href="#">++</a>]
                        [<a onclick="return false;" title="Build 10 More" href="#">+++</a>]
                        <?php
                    }
                    else
                    {
                        ?>
                        Enemy Owned
                        <?php
                        //$firephp->log($p['country'],"");
                        //$firephp->log($countries,"");
                        
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        </table>
        <?php
    }
    else
    {
        print "You dont have any of this building";
    }

    if($buildable)
    {
    ?>
    
    <form onsubmit="return false;">
        <select id="loc">
        <?php
        foreach($countries AS $option)
            print "<option id=\"".$option->id."\">".$option->name."</option>";
        ?>
        </select>
        <input id="qty" type="text" size="3" />
        <input type="submit" value="Build" onclick="submitForm(['qty','loc'],{page: 'build', id: <?=$building->id?>}); reloadOverview('general'); loadBuilding(<?=$building->id?>);" />
    </form>
    <?php
    }
}
?>
