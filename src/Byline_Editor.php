<?php
/**
 * Customizations to the byline term editor experience
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Customizations to the byline term editor experience
 */
class Byline_Editor {

	/**
	 * Customize the term table to look more like the users table.
	 *
	 * @param array $columns Columns to render in the list table.
	 * @return array
	 */
	public static function filter_manage_edit_byline_columns( $columns ) {
		// Reserve the description for internal use.
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}
		return $columns;
	}

	/**
	 * Render fields for the byline profile editor
	 *
	 * @param WP_Term $term Byline term being edited.
	 */
	public static function action_byline_edit_form_fields( $term ) {
		$byline = Byline::get_by_term_id( $term->term_id );
		foreach ( self::get_fields() as $key => $args ) {
			$args['key'] = $key;
			$args['value'] = $byline->$key;
			echo self::get_rendered_byline_partial( $args );
		}
		wp_nonce_field( 'byline-edit', 'byline-edit-nonce' );
	}

	/**
	 * Handle saving of term meta
	 *
	 * @param integer $term_id ID for the term being edited.
	 */
	public static function action_edited_byline( $term_id ) {
		if ( empty( $_POST['byline-edit-nonce'] )
			|| ! wp_verify_nonce( $_POST['byline-edit-nonce'], 'byline-edit' ) ) {
			return;
		}
		foreach ( self::get_fields() as $key => $args ) {
			if ( ! isset( $_POST[ $key ] ) ) {
				continue;
			}
			$sanitize = isset( $args['sanitize'] ) ? $args['sanitize'] : 'sanitize_text_field';
			update_term_meta( $term_id, $key, $sanitize( $_POST[ $key ] ) );
		}
	}

	/**
	 * Get the fields to be rendered in the byline editor
	 *
	 * @return array
	 */
	public static function get_fields() {
		return array(
			'first_name'   => array(
				'label'    => __( 'First Name', 'bylines' ),
				'type'     => 'text',
			),
			'last_name'   => array(
				'label'    => __( 'Last Name', 'bylines' ),
				'type'     => 'text',
			),
			'user_email'   => array(
				'label'    => __( 'Email', 'bylines' ),
				'type'     => 'email',
			),
		);
	}

	/**
	 * Get a rendered field partial
	 *
	 * @param array $args Arguments to render in the partial.
	 */
	private static function get_rendered_byline_partial( $args ) {
		$defaults = array(
			'type'            => 'text',
			'value'           => '',
			'label'           => '',
		);
		$args = array_merge( $defaults, $args );
		ob_start();
		?>
		<tr class="<?php echo esc_attr( 'form-field term-' . $args['key'] . '-wrap' ); ?>">
			<th scope="row">
				<?php if ( ! empty( $args['label'] ) ) : ?>
					<label for="<?php echo esc_attr( $args['key'] ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php endif; ?>
			</th>
			<td>
				<input name="<?php echo esc_attr( $args['key'] ); ?>" type="<?php echo esc_attr( $args['type'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

}
