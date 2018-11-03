<?php

/**
 * Submission of a file to the vv database.
 *
 * @version 0.01
 * @copyright 2018 Rob O'Donnell
 */
session_start();


include("vv.class.php");
include("vvdatabase.class.php");
include_once('smarty/libs/Smarty.class.php');
include_once('config.php');

$smarty = new Smarty;

$db = new vvDb();
// Connect to database.  Returns error message if unable to connect.
$r = $db->connect($config['dbserver'],$config['database'],$config['dbuser'], $config['dbpass']);
if (!empty($r)) {
	http_response_code(500);
	die ($r);
}

$vfile = new ViewdataViewer();


if (isset($_POST['action'])) {
	$action = $_POST['action'];
} else {
	if ($_GET['action']) {
		$action = $_GET['action'];
	} else {
		$action = '';
	}
}

//print_r($_POST);
$result = '';

switch(strtolower($action)){
	case 'submit':
		$r = submitFile();
		$smarty->assign('submissionresult',$r);
		$result = '';
		break;
	case 'login':
		if ( $r = $db->validateUser($_POST['user'],$_POST['pass']) ) {
			$result = '';
			$_SESSION['user'] = $r;
		} else {
			$result = '<strong>Incorrect login credentials</strong>';
		}
		break;
	case 'register':
		if ($_POST['pass'] !== $_POST['pass2']) {
			$result = '<strong>Passwords do not match. Please re-enter</strong>';
		} else {
			$name = filter_input(INPUT_POST, "name", FILTER_SANITIZE_STRING);
			$user = filter_input(INPUT_POST, "user", FILTER_SANITIZE_STRING);
			$email= filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
			if ($name && $user && $email && ($r = $db->addUser($user, $_POST['pass'], $name, $email))) {
				$result = '<strong>User created</strong>';
				$_SESSION['user'] = array('user_id' => $r, 'username' => $user, 'displayname' => $name );
				break;
			} else {
				$result = '<strong>Unable to create that user. Plesae try again with different values.</strong>';
			};
		};
	case 'newuser':
		$smarty->assign('registration',true);
		break;




	case 'logout':
		session_destroy();
		unset($_SESSION['user']);
		$result = '<strong>Logged out</strong>';
		break;

	default:
		$result = '';
} // switch


$results = $db->getServiceById(NULL);
$services=array();
foreach ($results as $r){
	$services[$r['service_id']] = $r['service_name'];
}
asort ($services, SORT_NATURAL | SORT_FLAG_CASE);
$smarty->assign('services',$services);

$formats = array();
$i = 1;
while(($desc = $vfile->vvtypes($i)) !== false){
	$formats[$i] = $desc;
	$i++;
}
$smarty->assign('filetypes',$formats);



$smarty->assign('action', $_SERVER['PHP_SELF']);
$smarty->assign('result',$result);
$smarty->assign('loggedin', !empty($_SESSION['user']));
if (isset($_SESSION['user']['user_id'])) {
	$smarty->assign('username',$_SESSION['user']['username']);
	$smarty->assign('name',$_SESSION['user']['displayname']);
}
$smarty->display('templates/submitfile.tpl');
exit;


function submitFile(){
	global $db;

	$r = array();
	$vv = new ViewdataViewer();
	foreach ($_FILES["file"]["error"] as $key => $error) {
		$r[$key]['name'] = basename($name);
		if ($error == UPLOAD_ERR_OK) {
			$tmp_name = $_FILES["file"]["tmp_name"][$key];
			// basename() may prevent filesystem traversal attacks;
			// further validation/sanitation of the filename may be appropriate
			$name = basename($_FILES["file"]["name"][$key]);
			$r[$key]['name'] = $name;
			if (strlen($name) > 250 || !preg_match("`^[-0-9A-Z_\.]+$`i",$name)) {
				$r[$key]['result'] = 'Unacceptable filename';
			} else if ($_FILES['file']['size'][$key] > 2 * 1024 * 1024) {	// 2MB, ~2,048 frames.
				$r[$key]['result'] = 'File too large';
			} else if (!$vv->LoadFile($tmp_name)) {
				$r[$key]['result'] = 'Unrecognised file or other load error';


			} else if ( $id = $db->addImportQueue($tmp_name, $_SESSION['user'],
					$_POST['format'], $_POST['service'], $_POST['comment'], IMPORT_NEW ) ) {

				$keys = array_keys($vv->frameindex);
				$i = reset($keys);
//				$i = array_key_first($vv->frameindex);
				$img = $vv->returnScreen($i, 'imagesize', 250);
				$ityp = $vv->returnScreen($i, 'imagetype');
				if ($ityp == 'png') {
					ob_start();
					imagepng($img);
					$image_string = ob_get_contents();
					ob_end_clean();
				} else {
					$image_string = $img;
				}
				$url =  'data: image/'.$ityp.';base64,'.base64_encode($image_string);

				$r[$key]['image'] = $url;
				$r[$key]['type'] = $vv->vvtypes($vv->format);
				$r[$key]['count'] = $vv->framesfound;

				$r[$key]['result'] = 'Added to queue OK';
			} else {
				$r[$key]['result'] = 'Add to queue Failed';
			}
		} else {
			$r[$key]['result'] = 'File Upload failed';
		}
	}

//print_r($r);

	return $r;
}