<?php
class Region { 
	
    public function __construct() {}

    public static function get_by_slug($slug) {
    	
    	$result = mysql_query("SELECT * FROM regions WHERE slug = '".$slug."'");
    	if (!mysql_num_rows($result)) return false;
    	$row = mysql_fetch_assoc($result);
    	return $row;
    	
    } 
    
} 
?>