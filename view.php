<?php

/**
 * view.php
 *
 * .htaccess redirects from -
 * http://host/view/prestel/20170101123456/800a.html
 * to
 * view.php?service=prestel&date=20170101123456&request=800a.html

 RewriteEngine on

 RewriteRule ^/?oembed(.*)$ view.php [QSA,L]

 RewriteRule ^/?([a-zA-Z_0-9]+)/([0-9]+|\*|s)/([0-9a-zA-Z_\.]+)$ view.php?service=$1&date=$2&request=$3 [L]

 *
 *
 * @version 0.6
 * @copyright 2017 Rob O'Donnell.
 *
 * BSD Licence applies.
 */



/*
   Collect parameters and validate format..

   Modes available.
	   Standard viewer.  translated from /sss/ddd/ppp.mmm into ?service=sss&date=ddd&request=ppp.mmm
		 where mmm is the mode, e.g. html, json, img
   		 * in date means find page on any variation.
   	   oEmbed version.	query params passed verbatim. URL translated herewithin

       search results.   translate from /sss/s/text.mmm
		 								?service=sss&date=s&request=text.mmm
						* in service means search all services!
   						other options on direct call

*/


//	Get basic parameters from url-requests


if (isset($_GET['url'])) {		// must be an oembed call
	$url = $_GET['url'];
	$matches = array();
	// For consistance, this regex should match that in the .htaccess.
	if (!preg_match('/^/?([a-zA-Z_0-9]+)/([0-9]+|\*|s)/([0-9a-zA-Z]+)(_[a-zA-Z0-9]*)?(\.[a-z]*)?$/',$url,$matches)) {
		http_response_code(404);
		die ('Invalid url for oembed');
	}
	$oembed = true;
	$sname = strtolower(trim($matches[1]));
	$date = $matches[2];
	// override suffix-style mode to .info
	$request = substr($matches[3],0,strrpos($matches[3],'.')) . '.info';
	$modifier = '';
	$mode = 'info';
} else {						// standard call.
	if (!isset($_GET['service']) || !isset($_GET['date']) || !isset($_GET['request'])) {
		http_response_code(400);
		die ('Invalid call');
	}
	$oembed = false;
	$sname = strtolower(trim($_GET['service']));
	$request = $_GET['request'];
	$date = $_GET['date'];
}


// validate date (when it is a number, not a * or s )
if (is_numeric($date)) {
	if (strlen($date) < 14) {	// partial date. find mipoint of possible range.
		$max = date_create_from_format('YmdHis',$date . substr('99991231235959', -14+strlen($date)));
		$min = date_create_from_format('YmdHis',$date . substr('19700101000000', -14+strlen($date)));
		if ($max === false || $min === false) {
			die ('Invalid date.');
		}
		$date = $min->getTimestamp() + ($max->getTimestamp() - $min->getTimestamp()) / 2;
	} else {					// it's long enough, but is it valid ?
		$d = date_create_from_format('YmdHis', $date);	// returns FALSE if invalid format
		if ($d === false) {
			http_response_code(404);
			die ('Invalid date');
		}
		$date = $d->getTimestamp();	// convert to simple timestamp.
	}
}

// override the response format if ?format= is specified. (mainly for oEmbed)
$pagename = $request;
if (isset($_GET['format']) && !empty($_GET['format'])) {
	$mode = ltrim($_GET['format'],'.');
	if ($oembed && $mode == 'json') {
		$mode = 'info';
	}
} else {
	// split request into page/searchterm and respons-mode.
	// using a dot makes for friendly urls like /s/d/800600a.html
	$dot = strrpos($request,'.');
	if ($dot) {
		$pagename = substr($request,0,$dot);
		$mode = substr($request,$dot+1);
	} else {
		$mode = 'html';				// display a single-frame page.
	}
}



// load up modifier.
if (isset($_GET['modifier'])) {
	$modifier = ltrim($_GET['modifier'],'_');
	$size = 0|$modifier;
} else {
	// or pagename can have a modifier appended, so split that off too.
	$us = strrpos($pagename,"_");
	if (($us)) {
		$modifier = substr($pagename,$us+1);
		$size = 0|$modifier;
		$pagename = substr($pagename,0,$us);
	} else {
		$modifier = '';
		$size = false;
	}
}




/*
   Now include necessary components.
*/

include_once('vvdatabase.class.php');
include_once('vv.class.php');
include_once('smarty/libs/Smarty.class.php');
include_once('config.php');

/*
   Access database and further validate request
*/

$db = new vvDb();

// Connect to database.  Returns error message if unable to connect.
$r = $db->connect($config['dbserver'],$config['database'],$config['dbuser'], $config['dbpass']);
if (!empty($r)) {
	http_response_code(500);
	die ($r);
}

