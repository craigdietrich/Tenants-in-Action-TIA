<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Category.php');
$save = $_POST;

try {
	if (empty($save['category_en']) && empty($save['category_es'])) {
		Category::delete($save['category_id']);
		$save['error'] = false;
		$save['deleted'] = true;
		$save['msg'] = ''; 
	} else {
   		$save = Category::save($save);
		$save['error'] = false;
		$save['deleted'] = false;
		$save['msg'] = '';    		
	}   
} catch (Exception $e) {
	$save['error'] = true;
	$save['deleted'] = false;
	$save['msg'] = $e->getMessage();        
}

echo json_encode($save);