<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Submission.php');
require('../../../common/Parse.php');
$send = $_POST;

try {
	Submission::send($send);
	$send['error'] = false;
	$send['sent'] = true;
	$send['msg'] = ''; 
} catch (Exception $e) {
	$send['error'] = true;
	$send['sent'] = false;
	$send['msg'] = $e->getMessage();        
}

echo json_encode($send);