<?php

class Category { 
    
    public function __construct() {} 
    
    public static function get_all($orderby='category_id', $orderdir='asc') {
    	
		$return = array();
		$result = mysql_query("SELECT * FROM categories ORDER BY $orderby $orderdir");
		while ($result && $row = mysql_fetch_assoc($result)) {
			$return[] = $row;
		};
		return $return;
		
    }
    
    public static function get($category_ids) {

    	$category_ids = (empty($category_ids)) ? null : explode(',',$category_ids);
    	$return = array();
    	if (empty($category_ids)) return $return;
	    $result = mysql_query("SELECT * FROM categories WHERE category_id IN (".implode(',',$category_ids).") ORDER BY name_en ASC");
	    if (mysql_errno()!=0) throw new Exception(mysql_error());
		while ($result && $row = mysql_fetch_assoc($result)) {
			$row['violations'] = array();
			$result2 = mysql_query("SELECT violations.* FROM violations, category_violations WHERE category_violations.category_id = ".$row['category_id']." AND violations.violation_id = category_violations.violation_id ORDER BY violations.violation_id ASC");
			while ($result2 && $row2 = mysql_fetch_assoc($result2)) {
				$row['violations'][] = $row2;
			}
			$return[] = $row;
		}
		return $return;
		
    }    
    
    public static function get_children($parent_ids) {
    	
    	$parent_ids = (empty($parent_ids)) ? null : explode(',',$parent_ids);
    	$return = array();
    	if (empty($parent_ids)) {
    		$result = mysql_query("SELECT * FROM categories WHERE parent_id = 0 ORDER BY name_en ASC");
    	} else {
	    	$result = mysql_query("SELECT * FROM categories WHERE parent_id IN (".implode(',',$parent_ids).") ORDER BY parent_id ASC, name_en ASC");
    	}
	    if (mysql_errno()!=0) throw new Exception(mysql_error());
		while ($result && $row = mysql_fetch_assoc($result)) {
			if (!array_key_exists($row['parent_id'], $return)) {
				$result2 = mysql_query("SELECT * FROM categories WHERE category_id = ".$row['parent_id']);
				if (0==mysql_num_rows($result2)) {
					$return[$row['parent_id']] = array();
					$return[$row['parent_id']]['type'] = 'root';
				} else {
					$return[$row['parent_id']] = mysql_fetch_assoc($result2);
					$return[$row['parent_id']]['type'] = 'parent';
				}
				$return[$row['parent_id']]['children'] = array();
			}
			$return[$row['parent_id']]['children'][] = $row;
		}
    	
		return $return;
		
    }
    
    public static function save($save) {
    	
    	$category_id = (int) $save['category_id'];
    	$name_en = trim($save['category_en']);
    	$name_es = trim($save['category_es']);
    	$parent_id = (int) $save['parent_id'];
    	
    	if (empty($category_id)) {
    		$result = mysql_query("INSERT INTO categories SET name_en = '$name_en', name_es = '$name_es', parent_id = $parent_id");
    		if (mysql_errno()!=0) throw new Exception(mysql_error());
    		$save['category_id'] = mysql_insert_id();
    	} else {
    		$result = mysql_query("UPDATE categories SET name_en = '$name_en', name_es = '$name_es', parent_id = $parent_id WHERE category_id = $category_id");
    		if (mysql_errno()!=0) throw new Exception(mysql_error());
    	}
    	
    	return $save;
    	
    }
    
    public static function delete($category_id=0) {
    	
    	$result = mysql_query("DELETE FROM categories WHERE category_id = $category_id");
    	if (mysql_errno()!=0) throw new Exception(mysql_error());
    	return $category_id;
    	
    }
    
} 
?>