// check specified service and verify page name format.
if ($sname == '*') { 	// any service, don't validate format, just build dummy record.
	$service = array('service_id' => null);	// null when passed to vvdb will search all.
	$page = $pagename; $subpage = null;
} else {
	$service = $db->getServiceByName($sname);
	if (empty($service)) {
		http_response_code(404);
		die('Specified service not found');
	}

	// Validate format of requested page according to service requirements.
	if ($date == 's') {	// search mode,
		// tidy up search string to just alphanumerics and space.
		$pagename = trim(preg_replace('/[^a-z\d ]/i', '', $pagename));
		if (strlen($pagename) < 3) {
			http_response_code(400);
			die('Search string too short');
		}
	} else {	// not in search mode,
		// validate requested page against page name format for this service,
		$matches = array();
		if (preg_match('/'.$service['page_format'].'/i', $pagename, $matches) != 1) {
			http_response_code(400);
			die('Invalid page name format');
		}
		$subpage = $matches[2];
		if (empty($subpage)) $subpage = null;	// force null if not present.
		$page = $matches[1];
	}
}

/*
   Prepare template engine for eventual output.
*/

$smarty = new Smarty;


/*
   Now, go look for the requested data.
*/

// Valid date, but is it a recognised capture, and does a frame exist for it ?
if (is_numeric($date)) {
	$varient = $db->getVarientByDate($service['service_id'], $date);
	if (empty($varient)) {	// Not able to find any varient for date.
							// At present, this means no varients exist at all.. however
		$date = '*';		// this may change to "no nearby dates" in which case we do
							// want to fall over to display the list of options.
		$smarty->assign('error','No captures on or near that date.');
	} else {
		// reset date to actual varient date.
		// TODO - Memento spec says to return a 302 redirect if exact date requested not exist.
		$date = strtotime($varient['varient_date']);
		if ($subpage) {		// particular subpage has been requested.
			$date = strtotime($varient['varient_date']);
//			var_dump($service['service_id'],$varient['varient_id'],$page,$subpage);
			$pagerecord = $db->getFrame($service['service_id'],$varient['varient_id'],$page,$subpage);
			if (empty($pagerecord)) {
				$smarty->assign('error','This frame was not found on that capture date. Try these:');
				$date = '*';	// fall through to query.
			}
		} else {			// no subpage requested, get the first available subpage.
			$pagerecord = $db->getFirstFrame($service['service_id'],$varient['varient_id'],$page);
			if (empty($pagerecord)) {
				$smarty->assign('error','This page was not found on that capture date. Try these:');
				$date = '*';	// fall through to query.
			}
		}
	}
}


/*
   Create the output - list of contenders, or specific frame data.
*/
if ($date == 's') {	// search
//	echo "Searching for $pagename<br/>";
	$candidates = $db->findText(		// in this case, $pagename is actually the search text
		$pagename, (empty($service) ? null : $service['service_id']));
} else { // not a search but a specific page or browse query.
	$candidates = $db->getAlternateFrames($service['service_id'],
		 (empty($varient) ? null : $varient['varient_id']),
		 (empty($pagerecord) ? $page : $pagerecord['frame_id']),
		 (empty($pagerecord) ? $subpage : $pagerecord['subframe_id']));
}

