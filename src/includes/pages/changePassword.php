<?php
/**
 * Page to allow users to change their password.
 * Users must supply their current password and
 * enter the new password twice
 *
 * @todo Log change password attempts
 */
if(isset($args['submit']))
{
	//Check to see if the passwords are the same
	if(isset($args['newpw']) && isset($args['newpw2']) &&
	   $args['newpw'] != $args['newpw2'])
		print "New passwords do not match!";
	else
		if($user->changePassword($args['oldpw'], $args['newpw']))
			print "1";
		else
			print "You entered the wrong current password. Remember that all attempts are logged";
}
else
{
	?>
<p>
	Insert the blurb about changing password here...All attempts at changing
	password (success or failure) are logged. We only do this so the system is
	not abused in anyway and to protect malicious users.<br>
	We <b>never</b> store passwords in plain text.
</p>

<div class="form">
	<form action="" method="post" onsubmit="return false;">
		
	<div class="row">
		<span class="label">Current Password</span>
		<span class="data">
			<input type="password" id="oldpw" />
		</span>
	</div>
	<div class="row">
		<span class="label">New Password</span>
		<span class="data">
			<input type="password" id="newpw" />
		</span>
	</div>
	<div class="row">
		<span class="label">Repeat New Password</span>
		<span class="data">
			<input type="password" id="newpw2" />
		</span>
	</div>
	<div class="row">
		<span class="submit">
			<input type="submit" value="Change Password" onclick="submitForm(['oldpw','newpw','newpw2'], {page: 'password'}); return false;" />
		</span>
	</div>
	</form>
</div>
	<?php
}