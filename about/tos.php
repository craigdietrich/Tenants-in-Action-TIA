<?php 
require('../common/db_connect.php');
require('../common/header.php');
require('../common/functions.php');
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");
header('Content-Type: text/html; charset=utf-8');
// Mobile
require('../common/lib/detectmobilebrowser.php');
if ($agent_is_mobile && !$server_is_mobile) {
	header('Location: http://m.calltia.com/about/tos.php');
	exit;
}
if (!$agent_is_mobile && $server_is_mobile) {
	header('Location: http://calltia.com/about/tos.php');
	exit;
}
// Language
$common = array();
$result = mysql_query("SELECT * FROM common ORDER BY common_id ASC");
while ($result && $row = mysql_fetch_assoc($result)) {
	$lang = $row['language'];
	$slug = $row['slug'];
	if (!isset($common[$lang])) $common[$lang] = array();
	$common[$lang][$slug] = $row;
}
$languages = array();
$result = mysql_query("SELECT DISTINCT language FROM about ORDER BY language ASC");
while ($result && $row = mysql_fetch_assoc($result)) {
	$languages[] = $row['language'];
}
$selected_lang =@ trim($_REQUEST['lang']);
if (!in_array($selected_lang, $languages)) $selected_lang = $languages[0];
// Texts
$texts = array();
$result = mysql_query("SELECT * FROM about ORDER BY text_id ASC");
while ($result && $row = mysql_fetch_assoc($result)) {
	$lang = trim($row['language']);
	$slug = trim($row['slug']);
	if (!isset($texts[$lang])) $texts[$lang] = array();
	if (!isset($texts[$lang][$slug])) $texts[$lang][$slug] = array();
	$texts[$lang][$slug][] = $row;
};
?>
<!DOCTYPE html>
<html>
<head>
<title>Terms of Service</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<!-- For both mobile and screen -->
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,700,300' rel='stylesheet' type='text/css'>
<link type="text/css" href="../common/css/south-street/jquery-ui-1.8.21.custom.css" rel="stylesheet" />
<script type="text/javascript" src="../common/js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../common/js/jquery-ui-1.8.21.custom.min.js"></script>
<? if ($server_is_mobile): ?>
<!-- For mobile -->
<link rel="stylesheet" href="../common/css/swatch-tia.css" />
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.css" />
<script src="http://code.jquery.com/mobile/1.2.0-alpha.1/jquery.mobile-1.2.0-alpha.1.min.js"></script>
<link type="text/css" href="css/mobile.css" rel="stylesheet" />
<? else: ?>
<!-- For screen -->
<link type="text/css" href="css/screen.css" rel="stylesheet" />
<? endif ?>
<script type="text/javascript">
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
$(function() {
	
	$(".button:not('.screen_no_render')").button();

	$("div.ui-collapsible-set").live("expand", function(e) {
	    var top = $(e.target).offset().top;
	    if ($(window).scrollTop() > top) $(window).scrollTop(top);
	});

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

<div class="return" data-role="header"><div>
	<a class="left button" data-role="button" data-icon="home" data-inline="true" href="<?=(($agent_is_mobile)?'http://m.calltia.com':'http://calltia.com')?>/?lang=<?=$selected_lang?>" rel="external"><?=$common[$selected_lang]['home']['title']?></a>
	<fieldset class="left languages" data-role="controlgroup" data-type="horizontal">	
		<input type="radio" name="lang" id="lang_en" value="en" <?=(('en'==$selected_lang)?'checked':'')?> /><label for="lang_en"> English</label> 
		<input type="radio" name="lang" id="lang_es" value="es" <?=(('es'==$selected_lang)?'checked':'')?> /><label for="lang_es"> Espa&ntilde;ol</label>	
	</fieldset>
	<br clear="both" />
</div></div>

<br />

<div data-role="content" id="content">

	<noscript><?=$common[$selected_lang]['no_script']['title']?></noscript>

	<p><?=nl2br(trim($common[$selected_lang]['terms_of_service']['title']))?></p>

	<div data-role="collapsible-set" data-inset="false">

</div>

<div data-role="footer" class="notice" id="ui-bar">
<? if ($server_is_mobile): ?>	
	<a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_standard']['title']?></a>
<? else: ?>
	<a class="button screen_no_render" href="<?=(($agent_is_mobile)?$docroot:$mdocroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'false':'true')?>" data-icon="refresh" rel="external"><?=$common[$selected_lang]['reload_mobile']['title']?></a>
<? endif ?>	
	&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/tos.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['tos_title']['title']?></a> 
	&nbsp;&nbsp; <a href="<?=(($agent_is_mobile)?$mdocroot:$docroot)?>about/privacy.php?lang=<?=$selected_lang?>&m=<?=(($agent_is_mobile)?'true':'false')?>" data-icon="info" rel="external"><?=$common[$selected_lang]['privacy_policy_title']['title']?></a>
</div><!--/footer -->

</body>
</html>
