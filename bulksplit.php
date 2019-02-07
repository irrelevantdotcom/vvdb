<?php

/**
 * Split a prestel bulk update file into managable portions
 *
 * @version $Id$
 * @copyright 2019
 */


if ($argc != 3) {
	die( 'Usage: ' . $argv[0]. " <filename> <records>\n" );
}

$file = $argv[1];
if (!file_exists($file)) {
	die ( "File not found\n");
}

$recs = $argv[2];
if ($recs < 50) {
	die ( "Must specify positive number of records to split on\n");
}

$content = file_get_contents($file);

if (strlen($content) == 0) {
	die ( "Unable to read, or empty, file\n");
}

$offset = 0;
$cnt = 0;
$suffix = 0;
$output = '';
while($offset < strlen($content)){
	$reclen = substr($content, $offset, 4);

	$record = substr($content, $offset, $reclen);

	$output .= $record;
	$cnt++;

	if ($cnt > $recs) {
		echo "Writing $file.$suffix\n";
		file_put_contents("$file.$suffix", $output);
		$suffix++;
		$output = '';
		$cnt = 0;
	}
	$offset += $reclen;
}
echo "Writing $file.$suffix\n";
file_put_contents("$file.$suffix", $output);

echo "Done.\n";

