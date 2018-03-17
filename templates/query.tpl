<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Query Results</title>
<script src="/js/blazy.min.js"></script>
<script>
        ;(function() {
            // Initialize
            var bLazy = new Blazy();
        })();
</script>

</head>
<body>
{if isset($error)}
	{if $error == 'stub'}
		<h1>Page Existence Deduced</h1>
		<hr>
		<p>We have reason to believe that the page you requested may well have existed
		at some point.  It may have been referenced in a magazine aticle or advert,
		or been listed within a directory or guidance leaflet.  Not every page so
		indicated will be included here, but we will try and add as many as we can
		so that, if you arrive at this message following a link, you know at least a
		little about what you are missing!</p>
		<p>If you have more information about this missing page, an actual saved
		copy, or even a photograph or printout, then please do get in touch with us
		on <email>info@viewdata.org.uk</email>.  Thank you!</p>
	{/if}



	<h1>{$error}</h1>
{/if}
{if !empty($results)}
	<table>
	{foreach $results as $result}
	{strip}
	   <tr bgcolor="{cycle values="#aaaaaa,#bbbbbb"}">
		  <td>
		  	{if $result.url}<a href="{$result.url}.html"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
		  data-src="{$result.url}_120x100.img" width=120 height=100 class="b-lazy">
		  </a>{/if}
		  {*if $result.pagedescription}<br />
		  	{$page_description}
		  {/if*}
		  </td>
	      <td>{$result.pagename}<br/>
	      {$result.date|date_format:"%b %e, %Y"}<br/>
	      {$result.service_name}<br/>
	      {$result.description}</td>
	      <td>{$result.snippit}</td>
	   </tr>
	{/strip}
	{/foreach}
	</table>
{/if}

</body>
</html>