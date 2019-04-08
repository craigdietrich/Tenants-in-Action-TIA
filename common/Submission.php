<?php
class Submission { 

	const ERR_INVALID_CAT = 1;
	const ERR_COULD_NOT_SAVE = 2;
	const ERR_INVALID_SUBMISSION_ID = 3;
	const ERR_INVALID_ADDRESSES = 4;
	const ERR_COULD_NOT_SEND = 5;
	const LEVEL_NONE = 0;
	const LEVEL_CAT = 1;
	const LEVEL_SUB_CAT = 2;
	const LEVEL_REVIEW = 3;
	const LEVEL_COMMIT = 4;
	
    public function __construct() {} 

    public static function submit_level($arr=array()) {
    	
    	$address =@ trim($arr['address']);
		$cats =@ $arr['c'];
		$subcats =@ $arr['c2'];	
		$commit =@ ('1'==$arr['commit']) ? true : false;
		
		if (empty($address)) return self::LEVEL_NONE;
		if ($commit) return self::LEVEL_COMMIT;
		if (!empty($subcats)) return self::LEVEL_REVIEW;
		if (!empty($cats)) return self::LEVEL_SUB_CAT;
		if (!empty($address)) return self::LEVEL_CAT;
		return self::LEVEL_NONE;
    	
    }
    
    public static function get($start=0, $total=20, $orderby='submission_id 	', $orderdir='desc') {
    	
		$return = array();
		$result = mysql_query("SELECT * FROM submissions WHERE is_deleted = 0 ORDER BY $orderby $orderdir LIMIT $start,$total");
		if (mysql_errno()!=0) throw new Exception(mysql_error());
		while ($result && $row = mysql_fetch_assoc($result)) {
			$row['violations'] = array();
			$submission_id = (int) $row['submission_id'];
			$result2 = mysql_query("SELECT violations.* FROM violations, submission_violations WHERE submission_violations.submission_id = $submission_id AND violations.violation_id = submission_violations.violation_id ORDER BY submission_violations.violation_id ASC");
			if (mysql_errno()!=0) throw new Exception(mysql_error());
			while ($result2 && $row2 = mysql_fetch_assoc($result2)) {
				$row['violations'][] = $row2;
			}
			$return[] = array_merge($row, unserialize($row['submission_data']));
		}
		return $return;
		
    }
    
    public function get_route($submission_id=0) {
    	
    	$route = null;
    	$routes = array(
    						1=>'lahd',
    						2=>'lacdh'
    					   );
    	
    	$result = mysql_query("SELECT violations.* 
    						   FROM violations, submission_violations
    						   WHERE violations.violation_id = submission_violations.violation_id
    						   AND submission_violations.submission_id = $submission_id");

    	// Check LACDH
    	while ($result && $row = mysql_fetch_assoc($result)) {
    		if (array_key_exists($row['organization_id'],$routes) && 'lacdh'==$routes[$row['organization_id']]) {
    			$route = $routes[$row['organization_id']];
    		}
    	} 
    	// Check LAHD
    	mysql_data_seek($result,0);
    	while ($result && $row = mysql_fetch_assoc($result)) {
    		if (array_key_exists($row['organization_id'],$routes) && 'lahd'==$routes[$row['organization_id']]) {
    			$route = $routes[$row['organization_id']];
    		}
    	}    
    	
    	return $route;
    	
    }
    
    public function save($save=array()) {
    	
    	// Submission
    	$submission_data = array(
    							'language' => trim($save['lang']),
    							'name' => trim($save['name']),
    							'email' => trim($save['email']),
    							'tel' => trim($save['tel']),
    							'address' => trim($save['address']),
    							'complaint' => trim($save['notes'])
    							);
    	$submission_data = (get_magic_quotes_gpc()) ? serialize($submission_data) : mysql_real_escape_string(serialize($submission_data));
		$notes = (get_magic_quotes_gpc()) ? $save['notes'] : mysql_real_escape_string($save['notes']);
		$parcel = (get_magic_quotes_gpc()) ? $save['apn'] : mysql_real_escape_string($save['apn']);
    	$parcel_is_supported = (isset($save['address_bypass']) && $save['address_bypass']) ? 0 : 1;
		$result = mysql_query("INSERT INTO submissions SET submission_data = '".$submission_data."', parcel = '".$parcel."', parcel_is_supported = $parcel_is_supported");
    	if (mysql_errno()!=0) throw new Exception(self::ERR_COULD_NOT_SAVE);
    	$submission_id = (int) mysql_insert_id();
    	
    	// Violations
    	$subcats =@ $save['c2'];
    	if (empty($subcats)) throw new Exception(self::ERR_INVALID_CAT);
    	$subcats = Category::get($subcats);
    	if (empty($subcats)) throw new Exception(self::ERR_INVALID_CAT);
	    $violations = array();
	    $used = array();
	    foreach ($subcats as $subcat) {
	    	foreach ($subcat['violations'] as $violation) {
	    		if (in_array($violation['violation_id'], $used)) continue;
	    		$used[] = $violation['violation_id'];
	    		$violations[] = $violation['violation_id'];
	    	}
	    }    	
    	foreach ($violations as $violation_id) {
    		$result = mysql_query("INSERT INTO submission_violations SET submission_id = $submission_id, violation_id = $violation_id");
    		if (mysql_errno()!=0) throw new Exception(self::ERR_COULD_NOT_SAVE);
    	}
    	
    	return $submission_id;
    	
    }
    
    public function send($send=array(), $submission_id=0) {
    	
    	switch ($send['route']) {
		    case 'lacdh':
		        self::send_to_lacdh($send);
		        break;
		    case 'lahd':
		        self::send_to_lahd($send);
		        break;		        
		    default:
		    	throw new Exception('Could not find a send path for '.$send['route']);
		}   	
    	
    }    
    
    public static function send_to_lahd($send, $submission_id=0) {
    	
    	$curl = curl_init();
    	$options = array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_TIMEOUT => 10,
		    CURLOPT_POST => 0,
		    CURLOPT_URL => 'http://cris.lacity.org/cris/informationcenter/code/propprofile.htm',
		    CURLOPT_HTTPGET => true
		);
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);;
		curl_close($curl);
		if (!$result) throw new Exception(self::ERR_COULD_NOT_SEND);
		
