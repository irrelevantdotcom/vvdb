<?php

/**
 * Import Directory
 *
 * @version $Id$
 * @copyright 2017
 */


if (!isset($_POST['submit'])) {

	?><html><form action='#' method='post'>
	<textarea name="text"></textarea>
	<input type="submit" name="submit">
	</form> </html>

	<?php
} else {

	$text = $_POST['text'];

	$lines =  preg_split ('/$\R?^/m', $text);
	$results = array();
	foreach ($lines as $line) {

		echo $line;		//  Fred Blogs and Co ................ 4321234
		if (strpos($line,'..') === false) {
			echo ' - No dots found, skipping<br/>';
			$prev = trim($line);
		} else {
			$title = trim(substr($line,0,strpos($line,'..')));
			echo "($prev)($title)";
			if ($prev != '' and strtoupper($prev) == $prev and strtoupper($title) == $title) {
				$title = $prev . ' ' . $title;
				$prev = '';
			}
			$pagenumber = trim(substr($line,strrpos($line,'.')+1));
			$pagenumber = str_replace(array(' ','B'),array('','8'),$pagenumber);
			if (is_numeric($pagenumber)) {

				$results[$title] = $pagenumber;
			}
			echo " - '$title' on '$pagenumber' <br/>";

		}

	}


	echo "<textarea rows=30 cols=120>\n";
	foreach ($results as $key => $value) {
		echo str_replace(',','%2c',str_pad($key,40,' ')).$value."\n";

//		echo "$key$value</td>\n";
	}

	echo "</textarea>";

}

?>