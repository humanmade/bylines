(function($){

	$(document).ready(function(){
		var bylinesSearch = $('.bylines-select2.bylines-search').bylinesSelect2({
			ajax: {
				url: window.ajaxurl + '?action=bylines_search',
				dataType: 'json',
				data: function( params ) {
					var ignored = [];
					$('.bylines-list input').each(function(){
						ignored.push( $(this).val() );
					});
					return {
						q: params.term,
						ignored: ignored,
					};
				},
			},
		});
		bylinesSearch.on('select2:select',function(e) {
			var template = wp.template('bylines-byline-partial');
			$('.bylines-list').append( template( e.params.data ) );
			bylinesSearch.val(null).trigger('change');
		});
		$('.bylines-list').sortable();
		$('.bylines-list').on('click', '.byline-remove', function(){
			var el = $(this);
			el.closest('li').remove();
		})

		$('.bylines-select2-user-select').each(function(){
			$(this).bylinesSelect2({
				allowClear: true,
				placeholder: $(this).attr('placeholder'),
				ajax: {
					url: window.ajaxurl + '?action=bylines_users_search',
					dataType: 'json',
					data: function( params ) {
						return {
							q: params.term,
						};
					},
				},
			});
		});
	});

}(jQuery))
