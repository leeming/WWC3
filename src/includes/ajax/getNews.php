<?php
if(!isset($args['id']) || !Validate::isInt($args['id']))
{
    print "Invalid news id";
    return;
}

$news = new News($args['id']);
print TextFormat::BBCode($news->body);
?>