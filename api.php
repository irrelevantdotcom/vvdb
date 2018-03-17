<?php

/**
 * API		* DRAFT 0.1 *
 *
 * @version $Id$
 * @copyright 2017
 */


	$requiredparams = array('key','mode','service');
	$validkeys = array('984u21312');

	foreach ($requiredparams as $p){
		if (!isset($_GET[$p])) {
			die('Incorrect parameters');
		}
	}

	if (array_search($_GET['key'],$validkeys) === false)  {
		die('No permission');
	}

	include_once('vvdatabase.class.php');
	include_once('vv.class.php');

	$db = new vvDb();

	$db->connect('localhost','vtext_pages','vtuser', '-0(*&(*I?dwad:PPY');

	$mode = $_GET['mode'];

	$service = $_GET['service'];
	$collection = isset($_GET['collection']) ? $_GET['collection'] : NULL ;
	$page = isset($_GET['subpage']) ? $_GET['subpage'] : NULL ;
	$subpage = isset($_GET['subpage']) ? $_GET['subpage'] : NULL ;

	$frame = $db->getNextFrame


	switch($mode){
			case '':
				;
				break;
			case :
				;
				break;
			default:
				;
		} // switch