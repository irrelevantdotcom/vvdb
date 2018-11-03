<?php


// viewdata/teletext frame database operations.
//
// (C) 2016 Rob O'Donnell robert@irrelevant.com
//
// BSD Licence applies.
//
// Credits:
// http://wiki.hashphp.org/PDO_Tutorial_for_MySQL_Developers

//
// version 0.1.1


// TODO - specify column names rather than SELECT *  Especially before we populate passwords ..!
// Use array, function, etc., to create list.


// include viewdataviewer class to support some actions.
include_once ('vv.class.php');


//define('_PS_ROOT_DIR_','.');
//include_once('db/Db.php');

// Frame Content Types.  Should this be here, or in whatever actually uses this class?

define ('VVDBTYPE_MATRIX',1);		// Standard 40x(24|25) 1-byte-per-character-position e.g. MODE7
define ('VVDBTYPE_SERIAL',2);		// Serial transmission format. used CR/LF, ESC, etc. e.g. Prestel
define ('VVDBTYPE_STUB',16);		// placeholder. e.g. "we know a page existed here, but no idea what was on it."
define ('VVDBTYPE_IMAGE', 32);		// scanned image - really needs to be replaced.
define ('VVDBTYPE_ERROR', 64);		// vvdb error template.

define ('IMPORT_NEW',1);
define ('IMPORT_VALID',2);
define ('IMPORT_DONE',3);

class vvDb{



	// in this case, bits 0-3 0000=jpeg, 0001=png.



	private $db;		// database handle


	private $sqlgetframe;
	private $sqlgetfirstframe;
	private $sqlgetstub;
	private $sqlgetnextframe;
	private $sqlgetaltframes;
	private $sqlgetsubframes;
	private $sqlgetservices;
	private $sqlgetservicebyname;
	private $sqlgetvarients;
	private $sqlgetvarientbydate;
	private $sqlnewvarient;
	private $sqlgetauthors;
	private $sqlgetusers;
	private $sqlgetuser;
	private $sqladduser;
	private $sqlgetauths;
	private $sqlinsertframe;
	private $sqlinsertmeta;
	private $sqlgetmeta;
	private $sqlfindtext;
	private $sqladdimport;

	private $framefields;

	/*
	   Connect to Database

	*/

