<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Submission.php');
$send = $_POST;
$send['has_published'] = 1;
$send['publish_type'] = 'manual';

try {
	Submission::save_fields($send);
	$send['error'] = false;
	$send['closed'] = true;
	$send['msg'] = ''; 
} catch (Exception $e) {
	$send['error'] = true;
	$send['closed'] = false;
	$send['msg'] = $e->getMessage();        
}

echo json_encode($send);