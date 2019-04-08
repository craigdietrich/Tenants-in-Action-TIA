<?php 
require('../common/db_connect.php');
require('../common/header.php');
require('../common/functions.php');
require('../common/About.php');
require('../common/Region.php');
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
header('Content-Type: text/html; charset=utf-8');
// Region
$title = 'Tenants in Action';
$region =@ Region::get_by_slug($_REQUEST['r']);
if (!$region) {
	header('Location: http://calltia.com');
	exit;
}
// Mobile
require('../common/lib/detectmobilebrowser.php');
if ($agent_is_mobile && !$server_is_mobile) {
	header('Location: http://m.calltia.com/app/'.$region['slug'].'/');
	exit;
}
if (!$agent_is_mobile && $server_is_mobile) {
	header('Location: http://calltia.com/app/'.$region['slug'].'/');
	exit;
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

<div data-role="page" id="one" class="page">
  
 	<div data-role="header">
		<h3><?=nl2br(trim($common[$selected_lang]['app_title']['title']))?></h3>
	</div><!-- /header -->
  
	<div data-role="content">
		<noscript><?=$common[$selected_lang]['no_script']['title']?></noscript>	
		<div class="main_image_wrapper">
			<img class="l" src="<?=$docroot?>app/images/6913141718_2aa626a493_o.jpg" />
			<img class="r" src="<?=$docroot?>app/images/5634838367_786159a8d0_o.jpg" />
		</div>
		<br clear="both" />
		<fieldset data-role="controlgroup" data-type="horizontal" class="lang">	
			<input data-inline="true" type="radio" name="lang" id="lang_en" value="en" <?=(('en'==$selected_lang)?'checked':'')?> /><label data-inline="true" for="lang_en"> English</label> 
			<input data-inline="true" type="radio" name="lang" id="lang_es" value="es" <?=(('es'==$selected_lang)?'checked':'')?> /><label for="lang_es"> Espa&ntilde;ol</label>	
		</fieldset>			
  		<p class="c tos_checkbox_wrapper">
  		<?=nl2br(trim($common[$selected_lang]['where_tos_privacy']['title']))?><br /><br />
		<input type="checkbox" name="tos" value="1" id="tos" /><label for="tos"> <?=$common[$selected_lang]['found-read-accepted']['title']?> <a href="/about/tos.php" target="_blank"><?=$common[$selected_lang]['tos_title']['title']?></a> <?=$common[$selected_lang]['and']['title']?> <a href="/about/privacy.php" target="_blank"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>.</label>
  		<br />
  		<a href="javascript:void(null);" onclick="if (check_tos($(this).closest('.tos_checkbox_wrapper'))) {window.location.href='#two';window.location.reload();}" class="button small" data-role="button" data-inline="true" data-mini="true" data-icon="check"><?=$common[$selected_lang]['yes_continue']['title']?></a>
  		</p>
	</div><!-- /content -->
	
	<div data-role="footer" class="notice footer" id="ui-bar">
		<a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>?lang=<?=$selected_lang?>" data-icon="arrow-l" rel="external"><?=$common[$selected_lang]['back']['title']?></a>
		<? if ($server_is_mobile): ?>	
		&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_standard']['title']?></a>
		<? else: ?>
		&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_mobile']['title']?></a>
		<? endif ?>	
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['tos_title']['title']?></a> 
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/privacy.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>
	</div><!--/footer -->	
  
</div><!-- /page one-->

<div data-role="page" id="two" class="page">
  
 	<div data-role="header">
		<h3><?=nl2br(trim($common[$selected_lang]['app_title']['title']))?></h3>
	</div><!-- /header -->
  
	<div data-role="content">	
		<noscript><?=$common[$selected_lang]['no_script']['title']?></noscript>
		<div class="main_image_wrapper">
			<img class="l" src="<?=$docroot?>app/images/6913141718_2aa626a493_o.jpg" />
			<img class="r" src="<?=$docroot?>app/images/5634838367_786159a8d0_o.jpg" />
		</div>
		<br clear="both" />
		<fieldset data-role="controlgroup" data-type="horizontal" class="lang">	
			<input data-inline="true" type="radio" name="lang_2" id="lang_en_2" value="en" <?=(('en'==$selected_lang)?'checked':'')?> /><label data-inline="true" for="lang_en_2"> English</label> 
			<input data-inline="true" type="radio" name="lang_2" id="lang_es_2" value="es" <?=(('es'==$selected_lang)?'checked':'')?> /><label for="lang_es_2"> Espa&ntilde;ol</label>	
		</fieldset>		
  		<p class="c">
		<?=nl2br(trim($common[$selected_lang]['landlord']['title']))?>
  		<br /><br />
  		<a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>app/la/submit.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" class="button small" data-role="button" data-inline="true" data-mini="true" data-icon="check"><?=$common[$selected_lang]['yes_continue']['title']?></a>
  		
  		</p>
	</div><!-- /content -->
	
	<div data-role="footer" class="notice footer" id="ui-bar">
		<a href="#one" data-icon="arrow-l"><?=$common[$selected_lang]['back']['title']?></a>
		<? if ($server_is_mobile): ?>	
		&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_standard']['title']?></a>
		<? else: ?>
		&nbsp;&nbsp; <a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>app/<?=$region['slug']?>/?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_mobile']['title']?></a>
		<? endif ?>	
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['tos_title']['title']?></a> 
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/privacy.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>
	</div><!--/footer -->	
  
</div><!-- /page two-->

</body>
</html>
