<?php
/**
 * Domains class
 *
 * @package ScreenReaderCheck
 * @since 1.0.0
 */

namespace ScreenReaderCheck;

use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * This class performs general domain-related functions.
 *
 * @since 1.0.0
 */
class Domains {
	/**
	 * Returns a domain object for a given URL.
	 *
	 * If no domain object exists, it will be created.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $url URL to get the domain object for.
	 * @return ScreenReaderCheck\Domain|WP_Error Either the domain object, or an error object on failure.
	 */
	public function get_by_url( $url ) {
		$domain = parse_url( $url, PHP_URL_HOST );

		$posts = get_posts( array(
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'post_type'      => 'src_domain',
			'post_status'    => 'publish',
			'title'          => $domain,
		) );

		if ( empty( $posts ) ) {
			return $this->create( $domain );
		}

		return $this->get( $posts[0] );
	}

	/**
	 * Creates a new domain.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $domain Domain name.
	 * @return ScreenReaderCheck\Domain|WP_Error Either the new domain object, or an error object on failure.
	 */
	public function create( $domain ) {
		$post_args = array(
			'post_type'     => 'src_domain',
			'post_status'   => 'publish',
			'post_title'    => $domain,
			'post_name'     => '',
		);

		$id = wp_insert_post( $post_args );
		if ( ! $id ) {
			return new WP_Error( 'could_not_store_domain', __( 'An internal error occurred while trying to store the domain.', 'screen-reader-check' ) );
		}

		return $this->get( $id );
	}

	/**
	 * Retrieves a domain by ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The domain ID.
	 * @return ScreenReaderCheck\Domain|WP_Error Either the domain object, or an error object on failure.
	 */
	public function get( $id ) {
		if ( 'src_domain' !== get_post_type( $id ) ) {
			return new WP_Error( 'invalid_id', __( 'Invalid domain ID.', 'screen-reader-check' ) );
		}

		return new Domain( $id );
	}

	/**
	 * Deletes a domain by ID.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $id The domain ID.
	 * @return bool|WP_Error True on success, an error object on failure.
	 */
	public function delete( $id ) {
		if ( 'src_domain' !== get_post_type( $id ) ) {
			return new WP_Error( 'invalid_id', __( 'Invalid domain ID.', 'screen-reader-check' ) );
		}

		$post = wp_delete_post( $id, true );
		if ( ! $post ) {
			return new WP_Error( 'could_not_delete_domain', __( 'An internal error occurred while trying to delete the domain.', 'screen-reader-check' ) );
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
				return call_user_func_array( array( $this, $method ), $args );
		}
	}

	/**
	 * Registers the domain post type in WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function register_post_type() {
		register_post_type( 'src_domain', array(
			'labels'               => $this->get_post_type_labels(),
			'public'               => false,
			'hierarchical'         => false,
			'show_ui'              => false,
			'show_in_menu'         => false,
			'show_in_nav_menus'    => false,
			'show_in_admin_bar'    => false,
			'supports'             => array( 'title' ),
			'has_archive'          => false,
			'rewrite'              => false,
		) );
	}

	/**
	 * Returns the labels for the domain post type in WordPress.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @return array Array of labels.
	 */
	private function get_post_type_labels() {
		return array(
			'name'                  => __( 'Domains', 'screen-reader-check' ),
			'singular_name'         => __( 'Domain', 'screen-reader-check' ),
			'add_new'               => __( 'Add New', 'screen-reader-check' ),
			'add_new_item'          => __( 'Add New Domain', 'screen-reader-check' ),
			'edit_item'             => __( 'Edit Domain', 'screen-reader-check' ),
			'new_item'              => __( 'New Domain', 'screen-reader-check' ),
			'view_item'             => __( 'View Domain', 'screen-reader-check' ),
			'search_items'          => __( 'Search Domains', 'screen-reader-check' ),
			'not_found'             => __( 'No domains found.', 'screen-reader-check' ),
			'not_found_in_trash'    => __( 'No domains found in Trash.', 'screen-reader-check' ),
			'all_items'             => __( 'All Domains', 'screen-reader-check' ),
			'archives'              => __( 'Domain Archives', 'screen-reader-check' ),
			'insert_into_item'      => __( 'Insert into domain', 'screen-reader-check' ),
			'uploaded_to_this_item' => __( 'Uploaded to this domain', 'screen-reader-check' ),
			'filter_items_list'     => __( 'Filter domains list', 'screen-reader-check' ),
			'items_list_navigation' => __( 'Domains list navigation', 'screen-reader-check' ),
			'items_list'            => __( 'Domains list', 'screen-reader-check' ),
		);
	}
}
