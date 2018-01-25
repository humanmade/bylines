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

		$('.byline-image-field-wrapper').each(function(){
			var frame,
			target = $(this), // Your meta box id here
			deleteImgLink = target.find('.select-byline-image-field'),
			delImgLink = target.find( '.delete-byline-image-field'),
			imgContainer = target.find( '.byline-image-field-container'),
			imgIdInput = target.find( '.byline-image-field-id' );

			deleteImgLink.on( 'click', function( event ){
				event.preventDefault();

				if ( frame ) {
					frame.open();
					return;
				}
				frame = wp.media({
					title: bylines.media_upload_title,
					button: {
						text: bylines.media_upload_button
					},
					multiple: false,
					library : {
						type : 'image'
					}
				});
				frame.on( 'select', function() {
					var attachment = frame.state().get('selection').first().toJSON();
					var attachment_src = ( "undefined" === typeof attachment.sizes.thumbnail ? attachment.url : attachment.sizes.thumbnail.url );
					var imgEl = $('<img />');
					imgEl.attr('src', attachment_src );
					imgContainer.append( imgEl );
					imgIdInput.val( attachment.id );
					deleteImgLink.addClass( 'hidden' );
					delImgLink.removeClass( 'hidden' );
				});

				frame.open();
			});

			delImgLink.on( 'click', function( event ){
				event.preventDefault();
				imgContainer.html( '' );
				deleteImgLink.removeClass( 'hidden' );
				delImgLink.addClass( 'hidden' );
				imgIdInput.val( '' );
			});
		
		});
	
	});

}(jQuery))
