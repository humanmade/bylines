(function($){

	$(document).ready(function(){
		var bylinesSearch = $('.bylines-select2.bylines-search').bylinesSelect2({
			ajax: {
				url: window.ajaxurl + '?action=bylines_search',
				dataType: 'json',
			},
			select: function(e) {
				console.log( e );
			}
		});
		bylinesSearch.on('select2:select',function(e) {
			var template = wp.template('bylines-byline-partial');
			$('.bylines-list').append( template( e.params.data ) );
			bylinesSearch.val(null).trigger('change');
		});
	});

}(jQuery))
