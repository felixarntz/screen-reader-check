<?php
/**
 * Checks class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * This class performs general check-related functions.
 *
 * @since 1.0.0
 */
class Checks {
	/**
	 * Global options.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array
	 */
	private $global_options = array();

	/**
	 * Sets the global options.
	 *
	 * These come from the tests class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $options Array of global options.
	 */
	public function set_global_options( $options ) {
		$this->global_options = $options;
	}

	/**
	 * Creates a new check.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args Array of arguments.
	 * @return ScreenReaderCheck\Check|WP_Error Either the new check object, or an error object on failure.
	 */
	public function create( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'html'    => '',
			'url'     => '',
			'options' => array(),
		) );

		if ( empty( $args['url'] ) && empty( $args['html'] ) ) {
			return new WP_Error( 'invalid_arguments', __( 'Neither a URL nor HTML code was provided.', 'screen-reader-check' ) );
		}

		if ( ! empty( $args['url'] ) ) {
			$args['html'] = Util::fetch_html_from_url( $args['url'] );
			if ( ! $args['html'] ) {
				return new WP_Error( 'could_not_fetch_html', sprintf( __( 'An error occurred while trying to fetch the HTML code from the URL %s.', 'screen-reader-check' ), $args['url'] ) );
			}
		}

		$title_tag = Util::get_html_title( $args['html'] );
		if ( ! $title_tag ) {
			return new WP_Error( 'could_not_parse_html', __( 'The HTML code could not be parsed.', 'screen-reader-check' ) );
		}

		$post_args = array(
			'post_type'     => 'src_check',
			'post_status'   => 'publish',
			'post_title'    => sprintf( __( 'Check for &quot;%s&quot;', 'screen-reader-check' ), $title_tag ),
			'post_name'     => '',
			'post_password' => wp_generate_password( 15, true ),
		);

		$id = wp_insert_post( $post_args );
		if ( ! $id ) {
			return new WP_Error( 'could_not_store_check', __( 'An internal error occurred while trying to store the check.', 'screen-reader-check' ) );
		}

		update_post_meta( $id, 'src_url', $args['url'] );
		update_post_meta( $id, 'src_html', $args['html'] );
		update_post_meta( $id, 'src_options', $args['options'] );

		return new Check( $id );
	}

	/**
	 * Retrieves a check by ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The check ID.
	 * @return ScreenReaderCheck\Check|WP_Error Either the check object, or an error object on failure.
	 */
	public function get( $id ) {
		if ( 'src_check' !== get_post_type( $id ) ) {
			return new WP_Error( 'invalid_id', __( 'Invalid check ID.', 'screen-reader-check' ) );
		}

		return new Check( $id );
	}

	/**
	 * Deletes a check by ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The check ID.
	 * @return bool|WP_Error True on success, an error object on failure.
	 */
	public function delete( $id ) {
		if ( 'src_check' !== get_post_type( $id ) ) {
			return new WP_Error( 'invalid_id', __( 'Invalid check ID.', 'screen-reader-check' ) );
		}

		$post = wp_delete_post( $id, true );
		if ( ! $post ) {
			return new WP_Error( 'could_not_delete_check', __( 'An internal error occurred while trying to delete the post.', 'screen-reader-check' ) );
		}

		return true;
	}

	/**
	 * Magic caller for semi-private methods.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method Method name.
	 * @param array  $args   Method arguments.
	 */
	public function __call( $method, $args ) {
		switch ( $method ) {
			case 'register_post_type':
			case 'register_post_type_meta_boxes':
			case 'render_post_type_data_meta_box':
			case 'render_post_type_tests_meta_box':
			case 'ajax_create':
				return call_user_func_array( array( $this, $method ), $args );
		}
	}

	/**
	 * Registers the check post type in WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_post_type() {
		register_post_type( 'src_check', array(
			'labels'               => $this->get_post_type_labels(),
			'public'               => false,
			'hierarchical'         => false,
			'show_ui'              => true,
			'show_in_menu'         => true,
			'show_in_nav_menus'    => false,
			'show_in_admin_bar'    => false,
			'menu_position'        => 101,
			'menu_icon'            => 'dashicons-universal-access',
			'supports'             => array( 'title' ),
			'register_meta_box_cb' => array( $this, 'register_post_type_meta_boxes' ),
			'has_archive'          => false,
			'rewrite'              => false,
		) );
	}

	/**
	 * Registers the meta boxes for the check post type admin user interface in WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_post_type_meta_boxes() {
		add_meta_box( 'src_data', __( 'Input Data', 'screen-reader-check' ), array( $this, 'render_post_type_data_meta_box' ), null, 'normal', 'high' );
		add_meta_box( 'src_tests', __( 'Test Results', 'screen-reader-check' ), array( $this, 'render_post_type_tests_meta_box' ), null, 'normal', 'high' );
	}

	/**
	 * Renders the Input Data meta box.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param WP_Post $post The current post object.
	 */
	private function render_post_type_data_meta_box( $post ) {
		$check = $this->get( $post->ID );

		$url = $check->get_url();
		$html = $check->get_html();

		$options = $check->get_options();

		?>
		<table class="form-table">
			<?php if ( ! empty( $url ) ) : ?>
				<tr>
					<th scope="row">
						<label for="src_url"><?php _e( 'URL', 'screen-reader-check' ); ?></label>
					</th>
					<td>
						<input type="url" id="src_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" readonly="readonly" aria-describedby="src_url_description" />
						<p id="src_url_description" class="description"><?php _e( 'This is the URL of the webpage checked.', 'screen-reader-check' ); ?></p>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<th scope="row">
					<label for="src_html"><?php _e( 'HTML Code', 'screen-reader-check' ); ?></label>
				</th>
				<td>
					<div id="template">
						<textarea id="src_html" cols="70" rows="20" readonly="readonly" aria-describedby="src_html_description"><?php echo esc_textarea( $html ); ?></textarea>
						<p id="src_html_description" class="description"><?php _e( 'This is the HTML code checked.', 'screen-reader-check' ); ?></p>
					</div>
				</td>
			</tr>
		</table>

		<h3><?php _e( 'Advanced Options', 'screen-reader-check' ); ?></h3>

		<table class="form-table">
			<?php foreach ( $this->global_options as $option ) : ?>
				<tr>
					<th scope="row">
						<label for="src_options_<?php echo $option['slug']; ?>"><?php echo $option['label']; ?></label>
					</th>
					<td>
						<input type="<?php echo $option['type']; ?>" id="src_options_<?php echo $option['slug']; ?>" value="<?php echo isset( $options[ $option['slug'] ] ) ? $options[ $option['slug'] ] : $option['default']; ?>" class="regular-text" readonly="readonly" aria-describedby="src_options_<?php echo $option['slug']; ?>_description" />
						<p id="src_options_<?php echo $option['slug']; ?>_description" class="description"><?php echo $option['admin_description']; ?></p>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}

	/**
	 * Renders the Test Results meta box.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param WP_Post $post The current post object.
	 */
	private function render_post_type_tests_meta_box( $post ) {
		$check = $this->get( $post->ID );

		$results = $check->get_test_results();
		?>

		<?php if ( empty( $results ) ) : ?>
			<p><?php _e( 'No tests have been executed yet for this check.', 'screen-reader-check' ); ?></p>
		<?php else : ?>
			<style type="text/css">
				.code-snippet {
					width: 100%;
					padding: 5px 10px;
					font-family: monospace;
					color: #aa0000;
					background-color: #eeeeee;
				}
			</style>
			<ul>
				<?php foreach ( $results as $result ) : ?>
					<li id="src-result-<?php echo $result->get_test_slug(); ?>" class="src-result src-result-<?php echo $result->get_type() ?>">
						<h3><?php echo $result->get_test_title(); ?></h3>
						<?php echo '<p>' . implode( '</p><p>', $result->get_messages() ) . '</p>'; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php
	}

	/**
	 * Returns the labels for the check post type in WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array Array of labels.
	 */
	private function get_post_type_labels() {
		return array(
			'name'                  => __( 'Checks', 'screen-reader-check' ),
			'singular_name'         => __( 'Check', 'screen-reader-check' ),
			'add_new'               => __( 'Add New', 'screen-reader-check' ),
			'add_new_item'          => __( 'Add New Check', 'screen-reader-check' ),
			'edit_item'             => __( 'Edit Check', 'screen-reader-check' ),
			'new_item'              => __( 'New Check', 'screen-reader-check' ),
			'view_item'             => __( 'View Check', 'screen-reader-check' ),
			'search_items'          => __( 'Search Checks', 'screen-reader-check' ),
			'not_found'             => __( 'No checks found.', 'screen-reader-check' ),
			'not_found_in_trash'    => __( 'No checks found in Trash.', 'screen-reader-check' ),
			'all_items'             => __( 'All Checks', 'screen-reader-check' ),
			'archives'              => __( 'Check Archives', 'screen-reader-check' ),
			'insert_into_item'      => __( 'Insert into check', 'screen-reader-check' ),
			'uploaded_to_this_item' => __( 'Uploaded to this check', 'screen-reader-check' ),
			'filter_items_list'     => __( 'Filter checks list', 'screen-reader-check' ),
			'items_list_navigation' => __( 'Checks list navigation', 'screen-reader-check' ),
			'items_list'            => __( 'Checks list', 'screen-reader-check' ),
		);
	}

	/**
	 * AJAX callback to create a new check.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param array $data Arguments passed through AJAX.
	 * @return array|WP_Error Response array on success, or error object on failure.
	 */
	private function ajax_create( $data ) {
		$check = $this->create( $data );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		return array( 'id' => $check->get_id() );
	}
}
