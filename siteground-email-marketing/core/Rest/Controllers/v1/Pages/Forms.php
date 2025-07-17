<?php
namespace SG_Email_Marketing\Rest\Controllers\v1\Pages;

use WP_REST_Posts_Controller;
use SG_Email_Marketing\Traits\Rest_Trait;
use SG_Email_Marketing\Loader\Loader;
use SG_Email_Marketing\Post_Types\Forms as Forms_Post_Type;

/**
 * Class responsible for the Forms plugin page.
 */
class Forms extends WP_REST_Posts_Controller {
	use Rest_Trait;

	/**
	 * Post Type
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $post_type = 'sg_form';

	/**
	 * The Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct( $this->post_type );
		$this->namespace = $this->rest_namespace;
		$this->rest_base = 'forms';
	}

	/**
	 * Prepare the item for creation.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return stdClass|WP_Error Post object or WP_Error.
	 */
	protected function prepare_item_for_database( $request ) {
		$params = $request->get_params();
		$body   = $params['body'];

		if ( ! isset( $body ) ) {
			return new \WP_Error(
				'message',
				__( 'Missing body', 'siteground-email-marketing' ),
				array( 'status' => 400 )
			);
		}

		$id = isset( $params['id'] ) ? $params['id'] : 0;

		// Bail if form with the same name exists.
		if ( $this->form_title_exists( $body['settings']['form_title'], $id ) ) {
			return new \WP_Error(
				'error',
				__( 'Name already exists.', 'siteground-email-marketing' ),
				array( 'status' => 403 )
			);
		}

		$request['content'] = wp_json_encode( $body );
		$request['status']  = 'publish';
		$request['title']   = $body['settings']['form_title'];

		unset( $request['body'] );

		return parent::prepare_item_for_database( $request );
	}

	/**
	 * Check if we have a form with this title.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $title The user specified title.
	 * @param  integer $id    The form id if is edit.
	 *
	 * @return boolean true/false Whether the title exists.
	 */
	public function form_title_exists( $title, $id ) {
		global $wpdb;

		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'sg_form' AND post_status = 'publish' LIMIT 1",
				$title,
			),
			ARRAY_A
		);

		// No form with that name exists.
		if ( ! $posts ) {
			return false;
		}

		// Allow changes if it is edit bail otherwise.
		return intval( $posts[0]['ID'] ) === $id ? false : true;
	}

	/**
	 * Prepare the item for response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post         $item    Post object.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return array  The modified response.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$body = json_decode( $request->get_body(), true );

		return array(
			'ID'           => $item->ID,
			'body'         => json_decode( $item->post_content ),
			'date_created' => strtotime( $item->post_date ),
			'meta'         => $body['meta'],
		);
	}


	/**
	 * Sync the labels and the custom fields before getting the forms data.
	 *
	 * @param \WP_REST_Request $request The incoming request object.
	 */
	public function get_items( $request ) {

		$status = Loader::get_instance()->mailer_api->get_status();

		// Sync only if we are connected.
		if ( isset( $status['status'] ) && 'connected' === $status['status'] ) {
			$this->sync_custom_fields();
		}

		return parent::get_items( $request );
	}

	/**
	 * Sync the custom fields before getting the forms data.
	 */
	public function sync_custom_fields() {
		// Get the custom fields form the EM service.
		$api_fields = Loader::get_instance()->mailer_api->get_custom_fields();

		// Store them, as arrays, with the ID as the key.
		$api_field_map = array();
		foreach ( $api_fields['data'] as $field ) {
			$api_field_map[ $field['id'] ] = $field;
		}

		$all_forms = Forms_Post_Type::get_all_forms();

		foreach ( $all_forms as $form ) {
			$body = json_decode( $form->post_content, true );

			if ( ! isset( $body['custom-fields'] ) || ! is_array( $body['custom-fields'] ) ) {
				continue;
			}

			$stored_fields     = $body['custom-fields'];
			$original_fields   = $stored_fields;
			$updated_fields    = array();

			foreach ( $stored_fields as $stored_field ) {
				$id = $stored_field['cf-id'];

				if ( ! isset( $api_field_map[ $id ] ) ) {
					continue;
				}

				// Get the custom field data, received from the ME Service.
				$api_field = $api_field_map[ $id ];

				// Check and update the name of the custom field in case it has changed.
				if ( $stored_field['name'] !== $api_field['name'] ) {
					$stored_field['name'] = $api_field['name'];
				}

				// Check and update the names of the options.
				if ( 'dropdown' === $stored_field['type'] && isset( $stored_field['options'] ) ) {
					$api_options = array();
					foreach ( $api_field['customFieldOptions'] as $option ) {
						$api_options[ $option['id'] ] = $option['name'];
					}

					$updated_options = array();
					foreach ( $stored_field['options'] as $stored_option ) {
						// Check if there is an EM Service option field with that id.
						if ( isset( $api_options[ $stored_option['id'] ] ) ) {
							// If the ID exists, then compare the name.
							if ( $api_options[ $stored_option['id'] ] !== $stored_option['name'] ) {
								// If the name has changed, update the stored name to the new one.
								$stored_option['name'] = $api_options[ $stored_option['id'] ];
							}

							$updated_options[] = $stored_option;
						}
					}
					$stored_field['options'] = $updated_options;
				}

				// Save the updated fields.
				$updated_fields[] = $stored_field;
			}

			// Check if update is needed.
			if ( json_encode( $original_fields ) !== json_encode( $updated_fields ) ) {

				$body['custom-fields'] = $updated_fields;

				wp_update_post(
					array(
						'ID'           => $form->ID,
						'post_content' => wp_json_encode( $body ),
					)
				);
			}
		}
	}
}