	function connect($server, $database, $dbuser, $dbpass) {

		try {
			$this->db = new PDO('mysql:host='.$server.';dbname='.$database.';charset=utf8mb4', $dbuser, $dbpass,
				array(PDO::ATTR_EMULATE_PREPARES=>true, // => false,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		} catch (PDOException $e) {
			return $e->getMessage();
		}

//		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

//		var_dump($this->db);

		$this->sqlgetframe = $this->db->prepare('SELECT * FROM `frames` `f`
					LEFT JOIN `varients` v ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
				    LEFT JOIN `authenticity` `a` ON `a`.`authenticity_id` = `v`.`authenticity`
					LEFT JOIN `users` u ON `v`.`originator_id` = `u`.`user_id`
					WHERE `f`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND (`f`.`service_id`=:sid OR :sid IS NULL) AND (`f`.`varient_id`=:vid OR :vid IS NULL)
					 AND `f`.`frame_id`=:fid AND `f`.`subframe_id`=:subid',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetfirstframe = $this->db->prepare('SELECT * FROM `frames` `f`
					LEFT JOIN `varients` v ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
				    LEFT JOIN `authenticity` `a` ON `a`.`authenticity_id` = `v`.`authenticity`
					LEFT JOIN `users` u ON `v`.`originator_id` = `u`.`user_id`
					WHERE `f`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND (`f`.`service_id`=:sid OR :sid IS NULL) AND (`f`.`varient_id`=:vid OR :vid IS NULL)
					 AND `f`.`frame_id`=:fid
					 ORDER BY `f`.`subframe_id` LIMIT 1 ',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetstub = $this->db->prepare('SELECT * FROM `frames` `f`
					LEFT JOIN `varients` v ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
					LEFT JOIN `services` `s` ON `s`.`service_id` = `v`.`service_id`
				    LEFT JOIN `authenticity` `a` ON `a`.`authenticity_id` = `v`.`authenticity`
					LEFT JOIN `users` u ON `v`.`originator_id` = `u`.`user_id`
					WHERE `f`.`frame_content_type` = '.VVDBTYPE_STUB.' AND (`f`.`service_id`=:sid OR :sid IS NULL) AND (`f`.`varient_id`=:vid OR :vid IS NULL)
					 AND `f`.`frame_id`=:fid
					 ORDER BY `f`.`subframe_id` LIMIT 1 ',
		array(PDO::ATTR_EMULATE_PREPARES=>true));

		$this->sqlgetaltframes = $this->db->prepare('SELECT v.service_id, v.varient_id, v.varient_date, v.varient_name, v.description, IFNULL(f.frame_id,ff.frame_id) as frame_id,
   IFNULL(f.subframe_id,ff.subframe_id) as subframe_id , IFNULL(f.frameunique, ff.frameunique) as frameunique, IFNULL(f.frame_content,ff.frame_content) as frame_content,
   `u`.`displayname`, `s`.`short_name`,`a`.`auth_description`, `a`.`auth_name`, `s`.`service_id`, `s`.`service_name`, IFNULL(f.frame_description, ff.frame_description) as frame_description
   FROM varients v
    LEFT JOIN `authenticity` `a` ON `a`.`authenticity_id` = `v`.`authenticity`
	LEFT JOIN `users` `u` ON `v`.`originator_id` = `u`.`user_id`
	LEFT JOIN `services` `s` ON `s`.`service_id` = `v`.`service_id`
   LEFT JOIN `frames` f ON `f`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id` AND  `f`.`frame_id` = :fid AND `f`.`subframe_id` = :subid
   LEFT JOIN `frames` ff ON `ff`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND `ff`.`varient_id` = `v`.`varient_id` AND `ff`.`service_id` = `v`.`service_id` AND  `ff`.`frame_id` = :fid
   AND `ff`.`subframe_id` = (select min(`subframe_id`) from `frames` `fff` where `fff`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND`fff`.`service_id` = `v`.`service_id`
   AND `fff`.`varient_id` = `v`.`varient_id` AND `fff`.`frame_id` = :fid )

   where `v`.`service_id` = :sid AND ff.frameunique IS NOT NULL
   ORDER BY `v`.`service_id`, `v`.`varient_date`',
   array(PDO::ATTR_EMULATE_PREPARES=>true));

		$this->sqlgetsubframes = $this->db->prepare('SELECT * FROM `frames` f
					LEFT JOIN `varients` v ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
					WHERE `f`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND (`f`.`service_id`=:sid OR :sid IS NULL) AND IF( :vid IS NULL, 1,`f`.`varient_id` = :vid)
					 AND `f`.`frame_id`=:fid AND IF(:subid IS NULL, 1, `subframe_id`<>:subid)
					 ORDER BY `f`.`frame_id`, `f`.`subframe_id`, `v`.`varient_date`',
				array(PDO::ATTR_EMULATE_PREPARES=>true));

		$this->sqlgetnextframe = $this->db->prepare('SELECT * FROM `frames` `f`
					LEFT JOIN `varients` v ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
					WHERE `f`.`frame_content_type` <> '.VVDBTYPE_STUB.' AND (`f`.`service_id`=:sid  OR :sid IS NULL) AND `f`.`varient_id`=:vid AND `f`.`frame_id`=:fid
					 AND `f`.`subframe_id`>:sfid
					ORDER BY `f`.`subframe_id` LIMIT 1', array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetservices = $this->db->prepare('SELECT * FROM `services`
					WHERE `service_id`=:sid OR :sid IS NULL',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetservicebyname = $this->db->prepare('SELECT * FROM `services`
					WHERE `short_name`=:name',
				 	 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetvarients = $this->db->prepare('SELECT * FROM varients v
							LEFT JOIN `users` u ON `v`.`originator_id` = `u`.`user_id`
					WHERE (service_id=:sid OR :sid IS NULL) AND (varient_id=:vid OR :vid IS NULL)',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetvarientbydate = $this->db->prepare('SELECT * FROM `varients` `v`
							LEFT JOIN `users` `u` ON `v`.`originator_id` = `u`.`user_id`
					WHERE (`service_id`=:sid  OR :sid IS NULL)
					ORDER BY ABS( DATEDIFF( `varient_date`, :date )) ASC LIMIT 1',
			 		array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlnewvarient = $this->db->prepare('INSERT INTO `varients` (`service_id`, `varient_date`, `varient_name`, `originator_id`)
					VALUES (:sid, :vdate, :vname, :oid)',
					array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetauthors = $this->db->prepare('SELECT * FROM `authors`
					WHERE (`author_id`=:aid OR :aid IS NULL)',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetusers = $this->db->prepare('SELECT * FROM `users`
					WHERE (`user_id` = :uid OR :uid IS NULL)',
					array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetuser = $this->db->prepare('SELECT * FROM `users`
					WHERE (`username` = :user)',
					array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetauths = $this->db->prepare('SELECT * FROM `authenticity`
					WHERE (`authenticity_id` = :aid OR :aid IS NULL)
					ORDER BY `auth_score`',
					array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlinsertmeta = $this->db->prepare('INSERT INTO `framemeta` (`frameunique`, `key`, `value`)
					VALUES (:frameunique, :key, :value)',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlgetmeta = $this->db->prepare('SELECT * FROM `framemeta`
					WHERE `frameunique` = :frameunique',
					 array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqlfindtext = $this->db->prepare('SELECT * FROM `frames` `f`
					LEFT JOIN `varients` `v` ON `f`.`varient_id` = `v`.`varient_id` AND `f`.`service_id` = `v`.`service_id`
					LEFT JOIN `services` `s` ON `s`.`service_id` = `f`.`service_id`
					WHERE (`f`.`service_id`=:sid OR :sid IS NULL)
					AND UPPER(`f`.`frame_content`) LIKE :text OR UPPER(`f`.`frame_description`) LIKE :text
					ORDER BY `v`.`varient_date`, `s`.`service_name`, `f`.`frame_id`, `f`.`subframe_id`',
					array(PDO::ATTR_EMULATE_PREPARES=>true));
		$this->sqladdimport = $this->db->prepare('INSERT INTO `importqueue`
						(`user_id`, `service_id`, `file_format`, `file_comment`, `file_data`, `file_status`)
					VALUES (:user, :service, :format, :comment, :file, :status)',
					 array(PDO::ATTR_EMULATE_PREPARES=>false));
		$this->sqladduser = $this->db->prepare('INSERT INTO `users` (`username`, `password`, `authlevel`, `displayname`, `email`)
					VALUES (:user, :pass, :auth, :name, :email)',
					array(PDO::ATTR_EMULATE_PREPARES=>false));


// TODO - this is overkill now we have moved metadata into separate table.
		$ins = 'INSERT INTO frames (';
		$fld = '';
		$this->framefields = array();
		// credit: http://stackoverflow.com/a/15671366
		$rs = $this->db->query('SELECT * FROM frames LIMIT 0');
		for ($i = 0; $i < $rs->columnCount(); $i++) {
			$col = $rs->getColumnMeta($i);
			if ($i) {
				$ins .= ',';
				$fld .= ',';
			}
			$ins .= $col['name'] ;
			$fld .= ':'.$col['name'];
			$this->framefields[] = ':'.$col['name'];
		}
		$ins .= ') VALUES ('.$fld.')';
		$this->sqlinsertframe = $this->db->prepare($ins);
//		echo $ins;

	}

	/*
	   Disconnect !
	*/

	function disconnect(){
		mysql_close();
	}


	/*
	    Fetch the exact frame from the database that we want.
	*/
	 function getFrame($service_id, $varient_id, $frame_id, $subframe_id){
		$this->sqlgetframe->execute(array(':sid'=>$service_id, ':vid'=>$varient_id,
										 ':fid'=>$frame_id, ':subid'=>$subframe_id));
		return $this->sqlgetframe->fetch(PDO::FETCH_ASSOC);
	}


	/*
	   Fetch the first subframe for a page from the database that we want.
		Hmm. Should we just call getNextFrame with a sub of null?
	*/
	function getFirstFrame($service_id, $varient_id, $frame_id){
		$this->sqlgetfirstframe->execute(array(':sid'=>$service_id, ':vid'=>$varient_id,
										 ':fid'=>$frame_id));
		return $this->sqlgetfirstframe->fetch(PDO::FETCH_ASSOC);
	}

	/*
	   Fetch any stubs for a page from the database that we want.
	*/
	function getStub($service_id, $varient_id, $frame_id){
		$this->sqlgetstub->execute(array(':sid'=>$service_id, ':vid'=>$varient_id,
										 ':fid'=>$frame_id));
		return $this->sqlgetstub->fetch(PDO::FETCH_ASSOC);
	}


	// Fetch all other versions of frames from the database that have the right frame_id
	// Can also be used to get all frames by specifying a varient_id of, e.g., null.
	function getAlternateFrames($service_id, $varient_id, $frame_id, $subframe_id = NULL){
		$this->sqlgetaltframes->execute(array(':sid'=>$service_id, //':vid'=>$varient_id,
		':fid'=>$frame_id, ':subid' => $subframe_id));
		return $this->sqlgetaltframes->fetchAll(PDO::FETCH_ASSOC);
	}

	// Fetch all subframes for a page.  Specifing a subframe id gets all but that subframe.
	// Specifying a varient id of null includes all alternate captures too.
	function getSubFrames($service_id, $varient_id, $frame_id, $subframe_id = NULL){
		$this->sqlgetsubframes->execute(array(':sid'=>$service_id, ':vid'=>$varient_id,
		':fid'=>$frame_id, ':subid' => $subframe_id));
		return $this->sqlgetsubframes->fetchAll(PDO::FETCH_ASSOC);
	}

	// Get next frame for a given page. (i.e., for 800b, find 800c.)
	function getNextFrame($id, $varient_id = NULL, $frame_id = NULL, $subframe_id = NULL){
		if ($varient_id == NULL and $frame_id == NULL and $subframe_id == NULL) {
			$row = $this->getUniqueFrame($id);
			$service_id = $row['service_id'];
			$varient_id = $row['varient_id'];
			$frame_id = $row['frame_id'];
			$subframe_id = $row['subframe_id'];
		} else {
			$service_id = $id;
		}
		$this->sqlgetnextframe->execute(array(':sid'=>$service_id, ':vid'=>$varient_id,
		':fid'=>$frame_id, ':subid' => $subframe_id));
		return $this->sqlgetnextframe->fetchAll(PDO::FETCH_ASSOC);
	}

	function findText($text, $service_id = NULL){
		$this->sqlfindtext->execute(array(':sid'=>$service_id, ':text' => '%'.strtoupper(trim($text)).'%' ));
		return $this->sqlfindtext->fetchAll(PDO::FETCH_ASSOC);
	}



	// fetch metadata for a frame.
	// TODO - change to use PDO::FETCH_KEY_PAIR
	function getFrameMeta($id = null){
		$this->sqlgetmeta->execute(array(':frameunique'=>$id));
		$r = $this->sqlgetmeta->fetchAll(PDO::FETCH_ASSOC);
		if (empty($r)) return false;

		$result = array();
		foreach ($r as $f ){
			$result[$f['key']] = $f['value'];
		}
		return $result;
	}




	// Fetch list of services, or specific service if specified
	function getServiceById($service_id = null){
		$x = $this->sqlgetservices->execute(array(':sid'=> $service_id ));
		if ($service_id === null) {
			return $this->sqlgetservices->fetchAll(PDO::FETCH_ASSOC);
		}
		return $this->sqlgetservices->fetch(PDO::FETCH_ASSOC);
	}

	// Fetch list of services, or specific service if specified
	function getServiceByName($name){
		$x = $this->sqlgetservicebyname->execute(array(':name'=> strtolower(trim($name))));
		return $this->sqlgetservicebyname->fetch(PDO::FETCH_ASSOC);
	}

	// Fetch nearest varient to requested date.
	function getVarientByDate($service_id, $date){
		$x = $this->sqlgetvarientbydate->execute(array(':sid' => $service_id, ':date' => date("Y-m-d H:i:s", $date)));
		return $this->sqlgetvarientbydate->fetch(PDO::FETCH_ASSOC);
	}


	// fetch list of varients for a service, or specific varient if specified.
	function getAllVarients($service_id = null, $varient_id = null){
		$this->sqlgetvarients->execute(array(':sid'=> isset($service_id) ? $service_id : null,
											 ':vid'=> isset($varient_id) ? $varient_id : null));
		return $this->sqlgetvarients->fetchAll(PDO::FETCH_ASSOC);
	}

	function newVarient($service_id, $date, $name, $originator){
		$this->sqlnewvarient->execute(array(':sid' => $service_id, ':vdate' => date("Y-m-d H:i:s", $date), ':vname' => $name, ':oid' =>$originator));
		return $this->db->lastInsertId();
	}


	// fetch a list of authors, or a specific author if specified.
	function getAuthors($author_id = null){
		$this->sqlgetauthors->execute(array(':aid' => $author_id));
		return $this->sqlgetauthors->fetchAll(PDO::FETCH_ASSOC);
	}

	// fetch a list of users(/contributors), or a specific user if specified.
	function getUsers($user_id = null){
		$this->sqlgetusers->execute(array(':uid' => $user_id));
		return $this->sqlgetusers->fetchAll(PDO::FETCH_ASSOC);
	}

	function validateUser($username, $password){
		$this->sqlgetuser->execute(array(':user' => $username));
		$user = $this->sqlgetuser->fetch(PDO::FETCH_ASSOC);
		if (password_verify($password, $user['password'])) {
			return $user;
		}
		return false;
	}

	function addUser($username, $password, $displayname, $email, $authlevel = 0){

		if (empty($password) || !($pw = password_hash($password, PASSWORD_DEFAULT))) {
			return false;
		}

		try {
			if ($this->sqladduser->execute(array(':user' => $username, ':pass' => $pw,
						':name' => $displayname, ':auth' => $authlevel, ':email' => $email))) {
					return $this->db->lastInsertId();
			} else {
				return false;
			}
		} catch (PDOException $e) {
			return false;
		}
	}

	function getAuthenticities($auth_id = null){
		$this->sqlgetauths->execute(array(':aid' => $auth_id));
		return $this->sqlgetauths->fetchAll(PDO::FETCH_ASSOC);
	}

	// Store a frame!
	function storeFrame($service_id, $frame_id, $varient_id, $data){

		if (!is_array($frame_id)) {
			$frame_id = array($frame_id,"");
		}

		$new = array_fill_keys($this->framefields, null);

		$new[':service_id']  = $service_id;
		$new[':varient_id'] = $varient_id;
		$new[':frame_id'] = $frame_id[0];
		$new[':subframe_id'] = $frame_id[1];

		$new[':frame_content'] = $data['frame_content'];
			unset ($data['frame_content']);
		$new[':frame_content_type'] = $data['frame_content_type'];
			unset ($data['frame_content_type']);


//		print_r($new);
		try {
			$this->sqlinsertframe->execute($new);
		} catch (PDOException $e) {
//			if ($e->getCode() == 1062) {
				// Take some action if there is a key constraint violation, i.e. duplicate name
				return false;
//			} else {
//				throw $e;
//			}
		}

		$id = $this->db->lastInsertId();

		// TODO - save authors in cross-reference table.


		foreach ($data as $key => $value) {
			$new = array('frameunique' => $id,
						':key' => $key,
						':value' =>  $value)
			;
			$this->sqlinsertmeta->execute($new);
		}

		return $id;
	}


	function addImportQueue($filename, $user, $format, $service_id, $comment, $status = IMPORT_NEW ){
		$fh = fopen($filename, 'rb');
		if (!$fh) {
			return false;
		}
/*		//		VALUES (:user, :service, :format, :commment, :file, :status)',
		$params = array(	':user' => $user['user_id'],
							':format' => $format,
							':service' => $service_id,
							':comment' => $comment,
							':status' => $status) ;
		$this->sqladdimport->bindParam(':file', $fh, PDO::PARAM_LOB);
		foreach ($params as $key => &$val){
			$this->sqladdimport->bindParam($key,$val);
		}

		$this->sqladdimport->execute();
		$id = $this->db->lastInsertId();
*/
		$fh = fopen($filename, 'rb');
		$this->sqladdimport->bindValue(':file', $fh, PDO::PARAM_LOB);
		$this->sqladdimport->bindValue(':user', $user['user_id'],PDO::PARAM_INT);
		$this->sqladdimport->bindValue(':format', $format,PDO::PARAM_INT);
		$this->sqladdimport->bindValue(':service', $service_id,PDO::PARAM_INT);
		$this->sqladdimport->bindValue(':comment', $comment,PDO::PARAM_STR);
		$this->sqladdimport->bindValue(':status', $status,PDO::PARAM_INT);
		$this->sqladdimport->execute();
		$id = $this->db->lastInsertId();

		return $id;
	}

}