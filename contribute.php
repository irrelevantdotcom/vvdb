<?php

/**
 *
 * @version $Id$
 * @copyright 2011
 */

// TODO - allow to select multiple authors.
// sort out varients names



include("vv.class.php");
include("vvdatabase.class.php");
include_once('smarty/libs/Smarty.class.php');
include_once('config.php');

if (empty($_GET['tag']) || $_GET['tag'] != $config['contag']) {
	if (empty($_POST['tag']) || $_POST['tag'] != $config['contag']) {
		die('Invalid call');
	}
}

$db = new vvDb();
// Connect to database.  Returns error message if unable to connect.
$r = $db->connect($config['dbserver'],$config['database'],$config['dbuser'], $config['dbpass']);
if (!empty($r)) {
	http_response_code(500);
	die ($r);
}

$vfile = new ViewdataViewer();


if (!empty($_POST)) {
	var_dump($_POST);
}

// no posts, need to show initial upload form.
if (!isset($_POST["action"])) {
	$smarty = new Smarty;

    $results = $db->getServiceById(NULL);
	$services=array();
	foreach ($results as $r){
		$services[$r['service_id']] = $r['service_name'];
	}
    asort ($services, SORT_NATURAL | SORT_FLAG_CASE);
	$smarty->assign('services',$services);


	$results = $db->getUsers(NULL);
	$users=array();
	foreach ($results as $r){
		$users[$r['user_id']] = $r['displayname'];
	}
	asort ($users, SORT_NATURAL | SORT_FLAG_CASE);
	$smarty->assign('users',$users);

	$results = $db->getAuthors(NULL);
	$authors=array();
	foreach ($results as $r){
		$authors[$r['author_id']] = $r['author_name'];
	}
	asort ($authors, SORT_NATURAL | SORT_FLAG_CASE);
	$smarty->assign('authors',$authors);

	$results = $db->getAuthenticities();
	$auths = array();
	foreach ($results as $r){
		$auths[$r['authenticity_id']] =  $r['auth_name'] . ' - ' . $r['auth_description'];
	}
	$smarty->assign('auths',$auths);



	$files=array();
    if ($dh = opendir ("./upload/")) {
        while (false !== ($dat = readdir ($dh))) { // for each file
            if (substr($dat, 0, 1) != ".") {
                $files[]=$dat;
            }
        }
    }
	$smarty->assign('existingfiles',$files);


	$formats = array();
	$i = 1;
	while(($desc = $vfile->vvtypes($i)) !== false){
		$formats[$i] = $desc;
		$i++;
	}
	$smarty->assign('filetypes',$formats);

	$smarty->assign('tag',$config['contag']);

	$smarty->display('templates/contribute.tpl');
    exit;
}


// relevant POST vars detected, assume form submission




// set up html for displaying found frames
echo '<form action="conimport.php" method="post">';
echo "<br /><br />";
echo "&nbsp;&nbsp;";


foreach (array('service','varient_date','varient_name', 'originator_id', 'authenticity') as $key){
	echo "$key : <input type=\"text\" name=\"$key\" value=\"{$_POST[$key]}\"><br/>";
}


echo "<table border=1>";
$cnt = 0;


if ($_POST["action"] == "Submit") {

	$dedupe = isset($_POST['dedupe']);

	foreach (array_keys($_FILES["file"]["name"]) as $i){

//		echo '<input type="hidden" name="filename".$i value="' . $_FILES["file"]["name"][$i] . '">';

	    if ($_FILES["file"]["error"][$i] > 0) {
	        echo "Error: " . $_FILES["file"]["error"][$i] . "<br />";
	    }else {
	        echo "Upload: " . $_FILES["file"]["name"][$i] . "<br />";
	        echo "Type: " . $_FILES["file"]["type"][$i] . "<br />";
	        echo "Size: " . ($_FILES["file"]["size"][$i] / 1024) . " Kb<br />";
	        echo "Stored in: " . $_FILES["file"]["tmp_name"][$i] . "<br />";

	        $vfsp = $_FILES["file"]["name"][$i];
	        if (file_exists("./upload/" . $vfsp)) {
	            echo "File upload/" . $vfsp . " already exists. <br />";
	            // exit;
	        } else {
	            if (move_uploaded_file($_FILES["file"]["tmp_name"][$i], "./upload/" . $vfsp)) {
	                echo "Stored in: upload/" . $vfsp . "<br />";
	            } else {
	                echo "Failed to transfer file to upload/.<br />";
	                exit;
	            }
	        }
   	 	}
		identify_and_offer($vfsp, $cnt);
	}

} else {
    $vfsp = $_POST["existing"];
    echo "Rescanning existing file upload/" . $vfsp . "<br />\n";
	identify_and_offer($vfsp, $cnt);
}

