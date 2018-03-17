<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<title>Contribute</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
</head>
<body>


<form action="conimport.php" method="post">
	<input type="hidden" name="service" value="{$service_name}">
	<input type="hidden" name="varient_date" value="{$varient_date}">
	<input type="hidden" name="varient_name" value="{$varient_name}">
	<input type="hidden" name="originator_id" value="{$originator_id}">
	<table border=1>
		<tr>
			<td>Scanning file {$filename}<br/>
			File determined to be type {$filetype}<br />
			This file contains (up to) {$filelength} frame(s)
			</td>
		</tr>
		{foreach $frames as $i => $frame}
		<tr>

			<td valign="top"> Frame {$frame.filenumber}/{$frame.fileindex}.<br />
				<input type="hidden" name="vfsp_{$i}" value="{$frame.filename}">
				<input type="hidden" name="index_{$i}" value="{$frame.fileindex}">
				<input type="hidden" name="format_{$i}" value="{$frame.fileformat}">
				<img src='data: image/png;base64,{$frame.img}' ><br />
			</td>
			<td valign="top">
				<label for="include_{$i}">Include this frame:</label>
					<input type=checkbox checked name="include_{$i}" value="1"><br /><br />
				<label for="pagenum_{$i}">Original page number:</label>
					<input type=input name="pagenum_{$i}" value="92"><br />
				<label for="subpage_{$i}">Subpage ID:</label>
					<input type=input name="subpage_{$i}" value="b"><br /><br />
				<label for="author_{$i}">Identified Author:</label>
					<select name="author_{$i}">
						{foreach $authors as $key => $value}
						<option value='{$key}'>{$value}</option>
						{/foreach}
						<option value='*'>Add New</option>
					</select><br />
					<input type=text name='newauthor_{$i}'><br />
				<label for="authenticity_{$i}">Authenticity:</label>
					<select name="authenticity_{$i}" style="width: 250px;">
						{foreach $authenticities as $key => $value}
						<option value='{$key}'>{$value}</option>
						{/foreach}
					</select><br/>
				<label for 'date_{$i}'>Date and Time:</label>
					<input type=text name='date_{$i}' value=''><br />
			</td>
			<td>
			{foreach $frame.meta as $key => $value}
				<label for="{$key}{$i}">{$key}:</label>
				<input type=input name="{$key}{$i}" value="{$value}"><br />
			{/foreach}
			</td>
		</tr>
		{/foreach}

	</table>

	<input type="hidden" name="action" value="import">
	<input type="submit" value="Now add them!"></form>
</form>
</body>
</html>