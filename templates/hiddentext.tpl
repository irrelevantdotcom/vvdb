<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Hidden Tesy</title>
</head>
<body>

<h1>Hidden Text Found</h1>
<table>
{foreach $results as $r}
	<tr>
		<td>Service: {$r.service_id}<br />
			Variant: {$r.varient_id}
		</td>
		<td>Page: {$r.frame_id}<br />
		Subpage: {$r.subframe_id}<br />
		Link: <a href="{$r.url}">clicky</a>
		</td>
		<td>{foreach $r.texts as $t}
			Position: {$t.row} / {$t.col} Text found: {$t.text}<br />
		{/foreach}</td>

	</tr>

	{/foreach}
</table>
*ends*
</body>
</html>