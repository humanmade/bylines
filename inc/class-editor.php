<?php
/**
 * Customizations to the editor experience
 *
 * @package Bylines
 */

namespace Bylines;

/**
 * Customizations to the editor experience
 */
class Editor {

	/**
	 * Deregister the author meta box, and register Bylines meta boxes
	 */
	public static function action_add_meta_boxes_late() {
		foreach ( Content_Model::get_byline_supported_post_types() as $post_type ) {
			remove_meta_box( 'authordiv', $post_type, 'normal' );
			// @todo only register meta box when user can assign authors
			add_meta_box( 'bylines', __( 'Bylines', 'bylines' ), array( __CLASS__, 'render_bylines_metabox' ), $post_type, 'normal', 'high' );
		}
	}

	/**
	 * Render the Bylines meta box.
	 */
	public static function render_bylines_metabox() {
		?>
		<ul class="bylines-list">
			<?php
			foreach ( get_bylines() as $byline ) {
				echo self::get_rendered_byline_partial( array(
					'display_name' => $byline->display_name,
					'term'         => $byline->term_id,
				) );
			}
			?>
		</ul>
		<select class="bylines-select2 bylines-search" style="min-width: 200px"></select>
		<script type="text/html" id="tmpl-bylines-byline-partial">
			<?php echo self::get_rendered_byline_partial( array(
				'display_name' => '{{ data.display_name }}',
				'term'         => '{{ data.term }}',
			) ); ?>
		</script>
		<?php
	}

	/**
	 * Handle saving of the Bylines meta box
	 *
	 * @param integer $post_id ID for the post being saved.
	 * @param WP_Post $post Object for the post being saved.
	 */
	public static function action_save_post_bylines_metabox( $post_id, $post ) {

		if ( ! in_array( $post->post_type, Content_Model::get_byline_supported_post_types(), true ) ) {
			return;
		}

		// @todo verify user can edit bylines on this post.
		if ( ! isset( $_POST['bylines'] ) ) {
			return;
		}

		$dirty_bylines = $_POST['bylines'];
		$bylines = array();
		foreach ( $dirty_bylines as $dirty_byline ) {
			// @todo support for users.
			$term_id = (int) $dirty_byline;
			$bylines[] = $term_id;
		}
		wp_set_object_terms( $post_id, $bylines, 'byline' );
	}

	/**
	 * Get a rendered byline partial
	 *
	 * @param array $args Arguments to render in the partial.
	 */
	private static function get_rendered_byline_partial( $args = array() ) {
		$defaults = array(
			'display_name'    => '',
			'term'            => '',
		);
		$args = array_merge( $defaults, $args );
		ob_start();
		?>
		<li>
			<span class="display-name"><?php echo wp_kses_post( $args['display_name'] ); ?></span>
			<input type="hidden" name="bylines[]" value="<?php echo esc_attr( $args['term'] ); ?>">
		</li>
		<?php
		return ob_get_clean();
	}

}
