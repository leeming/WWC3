<?php
if(isset($args['submit']))
{
	//try to login
	try
	{
		$login = User::login($args['uname'], $args['pword']);
		//$u = new User($login);
		$_SESSION['user'] = serialize(new User($login));
		print "1";
	}
	catch(Exception $e)
	{
		print "Login Failed: ". $e->getMessage();
	}
}
else
{
?>

<div class="login">
	<form action="" method="post" onsubmit="return false;">
	<div class="row">
		<span id="loginError" class="error"></span>
	</div>
	<div class="row">
		<span class="label">Username</span>
		<span class="data">
			<input type="text" id="uname" value="cocacola999" />
		</span>
	</div>
	<div class="row">
		<span class="label">Password</span>
		<span class="data">
			<input type="password" id="pword" value="" />
		</span>
	</div>
	<div class="row">
		<span class="submit">
			<input type="submit" id="submit" value="Login" onclick="submitLogin();" />
		</span>
	</div>
	</form>
	
	Dont have an account? <a onclick="loadWindow('register')" href="#">Sign Up</a>
</div>
<?php
}?>
