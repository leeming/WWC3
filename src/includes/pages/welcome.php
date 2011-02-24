<h2>Welcome Back <?=$user->username?></h2>

<?php
//Get last login details
$lastLogin = $user->getLogins(2);	//2 because last login is from the login just now

if(count($lastLogin) > 1)
{
?>
<p>
	Your last login was at [<?=date("d/m/Y H:i:s",$lastLogin[1]['timestamp'])?>]
	from [<?=$lastLogin[1]['ip']?>] (<a href="#">Not You?</a>)<br>
	[<a href="#">more</a>]
</p>

<p>
	You have [<a><?=MailCollection::numUnread($user->id)?></a>] number of new mail<br>
	[insert] number of games have ended<br>
	
</p>
<?php
}
else
{
	?>
	<p>
		You are a new member! Make sure you take a look at the <a href="#">FAQ</a>
		and <a href="#">Game Guides</a>. If you have any questions which are not
		answered in these sections feel free to ask for help in the <a href="#">Forums</a>
		or at the <a href="#">Help Centre</a>.
	</p>
<?php
}
?>
	
	