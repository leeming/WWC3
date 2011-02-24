<?php
if(!isset($user) || !$user->isAdmin())
{
    print 'Admin only. Didnt this go thru lobby?';
    return;
}

if(!isset($player))
{
    print "You are not currently in a game. Please join one so that you can manage it";
    return;
}

if(isset($args['forceTurn']))
{
    print"doing cycle";
    $player->game->doCycle();
}

?>
<ul>
    <li><a href="#" onclick="loadWindow('manageGames','forceTurn=true'); return false;">Force Turn</a></li>
    <li>Restart game</li>
    <li>End Game</li>
    <li>Edit Team :
        <form><select>
        <option>--Select Team--</option>
        <?php
        $teams = $player->game->getTeams();
        foreach($teams AS $team)
        {
            print "<option onclick=\"loadWindow('manageTeam','id={$team->id}')\">{$team->getName()}</option>";
        }
        ?>
        </select></form>
    </li>
    <li>Edit Player :
        <form><select>
        <option>--Select Player--</option>
        <?php
        $players = $player->game->getPlayers();
        foreach($players AS $person)
        {
            print "<option onclick=\"loadWindow('managePlayer','id={$person->id}')\">{$person->getHandle()}</option>";
        }
        ?>
        </select></form>
    </li>
</ul>