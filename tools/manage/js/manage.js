
function save_category(tosend, callback) {
	save('ajax/save_category.php', tosend, callback);
}
function delete_submission(tosend, callback) {
	save('ajax/delete_submission.php', tosend, callback);
}
function send_submission(tosend, callback) {
	save('ajax/send_submission.php', tosend, callback);
}
function close_submission(tosend, callback) {
	save('ajax/close_submission.php', tosend, callback);
}
function save_parcel(tosend, callback) {
	save('ajax/save_parcel.php', tosend, callback);
}
function save_submission_notes(tosend, callback) {
	save('ajax/save_submission_notes.php', tosend, callback);
}
function update_parcel_is_supported(tosend, callback) {
	save('ajax/update_parcel_is_supported.php', tosend, callback);
}
function save(url, tosend, callback) {
	$.ajax({
		type: "POST",
		url: url, 
		data: tosend,
		dataType: 'json', 
		success: function(json) {
			if (json.error) {
				alert(json.msg);
				callback();
			} else {
				callback(json);
			}
		},
		error: function(data) {
			alert(data.statusText);
			callback();
		}
	});
}

$(function() {

	/**
	 * Buttons
	 */
	
	$(".button:not('.screen_no_render')").button();
	
	$('a.expand, a.collapse').live('click', function() {  // Generic open or close a section
		var $this = $(this);
		var fields = $this.closest('.expand_parent').attr('class').split(' ');
		var id;
		for (var j = 0; j < fields.length; j++) {
			if (-1!=fields[j].indexOf('id_')) id = fields[j].replace('id_','');
		}
		if ('expand'==$this.find('span').html().toLowerCase()) {
			$this.find('span').html('collapse');
			$this.removeClass('expand').addClass('collapse');
		} else {
			$this.find('span').html('expand');
			$this.removeClass('collapse').addClass('expand');
		}
		$this.closest('.expand_parent').parent().children('table, div, tr').each(function() {
			if ($(this).hasClass('parent_'+id)) $(this).fadeToggle();
		});
	});	
	
	$('.category_table a.save').live('click', function() {  // Save a category
		var $this = $(this);
		if ($this.hasClass('saving')) return;
		$this.addClass('saving');
		var tosend = {};
		tosend.category_id = $this.closest('table').find('.category_id').html();
		tosend.category_en = $this.closest('table').find('.category_en input:first').val();
		tosend.category_es = $this.closest('table').find('.category_es input:first').val();
		var classes = $this.closest('table').attr('class').split(' ');
		for (var j = 0; j < classes.length; j++) {
			if (-1!=classes[j].indexOf('parent_')) tosend.parent_id = classes[j].replace('parent_','');
		}
		save_category(tosend, function(json) {
			var hl_color = '#5fb818';
			if ('undefined'!=typeof(json)) {
				if (json.deleted) {
					$this.closest('table').remove();
					return;
				}
				$this.closest('table').find('.category_id').html(json.category_id);
				$this.closest('table').find('.category_en input:first').val(json.category_en);
				$this.closest('table').find('.category_es input:first').val(json.category_es);
				var classes = $this.closest('table').attr('class').split(' ');
				$this.closest('table').attr('class','');
				for (var j = 0; j < classes.length; j++) {
					if (-1==classes[j].indexOf('parent_')) $this.closest('table').addClass(classes[j]);
				}
				$this.closest('table').addClass('parent_'+json.parent_id);
			} else {
				hl_color = 'red';
			}
			$this.removeClass('saving');
			$this.closest('table').find(".category_id, .category_en, .category_es, .category_op").effect("highlight", {color:hl_color}, 3000);
		});
	});	
	
	$('.category_add').live('click', function() {  // Add a new category
		var $this = $(this);
		var $row = $this.prevAll('table:first').clone();
		$row.attr('class','category_table');
		var classes = $this.attr('class').split(' ');
		for (var j = 0; j < classes.length; j++) {
			if (-1!=classes[j].indexOf('parent_')) $row.addClass(classes[j]);
		}
		$row.find("td[class*='_id']").html('');
		$row.find('input').each(function() {
			$(this).val('');
		});
		var margin = parseInt($this.css('margin-left')) - 2;
		$row.find(".category_sp").width(margin);
		$row.insertBefore($this);
	});	
	
	$('a.send').live('click', function() {  // Submit a submission
		var $this = $(this);
		if ($this.hasClass('sending')) return;
		$this.addClass('sending');
		var tosend = {};
		tosend.submission_id = $this.closest('tr').find('.submission_id').html();
		var name = $this.closest('tr').find('.name').html();
		var $dialog = $('<div class="dialog"></div>');
		$dialog.append('<p>Please select the agencies to submit for "'+name+'". Alternatively, click "Submitted by Hand" if closed manually.</p>');
		$dialog.append('<p><input type="checkbox" id="submit_to_lahd"><label for="submit_to_lahd"> Submit to LA Housing Department</label><br /><input type="checkbox" id="submit_to_lacdh"><label for="submit_to_lacdh"> Submit to LAC Department of Health Services</label></p>');
		$('body').append($dialog);
		$dialog.dialog({
		      modal: true,
		      width:400,
		      title:'Submit violation',
		      buttons: {
		        'Submit to Checked': function() {
					var send_to = ['lacdh','lahd'];
					if (!$(this).find('input:checked').length) {
						alert('Please select one or more departments');
						return;
					}
		        	$this.addClass('sending');
		        	$(this).dialog( "close" );					
		        	for (var j in send_to) {
		        		var send_to_id = send_to[j];
		        		console.log(send_to_id);
		        		console.log('#submit_to_'+send_to_id+':checked');
						if ($(this).find('#submit_to_'+send_to_id+':checked').length) {
							tosend['route'] = send_to_id;
							send_submission(tosend, function(json) {
								$this.removeClass('sending');
								if ('undefined'!=typeof(json) && json.sent) {
									$this.closest('.submission_op').children(':not(a:first)').remove();
									$('tr[class*="'+tosend.submission_id+'"]').find("td").removeClass('unpublished');
								} 
							});		
						}
		        	}		        	
		        },
		        'Submiited by Hand': function() {
		        	$this.addClass('sending');
		        	$(this).dialog( "close" );
					close_submission(tosend, function(json) {
						$this.removeClass('sending');
						if ('undefined'!=typeof(json) && json.closed) {
							$this.closest('.submission_op').children(':not(a:first)').remove();
							$('tr[class*="'+tosend.submission_id+'"]').find("td").removeClass('unpublished');
						} 									
					});				        	
		        },
		        'Cancel': function() {
		          $( this ).dialog( "close" );
		        }
		      }
		    });
		$dialog.find('input').blur();
		$this.removeClass('sending');
	});		
	
	$('a.delete').live('click', function() {  // Delete a submission
		var $this = $(this);
		if ($this.hasClass('deleting')) return;
		$this.addClass('deleting');
		var tosend = {};
		tosend.submission_id = $this.closest('tr').find('.submission_id').html();
		var name = $this.closest('tr').find('.name').html();
		if (!confirm('Are you sure you wish to DELETE this submission by '+name+'?')) {
			$this.removeClass('deleting');
			return false;
		}
		delete_submission(tosend, function(json) {
			$this.removeClass('deleting');
			if ('undefined'!=typeof(json)) {
				var fields = $this.closest('.expand_parent').attr('class').split(' ');
				var id;
				for (var j = 0; j < fields.length; j++) {
					if (-1!=fields[j].indexOf('id_')) id = fields[j].replace('id_','');
				}
				$this.closest('.expand_parent').parent().find('.id_'+id+', .parent_'+id).remove();
			}
		});
	});	
	
	$('.parcel_op a.save').live('click', function() {  // Save parcel number
		var $this = $(this);
		if ($this.hasClass('saving')) return;
		$this.addClass('saving');
		var tosend = {};
		tosend.parcel = $this.closest('.submission_row').find('.parcel input:first').val();
		var classes = $this.closest('.submission_row').attr('class').split(' ');
		for (var j = 0; j < classes.length; j++) {
			if (-1!=classes[j].indexOf('parent_')) tosend.submission_id = classes[j].replace('parent_','');
		}		
		save_parcel(tosend, function(json) {
			var hl_color = '#5fb818';
			if ('undefined'!=typeof(json)) {
				$this.closest('.submission_row').find('.parcel input:first').val(json.parcel);
			} else {
				hl_color = 'red';
			}
			$this.closest('tr').find(".parcel").effect("highlight", {color:hl_color}, 3000);
			// Second save is_supported
			update_parcel_is_supported(tosend, function(json) {
				var hl_color = '#5fb818';
				if ('undefined'!=typeof(json)) {
					var value = (json.msg) ? 'Supported by LAHD' : '(Not supported by LAHD)';
					$this.closest('.submission_row').next().find('.parcel_is_supported').html(value);
				} else {
					hl_color = 'red';
				}
				$this.removeClass('updating');
				$this.closest('tr').next().find(".parcel_is_supported").effect("highlight", {color:hl_color}, 3000);
				$this.removeClass('saving');
			});				
		});
	});		
	
	$('.notes_op a.save').live('click', function() {  // Save submission notes
		var $this = $(this);
		if ($this.hasClass('saving')) return;
		$this.addClass('saving');
		var tosend = {};
		tosend.notes = $this.closest('.submission_row').find('.notes textarea:first').val();
		var classes = $this.closest('.submission_row').attr('class').split(' ');
		for (var j = 0; j < classes.length; j++) {
			if (-1!=classes[j].indexOf('parent_')) tosend.submission_id = classes[j].replace('parent_','');
		}		
		save_submission_notes(tosend, function(json) {
			var hl_color = '#5fb818';
			if ('undefined'!=typeof(json)) {
				$this.closest('.submission_row').find('.notes textarea:first').val(json.notes);
			} else {
				hl_color = 'red';
			}
			$this.removeClass('saving');
			$this.closest('tr').find(".notes").effect("highlight", {color:hl_color}, 3000);
		});
	});			
	
	/**
	 * Sections (mobile view)
	 */

	$("div.ui-collapsible-set").live("expand", function(e) {
	    var top = $(e.target).offset().top;
	    if ($(window).scrollTop() > top) $(window).scrollTop(top);
	});

	/**
	 * Sections (standard view)
	 */
	
	$('.collapse_link').click(function() {
		var $this = $(this);	
		if ($this.parent().next().is(':hidden')) {
			$this.addClass('collapse_link_open');
		} else {
			$this.removeClass('collapse_link_open');
			setTimeout(function() {window.location.hash = '';}, 200);
		}			
		$this.parent().next().fadeToggle();
	});

	if (1<window.location.hash.length) {
		var section = window.location.hash;
		$('.collapse_link[href="'+section+'"]').trigger('click');
	}	
	
	/**
	 * About page language selector
	 */
	
	$(document).ready(function() {about_page_lang()});
	$('input[name="about_page_lang"]').change(function() {about_page_lang()});

	var about_page_lang = function() {
		var lang = $('input[name="about_page_lang"]:checked', '#about_page_form').val();
		var $form = $('#about_page_form');
		$form.find('table').hide();
		$form.find('.'+lang).show();
	}
	
});