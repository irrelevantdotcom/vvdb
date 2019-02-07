<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<title>Contribute to database</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<h1>Contribute to the Videotex Database..</h1>
<hr>
<h2>Submit Files.</h2>

<p>Please use this page to submit entries to the database.</p>
<p>You may upload raw teletext or viewdata files, NOT modern images.
(I.e. NO JPEGs!) Many filetypes are supported, however not all types contain all
necessary data.  You will be asked to confirm details on the next page.</p>

<form action="" method="post"
	enctype="multipart/form-data">

		<input type="hidden" name="tag" value="{$tag}">
<table border=1>
	<tr><td>
		If you wish to re-process a previously uploaded file, please select it here</p>
		<label for="existing">Existing File:</label>
		<select name="existing">
			<option value="">- select -</option>
		{foreach $existingfiles as $file}
			<option value="{$file}">{$file}</option>\n";
		{/foreach}
		</select>
	</td><td>
<p>To upload a new file, or files, please use this option</p>

	<label for="file[]">Filename:</label>
	<input type="file" name="file[]" id="file" multiple="multiple"/>
	<br />

</td></tr>


<tr><td colspan="2">
	<label for="format">File format:</label>
	<select name="format">
	<option value="0">Autodetect</option>
		{foreach $filetypes as $key => $filetype}
			<option value="{$key}">{$filetype}</option>
		{/foreach}
	</select>

	<br />
	<label for="service">Please choose a distinct service that this upload relates to.</label>
	<select name="service">
	<option value="0" disabled selected>Please select</option>
		{foreach $services as $key => $service}
			<option value="{$key}">{$service}</option>
		{/foreach}
{*		<option value="new">Add New Service</option> *}
	</select>
{*	Full name/short description <input name="newservicename"><br />
	Short single word name <input name="newserviceshort"><br />
	Startup Page <input name="newservicestartpage"><br />
	Page Name Format <input name="newserviceformat"><br />
	Description <input name="newservicedesc"><br>
	Source of data <input name="newservicesource">
*}

<br>
<label for="varient_name">Short Description of this capture</label>
<input name="varient_name"><br/>
<label for="varient_date">Date of the capture</label>
<input name="varient_date" type="date"><br/>
<label for="originator_id">Originator</label>
	<select name="originator_id">
	<option value="0" disabled selected>Please select</option>
		{foreach $users as $key => $user}
			<option value="{$key}">{$user}</option>
		{/foreach}
{*		<option value="new">Add New User</option> *}
	</select><br />
	<label for="authenticity">Authenticity level:</label>
	<select name="authenticity">
	<option value="0" disabled selected>Please select</option>
		{foreach $auths as $key => $auth}
			<option value="{$key}">{$auth}</option>
		{/foreach}
{*		<option value="new">Add New User</option> *}
	</select><br/>

	<label for="author_id">Author:</label>
	<select name="author_id">
	<option value="0" disabled selected>Please select</option>
		{foreach $authors as $key => $auth}
			<option value="{$key}">{$auth}</option>
		{/foreach}
{*		<option value="new">Add New User</option> *}
	</select><br />
	<input type='checkbox' name="dedupe" value="1" checked>
	<label for="dedupe">Deduplicate</label><br>
	<input type='checkbox' name="confirm" value="1" checked>
	<label for="confirm">Confirm contents</label><br>


</td></tr>


<tr><td>
	<input type="submit" name="action" value="Rescan"><br />

	</td><td>

	<input type="submit" name="action" value="Submit" />

	</td></tr>
</table>
	</form>

</body>
</html>