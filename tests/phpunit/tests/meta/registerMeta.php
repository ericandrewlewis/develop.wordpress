<?php

/**
 * @group meta
 */
class Tests_Meta_Register_Meta extends WP_UnitTestCase {
	protected static $editor_id;
	protected static $post_id;
	protected static $comment_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$editor_id = $factory->user->create( array( 'role' => 'editor' ) );
		self::$post_id = $factory->post->create();
		self::$comment_id = $factory->comment->create( array( 'comment_post_ID' => self::$post_id ) );
	}

	public static function wpTearDownAfterClass() {
		self::delete_user( self::$editor_id );
		wp_delete_comment( self::$comment_id, true );
		wp_delete_post( self::$post_id, true );
	}

	function setUp() {
		parent::setUp();
		wp_set_current_user( self::$editor_id );
	}

	public function _old_sanitize_meta_cb( $meta_value, $meta_key, $meta_type ) {
		return $meta_key . ' sanitized';
	}

	public function _new_sanitize_meta_cb( $meta_value, $meta_key, $object_type, $object_subtype ) {
		return $meta_key . ' sanitized';
	}

	public function test_register_meta_without_sanitize_callback_registers_meta_key() {
		register_meta( 'post', 'flight_number', array() );
		$this->assertTrue( registered_meta_key_exists( 'post', 'flight_number' ) );
	}

	public function test_register_meta_for_a_category() {
		register_meta( 'taxonomy', 'category_icon', array( 'object_subtype' => 'category' ) );
		$this->assertTrue( registered_meta_key_exists( 'taxonomy', 'category_icon', 'category' ) );
	}

	public function test_register_meta_for_a_comment() {
		register_meta( 'comment', 'comment_rating', array( 'object_subtype' => 'comment' ) );
		$this->assertTrue( registered_meta_key_exists( 'comment', 'comment_rating', 'comment' ) );
	}

	public function test_register_meta_with_sanitize_callback_registers_meta_key() {
		register_meta( 'post', 'flight_number', array( 'sanitize_callback' => 'esc_html' ) );
		$this->assertTrue( registered_meta_key_exists( 'post', 'flight_number' ) );
	}

	public function test_register_meta_valid_object_type_with_valid_subtype() {
		register_meta( 'post', 'longitude', array( 'object_subtype' => 'post' ) );
		$this->assertTrue( registered_meta_key_exists( 'post', 'longitude', 'post' ) );
	}

	public function test_register_meta_valid_object_type_with_invalid_subtype() {
		register_meta( 'post', 'latitude', array( 'object_subtype' => 'invalid-type' ) );
		$this->assertFalse( registered_meta_key_exists( 'post', 'latitude', 'invalid-type' ) );
	}

	public function test_register_meta_with_old_sanitize_callback_parameter() {
		register_meta( 'post', 'old_sanitized_key', array( $this, '_old_sanitize_meta_cb' ) );
		$meta = sanitize_meta( 'old_sanitized_key', 'unsanitized', 'post' );

		$this->assertEquals( 'old_sanitized_key sanitized', $meta );
	}

	public function test_register_meta_with_new_sanitize_callback_parameter() {
		register_meta( 'post', 'new_sanitized_key', array( 'sanitize_callback' => array( $this, '_new_sanitize_meta_cb' ) ) );
		$meta = sanitize_meta( 'new_sanitized_key', 'unsanitized', 'post' );

		$this->assertEquals( 'new_sanitized_key sanitized', $meta );
	}
}
