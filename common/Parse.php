<?php
class Parse { 
	
	const ERR_INVALID_ADDRESS = 1;
	const ERR_PARCEL_VALIDATE_PROBLEM = 2;
	const ERR_PARCEL_NOT_SUPPORTED = 3;
	const ERR_INCOMPLETE_USER_FIELDS = 4;
	
    public function __construct() {}

    public static function validate_user_fields($arr=array()) {
    	
    	$name =@ trim($_REQUEST['name']);
    	$email =@ trim($_REQUEST['email']);
    	$tel =@ trim($_REQUEST['tel']);
    	$address =@ trim($_REQUEST['address']);
    	
    	if (empty($name)) throw new Exception(self::ERR_INCOMPLETE_USER_FIELDS);
    	if (empty($tel)) throw new Exception(self::ERR_INCOMPLETE_USER_FIELDS);
    	if (empty($address)) throw new Exception(self::ERR_INCOMPLETE_USER_FIELDS);
    	
    }
    
    public static function get_parcel_number_from_address($str='') {
    	
		$url = 'http://maps.assessor.lacounty.gov/mapping/query_process.asp';
		$get_vars = array(
					'SearchStr' => $str,
					'ain' => '',
					'Street' => '',
					'XStreet' => '',
					'submit' => 'Submit'
					);
					
		try {
			$contents = Parse::get($url, $get_vars, 'get');
			if (stristr($contents,"We were unable to find a match for")) throw new Exception(self::ERR_INVALID_ADDRESS);
 			$dom = new DOMDocument();
			@$dom->loadHTML($contents);
			$xpath = new DOMXpath($dom);
			$elements = $xpath->query("//a[contains(@href,'rolldata')]");
			$return = null;
		  	foreach ($elements as $element) {
   				$nodes = $element->childNodes;
    			foreach ($nodes as $node) {
      				$return = trim($node->nodeValue);
      				break;
    			}
  			}
  			if (!$return) throw new Exception (self::ERR_INVALID_ADDRESS);
		  	return $return;
		} catch (Exception $e) {
		    throw new Exception ($e->getMessage());
		}
					
    } 
    
    public static function check_parcel_number_against_service($apn='') {
    	
    	if (empty($apn)) throw new Exception(self::ERR_INVALID_ADDRESS);
    	
    	$url = 'http://cris.lacity.org/cris/PublicPropProfile.aspx';
		$post_vars = array(
					'StreetNo' => '',
					'StreetName' => '',
					'APN' => urlencode(preg_replace( '/[^0-9]/', '', $apn )),
					'submit' => urlencode('SEARCH'),
					'Source' => urlencode('PropProfile')
					);

		try {
			$contents = Parse::get($url, $post_vars, 'post');
			if (stristr($contents,'Cannot find table 1')) throw new Exception(self::ERR_PARCEL_VALIDATE_PROBLEM);
			if (stristr($contents,'There were no properties matching your search criteria')) throw new Exception(self::ERR_PARCEL_NOT_SUPPORTED);
		} catch (Exception $e) {
		    throw new Exception ($e->getMessage());
		}    	
    	
    }
    
    private static function get($url, $vars, $method) {
    	
    	$get_str = '';
    	foreach ($vars as $var => $val) {
    		$get_str .= $var.'='.urlencode($val).'&';
    	}
		$get_str = rtrim($get_str, '&');
		
    	$curl = curl_init();
    	$options = array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_TIMEOUT => 10,
		    CURLOPT_POST => ((strtolower($method)=='post')?1:0),
		    CURLOPT_URL => $url.((strtolower($method=='get'))?'?'.$get_str:''),
		    CURLOPT_POSTFIELDS => (strtolower($method)=='get')?'':$get_str
		);
		curl_setopt_array($curl, $options);
		$result = curl_exec($curl);;
		curl_close($curl);
	
		if (!$result) throw new Exception(self::ERR_PARCEL_VALIDATE_PROBLEM);
		
		return $result;
    	
    }
    
} 
?>