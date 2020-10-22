import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { withSelect, withDispatch, dispatch, subscribe, select } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { SelectControl } from '@wordpress/components';
const { isSavingPost } = select( 'core/editor' );

// First add our rest api endpoint to core's registry.
dispatch( 'core' ).addEntities( [
    {
        name: 'bylines',
        kind: 'bylines/v1',
        baseURL: '/bylines/v1/bylines',
        key: 'id'
    }
] );

// We need to subscribe to the post saved event, because this plugin
// might transform a user into a byline on save, hence we need to invalidate
// the cached byline query after save so that the correct bylines are selected.
let shouldFetchBylinesAfterSave = false;
subscribe( () => {
    if ( isSavingPost() ) {
		 shouldFetchBylinesAfterSave = true;
    } else {
 		if ( shouldFetchBylinesAfterSave ) {
            shouldFetchBylinesAfterSave = false;
            dispatch( 'core/data' ).invalidateResolution( 'core', 'getEntityRecords', [ 'bylines/v1', 'bylines', { per_page: -1 } ] );
        }

    }
} );

// Let's roll out the native block editor meta box!
const BylinesRender = ( props ) => {
	return (
		<PluginDocumentSettingPanel
			name="bylines"
			title="Bylines"
		>
			<SelectControl
			    multiple
			    label="Select bylines"
			    value={ props.selectedBylines }
			    onChange={ ( value ) => props.onBylineChange( value ) }
			    options={ props.options }
			/>
		</PluginDocumentSettingPanel>
	);
};

const Bylines = compose( [
	withSelect( ( select ) => {
		const { getEditedPostAttribute } = select( 'core/editor' );
		const postMeta = getEditedPostAttribute( 'meta' );
		const options = [];
		const bylines = select( 'core' ).getEntityRecords(
			'bylines/v1',
			'bylines',
			{ per_page: -1 }
		);
		let selectedBylines = [];
		if ( postMeta ) {
			selectedBylines = postMeta.bylines;
		}
		if ( bylines ) {
			bylines.forEach( ( byline ) => {
				options.push( {
					value: byline.id,
					label: byline.display_name,
				} );
			} );
		}
		return {
			selectedBylines,
			options,
		};
	} ),
	withDispatch( ( dispatch ) => {
		const { editPost } = dispatch( 'core/editor' );
		return {
			onBylineChange: ( value ) => {
				editPost( { meta: { bylines: value } } );
			},
		};
	} ),
] )( BylinesRender );

registerPlugin( 'bylines', {
	render: Bylines,
	icon: '',
} );
