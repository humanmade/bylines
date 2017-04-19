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
		<ul>
			<?php foreach ( get_bylines() as $byline ) : ?>
				<li><?php echo self::get_rendered_byline_partial( array(
					'display_name' => $byline->display_name,
				) ); ?></li>
			<?php endforeach; ?>
		</ul>
		<select class="bylines-select2"></select>
		<script type="text/html" id="tmpl-bylines-byline-partial">
			<?php echo self::get_rendered_byline_partial( array(
				'display_name' => '{{ data.display_name }}',
			) ); ?>
		</script>
		<?php
	}

	/**
	 * Get a rendered byline partial
	 *
	 * @param array $args Arguments to render in the partial.
	 */
	private static function get_rendered_byline_partial( $args = array() ) {
		$defaults = array(
			'display_name'    => '',
		);
		$args = array_merge( $defaults, $args );
		ob_start();
		?>
		<span class="display-name"><?php echo wp_kses_post( $args['display_name'] ); ?></span>
		<?php
		return ob_get_clean();
	}

}
