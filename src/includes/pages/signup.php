<?php
if(isset($args['submit']))
{
	//try to register
	$register = User::add(array(
		'username' => $args['uname'],
		'email' => $args['email'],
		'handle' => $args['handle'],
		'password' => $args['pword']
	));

	
	//check that $register is an int as add returns user id
	if(Validate::isInt($register))
	{
		print "1";
	}
	else
	{
		print $register;
	}
}
else if(isset($args['done']) && $args['done'])
{
	?>
	<p>
		Yey an account has been created!
		Try to log in now... In the future there should be email
		verfication, but not yet.
	</p>
	<?php
}
else
{
?>
<style>
	div.notes
	{
		display: block;
		float:right;
		border: 1px solid #666;
		padding: 5px;
		
	}
	
	div.notes h1
	{
		font-size: 1em;
		font-weight: bold;
		border-bottom: 1px solid #fff;
		margin-top:0px;
	}
</style>

<div class="notes">
	<h1>Stuff</h1>
	<p>
		<strong>Username</strong>: This is what you use with your password
		to log into the game. This is not the name you will be known as in the
		game, see <i>handle</i>.
	</p>
	<p>
		<strong>Password</strong>: No strict rules on passwords but we recomend
		you have a <i>strong</i> password i.e with mix of alphanumeric and special
		characters.
	</p>
	<p>
		<strong>Handle</strong>: This is the name you will be known as in the game.
		This is not the same as username, handle is public username is not.
</div>
<div class="form" style="width:80%;">
	<form action="" method="post">
	<div class="row">
		<span id="regError" class="error"></span>
	</div>
	<div class="row">
		<span class="label">Username</span>
		<span class="data">
			<input type="text" id="uname" value="" />
		</span>
	</div>
	<div class="row">
		<span class="label">Password</span>
		<span class="data">
			<input type="password" id="pword" value="" />
		</span>
	</div>
	<div class="row">
		<span class="label">Repeat Password</span>
		<span class="data">
			<input type="password" id="pword2" value="" />
		</span>
	</div>
	<div class="row">
		<span class="label">Handle</span>
		<span class="data">
			<input type="text" id="handle" value="" />
		</span>
	</div>
	<div class="row">
		<span class="label">Email</span>
		<span class="data">
			<input type="text" id="email" value="" />
		</span>
	</div>
	<div class="row">
		<span class="submit">
			<input type="button" id="submit" value="Register" 
				onclick="submitForm(['uname','pword','handle','email'], 
				'page=register', ww(event), 
				function(){ 
					alert('Account created!\nNo additional verification needed (yet) so you are free to log in and look around.'); 
				})" />
		</span>
	</div>
	</form>
</div>
<?php
}?>
