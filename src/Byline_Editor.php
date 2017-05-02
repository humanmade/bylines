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
		// Add our own columns too.
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			if ( 'name' === $key ) {
				$new_columns['byline_name'] = __( 'Name', 'bylines' );
				$new_columns['byline_user_email'] = __( 'Email', 'bylines' );
			} else {
				$new_columns[ $key ] = $title;
			}
		}
		return $new_columns;
	}

	/**
	 * Set our custom name column as the primary column
	 *
	 * @return string
	 */
	public static function filter_list_table_primary_column() {
		return 'byline_name';
	}

	/**
	 * Render and return custom column
	 *
	 * @param string $retval      Value being returned.
	 * @param string $column_name Name of the column.
	 * @param int    $term_id     Term ID.
	 */
	public static function filter_manage_byline_custom_column( $retval, $column_name, $term_id ) {
		if ( 'byline_name' === $column_name ) {
			$byline = Byline::get_by_term_id( $term_id );
			$avatar = get_avatar( $byline->user_email, 32 );
			// Such hack. Lets us reuse the rendering without duplicate code.
			$term = get_term_by( 'id', $term_id, 'byline' );
			$retval = $avatar . ' ' . $GLOBALS['wp_list_table']->column_name( $term );
		} elseif ( 'byline_user_email' === $column_name ) {
			$byline = Byline::get_by_term_id( $term_id );
			if ( $byline->user_email ) {
				$retval = '<a href="' . esc_url( 'mailto:' . $byline->user_email ) . '">' . esc_html( $byline->user_email ) . '</a>';
			}
		}
		return $retval;
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
			if ( ! isset( $_POST[ 'bylines-' . $key ] ) ) {
				continue;
			}
			$sanitize = isset( $args['sanitize'] ) ? $args['sanitize'] : 'sanitize_text_field';
			update_term_meta( $term_id, $key, $sanitize( $_POST[ 'bylines-' . $key ] ) );
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
			'user_url'     => array(
				'label'    => __( 'Website', 'bylines' ),
				'type'     => 'url',
				'sanitize' => 'esc_url_raw',
			),
			'description'  => array(
				'label'    => __( 'Biographical Info', 'bylines' ),
				'type'     => 'textarea',
				'sanitize' => 'wp_filter_post_kses',
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
		$key = 'bylines-' . $args['key'];
		ob_start();
		?>
		<tr class="<?php echo esc_attr( 'form-field term-' . $key . '-wrap' ); ?>">
			<th scope="row">
				<?php if ( ! empty( $args['label'] ) ) : ?>
					<label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $args['label'] ); ?></label>
				<?php endif; ?>
			</th>
			<td>
				<?php if ( 'textarea' === $args['type'] ) : ?>
					<textarea name="<?php echo esc_attr( $key ); ?>"><?php echo esc_textarea( $args['value'] ); ?></textarea>
				<?php else : ?>
					<input name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $args['type'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" />
				<?php endif; ?>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}

}
