<?php
require('common/db_connect.php');
require('common/header.php');
require('common/functions.php');
require('common/About.php');
require('common/Region.php'); 
header('Content-Type: text/html; charset=utf-8');
require('common/lib/detectmobilebrowser.php');
// Mobile
require('common/lib/detectmobilebrowser.php');
if ($agent_is_mobile && !$server_is_mobile) {
	header('Location: http://m.calltia.com/');
	exit;
}
if (!$agent_is_mobile && $server_is_mobile) {
	header('Location: http://calltia.com/');
	exit;
}
//$server_is_mobile = true;
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
<title>Tenants in Action</title>
<meta name="viewport" content="width=device-width, initial-scale=1"> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
<link type="text/css" href="<?=$docroot?>common/css/south-street/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="<?=$docroot?>common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="<?=$docroot?>common/js/jquery-ui-1.8.21.custom.min.js"></script>
<? if ($server_is_mobile): ?>
<!-- For mobile -->
<link rel="stylesheet" href="<?=$docroot?>common/css/swatch-tia.css" />
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.css" />
<script src="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.js"></script>
<link type="text/css" href="<?=$docroot?>app/css/mobile.css" rel="stylesheet" />
<? else: ?>
<!-- For screen -->
<link type="text/css" href="<?=$docroot?>app/css/screen.css" rel="stylesheet" />
<? endif ?>
<script type="text/javascript">
var selected_language = '<?=$selected_lang?>';  // global;
var get_vars = function(remove, add) {
	var hashes = (-1==window.location.href.indexOf('?')) ? [] : window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	if ('undefined'!=typeof(remove)||remove.length) {
		var arr = [];
		for (var j = 0; j < hashes.length; j++) {
			if (-1!=hashes[j].indexOf(remove+'=')) continue;
			arr.push(hashes[j]);
		}
		hashes = arr;
	}
	if ('undefined'!=typeof(add)) hashes.push(add);
	return (hashes.join('&'));
}
function add_link_vars(the_link) {
	var $link = $(the_link);
	var url = $link.attr('href');
	if (url.substr(-1,1)!='/') url += '/';
	url += '?lang='+selected_language; 
	$link.attr('href', url);
	return true;
}
$(function() {
	// Buttons
	$(".button:not('.screen_no_render')").button();
	// Resize main image for small screens
	var viewportWidth = $(window).width();
	var smallScreenWidth = 600;
	if (viewportWidth < smallScreenWidth) {
		$('#home_image_wrapper').attr('id', 'home_image_wrapper_small');
		$('#home_text').attr('id', 'home_text_small');
	}
	// Language select
	$('input[name="lang"]').change(function() {
		var lang = $('input[name="lang"]:checked').val();
		var url = (-1==window.location.href.indexOf('?')) ? window.location.href : window.location.href.slice(0,window.location.href.indexOf('?'));
		window.location.href = url+'?'+get_vars('lang','lang='+lang);
	});
});

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

<div data-role="page" id="one" class="page home">
  
 	<div id="home_header" data-role="header">
		<h1><?=$common[$selected_lang]['title']['title']?></h1>
	</div><!-- /header -->
  
	<div data-role="content">
		<noscript><?=$common[$selected_lang]['no_script']['title']?></noscript>
		<div id="home_image_wrapper">
			<img src="about/images/TenantsInAction-MobileApp-Info-Panel1-w276.png" />
		</div>
		<div id="home_text">
			<fieldset data-role="controlgroup" data-type="horizontal">	
				<input data-inline="true" type="radio" name="lang" id="lang_en" value="en" <?=(('en'==$selected_lang)?'checked':'')?> /><label data-inline="true" for="lang_en"> English</label> 
				<input data-inline="true" type="radio" name="lang" id="lang_es" value="es" <?=(('es'==$selected_lang)?'checked':'')?> /><label for="lang_es"> Espa&ntilde;ol</label>	
			</fieldset>		
			<h3 id="home_inline_header"><?=$common[$selected_lang]['title']['title']?></h3>
			<p>
			<?=nl2br($common[$selected_lang]['description']['title'])?>
			<br clear="both" /><br />
	    	<?=nl2br($common[$selected_lang]['city_select_then_launch']['title'])?>
	    	<br />
			<select name="region">
			   <option value="la">Los Angeles</option>
			</select>&nbsp; 
	    	<a class="button small ui-select-slim-margin" href="http://<?=(($agent_is_mobile)?'m.':'')?>calltia.com/app/la/" rel="external" onclick="return add_link_vars(this);"><?=$common[$selected_lang]['launch']['title']?></a>			
			<br />
			<?=$common[$selected_lang]['or_about']['title']?>
			<br />
			<a class="button small" href="http://<?=(($agent_is_mobile)?'m.':'')?>calltia.com/about/" rel="external" onclick="return add_link_vars(this);"><?=$common[$selected_lang]['about']['title']?></a>
		</div>
	</div><!--/content -->
	
	<br clear="both" />

	<div data-role="footer" class="notice footer" id="home_footer">
		<? if ($server_is_mobile): ?>	
		<a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_standard']['title']?></a>
		<? else: ?>
		<a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_mobile']['title']?></a>
		<? endif ?>	
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['tos_title']['title']?></a> 
		&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/privacy.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>
	</div><!--/footer -->

</div><!--/page-->

</body>
</html>