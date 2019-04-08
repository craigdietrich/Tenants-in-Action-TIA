var get_vars = function(remove, add) {
	var href = window.location.href;
	if (-1!=href.indexOf('#')) href = href.split('#')[0];
	var hashes = (-1==href.indexOf('?')) ? [] : href.slice(href.indexOf('?') + 1).split('&');
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
function check_tos(parent) {
	var $parent = $(parent);
	if (!$parent.find('input:checked').length) {
		alert('Please click the checkbox verifying that you have read the Terms of Service and Privacy Policy');
		return false;
	}
	return true;
}
function check_info(form) {
	var $form = $(form);
	var valid = true;
	if (''==$form.find('input[name="name"]').val()) valid = false;
	if (''==$form.find('input[name="tel"]').val()) valid = false;
	if (''==$form.find('textarea[name="address"]').val()) valid = false;
	if (!valid) alert('Please enter full name, telephone, and address fields');
	return valid;
}
function set_cats(form, input_wrapper, input_to_update) {
	var $form = $(form);
	var $input_wrapper = $(input_wrapper);
	var categories = [];
	$input_wrapper.find('input:checked').each(function() {
		var id = $(this).attr('id').replace('category_','');
		categories.push(id);
	});
	if (!categories.length) {
		alert('Please select one or more categories');
		return false;
	}
	$form.find('input[name="'+input_to_update+'"]').val(categories.join(','));
	return true;
}
function set_sub_cats(form, input_wrapper, input_to_update) {
	var $form = $(form);
	var $input_wrapper = $(input_wrapper);
	var subcategories = [];
	$input_wrapper.find('input:checked').each(function() {
		var id = $(this).attr('id').replace('category_','');
		subcategories.push(id);
	});
	if (!subcategories.length) {
		alert('Please select one or more subcategories');
		return false;
	}
	$form.find('input[name="'+input_to_update+'"]').val(subcategories.join(','));
	return true;
}
$(document).ready(function() {

	$('fieldset[class="lang"]').find('input').change(function() {
		var $fieldset = $(this).closest('fieldset');
		var lang = $fieldset.find('input:checked').val();
		var page = $fieldset.closest("div[class*='page']").attr('id');
		var hash = '#'+page;
		var url = (-1==window.location.href.indexOf('?')) ? window.location.href : window.location.href.slice(0,window.location.href.indexOf('?'));
		if (-1!=url.indexOf('#')) url = url.substr(0, window.location.href.indexOf('#'));
		window.location.href = url+'?'+get_vars('lang','lang='+lang)+hash;		
	});
	
});