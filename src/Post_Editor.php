<?php
/**
 * Customizations to the editor experience
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Customizations to the editor experience
 */
class Post_Editor {

	/**
	 * Register callbacks for managing custom columns
	 */
	public static function action_admin_init() {
		foreach ( Content_Model::get_byline_supported_post_types() as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'filter_manage_posts_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'action_manage_posts_custom_column' ), 10, 2 );
		}
	}

	/**
	 * Filter post columns to include the Bylines column
	 *
	 * @param array $columns All post columns with their titles.
	 * @return array
	 */
	public static function filter_manage_posts_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'title' === $key ) {
				$new_columns['bylines'] = __( 'Bylines', 'bylines' );
			}

			if ( 'author' === $key ) {
				unset( $new_columns[ $key ] );
			}
		}
		return $new_columns;
	}

	/**
	 * Render the bylines for a post in the table
	 *
	 * @param string  $column  Name of the column.
	 * @param integer $post_id ID of the post being rendered.
	 */
	public static function action_manage_posts_custom_column( $column, $post_id ) {
		if ( 'bylines' !== $column ) {
			return;
		}
		$bylines = get_bylines( $post_id );
		$post_type = get_post_type( $post_id );
		$bylines_str = array();
		foreach ( $bylines as $byline ) {
			$args = array(
				'author_name' => $byline->slug,
			);
			if ( 'post' !== $post_type ) {
				$args['post_type'] = $post_type;
			}
			$url = add_query_arg( array_map( 'rawurlencode', $args ), admin_url( 'edit.php' ) );
			$bylines_str[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $byline->display_name ) . '</a>';
		}
		echo implode( ', ', $bylines_str );
	}

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
		$classes = array(
			'bylines-list',
		);
		if ( current_user_can( get_taxonomy( 'byline' )->cap->assign_terms ) ) {
			$classes[] = 'bylines-current-user-can-assign';
		}
		?>
		<ul class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php
			foreach ( get_bylines() as $byline ) {
				$display_name = $byline->display_name;
				$term = is_a( $byline, 'WP_User' ) ? 'u' . $byline->ID : $byline->term_id;
				echo self::get_rendered_byline_partial( array(
					'display_name' => $display_name,
					'avatar_url'   => get_avatar_url( $byline->user_email, 32 ),
					'term'         => $term,
				) );
			}
			?>
		</ul>
		<?php if ( current_user_can( get_taxonomy( 'byline' )->cap->assign_terms ) ) : ?>
			<select data-nonce="<?php echo esc_attr( wp_create_nonce( 'bylines-search' ) ); ?>" class="bylines-select2 bylines-search" style="min-width: 200px"></select>
			<script type="text/html" id="tmpl-bylines-byline-partial">
				<?php echo self::get_rendered_byline_partial( array(
					'display_name' => '{{ data.display_name }}',
					'avatar_url'   => '{{ data.avatar_url }}',
					'term'         => '{{ data.term }}',
				) ); ?>
			</script>
		<?php endif;
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

		if ( ! isset( $_POST['bylines'] ) || ! current_user_can( get_taxonomy( 'byline' )->cap->assign_terms ) ) {
			return;
		}

		$dirty_bylines = $_POST['bylines'];
		$bylines = array();
		foreach ( $dirty_bylines as $dirty_byline ) {
			if ( is_numeric( $dirty_byline ) ) {
				$bylines[] = Byline::get_by_term_id( $dirty_byline );
			} elseif ( 'u' === $dirty_byline[0] ) {
				$user_id = (int) substr( $dirty_byline, 1 );
				$byline = Byline::get_by_user_id( $user_id );
				if ( ! $byline ) {
					$byline = Byline::create_from_user( $user_id );
					if ( is_wp_error( $byline ) ) {
						continue;
					}
				}
				$bylines[] = $byline;
			}
		}
		Utils::set_post_bylines( $post_id, $bylines );
	}

	/**
	 * Get a rendered byline partial
	 *
	 * @param array $args Arguments to render in the partial.
	 */
	private static function get_rendered_byline_partial( $args = array() ) {
		$defaults = array(
			'display_name'    => '',
			'avatar_url'      => '',
			'term'            => '',
		);
		$args = array_merge( $defaults, $args );
		ob_start();
		?>
		<li>
			<span class="byline-remove"><span class="dashicons dashicons-no-alt"></span></span>
			<?php if ( ! empty( $args['avatar_url'] ) ) : ?>
				<img height="16px" width="16px" src="<?php echo esc_attr( $args['avatar_url'] ); // Uses esc_attr() because can be JS template tag. ?>" >
			<?php endif; ?>
			<span class="display-name"><?php echo wp_kses_post( $args['display_name'] ); ?></span>
			<input type="hidden" name="bylines[]" value="<?php echo esc_attr( $args['term'] ); ?>">
		</li>
		<?php
		return ob_get_clean();
	}

}