if (!is_numeric($date)) {				// Generate results listing.
	if (empty($candidates)) {
		// TODO - if specific subframe isn't avail at all, offer alternate subframes/first frames
		$option = $db->getStub($service['service_id'],$varient['varient_id'],$page);
		if (empty($option)) {
			http_response_code(404);
			$smarty->assign('error','Sorry, nothing found for that request.');
		} else {
			$smarty->assign('error','stub');
			$smarty->assign('results',array(array(
			'url' => '', // '/' . $option['short_name'] . '/' . date('YmdHis',strtotime($option['varient_date'])) . '/' . $option['frame_id'] . $option['subframe_id'],
			'date' => '', //strtotime($option['varient_date']),
			'service_name' => $option['service_name'],
			'description' => $option['varient_name'],
			'pagename' => $option['frame_id'] . $option['subframe_id'],
			'snippit' => $option['frame_description'])));
		}
	} else {
		$results = array();
		foreach ($candidates as $option){

			// generate a text snippit..
			$i = ViewdataViewer::createImage(true,$option['frame_content']); // text version
			$i = trim(str_replace(array('*', chr(160),'  ','&nbsp;',),' ', strip_tags($i))); // strip graphics stars
			$i = preg_replace('/\s+/', ' ', $i); // reduce all whitespace to single space.
			if ($date == 's') {	// search
				// highlight search term
				$j = strpos(strtoupper($i), strtoupper($pagename)); // shouldn't fail as SQL gave us this as a result, however,,
				if ($j) {
					$i = substr($i, max(0, $j-50), 100);
					$i = '...' . preg_replace("/$pagename/i", '<strong>$0</strong>', $i)  . '...';
				}
			} else {	// straight list - just do some from first bit of text..
					$i = '...' . substr($i, 50, 100) . '...';
			}

			$results[] = array('url' => '/' . $option['short_name'] . '/' . date('YmdHis',strtotime($option['varient_date'])) . '/' . $option['frame_id'] . $option['subframe_id'],
								'date' => strtotime($option['varient_date']),
								'service_name' => $option['service_name'],
								'description' => $option['varient_name'],
								'pagename' => $option['frame_id'] . $option['subframe_id'],
								'snippit' => $i,
							//	'content' => $option['frame_content']
								); // .txt version?
		}
		$smarty->assign('results',$results);
	}

	// output in requested format.
	switch($mode){
		case 'json':
			$string = '{$results|@json_encode:64 nofilter}';
			$smarty->display('string:'.$string);
			break;
		case 'html':
			$smarty->display('templates/query.tpl');
			break;
		default:
			// TODO other modes on query result .. and better templates!
	} // switch

} else {
	// we only get here if a specific page managed to be loaded into $pagerecord

	// Let's update the request fields with what we actually loaded..
	$page = $pagerecord['frame_id'];
	$subpage = $pagerecord['subframe_id'];

	// canonnical URL for page.
	$canonbase = '/'.$service['short_name'].'/'.date('YmdHis',$date).'/'.$page.$subpage;
	$smarty->assign('canonbase', $canonbase);
	$smarty->assign('canonurl', (isset($_SERVER['HTTPS']) ? "https" : "http") . '://db.viewdata.org.uk' . $canonbase);
	$smarty->assign('service',$service);
	$smarty->assign('datename',$varient['varient_name']);
	$smarty->assign('date', $date);
	$smarty->assign('originator', $varient['displayname']);
	$smarty->assign('authenticity', $pagerecord['auth_description']);
	$smarty->assign('pagename', $page.$subpage);
	$smarty->assign('page', $page);
	$smarty->assign('subpage', $subpage);
	$smarty->assign('description', $pagerecord['frame_description']);
	//	$smarty->assign('frame',$pagerecord);	// all data, just in case.

	// add flag for if this was an oembed request.
	$smarty->assign('oembed',$oembed);

	// if nothing in the page, but we have a description field, then replace content with templated content.
	if ($pagerecord['frame_content_type'] == 16 /*VVDBTYPE_STUB*/ or (empty($pagerecord['frame_content']) && !empty($pagerecord['frame_description']))) {	// no data held for this page
		$np = $db->getFirstFrame($varient['service_id'], $varient['varient_id'], $config['err_nodata']);
		if (empty($np)) {
			$np = $db->getFirstFrame($varient['service_id'], $config['err_variant'], $config['err_nodata']);
			if (empty($np)) {
				$np = $db->getFirstFrame($config['err_service'], $config['err_variant'], $config['err_nodata']);
				if (empty($np)) {
					$np = array('frame_content' => '%sssssssssssssssssssssssssssssssssssssss');
				}
			}
		}
		$i = strpos($np['frame_content'],'%s');
		if ($i !== false) {
			$j = strspn($np['frame_content'],'s',$i+1) + 1;
			$desc = substr(str_pad($pagerecord['frame_description'],$j,' '),0,$j);
			$pagerecord['frame_content'] = substr_replace($np['frame_content'],$desc,$i,$j);
		}
	}



/*
  	Now generate output in requested format
*/
	switch($mode){
		case 'html':	// "normal" - page with details, image, routing, etc.
						// There should already be more than enough details passed to Smarty
						//for template to call-back whatever else it needs.
			// allow pagename_templatename.html
			if ($modifier && file_exists($t = 'templates/' . $modifier . '.tpl')) {
				$smarty->display($t);
			} else {
				$smarty->display('templates/htmlpage.tpl');
			}
			break;
		case 'txt':		// Plain text.  All graphics replaced by asterisks.
						// NOTE - this returns HTML Entities, &nbsp; &pound; etc, and <BR> at EOL.
			$i = ViewdataViewer::createImage(true,$pagerecord['frame_content']);
			echo $i;
			break;
		case 'tf':		// edit.tf hash string - use as http://edit.tf/#<hashstring>
			// grab all metadata for a page.
			$metadata = $db->getFrameMeta($pagerecord['frameunique']);
			// grab the page content in tf format.
			echo get_tf($pagerecord['frame_content'], $metadata);
			break;

		case 'img':		// static image in whatever format is best..
			$i = ViewdataViewer::createImage(false,$pagerecord['frame_content'],40,25,0,$size);
			$img = $i['image'];
			$ityp = $i['imagetype'];

			if ($ityp == 'png') {
				header("Content-type: image/png");
				imagepng($img);
			} else {
				header("Content-type: image/gif");
				echo $img;
			}
			break;
		case 'xml':		// oembed result
		case 'info':	// brief data about an item.  OR oembed json result.
			// grab all metadata for a page.
			$metadata = $db->getFrameMeta($pagerecord['frameunique']);
			// grab the page content in tf format.
			$tf = get_tf($pagerecord['frame_content'], $metadata, $page, $subpage);
			$smarty->assign('tf', $tf);

			unset($pagerecord['frame_content']);
			$smarty->assign('frame',$pagerecord);
			$smarty->assign('meta',	$metadata);

			if (!isset($_GET['maxwidth']) || !($w = (int)$_GET['maxwidth'])) {
				$w = 604;
			}
			if (!isset($_GET['maxheight']) || !($h = (int)$_GET['maxheight'])) {
				$h = (int)($w / 1.184);
			}
			if ($w > (int)($h * 1.185)) {
				$w = (int)($h * 1.185);
			}
			if ($h > (int)($w / 1.184)) {
				$h = (int)($w / 1.184);
			}
			$smarty->assign('width', $w);
			$smarty->assign('height', $h);

			if ($mode == 'xml') {
				header("Content-type: text/xml");
			} else {
				header("Content-type: text/json");
			}
			$smarty->display('templates/'.$mode.'page.tpl');
			break;

		case 'json':	// lots of data about an item.
			// grab all metadata for a pag
			$metadata = $db->getFrameMeta($pagerecord['frameunique']);

			// grab the page content in tf format.
			$tf = get_tf($pagerecord['frame_content'], $metadata, $page, $subpage);
			$smarty->assign('tf', $tf);

			// list of subframes
			$ss = $db->getSubFrames($service['service_id'], $varient['varient_id'], $page, null );
			// fiddle with list of subpages.
			foreach ($ss as $key => $s){
				$ss[$key]['canonbase'] = '/'.$service['short_name'].'/'.date('YmdHis',strtotime($s['varient_date'])).'/'.$s['frame_id'].$s['subframe_id'];
				$ss[$key]['tf'] = get_tf($s['frame_content'], $db->getFrameMeta($s['frameunique']), $s['frame_id'], $s['subframe_id']);
				unset($ss[$key]['frame_content']); // don't need this cluttering up json...
				// TODO what other data do we need to supply for each subframe ?
			}

			// fiddle with alternative captures, got already so don't need to grab from database again!
			foreach ($candidates as $key => $o ){
				$candidates[$key]['canonbase'] = '/'.$service['short_name'].'/'.date('YmdHis',strtotime($o['varient_date'])).'/'.$o['frame_id'].$o['subframe_id'];
				$candidates[$key]['tf'] = get_tf($o['frame_content'], $db->getFrameMeta($o['frameunique']), $o['frame_id'], $o['subframe_id']);
				unset($candidates[$key]['frame_content']); // don't need this...
			}

			$smarty->assign('subpages', $ss	);
			unset($pagerecord['frame_content']);
			$smarty->assign('frame',$pagerecord);
			$smarty->assign('alternatives', $candidates);
			$smarty->assign('meta',	$metadata);

			header("Content-type: text/json");
			$smarty->display('templates/jsonpage.tpl');
			break;

		default:
			http_response_code(501);
			echo 'unsupported';
			;
	} // switch
}

function get_tf($content, $metadata, $page, $subpage){
	// add pagenumber etc to hash
	$extras = array('pn' => $page, 'sc' => $subpage);
	// teletext fasttext links - only add if all exist!
	// these won't exist on viewdata pages ...
	// There is different metadata for viewdata ... !
	$links = array('red','green','yellow','blue','link4','index');
	$values = array();
	$c = 0;
	foreach ($links as $key){
		if (isset($metadata[$key])) {
			switch(strlen($metadata[$key])){
				case 0:
					$values[$key] = '8FF3F7F';
					break;
				case 3:
					$values[$key] = $metadata[$key] . '3F7F';;
					break;
				case 7:
					$values[$key] = $metadata[$key];
					break;
				default:
					$values[$key] = substr($metadata[$key].'000000',0,7);
			} // switch
			$c++;
		}
	}
	// did they all exist?
	if ($c == count($links)) {
		$extras['x270'] = $values;
	}
	return ViewdataViewer::to_hash($content, 0, 0, $extras);
}