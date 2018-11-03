<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Database Submission</title>
</head>
<body>
<h1>Database Submission Form</h1>

{if !$loggedin }
	{if $result}
		<p>{$result}</p>
	{/if}

	{if $registration}
		<p>Please enter the details you wish to use.</p>
		<form method="post" action="{$action}">
		<table boder=0>
			<tr><td align="right">Username:</td><td><input type="text" name="user"></td></tr>
			<tr><td align="right">Password:</td><td><input type="password" name="pass"></td></tr>
			<tr><td align="right">Verify Password:</td><td><input type="password" name="pass2"></td></tr>
			<tr><td align="right">Display name:</td><td><input type="text" name="name"></td></tr>
			<tr><td align="right">Email address:</td><td><input type="text" name="email"></td></tr>
		</table>
		<input type="submit" name="action" value="Register"><br />
		</form>
		<p><a href="{$action}">Cancel</a></p>

	{else}
		<p>Please Login</p>
		<form method="post" action="{$action}">
		<table boder=0>
			<tr><td align="right">Username:</td><td><input type="text" name="user"></td></tr>
			<tr><td align="right">Password:</td><td><input type="password" name="pass"></td></tr>
		</table>
		<input type="submit" name="action" value="Login"><br />
		</form>
		<p><a href="{$action}?action=newuser">Register an account</a></p>
		<p>For all other assistance with usernames / passwords please contact Rob directly.</p>
	{/if}
{else}

	{if $result}
		<p>{$result}</p>
	{/if}
	{if $submissionresult}
	<table>
		{foreach $submissionresult as $sr}
			<tr><td valign="top">File: <strong>{$sr.name}</strong><br>
					Type: <strong>{$sr.type}</strong><br>
					Contains: <strong>{$sr.count} frames</strong><br>
					Status: <stromg>{$sr.result}</strong>
				</td>
				<td align="centre">First Image<br><img src="{$sr.image}">
				</td>
			</tr>
		{/foreach}
	</table>
	{/if}


	<form method="post" action="{$action}" 	enctype="multipart/form-data">
	<h3>Logged in as {$name}</h3>
	<input type="submit" name="action" value="Logout">

	<p>This function will accept viewdata or teletext files in a wide variety of
	formats.  It does NOT accept modern image formats, such as JPEG or PNG, as
	these do not contain the raw videotex data, merely a graphical representation
	of it.  For a complete list of file types accepted, please <a href='filesaccepted.php'
	target='_new'>click here!</a></p>

	<p>You may submit multiple files at a time, however, please restict this to linked
    or related files. Please submit unrelated files seperately. </p>

	<table boder=0>

		<tr><td align="right"><label for="service">Please choose a distinct service that this upload relates to.</label></td>
		<td><select name="service">
		<option value="0" disabled selected>Please select</option>
			{foreach $services as $key => $service}
				<option value="{$key}">{$service}</option>
			{/foreach}</td></tr>

		<tr><td align="right">Choose file(s):</td>
		<td><input type="file" name="file[]" id="file" multiple="multiple"/></td></tr>

		<tr><td align="right"><label for="format">File format:</label></td>
		<td><select name="format">
		<option value="0">Autodetect</option>
			{foreach $filetypes as $key => $filetype}
				<option value="{$key}">{$filetype}</option>
			{/foreach}
		</select>
		</td></tr>
		<tr><td align="right">Enter a comment or description</td><td><textarea name="comment" rows=6 cols=40></textarea></td></tr>

	</table>
	<input type="submit" name="action" value="Submit"><br />
	</form>

{/if}
</body>
</html>