<?php

/**
 *
 * Check if I've understood VT file format ...
 * @version $Id$
 * @copyright 2017
 */


	$filename = "TEST.VTF";

	$data = file_get_contents($filename);
	$filelen = strlen($data);
	// ignore validation check

	$ptr = 1024;	// 0400

	while($ptr < $filelen)){
		$blockstart = $ptr;
		switch(ord($data{$ptr})){
			case 1:	// index
				;
				break;
			case 0: // page
				echo "Page block found\n";

				$f_blocks = ord($data{$ptr+2}) + 256 * ord($data{$ptr+3});

				echo "0002-0003 F Blocks - $f_blocks \n"				;

				$blocklen = ord($data{$ptr+4}) + 256 * ord($data{$ptr+5});

				echo "0004-0007 Block len?? - $f_blocks \n";

				echo "0008-000B All Fs? - " . bin2hex(substr($data,ptr+8,4)) . "\n";

				$ptr += 12;

				while($ptr < $blockstart + $blocklen){

					echo substr('000'.dechex($ptr-$blockstart),-4).' ';
					switch(ord($data{$ptr})){
						case 254: // F Block
							echo 'F Block';

							break;



				}




				;
				break;
			default:
				;
		} // switch


	}