		print_r($result);
		exit;
		return $result;
    	
    }
    
    public static function send_to_lacdh($send) {
    	
    	// TODO: this doesn't actually send
    	
    	$submission_id = (int) $send['submission_id'];
    	
    	$results = mysql_query("SELECT *, DATE_FORMAT(somedate, '%m/%d/%Y') AS human_date FROM submissions WHERE submission_id = ".$submission_id);
    	if (!mysql_num_rows($results)) throw new Exception('Could not find submission in the database');
    	$row = mysql_fetch_assoc($results);
    	$data = unserialize($row['submission_data']);
		
    	$notes = 'There was a roach or vermon problem reported at this location by the Tenants in Action app at http://callTIA.com'."\n\n";
    	$notes .= $row['notes'];
    	
    	$name = $data['name'];
    	$name_arr = explode(' ', $name);
    	if (count($name_arr)<=1) {
    		$fname = $name;
    		$lname = '';
    	} else {
    		$fname = $name_arr[0];
    		unset($name_arr[0]);
    		$lname = implode(' ', $name_arr);
    	}
    	
    	$post = array(
    				 'type' => 'Housing-Apartment/Condo',
    				 'incdate' => $row['human_date'],
    				 'incloc' => $data['address'],
    				 'incident' => $notes,
    				 'answer2' => 'Yes',
    				 'answer3' => 'Constantly Occurring',
    				 'title' => 'Mr.',
    				'fname' => $fname,
    				'lname' => $lname,
    				'phone1' => $data['tel'],
    				'phone2' => $data['tel'],
    				'fax' => '',
    				'email1' => $data['email'],
    				'addr1' => $data['address'],
    				'zip' => '11111',
    				'contact' => 'phone',
    				'answer4' => '8:00am to 10:00am'
    				 );
    	
    }    
    
    public static function notify($arr=array(), $submission_id=0) {
    	
    	self::notify_admin($arr, $submission_id, array('craigdietrich@gmail.com'));
    	// , 'info@saje.net'
    	
    	self::notify_user($arr, $submission_id, $arr['email']);
    	
    }
    
    public static function notify_admin($arr=array(), $submission_id=0, $addresses=array()) {
    	
    	if (empty($submission_id)) throw new Exception(self::ERR_INVALID_SUBMISSION_ID);
    	if (empty($addresses)) throw new Exception(self::ERR_INVALID_ADDRESSES);
    	if (!is_array($addresses)) $addresses = array($addresses);
    	
        foreach ($addresses as $to) { 
    	
			$subject = "Tenants in Action (TIA) Submission: ".date('r');
			
			// compose headers
			$headers = "From: Tenants in Action <no-reply@calltia.com>\r\n";
			$headers .= "Reply-To: info@saje.net\r\n";
			$headers .= "X-Mailer: PHP/".phpversion();
			
			// compose message
			$message = "A new violation report was submitted to Tenants in Action at http://callTIA.com.\r\n\r\n";
			$message .= "The submission was NOT automatically sent to city agencies. ";
			$message .= "However, a new record has been placed in the TIA database for management, available at: http://calltia.com/tools/manage\r\n\r\n";
			$message .= "Language: ".$arr['lang']."\r\n";
			$message .= "Full Name: ".$arr['name']."\r\n";
			$message .= "Email: ".$arr['email']."\r\n";
			$message .= "Telephone: ".$arr['tel']."\r\n";
			$message .= "Address: ".$arr['address']."\r\n";
			$message .= "Parcel: ".$arr['apn']."\r\n";
			$message .= "Violations:\r\n";
			
			$violations = array();
			$result = mysql_query("SELECT violations.* FROM violations, submission_violations WHERE submission_violations.submission_id=$submission_id AND violations.violation_id=submission_violations.violation_id ORDER BY violations.violation_id ASC");
			while ($result && $row = mysql_fetch_assoc($result)) {
				$message .= "- ".$row['name_en']."\r\n";
			}
	
			$message .= "Notes:\r\n";
			$message .= (!empty($arr['notes'])) ? $arr['notes'] : '(No notes)';
			$message .= "\r\n";
			
			// send email
			mail($to, $subject, $message, $headers);   	
			
    	}    	
    	
    }
    
    public static function notify_user($arr=array(), $submission_id=0, $addresses=array()) {
    	
    	if (empty($submission_id)) throw new Exception(self::ERR_INVALID_SUBMISSION_ID);
    	if (empty($addresses)) throw new Exception(self::ERR_INVALID_ADDRESSES);
    	if (!is_array($addresses)) $addresses = array($addresses);
    	
        foreach ($addresses as $to) { 
    	
			$subject = "Your Tenants in Action (TIA) Submission";
			
			// compose headers
			$headers = "From: Tenants in Action <no-reply@calltia.com>\r\n";
			$headers .= "Reply-To: info@saje.net\r\n";
			$headers .= "X-Mailer: PHP/".phpversion();
			
			// compose message
			$message = "Thank you for submitting a housing violation using the Tenants in Action (TIA) App at http://callTIA.com.\r\n\r\n";
			$message .= "The submission is being processed using the following information. ";
			if (isset($arr['address_bypass']) && $arr['address_bypass']) {
				$message .= "Please note that the address entered is not supported by our partner agency's submission system. This is most likely because the address is either outside of Los Angeles, or is not a multi-family dwelling, and may complicate TIA's ability to process the submission.";
			}
			$message .= "\r\n\r\n";
			$message .= "Full Name: ".$arr['name']."\r\n";
			$message .= "Email: ".$arr['email']."\r\n";
			$message .= "Telephone: ".$arr['tel']."\r\n";
			$message .= "Address: ".$arr['address']."\r\n";
			$message .= "Parcel: ".$arr['apn']."\r\n";
			$message .= "Violations:\r\n";
			
			$violations = array();
			$result = mysql_query("SELECT violations.* FROM violations, submission_violations WHERE submission_violations.submission_id=$submission_id AND violations.violation_id=submission_violations.violation_id ORDER BY violations.violation_id ASC");
			while ($result && $row = mysql_fetch_assoc($result)) {
				$message .= "- ".$row['name_en']."\r\n";
			}
	
			$message .= "Notes:\r\n";
			$message .= (!empty($arr['notes'])) ? $arr['notes'] : '(No notes)';
			$message .= "\r\n\r\n";
			$message .= "- The TIA Team\r\n";
			
			// send email
			mail($to, $subject, $message, $headers);   	
			
    	}    	
    	
    }    
    
    public static function num() {
    	
    	$result = mysql_query("SELECT COUNT(*) AS count FROM submissions WHERE is_deleted = 0");
    	$row = mysql_fetch_assoc($result);
    	return (int) $row['count'];
    	
    }
    
    public static function save_fields($save) {

    	$submission_id = (int) $save['submission_id'];
    	unset($save['submission_id']);
    	if (empty($submission_id)) throw new Exception('Invalid submission ID');
    	
    	if (isset($save['publish_type'])) {
    		$publish_type = '';
	    	$result = mysql_query("SELECT publish_type FROM submissions WHERE submission_id = ".$submission_id);
	    	$row = mysql_fetch_assoc($result);
	    	if (!empty($row['publish_type'])) $publish_type = $row['publish_type'];
	    	$publish_type_arr = explode(' ', $publish_type);
	    	$publish_type_arr[] = $save['publish_type'];
	    	$save['publish_type'] = implode(' ', $publish_type_arr);
    	}
    	
    	$sql = "UPDATE submissions SET ";
    	$count = 1;
    	foreach ($save as $field => $value) {
    		$sql .= "$field = '".((get_magic_quotes_gpc()) ? $value : mysql_real_escape_string($value))."'";
    		if ($count < count($save)) $sql .= ", ";
    		$count++;
    	}
    	$sql .= " WHERE submission_id = $submission_id";
    
    	$result = mysql_query($sql);
    	if (mysql_errno()!=0) throw new Exception(mysql_error());
    	
    	return $save;
    	
    }
    
    public static function delete($submission_id=0) {

    	if (empty($submission_id)) throw new Exception('Invalid submission ID');
    	$result = mysql_query("UPDATE submissions SET is_deleted = 1 WHERE submission_id = $submission_id");
    	if (mysql_errno()!=0) throw new Exception(mysql_error());
    	return $submission_id;
    	
    }
    
    /**
     * Temp
     */
    
    public static function output_serialized() {
    	
		$result = mysql_query("SELECT submission_data FROM submissions");
		if (mysql_errno()!=0) throw new Exception(mysql_error());
		while ($result && $row = mysql_fetch_assoc($result)) {
			print_r(unserialize($row['submission_data']));
			echo serialize($row['submission_data']);
		}
    	
    }
        
} 
?>