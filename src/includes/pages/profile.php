<?php
#Classes used
#require_once("../classes/News.class.php");

if(isset($args['submit']))
{
	#submit stuff here
}
else
{
	?>
	<p>This page does not do anything... what you see is only a template</p>
	<table>
		<tr>
			<th colspan="2">Edit Profile</th>
		</tr>
		<tr>
			<td><b>Name</b></td>
			<td><input type="text" id="proName" value="" /></td>
		</tr>
		<tr>
			<td><b>Location</b></td>
			<td><input type="text" id="proLoc" value="" /></td>
		</tr>
		<tr>
			<td><b>Birthday</b></td>
			<td>...</td>
		</tr>
		<tr>
			<td><b>Gender</b></td>
			<td>
				<select id="proGender">
					<option>Male</option>
					<option>Female</option>
					<option>Unknown</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>unused</b></td>
			<td><input type="text" id="" value="" /></td>
		</tr>
	</table>
	<?php
}
?>