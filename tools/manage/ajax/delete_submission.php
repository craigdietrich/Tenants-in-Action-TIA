<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Submission.php');
$delete = $_POST;

try {
	Submission::delete($delete['submission_id']);
	$delete['error'] = false;
	$delete['deleted'] = true;
	$delete['msg'] = ''; 
} catch (Exception $e) {
	$delete['error'] = true;
	$delete['deleted'] = false;
	$delete['msg'] = $e->getMessage();        
}

echo json_encode($delete);