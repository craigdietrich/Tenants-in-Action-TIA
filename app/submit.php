<?php 
ini_set('display_errors',1);
error_reporting(E_ALL);
require('../common/db_connect.php');
require('../common/header.php');
require('../common/functions.php');
require('../common/Region.php');
require('../common/Category.php');
require('../common/Submission.php');
require('../common/Parse.php');
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
header('Content-Type: text/html; charset=utf-8');
// Region
$title = 'Tenants in Action';
$region =@ Region::get_by_slug($_REQUEST['r']);
if (!$region) {
	header('Location: http://calltia.com');
	exit;
}
// Page level
$level = Submission::submit_level($_REQUEST);
// APN
$apn = $user_fields_error = null;
$address_bypass =@ ('1'==$_REQUEST['address_bypass']) ? true : false;
if (Submission::LEVEL_CAT==$level) {
	try { 
		Parse::validate_user_fields($_REQUEST);
		$apn = Parse::get_parcel_number_from_address(str_replace("\n", ", ", trim($_REQUEST['address'])));	
		if (!$address_bypass) Parse::check_parcel_number_against_service($apn);
	} catch (Exception $e) {
		$user_fields_error = $e->getMessage();
		$arr = $_REQUEST;
		unset($arr['address']);
		$level = Submission::submit_level($arr);
	}	
}
// Commit
$commit_error = null;
if (Submission::LEVEL_COMMIT==$level) {
	try {
	    $submission_id = Submission::save($_REQUEST);
	    // Determine the route
	    $route = Submission::get_route($submission_id);
	    switch ($route) {
	    	case 'lahd':
	    		Submission::send_to_lahd($_REQUEST, $submission_id);
	    		break;
	    	case 'lacdh':
	    		$commit_error = 'TIA has saved your submission to our database.  However, because the submission is specific to the Los Angeles County Department of Health, the submission will need to be processed by hand.';
	    		break;
	    	default:
	    		$commit_error = 'Could not find a housing authority to submit to.  Please try again.';
	    }
	    //Submission::notify($_REQUEST, $submission_id);
	} catch (Exception $e) {
	    $commit_error = $e->getMessage();
		$arr = $_REQUEST;
		unset($arr['c2']);
		$level = Submission::submit_level($arr);	    
	}		
}
// Language
$common = $languages = array();
$result = mysql_query("SELECT * FROM common ORDER BY common_id ASC");
while ($result && $row = mysql_fetch_assoc($result)) {
	$lang = $row['language'];
	$slug = $row['slug'];
	if (!in_array($lang, $languages)) $languages[] = $lang;
	if (!isset($common[$lang])) $common[$lang] = array();
	$common[$lang][$slug] = $row;
}
$selected_lang =@ trim($_REQUEST['lang']);
if (!in_array($selected_lang, $languages)) $selected_lang = $languages[0];
// Mobile
require('../common/lib/detectmobilebrowser.php');
?>
<!DOCTYPE html> 
<html> 
<head> 
<title><?=$title?></title> 
<meta name="viewport" content="width=device-width, initial-scale=1"> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
<link type="text/css" href="<?=$docroot?>common/css/south-street/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="<?=$docroot?>common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?=$docroot?>common/js/jquery-ui-1.8.21.custom.min.js"></script>
<script src="<?=$docroot?>app/js/common.js"></script>
<? if ($server_is_mobile): ?>
<!-- For mobile -->
<link rel="stylesheet" href="<?=$docroot?>common/css/swatch-tia.css" />
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.css" />
<script src="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.js"></script>
<link type="text/css" href="<?=$docroot?>app/css/mobile.css" rel="stylesheet" />
<? else: ?>
<!-- For screen -->
<link type="text/css" href="<?=$docroot?>app/css/screen.css" rel="stylesheet" />
<script src="<?=$docroot?>app/js/screen.js"></script>
<? endif ?>
<script>
var selected_language = '<?=$selected_lang?>';  // global;
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-2487623-15']);
_gaq.push(['_setDomainName', 'calltia.com']);
_gaq.push(['_trackPageview']);
(function() {
  var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
  var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
</script>
</head> 
<body> 

<div data-role="header">
	<h3><?=nl2br(trim($common[$selected_lang]['app_title']['title']))?></h3>
</div><!-- /header -->
  
<div data-role="content" class="submit">

	<noscript><?=$common[$selected_lang]['no_script']['title']?></noscript>		
  	<p class="c">
  	
  		<!-- errors -->
		<? if (Parse::ERR_INCOMPLETE_USER_FIELDS==$user_fields_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['incomplete_user_fields']['title']))?></div>
		<? endif; ?>		
		<? if (Parse::ERR_INVALID_ADDRESS==$user_fields_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['no_parcel_num']['title']))?></div>
		<? endif; ?>
		<? if (Parse::ERR_PARCEL_VALIDATE_PROBLEM==$user_fields_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['parcel_service_error']['title']))?></div>
		<? endif; ?>			
		<? if (Parse::ERR_PARCEL_NOT_SUPPORTED==$user_fields_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['unsupported_parcel_num']['title']))?></div>
		<? endif; ?>			  	
		<? if (Submission::ERR_INVALID_CAT==$commit_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['could_not_find_categories']['title']))?></div>
		<? endif; ?>
		<? if (Submission::ERR_COULD_NOT_SAVE==$commit_error): ?>
		<div class="error"><?=nl2br(trim($common[$selected_lang]['problem_saving']['title']))?></div>
		<? endif; ?>		
  	
		<!-- Info -->
		<? if (Submission::LEVEL_NONE==$level): ?>
		<? $back_url = 'http://'.(($server_is_mobile)?'m.':'').'calltia.com/app/la/?lang='.$selected_lang.'&m='.(($server_is_mobile)?'true':'false').'#two'; ?>				
		<form method="get" action="http://<?=(($server_is_mobile)?'m.':'')?>calltia.com/app/la/submit.php?m=<?=(($server_is_mobile)?'true':'false')?>">
			<input type="hidden" name="lang" value="<?=(isset($_REQUEST['lang']))?$_REQUEST['lang']:'en'?>" />
			<?=nl2br(trim($common[$selected_lang]['step_one']['title']))?>
			<br /><br />
			<?=nl2br(trim($common[$selected_lang]['full_name']['title']))?><br /><input class="text_input" type="text" name="name" id="name" value="<?=(isset($_REQUEST['name']))?$_REQUEST['name']:''?>" />	
			<br />		 
			<?=nl2br(trim($common[$selected_lang]['email']['title']))?><br /><input class="text_input" type="email" name="email" id="email" value="<?=(isset($_REQUEST['name']))?$_REQUEST['email']:''?>" />
			<br />
			<?=nl2br(trim($common[$selected_lang]['telephone']['title']))?><br /><input class="text_input" type="tel" name="tel" id="tel" value="<?=(isset($_REQUEST['name']))?$_REQUEST['tel']:''?>" />
			<br />	
			<?=nl2br(trim($common[$selected_lang]['address_with_example']['title']))?><br /><textarea name="address" id="address"><?=(isset($_REQUEST['name']))?$_REQUEST['address']:''?></textarea>	
			<? if (Parse::ERR_INVALID_ADDRESS==$user_fields_error || Parse::ERR_PARCEL_NOT_SUPPORTED==$user_fields_error): ?>
			<br />
			<input type="checkbox" name="address_bypass" value="1" id="address_bypass" /><label for="address_bypass" class="error"> <?=nl2br(trim($common[$selected_lang]['address_bypass']['title']))?></label>
			<br />
			<? endif; ?>		
			<div class="button_spacer"></div>
			<a href="javascript:void(null);" onclick="if (check_info($(this).closest('form'))) $(this).closest('form').submit();" class="button small submit_button" data-role="button" data-inline="true" data-mini="true" data-icon="check" data-transition="slide"><?=$common[$selected_lang]['continue']['title']?></a>
		</form>
  		
		<!-- Root categories -->
		<? elseif (Submission::LEVEL_CAT==$level): ?>
		<? $back_url = 'http://'.(($server_is_mobile)?'m.':'').'calltia.com/app/la/submit.php?lang='.$selected_lang; ?>
		<form method="get" action="http://<?=(($server_is_mobile)?'m.':'')?>calltia.com/app/la/submit.php?m=<?=(($server_is_mobile)?'true':'false')?>">
	  		<input type="hidden" name="lang" value="<?=(isset($_REQUEST['lang']))?$_REQUEST['lang']:'en'?>" />
	  		<input type="hidden" name="c" value="0" />
	  		<input type="hidden" name="name" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['name']):''?>" />
	  		<input type="hidden" name="email" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['email']):''?>" />
	  		<input type="hidden" name="tel" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['tel']):''?>" />
	  		<input type="hidden" name="address" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['address']):''?>" />
	  		<input type="hidden" name="apn" value="<?=$apn?>" />
	  		<input type="hidden" name="address_bypass" value="<?=($address_bypass)?'1':'0'?>" />
	  		<?=nl2br(trim($common[$selected_lang]['step_two']['title']))?>
	  	</form>	
