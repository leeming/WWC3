<?php
$bats = $player->getBattalions();
foreach($bats AS $b)
{
    if($b->id == $args['batid'])
    {
        $firephp->log("Changing players active battalion");
        $player->setSelectedBattalion($b);
        return;
    }
}

$firephp->warn("Bat not found");
?>