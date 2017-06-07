(function($){

	$(document).ready(function(){
		$('.bylines-select2.bylines-search').each(function(){
			var bylinesSearch = $(this).bylinesSelect2({
				placeholder: $(this).data('placeholder'),
				ajax: {
					url: window.ajaxurl + '?action=bylines_search&nonce=' + $(this).data('nonce'),
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
		});
		$('.bylines-list.bylines-current-user-can-assign').sortable();
		$('.bylines-list.bylines-current-user-can-assign').on('click', '.byline-remove', function(){
			var el = $(this);
			el.closest('li').remove();
		})

		$('.bylines-select2-user-select').each(function(){
			$(this).bylinesSelect2({
				allowClear: true,
				placeholder: $(this).attr('placeholder'),
				ajax: {
					url: window.ajaxurl + '?action=bylines_users_search&nonce=' + $(this).data('nonce'),
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