<? 
		$categories =@ Category::get_children();
		foreach ($categories as $cat):
			$name = name($selected_lang, @$cat['name_en'], @$cat['name_es'], $common);
			echo '<fieldset data-role="controlgroup">'."\n";;
			if (!empty($name)) echo '<legend>'.$name.'</legend>';
			foreach ($cat['children'] as $subcat):
				$name = name($selected_lang, $subcat['name_en'], $subcat['name_es'], $common);
?>
		<span class="category_row">
			<input type="checkbox" name="category_<?=$subcat['category_id']?>" id="category_<?=$subcat['category_id']?>" />
			<label for="category_<?=$subcat['category_id']?>"><?=$name?></label>	
		</span>	
<? 
			endforeach;
			echo '</fieldset>';
		endforeach; 
?>
		<div class="button_spacer"></div>
	  	<a href="javascript:void(null);" onclick="if (set_cats($(this).prevAll('form:first'),$(this).parent(),'c')) $(this).prevAll('form:first').submit();" class="button small submit_button" data-role="button" data-inline="true" data-mini="true" data-icon="check" data-transition="slide"><?=$common[$selected_lang]['continue']['title']?></a>
 
		<!-- Subcategories -->
		<? elseif (Submission::LEVEL_SUB_CAT==$level): ?>
		<? $back_url = 'submit.php?'.get_vars('c', 'c=0').''; ?>
		<form method="get" action="http://<?=(($server_is_mobile)?'m.':'')?>calltia.com/app/la/submit.php?m=<?=(($server_is_mobile)?'true':'false')?>">
	  		<input type="hidden" name="lang" value="<?=(isset($_REQUEST['lang']))?$_REQUEST['lang']:'en'?>" />
	  		<input type="hidden" name="c" value="<?=(isset($_REQUEST['c']))?htmlspecialchars($_REQUEST['c']):''?>" />
	  		<input type="hidden" name="c2" value="0" />
	  		<input type="hidden" name="name" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['name']):''?>" />
	  		<input type="hidden" name="email" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['email']):''?>" />
	  		<input type="hidden" name="tel" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['tel']):''?>" />
	  		<input type="hidden" name="address" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['address']):''?>" />
	  		<input type="hidden" name="apn" value="<?=(isset($_REQUEST['apn']))?htmlspecialchars($_REQUEST['apn']):''?>" />
	  		<input type="hidden" name="address_bypass" value="<?=($address_bypass)?'1':'0'?>" />
			<?=nl2br(trim($common[$selected_lang]['step_three']['title']))?>
		</form>
