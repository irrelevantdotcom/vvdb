<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB" lang="en-GB">
<head>
	<title>{$page} - {$date|date_format:"%b %e, %Y"} {$service.service_name}</title>
	<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />

<meta property="og:url" content="{$canonurl}.html">
<meta property="og:type" content="website">
<meta property="og:title" content="{$service.service_name}: Page {$page} - {$date|date_format:"%b %e, %Y"} ">
<meta property="og:description" content="{$service.service_description}">
<meta property="og:image" content="{$canonurl}.img">

<link rel="alternate" type="application/json+oembed"   href="http://db.viewdata.org.uk/oembed?url={$canonurl}.html&format=json"
  title="{$service.service_name} - Page {$page} - {$date|date_format:"%b %e, %Y"}" />
	<link href='https://fonts.googleapis.com/css?family=Playfair+Display:700,900|Fira+Sans:400,400italic' rel='stylesheet' type='text/css'>

	<link rel="stylesheet" href="/horizontal-timeline/css/reset.css"> <!-- CSS reset -->
	<link rel="stylesheet" href="/horizontal-timeline/css/style.css"> <!-- Resource style -->
	<link rel="stylesheet" href="/js/jquery.lazyloadxt.spinner.css">
	<script src="/horizontal-timeline/js/modernizr.js"></script> <!-- Modernizr -->
    <!--	horizontal scroll for subpages,.http://ressio.github.io/lazy-load-xt/demo/horizontal.htm -->
    <style>
    	.wrapper {
            max-width: 150px;
            height: 510px;
            vertical-align: top;
            overflow-x: hidden;
            overflow-y: scroll;
            white-space: nowrap;
        }
        .wrapper > img {
            display: inline-block;
            *display: inline;
            *zoom: 1;
        }

		.rTable { display: table; }
		.rTableRow { display: table-row; }
		.rTableHeading { display: table-header-group; }
		.rTableBody { display: table-row-group; }
		.rTableFoot { display: table-footer-group; }
		.rTableCell, .rTableHead { display: table-cell; vertical-align: top; margin-right: 10px; }


    </style>
</head>
<body>

<section class="cd-horizontal-timeline">
	<div class="timeline">
		<div class="events-wrapper">
			<div class="events">
				<ol id="altlist">
					<li data-canonbase="{$canonbase}"><a class="selected" href="#0" data-date="{$date|date_format:"%d/%m/%YT%H:%m"}" >{$date|date_format:"%b %e, %Y"}</a></li>
				</ol>

				<span class="filling-line" aria-hidden="true"></span>
			</div> <!-- .events -->
		</div> <!-- .events-wrapper -->

		<ul class="cd-timeline-navigation">
			<li><a href="#0" class="prev inactive">Prev</a></li>
			<li><a href="#0" class="next">Next</a></li>
		</ul> <!-- .cd-timeline-navigation -->
	</div> <!-- .timeline -->

	<div class="events-content">
		<ol class="contentlist">
			<li class="selected" data-date="{$date|date_format:"%d/%m/%YT%H:%m"}" data-canonbase="{$canonbase}">


<div class="rTable">
 <div class="rTableBody">
  <div class="rTableRow">
   <div class="rTableCell">
		<div><canvas width="604" height="510">
					<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
						data-src="{$canonbase}.img" width="604" height="510" class="mainimage b-lazy">

 		</canvas></div>



				<h5>Further Information</h5>
				<p>Originating service: <span class="i_service">{$service.service_name}</span></p>
				<p>Variation: <span class="i_datename">{$datename}</span></p>
				<p>Page Number: <span class="i_page">{$page}</span></p>
				<p>Subpage: <span class="i_subpage">{$subpage}</span></p>
				<p>Capture date: <span class="i_date">{$date|date_format:"%b %e, %Y"}</span></p>
				<p>Source of data: <span class="i_originator">{$originator}</span></p>
				<p>&nbsp;</p>
				<p>Edit it at <a class="tflink" href="http://edit.tf/#" target="_blank">Edit.tf</a></p>
				<p>Edit it at <a class="tflink" href="http://temp.zxnet.co.uk/editor/#" target="_blank">ZXnet</a></p>
   </div>
   <div class="rTableCell">
    <div class="subpagelist wrapper">
				 		{*<a href="#"  onclick="return imgswap(this);"><img height="120" data-src="" width="120"></a> *}
    </div>
   </div>
  </div>
 </div>
</div>

		  </li>
		</ol>
	</div> <!-- .events-content -->
</section>

<script src="/js/teletext-editor.js"></script>
<script src="/horizontal-timeline/js/jquery-2.1.4.js"></script>
<script src="/horizontal-timeline/js/jquery.mobile.custom.min.js"></script>
<script src="/horizontal-timeline/js/main.js"></script> <!-- Resource jQuery -->
<script src="/js/blazy.min.js"></script>
<script src="/js/teletext-editor.js"></script>

<script src="/templates/htmlpage2.js"></script>

</body>
</html>