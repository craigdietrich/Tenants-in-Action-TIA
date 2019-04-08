<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Submission.php');
$save = $_POST;

try {
	Submission::save_fields($save);
	$save['error'] = false;
	$save['msg'] = ''; 
} catch (Exception $e) {
	$save['error'] = true;
	$save['msg'] = $e->getMessage();        
}

echo json_encode($save);