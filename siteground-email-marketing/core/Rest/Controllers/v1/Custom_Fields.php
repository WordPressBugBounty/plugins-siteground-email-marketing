<?php
namespace SG_Email_Marketing\Rest\Controllers\v1;

use SG_Email_Marketing\Traits\Rest_Trait;
use SG_Email_Marketing\Loader\Loader;

/**
 * Class responsible for the Custom_Fields.
 */
class Custom_Fields {
	use Rest_Trait;

	/**
	 * Register the rest routes for the custom fields.
	 *
	 * @since 1.0.0
	 */
	public function register_rest_routes() {
		// Get all custom fields.
		register_rest_route(
			$this->rest_namespace,
			'/custom-fields/',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			)
		);
	}

	/**
	 * Get all custom fields.
	 *
	 * @since 1.0.0
	 */
	public function get_items() {
		try {
			return rest_ensure_response( Loader::get_instance()->mailer_api->get_custom_fields() );
		} catch ( \Exception $e ) {
			return $this->get_errors( $e );
		}
	}
}
