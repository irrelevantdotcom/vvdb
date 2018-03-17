<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB" lang="en-GB">
<head>
	<title>{$pagename} - {$date|date_format:"%b %e, %Y"} {$servicename}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
	<link href='https://fonts.googleapis.com/css?family=Playfair+Display:700,900|Fira+Sans:400,400italic' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="/horizontal-timeline/css/reset.css"> <!-- CSS reset -->
	<link rel="stylesheet" href="/horizontal-timeline/css/style.css"> <!-- Resource style -->
	<script src="/horizontal-timeline/js/modernizr.js"></script> <!-- Modernizr -->

</head>
<body>
</div>
<section class="cd-horizontal-timeline">
	<div class="timeline">
		<div class="events-wrapper">
			<div class="events">
				<ol id="altlist">
					<li canonbase="{$canonbase}"><a class="selected" href="#0" data-date="{$date|date_format:"%d/%m/%YT%H:%m"}" >{$date|date_format:"%b %e, %Y"}</a></li>
{*					<li><a href="#0" data-date="28/02/2014">28 Feb</a></li>
					<li><a href="#0" data-date="20/04/2014">20 Mar</a></li>
					<li><a href="#0" data-date="20/05/2014">20 May</a></li>
					<li><a href="#0" data-date="09/07/2014">09 Jul</a></li>
					<li><a href="#0" data-date="30/08/2014">30 Aug</a></li>
					<li><a href="#0" data-date="15/09/2014">15 Sep</a></li>
					<li><a href="#0" data-date="01/11/2014">01 Nov</a></li>
					<li><a href="#0" data-date="10/12/2014">10 Dec</a></li>
					<li><a href="#0" data-date="19/01/2015">29 Jan</a></li>
					<li><a href="#0" data-date="03/03/2015">3 Mar</a></li>
*}				</ol>

				<span class="filling-line" aria-hidden="true"></span>
			</div> <!-- .events -->
		</div> <!-- .events-wrapper -->

		<ul class="cd-timeline-navigation">
			<li><a href="#0" class="prev inactive">Prev</a></li>
			<li><a href="#0" class="next">Next</a></li>
		</ul> <!-- .cd-timeline-navigation -->
	</div> <!-- .timeline -->

	<div class="events-content">
		<ol id="contentlist">
			<li class="selected" data-date="{$date|date_format:"%d/%m/%YT%H:%m"}">

			<h1>{$servicename} - Page {$pagename} from {$date|date_format:"%b %e, %Y"}</h1>
			<table>
				<tr><td valign="top">
					<img src='{$canonbase}.img'}>
				    <p>More details goes here</p>
				</td></tr>
				<tr><td valign="top">
				 	<center>Other sub-pages</center><br>
				 	<ul id="subpagelist">
				 	</ul>
				</td></tr>
			</table>
			<p>Edit it at <a class="tflink" href="">Edit.tf</a></p>

		  </li>
		</ol>
	</div> <!-- .events-content -->
</section>

<script src="/horizontal-timeline/js/jquery-2.1.4.js"></script>
<script src="/horizontal-timeline/js/jquery.mobile.custom.min.js"></script>
<script src="/horizontal-timeline/js/main.js"></script> <!-- Resource jQuery -->
<script src="/js/jquery.lazyloadxt.js"></script>
<script src="/js/jquery.lazyloadxt.widget.js"></script>

<script src="/templates/htmlpage.js"></script>

</body>
</html>