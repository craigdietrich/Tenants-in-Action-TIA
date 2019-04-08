<?php 
require('../../common/db_connect.php');
require('../../common/functions.php');
require('../../common/About.php');
require('../../common/Category.php');
require('../../common/Submission.php');
$docroot = 'http://calltia.com/';
$action =@ $_POST['action'];
$msg =@ $_REQUEST['msg'];
// Mobile
require('../../common/lib/detectmobilebrowser.php');
if ($agent_is_mobile && !$server_is_mobile) {
	header('Location: http://m.calltia.com/about');
	exit;
}
if (!$agent_is_mobile && $server_is_mobile) {
	header('Location: http://calltia.com/about');
	exit;
}
// Save about page
if ('do_save_about_page'==$action) {
	try {
		About::update_from_post($_POST);
		header('Location: '.$_SERVER['PHP_SELF'].'?msg=saved#about');  
	} catch (Exception $e) {
		header('Location: '.$_SERVER['PHP_SELF'].'?msg=error#about');       
	}	
	exit;
}

$test = array(
    'name' => 'Craig Dietrich',
    'email' => '',
    'tel' => '',
    'address' => '',
    'complaint' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam ac gravida libero. Etiam interdum enim vitae lorem porttitor pretium. Donec varius ultrices urna id molestie. Nunc sollicitudin dui non dui blandit volutpat. Duis porttitor euismod tortor, nec pellentesque tellus laoreet ornare. Suspendisse consectetur, nisl accumsan ultricies porta, tortor neque facilisis ipsum, et tristique eros eros sit amet orci. Quisque non bibendum massa. Curabitur volutpat scelerisque odio id lobortis.'
);
// Languages
$languages = About::get_languages();
$selected_lang = $languages[0];
// About texts
$texts = About::get_all_by_language();
// Categories
$categories = Category::get_all();
// Submissions
$start=(isset($_REQUEST['start'])) ? (int) $_REQUEST['start'] : 0;
$total=20;
$submissions = Submission::get($start, $total);
$num_submissions = Submission::num();
// Print category HTML rows
function print_categories($categories, $parent_id=0, $indent=0) {
	$margin_amount = 40;
	foreach ($categories as $cat):
		if ($parent_id != $cat['parent_id']) continue;
		$margin = $indent * $margin_amount;
?>
				<table cellspacing="1" cellpadding="0" class="expand_parent category_table id_<?=$cat['category_id']?> parent_<?=$cat['parent_id']?>" style="display:<?=($indent?'none':'table')?>;">
				<tr>
					<td class="category_sp" style="width:<?=$margin?>px;"></td>
					<td class="category_id"><?=$cat['category_id']?></td>
					<td class="category_en"><input type="text" name="name_en" value="<?=htmlspecialchars(trim($cat['name_en']))?>" /></td>
					<td class="category_es"><input type="text" name="name_es" value="<?=htmlspecialchars(trim($cat['name_es']))?>" /></td>
					<td class="category_op">
						<a class="button expand" data-role="button" data-inline="true" href="javascript:void(null);">expand</a>&nbsp; 
						| 
						&nbsp;<a class="button save" data-role="button" data-inline="true" href="javascript:void(null);">save</a>
					</td>
				</tr>
				</table>
<? 				print_categories($categories, $cat['category_id'], ($index+1)) ?>	
				<div style="margin-left:<?=(($margin+40)+2)?>px; display:none;" class="category_add parent_<?=$cat['category_id']?>">add</div>	
				
<?
	endforeach;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>TIA Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- For both mobile and screen -->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
<link type="text/css" href="../../common/css/south-street/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../../common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../common/js/jquery-ui-1.8.21.custom.min.js"></script>
<? if ($server_is_mobile): ?>
<!-- For mobile -->
<link rel="stylesheet" href="../../common/css/swatch-tia.css" />
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.css" />
<script src="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.js"></script>
<link type="text/css" href="css/mobile.css" rel="stylesheet" />
<? else: ?>
<!-- For screen -->
<link type="text/css" href="css/screen.css" rel="stylesheet" />
<? endif ?>
<script src="js/manage.js"></script>
</head>
<body>

<div class="return" data-role="header"><div>
	<a class="left button" data-role="button" data-icon="arrow-l" data-inline="true" href="<?=(($agent_is_mobile)?'http://m.calltia.com':'http://calltia.com')?>/../">Home</a> 
</div></div>

<div data-role="content" id="content">

	<h4 class="title">TIA Management&nbsp; &nbsp; <a href="javascript:void(null);" class="refresh" onclick="location.reload();">Reload</a></h4>
	
<? 
if (!empty($msg) && 'saved'==$msg):
	echo '<div class="saved">Content has been saved&nbsp; &nbsp;<a href="'.$_SERVER['PHP_SELF'].'#about">clear msg</a></div>'."\n";
elseif (!empty($msg) && 'error'==$msg):
	echo '<div class="error">There was a problem attemptin to save&nbsp; &nbsp;<a href="'.$_SERVER['PHP_SELF'].'#about">clear msg</a></div>'."\n";
endif;
?>

	<div class="spacer"></div>

	<div data-role="collapsible-set" data-inset="false">

		<div data-role="collapsible" data-collapsed="true" data-theme="tia">

			<h3 class="title">View Submissions</h3>
			<h4 class="subtitle"><a href="#submissions" class="collapse_link">View Submissions</a></h4>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<table cellspacing="1" cellpadding="0">
				<tr>
					<th>ID</th>
					<th>datetime</th>
					<th>name</th>
					<th>email</th>
					<th>phone</th>
					<th>address</th>
					<th>actions</th>	
					<th></th>			
				</tr>				
<? 
	$k = 0;
	foreach ($submissions as $row): 
?>				
				<tr class="expand_parent submission_row id_<?=$row['submission_id']?> fieldset_1">
					<td class="<?=($row['has_published'])?'':'unpublished'?> submission_id"><?=$row['submission_id']?></td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> datetime"><?=date("m M Y, H:i", strtotime($row['datetime']))?></td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> name"><?=$row['name']?></td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> email"><?=$row['email']?></td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> tel"><?=$row['tel']?></td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> address"><?=$row['address']?></td>						
					<td class="<?=($row['has_published'])?'':'unpublished'?> submission_op">
						<a class="button expand" data-role="button" data-inline="true" href="javascript:void(null);">expand</a>&nbsp; 
					<? if (!$row['has_published']): ?>
						|
						&nbsp;<a class="button send" data-role="button" data-inline="true" href="javascript:void(null);">submit</a>&nbsp;
						|
						&nbsp;<a class="button delete" data-role="button" data-inline="true" href="javascript:void(null);">delete</a>
					<? endif ?>
					</td>						
				</tr>
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_2">
					<td class="blank"></td>
					<td class="blank comment_header">violations</td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> violations" colspan="4">
						<? foreach ($row['violations'] as $violation): ?>
						<b><?=$violation['violation_id']?>.</b>&nbsp; <?=$violation['name_en']?><br />
						<? endforeach ?>	
						<? if (empty($row['violations'])): ?>
						(No violations submitted)
						<? endif ?>				
					</td>	
					<td class="blank"></td>									
				</tr>										
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_3">
					<td class="blank"></td>
					<td class="blank comment_header">comment</td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> complaint" colspan="4"><?=(!empty($row['complaint']))?nl2br($row['complaint']):'(No comment)'?></td>	
					<td class="blank"></td>									
				</tr>	
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_4">
					<td class="blank"></td>
					<td class="blank notes_header">parcel #</td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> parcel" colspan="4"><input type="text" name="parcel" value="<?=htmlspecialchars($row['parcel'])?>" /></td>	
					<td class="blank submission_op parcel_op"><a class="button save" data-role="button" data-inline="true" href="javascript:void(null);">save</a></td>									
				</tr>
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_4">
					<td class="blank"></td>
					<td class="blank notes_header">parcel support</td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> parcel_is_supported" colspan="4"><?=(!empty($row['parcel_is_supported']))?'Supported by LAHD':'(Not supported by LAHD)'?></td>	
					<td class="blank"></td>									
				</tr>												
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_5">
					<td class="blank"></td>
					<td class="blank notes_header">SAJE notes</td>
					<td class="<?=($row['has_published'])?'':'unpublished'?> notes" colspan="4"><textarea name="notes"><?=nl2br($row['notes'])?></textarea></td>	
					<td class="blank submission_op notes_op"><a class="button save" data-role="button" data-inline="true" href="javascript:void(null);">save</a></td>									
				</tr>	
				<? if ($row['has_published']): ?>
				<tr class="submission_row parent_<?=$row['submission_id']?> fieldset_6">
					<td class="blank"></td>
					<td class="blank notes_header">Submit type</td>
					<td class="notes" colspan="4"><?=(!empty($row['publish_type']))?$row['publish_type']:'(None listed)'?></td>	
					<td class="blank"></td>										
				</tr>				
				<? endif ?>		
<? 
	$k++;
	endforeach; 
?>
				</table>	
				<div class="row_count">
					<? if ($start): ?>
					<a href="<?=$_SERVER['PHP_SELF']?>?start=<?=($start-$total)?>#submissions">display prev <?=$total?></a>
					&nbsp; &nbsp;
					<? endif ?>
					Displaying rows <?=$start?> - <?=($start+$total)?> of <?=$num_submissions?> total
					<? if (($start+$total)<$num_submissions): ?>
					&nbsp; &nbsp;
					<a href="<?=$_SERVER['PHP_SELF']?>?start=<?=($start+$total)?>#submissions">display next <?=$total?></a>
					<? endif ?>
					&nbsp; &nbsp;|&nbsp; &nbsp; 
					<a href="javascript:alert('Pending...');">Download CSV</a>
				</div>
			</form>
			
		</div>
		
	</div>	

	<div data-role="collapsible-set" data-inset="false">

		<div data-role="collapsible" data-collapsed="true" data-theme="tia">

			<h3 class="title">About Page</h3>
			<h4 class="subtitle"><a href="#about" class="collapse_link">Edit About Page Texts</a></h4>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post" id="about_page_form">
			<input type="hidden" name="action" value="do_save_about_page" />
			<fieldset data-role="controlgroup" data-type="horizontal">
				<input type="radio" name="about_page_lang" id="about_page_lang_en" value="en" <?=(('en'==$selected_lang)?'checked':'')?> /><label for="about_page_lang_en"> English</label> 
				<input type="radio" name="about_page_lang" id="about_page_lang_es" value="es" <?=(('es'==$selected_lang)?'checked':'')?> /><label for="about_page_lang_es"> Espa&ntilde;ol</label>
			</fieldset>
<? foreach ($languages as $lang): ?>
			<table cellspacing="1" cellpadding="0" class="about_page_table <?=$lang?>">
				<tr>
					<th>slug</th>
					<th>short title</th>
					<th>title</th>
					<th>content</th>
					<th>image</th>
				</tr>
<? 
	$k = 0;
	foreach ($texts[$lang] as $row): 
?>
				<tr>
					<td style="display:none;"><input type="hidden" name="text_id[]" value="<?=$row['text_id']?>" /></td>
					<td style="display:none;"><input type="hidden" name="language[]" value="<?=$row['language']?>" /></td>
					<td class="<?=((0==$k%2)?'e':'o')?>"><input type="text" name="slug[]" value="<?=htmlspecialchars(trim($row['slug']))?>" /></td>
					<td class="<?=((0==$k%2)?'e':'o')?>"><input type="text" name="short_title[]" value="<?=htmlspecialchars(trim($row['short_title']))?>" /></td>
					<td class="<?=((0==$k%2)?'e':'o')?>"><input type="text" name="title[]" value="<?=htmlspecialchars(trim($row['title']))?>" /></td>
					<td class="<?=((0==$k%2)?'e':'o')?>"><textarea name="content[]"><?=trim($row['content'])?></textarea></td>
					<td class="<?=((0==$k%2)?'e':'o')?>">
						<input type="text" name="image[]" value="<?=htmlspecialchars(trim($row['image']))?>" />
<? if (!empty($row['image'])): ?>
						<a href="<?=$docroot.trim($row['image'])?>" target="_blank"><img class="thumb" src="<?=$docroot.trim($row['image'])?>" /></a><br />
<? endif ?>
					</td>
				</tr>
<? 
		$k++;
	endforeach;
?>
			</table>
<? endforeach ?>
			<div class="save"><input class="left button" data-role="button" data-icon="star" data-inline="true" type="submit" value="Save changes" /></div>
			</form>
		</div>
		
	</div>
	
	<div data-role="collapsible-set" data-inset="false">

		<div data-role="collapsible" data-collapsed="true" data-theme="tia">

			<h3 class="title">Categories</h3>
			<h4 class="subtitle"><a href="#categories" class="collapse_link">Edit Categories</a></h4>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="post">
				<input type="hidden" name="action" value="do_save_about_page" />
<? 				print_categories($categories) ?>				
			</form>
			
		</div>
		
	</div>		
	
</div>

<? if ($server_is_mobile): ?>
<div data-role="footer" class="notice ui-bar">
	<small>Not on a mobile device?</small>&nbsp; &nbsp; 
	<a class="button screen_no_render" href="http://calltia.com/tools/manage?m=false" data-role="button" data-icon="refresh" data-inline="true" >Reload page in standard mode</a>
</div>
<? else: ?>
<p>&nbsp;</p>
<div data-role="footer" class="notice ui-bar">
	<small>On a mobile device?</small>&nbsp; &nbsp; 
	<a class="button screen_no_render" href="http://m.calltia.com/tools/manage?m=true" data-role="button" data-icon="refresh" data-inline="true" >Reload page in mobile mode</a>
</div>
<? endif ?>

</body>
</html>