echo "</table>\n";
echo '<input type="hidden" name="action" value="import">';
echo '<input type="submit" value="Now add them!">';
echo "</form>";


function identify_and_offer($vfsp, &$cnt){

	// fetch tables.
	global $db;

	$authors = $db->getAuthors();
	$authenticities = $db->getAuthenticities();

	$vfile = new ViewdataViewer();
	echo "<td>";
	if (!$vfile->LoadFile("./upload/" . $vfsp, $_POST["format"], implode(',',$_FILES["file"]["name"]))) {
	    echo "Error during file identification.<br />";
		if ($vfile->format == 0) {
			echo "</td></tr>";
			return;
		}
	} else {
	    echo "File determined to be type " . $vfile->format . " (" . $vfile->vvtypes($vfile->format) . ")<br />";
	}
// type identified.  Now to parse file.
// for each file:
// offer:  screenshot
// prompt: date, service, page number,
// routing. page type,
// list of tags
	if ($vfile->framesfound) {
	    echo "This file contains (up to) " . $vfile->framesfound . " frame(s).<br /></td>\n";
	} else {
	    echo "Unable to enumerate frames. Unsupported format? Stopping here.<br /></td></tr>\n";
	    return;
	}

	$prevtxt = ''; $prevprevtxt = ''; $mergetxt = array(); $merge = false;
	$prevpage = '';
	foreach ($vfile->frameindex as $i => $j) {

		$pagenum = $vfile->ReturnMetaData($i, "pagenumber");
		if (is_array($pagenum)) {
			$pn = $pagenum[0];
			$sub = $pagenum[1];
		} else {
			$pn = $pagenum;
			$sub = '';
		}

		$td = $vfile->ReturnMetaData($i, 'date');
		$txt = $vfile->returnScreen($i,'internal');

		if ($td) {
			$date = $td;
		} else {
			$date = strtotime($_POST['varient_date']);
			if (date('H:i:s',$date) == '00:00:00') {
//	echo '$$' . substr($txt,32,8) . '$$';
				$d = strtotime(date('d M Y ',$date)  . substr($txt,32,8));
				if ($d) {
					$date = $d;
				}
			}
		}

		if ($dedupe && (trim(substr($txt,80)) != '' && (substr($txt,80) == substr($prevtxt,80) || substr($txt,80) == substr($prevprevtxt,80) ))) {
			echo "<tr>\n<td valign=\"top\">Duplicate frame content skipped.. </td></tr>\n";
			$prevprevtxt = $prevtxt; $prevtxt = $txt;
			$cnt++;
			continue;
		}
/*
		if ($pn !== $prevpage) {
			for ($i = 1; $i < 24; $i++) {
				$mergetxt[$i] = substr($txt,$i * 40, 40);
			}
		} else {
			$difs = 0;
			for ($i = 1; $i < 24; $i++) {
				$t = substr($txt,$i * 40, 40);
				if (!empty($t) && !empty($mergetxt[$i])) {
					if ($t != $mergetxt ) {
						$difs++;
					}
				} else {
					if (empty($mergetxt[$i])) {
						$mergetxt[$i] = $t;
					}
				}
			}
			if ($difs < 3) {
				$txt = implode('',$mergetxt)	;
			}
		}

		if (substr($txt,40) == substr($prevtxt,40) || substr($txt,40) == substr($prevprevtxt,40) ) {
			echo "<tr>\n<td valign=\"top\">Duplicate frame content skipped.. </td></tr>\n";
			$prevprevtxt = $prevtxt; $prevtxt = $txt;
			$cnt++;
			continue;
		}
*/
		{

		    echo "<tr>\n<td valign=\"top\"> ";
		    echo "Frame $i ($cnt).<br />\n";

			echo '<input type="hidden" name="vfsp_'.$cnt.'" value="' . $vfsp . '">';
			echo '<input type="hidden" name="index_'.$cnt.'" value="' . $i . '">';
			echo '<input type="hidden" name="format_'.$cnt.'" value="' . $_POST['format'] . '">';


			$img = $vfile->returnScreen($i, 'imagesize', 250);
			$ityp = $vfile->returnScreen($i, 'imagetype');

			if ($ityp == 'png') {
				ob_start();
				imagepng($img);
				$image_string = ob_get_contents();
				ob_end_clean();
			} else {
				$image_string = $img;
			}
			$url =  'data: image/'.$ityp.';base64,'.base64_encode($image_string);

		    echo "<img src='$url' ><br />\n";
		    echo "</td><td valign=\"top\"><br />\n";

		    echo "<label for=\"include_" . $cnt . "\">Include this frame:</label>";
		    echo "<input type=checkbox checked name=\"include_" . $cnt . "\" value=\"1\"><br /><br />\n";



		    echo "<label for=\"pagenum_" . $cnt . "\">Original page number:</label>";
		    echo "<input type=input name=\"pagenum_" . $cnt . "\" value=\"" . $pn . "\"><br />\n";
			echo "<label for=\"subpage_" . $cnt . "\">Subpage ID:</label>";
			echo "<input type=input name=\"subpage_" . $cnt . "\" value=\"" . $sub . "\"><br /><br />\n";
			// echo "<label for=\"pagedate_" . $cnt . "\">Date of page:</label>";
		    // echo "<input type=input name=\"pagedate_" . $cnt . "\" value=\"\"><br />\n";
			echo "<label for=\"author_" . $cnt . "\">Identified Author:</label>";
			echo "<select name=\"author_" . $cnt . "\">\n
				<option value='0' disabled selected>Please select</option>";
				foreach ($authors as $r) {
				$key = $r['author_id'];
				$value = $r['author_name'];
				echo "<option value='$key'";
				if (isset($_POST['author_id']) && $key == $_POST['author_id']) {
					echo ' selected';
				}
				echo ">$value</option>\n";
			}
			echo "<option value='*'>Add New</option>\n</select>";
			echo "<input type=text name='newauthor_$cnt'><br />";
			echo "<label for=\"authenticity_" . $cnt . "\">Authenticity:</label>";
			echo "<select name=\"authenticity_" . $cnt . "\" style=\"width: 250px;\"\n";
			foreach ($authenticities as $r) {
				$key = $r['authenticity_id'];
				$value = $r['auth_name'] . ' - ' . $r['auth_description'];

				echo "<option value='$key'";
				if ($key == $_POST['authenticity']) {
					echo ' selected';
				}
				echo ">$value</option>\n";
			}
			echo "</select><br/>";
			echo "<label for 'date_$cnt'>Date and Time:</label>";
			echo "<input type=text name='date_$cnt' value='".date( 'd-m-Y H:i:s', $date)."'><br />";
		    echo "</td><td><br />\n";

			$r = $vfile->ReturnMetaData($i, NULL);	// get list of meta's.
			if (!empty($r)) {
				foreach ($r as $key){
					if ($key != 'pagenumber') {	// already done this one
						$value = $vfile->ReturnMetaData($i, $key);
						if (empty($value)) $value = "";
						echo '<label for="' . $key . $cnt . '">' . $key . ':</label>';
						echo '<input type=input name="' . $key . $cnt . '" value="' . $value . '"><br />';
					}
			    }
			}
		    echo "</td></tr>\n";
		}
		$prevpage = $pn . $sub;
		$prevprevtxt = $prevtxt; $prevtxt = $txt;
		$cnt++;
	}
}


