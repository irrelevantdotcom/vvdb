<?php

/**
 * Configuraration
 *
 * @version $Id$
 * @copyright 2017
 */


$config = array();


$config['database'] = 'vtext_pages';
$config['dbserver'] = 'localhost';
$config['dbuser'] = 'vtuser';
$config['dbpass'] = '-0(*&(*I?dwad:PPY';

// Error messages.  These are stored viewdata pages.
// They will be searched for in the context of the current
// service and variant first, then within these defaults:
$config['err_service'] = 1;
$config['err_variant'] = 0;

$config['err_nodata'] = 'NODATA'; 	// Frame exists in DB but has no frame_content