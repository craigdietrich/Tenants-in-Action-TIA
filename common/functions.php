<?php
function nl2br_nolb($text) {
	$text = nl2br($text);
	$text = str_replace("\r", "", $text);
	$text = str_replace("\n", "", $text);
	return $text;
}

function get_vars($remove=null, $add=null) {
	$return = array();
	foreach ($_GET as $field => $value) {
		if ($remove==$field) continue;
		$return[$field] = $value;
	}
	if (!empty($add)) {
		$arr = explode('=',$add);
		$field = $arr[0];
		$value = $arr[1];
		$return[$field] = $value;
	}
	$str = '';
	$j = 1;
	foreach ($return as $field => $value) {
		$str .= $field.'='.$value;
		if ($j < count($return)) $str .= '&';
		$j++;
	}
	return $str;
}

function name($lang, $en, $es, $common) {
	$name = '';
	if (empty($en) && empty($es)) return $name;
	if ('es'==$lang && empty($es)) {
		$name = $en.'&nbsp; <span style="font-weight:normal;">('.$common[$lang]['no_translation']['title'].')</span>';
	} elseif ('es'==$lang) {
		$name = $es;
	} else {
		$name = $en;
	}
	return $name;
}