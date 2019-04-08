<?php
require('../../../common/db_connect.php');
require('../../../common/functions.php');
require('../../../common/Parse.php');
require('../../../common/Submission.php');
$save = $_POST;

try {
	$parcel_is_supported = Parse::check_parcel_number_against_service($save['parcel']);
	Submission::save_fields(array('submission_id'=>$save['submission_id'],'parcel_is_supported'=>1));
	$save['error'] = false;
	$save['msg'] = true; 
} catch (Exception $e) {
	Submission::save_fields(array('submission_id'=>$save['submission_id'],'parcel_is_supported'=>0));
	$save['error'] = false;
	$save['msg'] = false;        
}

echo json_encode($save);