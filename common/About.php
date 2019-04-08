<?php
class About { 
	
    public function __construct() {}

    public static function get_languages($orderby='language', $orderdir='asc') {
    	$return = array();
		$result = mysql_query("SELECT DISTINCT language FROM about ORDER BY $orderby $orderdir");
		while ($result && $row = mysql_fetch_assoc($result)) {
			$return[] = $row['language'];
		}    	
    	return $return;
    }
    
    public static function get_all_by_language($order_by='text_id', $orderdir='asc') {
		$return = array();
		$result = mysql_query("SELECT * FROM about ORDER BY $order_by $orderdir");
		while ($result && $row = mysql_fetch_assoc($result)) {
			$lang = trim($row['language']);
			if (!isset($return[$lang])) $return[$lang] = array();
			$return[$lang][] = $row;
		};    	
		return $return;
    }
    
    public static function update_from_post($save) {

    	$arr = array();
    	for ($j = 0; $j < count($save['language']); $j++) {
    		$arr[$j] = array();
    		$arr[$j]['text_id']     = (get_magic_quotes_gpc()) ? $save['text_id'][$j] : mysql_real_escape_string($save['text_id'][$j]);
    		$arr[$j]['language']    = (get_magic_quotes_gpc()) ? $save['language'][$j] : mysql_real_escape_string($save['language'][$j]);
    		$arr[$j]['slug']        = (get_magic_quotes_gpc()) ? $save['slug'][$j] : mysql_real_escape_string($save['slug'][$j]);
    		$arr[$j]['short_title'] = (get_magic_quotes_gpc()) ? $save['short_title'][$j] : mysql_real_escape_string($save['short_title'][$j]);
    		$arr[$j]['title']       = (get_magic_quotes_gpc()) ? $save['title'][$j] : mysql_real_escape_string($save['title'][$j]);
    		$arr[$j]['content']     = (get_magic_quotes_gpc()) ? $save['content'][$j] : mysql_real_escape_string($save['content'][$j]);
    		$arr[$j]['image']       = (get_magic_quotes_gpc()) ? $save['image'][$j] : mysql_real_escape_string($save['image'][$j]);
    	}
    	
    	foreach ($arr as $row) {
    		$text_id = (int) $row['text_id'];
    		$result = mysql_query(
    			"UPDATE about SET 
    			language = '".$row['language']."', 
    			slug = '".$row['slug']."', 
    			short_title = '".$row['short_title']."',
    			title = '".$row['title']."',
    			content = '".$row['content']."',
    			image = '".$row['image']."'
    			WHERE text_id = $text_id");
    		if (mysql_errno()!=0) {
    			echo mysql_error();
    			exit;
    			
    		}
    		if (mysql_errno()!=0) throw new Exception(mysql_error());
    	}
    	return $arr;
    	
    }    
    
} 
?>