<? 
		$cats =@ trim($_REQUEST['c']);
		$subcats =@ Category::get_children($cats);
		foreach ($subcats as $cat): 
			$name = name($selected_lang, @$cat['name_en'], @$cat['name_es'], $common);
			echo '<fieldset data-role="controlgroup">'."\n";;
			echo '<legend>'.((!empty($name))?$name:'(Empty category name)').'</legend>';
			foreach ($cat['children'] as $subcat):
				$name = name($selected_lang, $subcat['name_en'], $subcat['name_es'], $common);
?>
		<span class="category_row">
			<input type="checkbox" name="category_<?=$subcat['category_id']?>" id="category_<?=$subcat['category_id']?>" />
			<label for="category_<?=$subcat['category_id']?>"><?=$name?></label>	
		</span>	
<? 
			endforeach;
			echo '</fieldset>';
		endforeach; 
?>
		<div class="button_spacer"></div>
	  	<a href="javascript:void(null);" onclick="if (set_sub_cats($(this).prevAll('form:first'),$(this).parent(),'c2')) $(this).prevAll('form:first').submit();" class="button small submit_button" data-role="button" data-inline="true" data-mini="true" data-icon="check" data-transition="slide"><?=$common[$selected_lang]['continue']['title']?></a>
  		
<!-- Commit --> 		
		<? elseif (Submission::LEVEL_REVIEW==$level): ?>
		<? $back_url = 'submit.php?'.get_vars('c2', 'c2=0').''; ?> 
		<form class="commit" method="get" action="http://<?=(($server_is_mobile)?'m.':'')?>calltia.com/app/la/submit.php?m=<?=(($server_is_mobile)?'true':'false')?>">
	  		<input type="hidden" name="lang" value="<?=(isset($_REQUEST['lang']))?$_REQUEST['lang']:'en'?>" />
	  		<input type="hidden" name="c" value="<?=(isset($_REQUEST['c']))?htmlspecialchars($_REQUEST['c']):''?>" />
	  		<input type="hidden" name="c2" value="<?=(isset($_REQUEST['c2']))?htmlspecialchars($_REQUEST['c2']):''?>" />
	  		<input type="hidden" name="commit" value="1" />
	  		<input type="hidden" name="name" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['name']):''?>" />
	  		<input type="hidden" name="email" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['email']):''?>" />
	  		<input type="hidden" name="tel" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['tel']):''?>" />
	  		<input type="hidden" name="address" value="<?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['address']):''?>" />
	  		<input type="hidden" name="apn" value="<?=(isset($_REQUEST['apn']))?htmlspecialchars($_REQUEST['apn']):''?>" />
	  		<input type="hidden" name="address_bypass" value="<?=($address_bypass)?'1':'0'?>" />
	  		<?=nl2br(trim($common[$selected_lang]['final_step']['title']))?>
		    <br /><br />
		    <span class="field_name"><?=nl2br(trim($common[$selected_lang]['full_name']['title']))?></span><br /><?=(isset($_REQUEST['name']))?htmlspecialchars($_REQUEST['name']):''?>
		    <br /><br />
		    <span class="field_name"><?=nl2br(trim($common[$selected_lang]['email']['title']))?></span><br /><?=(isset($_REQUEST['email']))?htmlspecialchars($_REQUEST['email']):''?>
		    <br /><br />
		    <span class="field_name"><?=nl2br(trim($common[$selected_lang]['telephone']['title']))?></span><br /><?=(isset($_REQUEST['tel']))?htmlspecialchars($_REQUEST['tel']):''?>
		    <br /><br />
		    <span class="field_name"><?=nl2br(trim($common[$selected_lang]['address']['title']))?></span><br /><?=(isset($_REQUEST['address']))?nl2br(htmlspecialchars($_REQUEST['address'])):''?>		    		    		    
			<br /><br />
			<span class="field_name"><?=nl2br(trim($common[$selected_lang]['ain']['title']))?></span><br /><?=(isset($_REQUEST['apn']))?$_REQUEST['apn']:''?> <?=(($address_bypass)?'&nbsp; <span class="error">'.nl2br(trim($common[$selected_lang]['may_no_be_supported']['title'])).'</span>':'')?>	 
			<br /><br />			
			<span class="field_name"><?=nl2br(trim($common[$selected_lang]['violations_to_send']['title']))?></span>
