function show_page(id) {
	if ('undefined'==typeof(id)) id = window.location.hash;
	if (!id.length || '#'==id) return;
	$('.page').hide();
	$(id).fadeIn();
}

$(document).ready(function() {

	$(".button:not('.screen_no_render')").button();

	$('a').click(function() {
		var $this = $(this);
		if ('#'!=$this.attr('href').substr(0,1)) return true;
		show_page($this.attr('href'));
		return true;
	});
	
	show_page();
	
});
