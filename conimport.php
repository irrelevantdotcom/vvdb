<?php

// conimport.php



include("vv.class.php");
include("vvdatabase.class.php");
include_once('config.php');


$db = new vvDb();

// Connect to database.  Returns error message if unable to connect.
$r = $db->connect($config['dbserver'],$config['database'],$config['dbuser'], $config['dbpass']);
if (!empty($r)) {
	http_response_code(500);
	die ($r);
}

print_r($_POST);


$service = $_POST['service'];
$varient_name = $_POST['varient_name'];
$origdate = strtotime($_POST['varient_date']);
$originator = $_POST['originator_id'];
$origauthor = $_POST['author_id'];
$authenticity_id = $_POST['authenticity_id'];
$oldvfsp = '';
$vfile = '';


$varient = $db->newVarient($service, $origdate, $varient_name, $originator, $authenticity_id);


$i = 0;
foreach (array_keys($_POST) as $k){			// run through all post variables
	if (substr($k, 0, 5) == 'vfsp_') {		// every time we hit a vfsp_nn
		$cnt = substr($k,5);				// get the nn
		$vfsp = $_POST[$k];					// and collect all the necessary posts for that row.
		$format = $_POST["format_$cnt"];

		$author = $_POST["author_$cnt"];
		$authenticity = $_POST["authenticity_$cnt"];
		$pagedate = $_POST["date_$cnt"];


		$i = $_POST["index_$cnt"];
		if ($vfsp != $oldvfsp) {
			unset($vfile);
			$vfile = new ViewdataViewer();
			if (!$vfile->LoadFile("./upload/" . $vfsp,  $format, $vfsp )) {
				echo "Error during file identification of /upload/" . $vfsp . " <br />";
			} else {
				echo "File determined to be type " . $vfile->format . " (" . $vfile->vvtypes($vfile->format) . ")<br />";
			}


		}
		if ($vfile->format != 0) {
			echo "Frame $cnt - $vfsp/$i : " . $_POST["pagenum_$cnt"] . $_POST["subpage_$cnt"] ;

			if ($_POST['include_'.$cnt]) {

				$data['frame_content'] = $vfile->ReturnScreen($i, 'internal');


				if ($data['frame_content'] === FALSE) {
					echo 'Unable to convert to internal format<br/>';
					break;
				}
				$data['frame_content'] = str_replace('Todo: implement header row', '                          ',$data['frame_content']);
					$data['frame_content_type'] = VVDBTYPE_MATRIX;

				$r = $vfile->ReturnMetaData($i, NULL);
				foreach ($r as $key){
					if ($key != 'pagenumber') {
						$data[$key] = $_POST[$key . $cnt];
					}
				}

				// page specific overrides
				if ($pagedate != $origdate_date) {
					$data['date'] = date("Y-m-d H:i:s", strtotime($pagedate));
				}
				if ($author != $origauthor) {
					$data['author'] = $author;
				}
				if ($authenticity != $authenticity_id) {
					$data['authenticity'] = $authenticity;
				}

				$result = $db->storeFrame($service,array($_POST["pagenum_$cnt"],$_POST["subpage_$cnt"]), $varient, $data);

				if ($result) {
					echo " Frame $result stored.<br/>\n";
				} else {
					echo "Unable to save frame $result <br/>\n";
				}
			} else {
				echo "Skipped.<br/>\n";
			}
		}
	}
}