<? 
			echo '<ul class="violations">'."\n";
			$used = array();
			$subcats =@ trim($_REQUEST['c2']);
			$subcats =@ Category::get($subcats);
			foreach ($subcats as $subcat):
				foreach ($subcat['violations'] as $violation):
					if (in_array($violation['violation_id'], $used)) continue;
					$used[] = $violation['violation_id'];
					$name = name($selected_lang, $violation['name_en'], $violation['name_es'], $common);
					echo '<li>'.$name.'</li>'."\n";	
				endforeach; 
			endforeach; 
			echo '</ul>'."\n";
?>			
			<span class="field_name"><?=nl2br(trim($common[$selected_lang]['additional_comments']['title']))?></span><br /><textarea name="notes" id="notes"></textarea>	
			<div class="button_spacer"></div>
	  		<a href="javascript:void(null);" onclick="$(this).closest('form').submit();" class="button small submit_button" data-role="button" data-inline="true" data-mini="true" data-icon="check" data-transition="slide">Submit</a>
  		</form>

<!-- Completed -->
		<? elseif (Submission::LEVEL_COMMIT==$level): ?>
		<? $back_url = 'submit.php?'.get_vars('commit', '').''; ?>
		<?
		if (!empty($commit_error)):
			echo '<div class="error">'.$commit_error.'</div>'."\n";
		endif;
		?>
		<form>
	  		<?=nl2br(trim($common[$selected_lang]['complete']['title']))?>
			<br /><br />	
	  		<a href="http://calltia.com/?lang=<?=$selected_lang?>" class="button small submit_button" data-role="button" data-inline="true" data-mini="true" data-icon="check" data-transition="slide"><?=$common[$selected_lang]['return_home']['title']?></a>
  		</form>
  		
<!-- Incorrect level -->
  		<? else: ?>
  		Invalid level. <a href="http://callTIA.com">Return to TIA home</a>.
		<? endif ?>
		
  	</p>
</div><!-- /content -->
	
<div data-role="footer" class="notice footer" id="ui-bar">
	<a href="<?=$back_url?>" data-icon="arrow-l" rel="external"><?=$common[$selected_lang]['back']['title']?></a>
	<? if ($server_is_mobile): ?>	
	&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/submit.php?m=<?=(($agent_is_mobile)?'false':'true')?>&<?=get_vars('m')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_standard']['title']?></a>
	<? else: ?>
	&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/submit.php?m=<?=(($agent_is_mobile)?'false':'true')?>&<?=get_vars('m')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_mobile']['title']?></a>
	<? endif ?>	
	&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['tos_title']['title']?></a> 
	&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/privacy.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>
</div><!--/footer -->		
	
</body>
</html>
