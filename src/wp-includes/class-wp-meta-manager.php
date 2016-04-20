<?php
/**
 * A registry for meta data.
 *
 * @package WordPress
 * @since 4.6.0
 */
final class WP_Meta_Manager {
	/**
	 * Registered meta of the post meta type (including pages and CPTs).
	 *
	 * @since 4.6.0
	 * @access private
	 * @var array
	 */
	private $post = array(
		'all' => array()
	);

	/**
	 * Registered meta of the taxonomy meta type.
	 *
	 * @since 4.6.0
	 * @access private
	 * @var array
	 */
	private $taxonomy = array(
		'all' => array()
	);

	/**
	 * Registered meta of the user meta type.
	 *
	 * @since 4.6.0
	 * @access private
	 * @var array
	 */
	private $user = array(
		'all' => array()
	);

	/**
	 * Registered meta of the comment meta type.
	 *
	 * @since 4.6.0
	 * @access private
	 * @var array
	 */
	private $comment = array(
		'all' => array()
	);

	/**
	 * @since 4.6.0
	 */
	function __construct() {
		$post_types_without_meta = array( 'revision', 'nav_menu_item' );
		foreach ( get_post_types( null, 'names' ) as $post_type ) {
			if ( in_array( $post_type, $post_types_without_meta ) ) {
				continue;
			}
			$this->post[$post_type] = array();
		}

		foreach ( get_taxonomies() as $taxonomy ) {
			$this->taxonomy[$taxonomy] = array();
		}

		foreach ( array( 'comment', 'ping' ) as $comment_type ) {
			$this->comment[$comment_type] = array();
		}
	}

	/**
	 * Register a meta key.
	 *
	 * @since 4.6.0
	 *
	 * @param array|object $query {
	 *     Array or object of meta data properties.
	 *
	 *     @type string $object_type  The meta type this key belongs to, e.g. `post`.
	 *     @type string $object_subtype The meta subtype this key belongs to, e.g. `page`.
	 *     @type string $key          The meta key.
	 *     @type array  $schema       The meta field's schema, according to JSON schema.
	 *     @type array  $public       Whether the meta key should be publicly queryable.
	 * @return true|WP_Error
	 */
	public function register( $args ) {
		if ( ! is_object( $args ) ) {
			$args = (object) $args;
		}
		if ( empty( $args->object_type ) ) {
			return new WP_Error( 'missing_object_type', __( 'The object type is required to register meta.' ) );
		}
		if ( ! property_exists( $this, $args->object_type ) ) {
			return new WP_Error( 'object_type_does_not_exist', __( sprintf( 'The object type "%s" does not exist.', esc_html( $args['object_type'] ) ) ) );
		}
		if ( ! empty( $args->object_subtype ) && ! isset( $this->{$args->object_type}[ $args->object_subtype ] ) ) {
			return new WP_Error( 'meta_subtype_does_not_exist', __( 'The meta subtype does not exist in the registry.' ) );
		}

		if ( empty( $args->key ) ) {
			return new WP_Error( 'meta_key_required', __( 'The meta key is required.' ) );
		}

		if ( ! isset( $args->schema ) ) {
			return new WP_Error( 'schema_required', __( 'The JSON schema for the meta key is required.' ) );
		}

		if ( ! isset( $args->public ) ) {
			return new WP_Error( 'public_required', __( 'The public argument is required to register meta.' ) );
		}

		if ( empty( $args->object_subtype ) ) {
			$args->object_subtype = 'all';
		}

		$this->{$args->object_type}[ $args->object_subtype ][ $args->key ] = $args;

		return true;
	}

	/**
	 * Retrieve a list of registered meta keys for an object type and subtype.
	 *
	 * @since 4.6.0
	 *
	 * @param string $object_type The type of object. Post, comment, user, term.
	 * @param string $object_subtype A subtype of the object.
	 *
	 * @return array List of registered meta keys.
	 */
	public function get_registered_meta_keys( $object_type = 'post', $object_subtype = 'all' ) {
		if ( ! property_exists( $this, $object_type ) ) {
			return array();
		}

		if ( ! isset( $this->{$object_type}[ $object_subtype ] ) ) {
			return array();
		}

		return $this->{$object_type}[ $object_subtype ];
	}

	/**
	 * Check if a meta key is registered.
	 *
	 * @since 4.6.0
	 *
	 * @param string $object_type
	 * @param string $meta_key
	 * @param string $object_subtype
	 *
	 * @return bool True if the meta key is registered to the object type and subtype. False if not.
	 */
	public function registered_meta_key_exists( $object_type, $meta_key, $object_subtype = 'all' ) {
		if ( ! property_exists( $this, $object_type ) ) {
			return false;
		}

		if ( ! isset( $this->{$object_type}[ $object_subtype ] ) ) {
			return false;
		}

		if ( ! isset( $this->{$object_type}[ $object_subtype ][ $meta_key ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Unregister a meta key from the list of registered keys.
	 *
	 * @since 4.6.0
	 *
	 * @param string $object_type    The type of object.
	 * @param string $meta_key       The meta key.
	 * @param string $object_subtype Optional. The subtype of the object type.
	 *
	 * @return bool|WP_Error True if successful. WP_Error if the meta key is invalid.
	 */
	public function unregister_meta_key( $object_type, $meta_key, $object_subtype = 'all' ) {
		if ( ! $this->registered_meta_key_exists( $object_type, $meta_key, $object_subtype ) ) {
			return new WP_Error( 'meta_key_is_not_registered', __( 'Meta key is not registered.' ) );
		}

		unset( $this->{$object_type}[ $object_subtype ][ $meta_key ] );

		return true;
	}
}
