<?php

/**
 *
 *
 * @version $Id$
 * @copyright 2017
 */


include_once('vvdatabase.class.php');
include_once('vv.class.php');
include_once('smarty/libs/Smarty.class.php');
include_once('config.php');

$smarty = new Smarty;
$db = new PDO('mysql:host='.$config['dbserver'].';dbname='.$config['database'].';charset=utf8mb4',
	$config['dbuser'], $config['dbpass'],
	array(PDO::ATTR_EMULATE_PREPARES=>true, // => false,
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

// Connect to database.  Returns error message if unable to connect.

$sqlgetall = $db->prepare("SELECT f.service_id, f.varient_id, f.frame_id, f.subframe_id, f.frame_content, s.short_name, v.varient_date FROM frames f
LEFT JOIN services s ON s.service_id = f.service_id
LEFT JOIN varients v ON v.service_id = f.service_id AND v.varient_id = f.varient_id",
							array(PDO::ATTR_EMULATE_PREPARES=>true));

$results = array();
if ($sqlgetall->execute()) {
	while($frame = $sqlgetall->fetch(PDO::FETCH_ASSOC)){
		$r = locate_hidden($frame['frame_content']);
		if (is_array($r)) {
			$frame['texts'] = $r;
			$frame['url']  = '/'.$frame['short_name'].'/'.date('YmdHis',$frame['varient_date']).'/'.$frame['frame_id'].$frame['subframe_id'];
unset ($frame['frame_content']);
print_r($frame); echo "<br />";
			$results[] = $frame;
		}
	}
}
$smarty->assign('results', $results);

$smarty->display('templates/hiddentext.tpl');

exit;

function locate_hidden($text){
	$r = array();
	$cnt = 0;

	$dh = 0;								// no dh so far
	for ($row = 0; $row < 25; $row++) {		// start each line with:
		$fg = 7;							// white forefgeound
		$bg = 0;							// black background
		$cc = 0;							// no conceal

		$pdh = $dh;							// remember previous lines dh status
		$dh = 0;							// no double height


		for ($col = 0; $col < 40; $col++) {
			if ($row * 40 + $col >= strlen($text)) {
				continue;
			}
			$char = ord($text{$row * 40 + $col});

			switch($char){
				case 0:			// black
				case 1:			// red
				case 2:			// green
				case 3:			// yellow
				case 4:			// blue
				case 5:			// magenta
				case 6:			// cyan
				case 7:			// white
					$fg = $char;
					$cc = 0;	// kill conceal
					break;
				case 13:		// double height
					if (!$pdh) {	// previous line was not a first dh line
						$dh = 1;	// flag as a new dh row found
					}
					break;
				case 16:		// grfx black	(these still affect new background code)
				case 17:		// red
				case 18:		// green
				case 19:		// yellow
				case 20:		// blue
				case 21:		//magenta
				case 22:		// cyan
				case 23:		// white
					$fg = $char - 16;
					$cc = 0;	// kill conceal
					break;
				case 24:		// conceal
					$cc = 1;
					break;

				case 28:		// black background
					$bg = 0;
					break;
				case 29:		// new background
					$bg = $fg;
					break;
				default:
					;
			} // switch


			if ($char > 31 ) {		// printable character (except space..)

				if ($cc == 1			// but concealed text found
					or $fg == $bg	// or text is in same colour as background
					or ($pdh and ord($text{$row * 40 + $col - 40}) != $char )) { // or differnet text under a dh
						if ($cnt > 0 && $row == $r[$cnt]['row'] && $col == $r[$cnt]['col']+strlen($r[$cnt]['text'])) {
							$r[$cnt]['text'] .= chr($char);
						} else {
							if ($char > 32) {
								$cnt++;
								$r[$cnt] = array('row' => $row, 'col' => $col, 'text' =>chr($char));
							}
						}
				}
			} //if
		} 	// for $col
	} // for $row

	if (empty($r)) {
		return false;
	}
	return $r;
}