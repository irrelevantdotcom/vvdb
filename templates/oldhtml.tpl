<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US" lang="en-US">
<head>
	<title>{$pagename} - {$date|date_format:"%b %e, %Y"}{$servicename}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<h1>{$servicename}</h1>
<h2>Page {$pagename} - Capture made at {$date|date_format:"%b %e, %Y"}</h2>


<table>
<tr><td valign="top">
	<img src='{$canonbase}.img'}>
</td>
<td valign="top">
<center>Sub-pages</center>
<ul id="subpagelist">
</ul>
</td>
<td valign="top">
<center>Alternate Versions</center>
<ul id="altlist">
</ul>
</td>
</tr>
</table>


<p>Edit it at <a id="editurl" href="">Edit.tf</a></p>
<script>
fetch("{$canonbase}.tf")
	.then((resp) => resp.text())
	.then(function(data) {
		document.getElementById("editurl").href = "http://edit.tf/#" + data;
		}
	);
</script>

<script>
fetch("{$canonbase}.json")
	.then((resp) => resp.json())
	.then(
	    function(data) {

			let subpages = data.subpages,
				alternates = data.alternatives;
			return subpages.map(
			    function(subpage) {
					let li = document.createElement('li'),
						img = document.createElement('img'),
						span = document.createElement('span'),
						a = document.createElement('a');
					img.src = subpage.canonbase+'_120x100.img';
					span.innerHTML = subpage.frame_id + subpage.subframe_id;
					a.href = subpage.canonbase + '_oldhtml.html';
					a.appendChild(img);
		      		a.appendChild(span);
		      		li.appendChild(a);
		      		document.getElementById('subpagelist').appendChild(li);
				}
			) + alternates.map(
				function(alt) {
					let li = document.createElement('li'),
						img = document.createElement('img'),
						span = document.createElement('span'),
						a = document.createElement('a');
					img.src = alt.canonbase+'_120x100.img';
					span.innerHTML = alt.varient_date + '<br>' + alt.varient_name;
					a.href = alt.canonbase + '_oldhtml.html';
					a.appendChild(img);
		      		a.appendChild(span);
		      		li.appendChild(a);
		      		document.getElementById('altlist').appendChild(li);
				}
			);
		}
	);
</script>

</body>
</html>