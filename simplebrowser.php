<?php

// very simple browser to test things out.

include_once('vvdatabase.class.php');
include_once('vv.class.php');

$db = new vvDb();

$db->connect('localhost','vtext_pages','vtuser', '-0(*&(*I?dwad:PPY');



if (!isset($_GET['sid'])) {

	$services = $db->getServices();
//	var_dump($services);

?>	<p>Please select a service to browse</p>
	<form method=GET action=>
	<select name='sid'>
<?php	foreach ($services as $s) {
		echo '<option value='.$s['service_id'].'>'.$s['service_name']."</option>\n";
	}
?>	</select>
	<input type='submit'/>
	</form>

<?php

} else {

	$sid = $_GET['sid'];
	if (1 != count($services = $db->getServices($sid))) {
		die ('Invalid service requested.');
	}

	$service=reset($services);

	if ($sid != $service['service_id'])
		die ("Something very inconsistent happened..");

	echo 'Browsing ' . $service['service_name'];


	if (!isset($_GET['vid'])) {
		$vid = null;
/*		$varients = $db->getVarients($sid);
		var_dump($varients);
		?>	<p>Please select a varient to browse</p>
		<form method=GET action=>
		<input name="sid" type="hidden" value="<?php echo $sid; ?>"/>
		<select name='vid'>
		<?php	foreach ($varients as $v) {
			echo '<option value='.$v['varient_id'].'>'.$v['varient_name']."</option>\n";
		}
		?>	</select>
		<input type='submit'/>
		</form>
		<?php

*/
	} else {

		if (1 != count($varients = $db->getVarients($sid,$vid = $_GET['vid']))) {
			die ('Invalid varient requested.');
		}
		$varient = reset($varients);

		if ($vid != $varient['varient_id'])
			die ("Something else very inconsistent happened..");
	}
	if (!isset($_GET['fid'])) {
		$fid = $service['start_frame'];
		$sfid = '';
	} else {
		$fid = $_GET['fid'];
		$sfid = isset($_GET['sfid']) ? $_GET['sfid'] : '';
	}

	?><form method=GET action=>
	<input name="sid" type="hidden" value="<?php echo $sid; ?>"/>
<?php
	if (empty($sfid)) {
		$frame = $db->getFirstFrame($sid, $vid, $fid);

	} else {
		$frame = $db->getFrame($sid, $vid, $fid, $sfid);
		if (!empty($frame['subframe_id'])) {
			$sfid = $frame['subframe_id'];
		}
	}

	if ($frame === FALSE) {
		echo "frame not found";
		$frames = $db->getAlternateFrames($sid, $vid, $fid);
		if (empty($frames)) {
			echo ' and no alternatives are available.';
		} else {
			echo ' but you can select from...<br/>';
			echo '<input name="fid" type="hidden" value="'.$fid.'"/>';
			echo '<select name="vid">';
			foreach ($frames as $f) {
				$v = $db->getVarients($sid, $f['varient_id']);
				$v = reset($v);
				echo '<option value='.$v['varient_id'].'>'.$v['varient_name']."</option>\n";
			}
			echo '</select>';
		}
	} else {
		echo 'Page found...<br/>';

//			echo $frame['frame_content'];	// very temporary!!

		$i = ViewdataViewer::createImage(false,$frame['frame_content']); // for comms layout ...
		$img = $i['image'];
		$ityp = $i['imagetype'];

		if ($ityp == 'png') {
			ob_start();
			imagepng($img);
			$image_string = ob_get_contents();
			ob_end_clean();
		} else {
			$image_string = $img;
		}



		echo '<img src="data: image/'.$ityp.';base64,'.base64_encode($image_string).'">';

		echo "<br/><br/>\n";

		echo '<input name="vid" type="hidden" value="'.$vid.'"/>';


		echo 'Links - ';
		$m = $db->getFrameMeta($frame['frameunique']);
//		print_r($m);
		foreach ($m as $meta) {
			if (substr($meta['key'],0,5) == 'route' && !empty($meta['value'])) {
				echo '<button type="submit" name="fid" value="'.$meta['value'].'">' . substr($meta['key'],5) . '</button>' ;
			}
		}
	}
	?>
	</form>
	<p>Or enter a new page id -
	<form method=GET action=>
		<input name="sid" type="hidden" value="<?php echo $sid; ?>"/>
	    <input name="vid" type="hidden" value="<?php echo $vid; ?>"/>
	    <input name="fid" type="text"/>;
		<input type="submit" value=">go>">
	</form>
	</p>
<?php
}