/*// this function is specifically for teletext stream files.
// we may end up with multiple copies of a frame, some in better condition than others.
// this find the best one.
function bestframe($a, $b, $c){
	// ensure they are all the same length.
	$len = max(strlen($a),strlen($b),strlen($c));
	$a .= str_repeat(' ',$len-strlen($a));
	$b .= str_repeat(' ',$len-strlen($b));
	$c .= str_repeat(' ',$len-strlen($c));

	// count displayable characters, and zap anything else on thee way.
	$ca = $cb = $cc = 0;
	for ($i=0; $i<$len; $i++) {
		if ($a{$i} < '!' || $a{$i} > '~') $a{$i} = ' '; else $ca++;
		if ($b{$i} < '!' || $b{$i} > '~') $b{$i} = ' '; else $cb++;
		if ($c{$i} < '!' || $c{$i} > '~') $c{$i} = ' '; else $cc++;

/ *		if ($a{$i} > $b{$i}) $ba++; else $bb++;
		if ($b{$i} > $c{$i}) $bb++; else $bc++;
		if ($c{$i} > $a{$i}) $bc++; else $ba++;
		if ($a{$i} > $c{$i}) $ba++; else $bc++;
* /
	}
	if ($ca > $cb && $ca > $cc) return $a;
	if ($cb > $cc) return $b;
	return $c;


}
* /

/ *
function dedupeandmerge($vfile){

	// we need to construct an array of arrays of pages ...
	$pages = array();

	// ok, get list of pages, ignoring subpage number as might be misleading.
	// we have to assume the actual page number is correct.
	foreach ($vfile->frameindex as $i => $j) {
		$pn = $j['ttpage'];
		$pages[$pn][] = $i;
	}

	// right. now work through each page number, and process each that has more than one entry

	foreach ($pages as $page){

		// First we need to identify just how many different texts we are dealing with.
		// Given lines may be missing, or corrupted, this is harder that it sounds. ...
		// Let's start an array
		$texts = array();
		// start with the first page.
		$i = 0;
		//get the textual content
		$txt = $vfile->returnScreen($page[$i], 'simple');
		// compare against other pages
		$similar = array();
		// do this line by line
		for ($k = 1; $k<25; $k++) {
			$line = trim(substr($txt, $k*40, 40));
			for ($j = 0; $j<count($page); $j++) {
				if ($j != $i) {	// don't compare against ourself!
					$txt2 = $vfile->returnScreen($page[$i], 'simple');
					$line2 = trim(substr($txt2, $k*40, 40));
					// ignore if we have an empty line
					if (!empty($txt) && !empty($txt2)) {
						// check similarity. max 5 characters different on a 40 character line?
						$pc = 0;
						if (similar_text($line, $line2, $pc) < max(strlen($line),stelen($line2)) / 8) {
							$similar[$k][$j] += $pc;	// save percentage
						}
					}
				}
			}
			// OK. Now we have a list of how similar each line on each frame is to our first frame.
			//
		}

	}
}

	}
/*		if (count($page) > 1) {
			// make a table of each frame's content
			$contents = array();
			foreach ($page as $i => $frame){
				$contents[$i] = $vfile->returnScreen($frame, 'simple');	// simple returns just visible chars, \n at eol..
			}
			// $page[n] contains vfile idx, $contents[n] for the same "n" is the text,


			// now, for each frame ..
			$score = array();
			foreach ($contents as $i => $content){
				foreach ($contents as $j => $content1){
					$content = str_replace(' ','',$content);
					$content1 = str_replace(' ','',$content1);

					$score[$j] = levenshtein($content, $content1, 1,1,0);

					$diff = $score[$j] / strlen($content);

					if ($diff > 50) {		// half of all characters are different
						unset($score[$j]);	// forget this frame.
					}
				}
				// SO now we have a list of scores for comparable frames.
				// Now try and build a new frame based on the best match



			}
			// if each frame is very different, they are probably separate subframes.
			// if several are pretty similar, they are probably just decoding errors of the same frame.
			// in that case, we need to merge them.

			// So.  We need to


			}

		}
	}

}


*/