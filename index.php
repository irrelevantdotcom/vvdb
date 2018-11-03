<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Viewdata Frame Database</title>
</head>
<body>
<h1>The Viewdata Frame Database</h1>
<p>Designed to be the definitative place to browse viewdata and teletext
services...</p>
<p>Currently in development. Not all works for now, and what does has not been
made pretty yet!  Nor has all data been loaded, and some of what has is not of the quality I want.</p>

<fieldset>
<legend>Search</legend>
<form method="GET" action="view.php">
Service: <select name="service">
<option value="*">Entire database</option>
</select><br/>
<input type="hidden" name="date" value="s">
Word or phrase: <input type="text" name="request" size=30><br />
<input type="submit" value="Search!">
</form>
</fieldset>

<p><strong>NEW!</strong> If you have old teletext or viewdata service pages, you can <a href='submitfile.php'>submit your data</a> here! </p>
<p>Frames can be accessed in standard wayback-machine format -</p>
<p>i.e. http://db.viewdata.org.uk/<i>service</i>/<i>date</i>/<i>pageandsubpage</i></p>
<p>Here's an example: <a href='http://db.viewdata.org.uk/ceefax1/19830623200800/1000001.html'>http://db.viewdata.org.uk/ceefax1/19830623200800/1000001.html</a>	</p>
<p>Specifying date as <i>*</i> will generate a list of all contenders for that page.</p>
<p>Specifying date as <i>s</i> will search page content for the word or phrase specified instead of a page identifier.</p>
<p>Specifying service as <i>*</i> works for the seach option <strong>only</strong> and widens search to entire database.</p>
<p>Specifying a page without a subpage id will return the first subpage.</p>
<p>Adding .html will create a friendly html page (this is actually default, if missing)</p>
<p>There are/will be various other file extensions that return data in other formats. See below.</p>
<p>Routing between pages is not currently implemented. Edit the URL to pick a new page.</p>
<p>I try not to break the visible stuff during development, but please let me know if I do!</p>
<p>Tested in Opera, Chrome, Edge.<br />
MSIE does not work due to unsupported javascript that I don't want to get into changing just yet.<br />
Firefox not tested as I don't have it installed. Was reported broken, but fix for Edge should make that work too.<br />
Other browsers not tested for ditto.</p>
<p>Contact: Twitter: @irrelevant_com, @viewdataUK.  email: <email>robert@viewdata.org.uk</email>.</p>

<hr>
<p>http://host/service/date/pagesubpage.mode</p>
<p>"mode" will indicate viewing mode - <ul>
<li>html - Main default mode - display a page with it's subpages. Alternate templates can be specified with _</li>

<li><strike>png - static image only.</strike></li>
<li><strike>gif - animated image only (i.e. flashing characters flash!)</strike></li>
<li>img - most appropriate of png or gif.</li>
<li><strike>dyn - animated image of typical 1200 baud presentation.</strike></li>
<li>txt - plain text "image" only.</li>
<li><strike>inc - HTML encoded text and graphics "image".</strike></li>
<li>tf - edit.tf encoded byte string of static frame.</li>

<li>json - Pretty much everything about the frame, subpages, alternatives, etc.</lo>
<li>info - basic information about the frame. (also in json format)</li>

</ul>
<p>html template will typically call-back the viewer with requests for other information
(e.g. a specific image type, meta data, etc.)</p>
<p>Image options only display a single frame, and are intended for inclusion
elsewhere.</p>
<p>Images should support _nnnxnnn at tail of page name, e.g. 84a_120x100.png.
Currently only width is used. Height will be proportional.</p>

<h2>News</h2>
<p><i>20th October</i> Bloody hell, forgotten to update this in a while. Edge now tested working. MSIE will not. Firefox should work.
Lots of little bug fixes. Added code to support a couple of new file formats to vv.class to allow: lots more data has been loaded.
Changes to upload routines to allow specification of authenticity, author, etc.</p>
<p><i>6th Oct 2017</i> Added authenticity flag into record. Added to templates, upload page, etc.  Upload bugfixes.</p>
<p><i>3rd-5th Oct 2017</i> Various small tinkery type changes. Added minimum length to search term validation.</p>
<p><i>2nd Oct 2017</i> Redesigned search/choices page, added text snippits.</p>
<p><i>1st Oct 2017</i> Debugged search, added search box to front page. Updated docs.</p>
<p><i>31st Sept 2017</i> Implemented search functionality. Edit.tf viewer works but slow.</p>
<p><i>30th Sept 2017</i> Some html fixes. Dev work on edit.tf viewer version of default html page.</p>
<p><i>29th Sept 2017</i> oEmbed support implemented.  To use in Wordpress, add wp_oembed_add_provider( 'http://db.viewdata.org.uk/*', 'http://db.viewdata.org.uk/oembed' ); to your theme.</p>
<p><i>27th Sept 2017</i> Open Graph tags now applied so sharing on e.g. facebook looks better.</p>
<p><i>26th Sept 2017</i> URL now updates with currently displayed image, allowing "back" and sharing by URL to work as expected.</p>
</html>