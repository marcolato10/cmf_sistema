(function($) {
	
	$.fn.stickynote = function(options) {
		var opts = $.extend({}, $.fn.stickynote.defaults, options);
		return this.each(function() {
			$this = $(this);
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
			switch(o.event){
				case 'dblclick':
					$this.dblclick(function(e){$.fn.stickynote.createNote(o);})
					break;
				case 'click':
					$this.click(function(e){$.fn.stickynote.createNote(o);})
					break;
			}		
		});
	};
	$.fn.stickynote.defaults = {
		size 	: 'small',
		event	: 'click',
		color	: '#000000',
		cerrar	: true,
		ok		: true,
		id_textarea : 'textarea',
		funcion : function(){}
	};
	
	$.fn.stickynote.valida = function(o){		
	alert($(this).parent());
	return;
	
		var _p_note_text = 	$(this).parent().find('textarea').val();
		alert( _p_note_text)
			/*		
					$(this)
					.parent()
					.find('textarea')
					.before(_p_note_text)
					.remove(); 
					
					$(this).remove();*/
				
	}
	
	$.fn.stickynote.createNote = function(o) {
		var _note_content = $(document.createElement('textarea'));
		_note_content.attr('id',o.id_textarea);
		var _div_note 	= 	$(document.createElement('div'))
							.addClass('jStickyNote')
							.css('cursor','move')
							;
		if(!o.text){
			_div_note.append(_note_content);
			if(o.ok){
				var _div_create = $(document.createElement('div'))
							.addClass('jSticky-create')
							.attr('title','Create Sticky Note');
			
				_div_create.click(function(e){
					var _p_note_text = 	$(document.createElement('p'))
										.css('color',o.color)
										.html	(
												$(this)
												.parent()
												.find('textarea')
												.val()
												);
					
					$(this)
					.parent()
					.find('textarea')
					.before(_p_note_text)
					.remove(); 
					
					$(this).remove();						
				})
				}
		}	
		else
			_div_note.append('<p style="color:'+o.color+'">'+o.text+'</p>');					
		
		if (o.cerrar){
			var _div_delete = 	$(document.createElement('div'))
							.addClass('jSticky-delete');
		
		
			_div_delete.click(function(e){
				$(this).parent().remove();
			})
		}
		var _div_wrap 	= 	$(document.createElement('div'))
							.css({'position':'absolute','top':'0','left':'0'})
							.append(_div_note)
							.append(_div_delete)
							.append(_div_create);	
		switch(o.size){
			case 'large':
				_div_wrap.addClass('jSticky-large');
				break;
			case 'small':
				_div_wrap.addClass('jSticky-medium');
				break;
		}
		
		if(o.containment){
			_div_wrap.draggable({ containment: '#'+o.containment , scroll: false ,start: function(event, ui) {
				if(o.ontop)
					$(this).parent().append($(this));
			}});	
		}	
		else{
			_div_wrap.draggable({ scroll: false ,start: function(event, ui) {
				if(o.ontop)
					$(this).parent().append($(this));
			}});	
		}
		$('#'+o.containment).append(_div_wrap);
	};
})(jQuery);