<?php
if(!isset($user) || !$user->isAdmin())
{
    print 'Admin only. Didnt this go thru lobby?';
    return;
}

//Show dropdown menu with name of all users
$users = User::getAll();
print "<select onchange='return false;'>";

foreach($users AS $u)
{
    print "<option id='{$u->id}'>{$u->handle}</option>";
}
print "<option id='0'>-- New User --</option>";
print "</select>";


//Check to see if id is 0, new user
if(isset($args['id']) && $args['id'] == 0)
{
    
}

//Show the info about a user
else if(isset($args['id']) && Validate::isInt($args['id']))
{
    $u = new User($args['id']);
}



?>