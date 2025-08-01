<?php
namespace SG_Email_Marketing\Forms;

use SG_Email_Marketing\Traits\Ip_Trait;
/**
 * Forms FE class.
 */
class Forms {
	use Ip_Trait;

	protected $mailer_api;

	public function __construct( $mailer_api ) {
		$this->mailer_api = $mailer_api;
	}

	/**
	 * Enqueue the styles for preview mode.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_scripts() {

		wp_enqueue_script(
			'sg-email-marketing-design',
			\SG_Email_Marketing\URL . '/assets/js/design.js',
			array( 'jquery' ),
			\SG_Email_Marketing\VERSION,
			true
		);

		wp_enqueue_script(
			'sg-email-marketing-frontend',
			\SG_Email_Marketing\URL . '/assets/js/sg-email-marketing-frontend.js',
			array( 'jquery' ),
			\SG_Email_Marketing\VERSION,
			true
		);

		wp_localize_script(
			'sg-email-marketing-frontend',
			'ajaxData',
			array( 'url' => admin_url( 'admin-ajax.php' ) )
		);

		wp_localize_script(
			'sg-email-marketing-design',
			'wpData',
			array(
				'errors' => array(
					'email' => __( 'Please provide a valid email address', 'siteground-email-marketing' ),
					'default' => __( 'This field is required', 'siteground-email-marketing' ),
				),
			)
		);
	}

	/**
	 * Process AJAX form submit.
	 *
	 * @since 1.5.3
	 */
	public function handle_form_submission() {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['wpnonce'] ), 'sg-email-marketing-form' ) ) {
			wp_send_json_error();
		}

		// Parse the URL-encoded string into an associative array
		parse_str( html_entity_decode( $_POST['form_data'] ), $data );

		if ( empty( $data['form-id'] ) || ! empty( $data['spam-protection'] )  ) {
			wp_send_json_error();
		}

		$result = $this->process_form( $data );

		if ( ! empty( $result ) ) {
			wp_send_json_error( $result, 403 );
		}

		wp_send_json_success( array( 'confirmation' => ob_get_clean() ) );
	}


	public function process_form( $form_data ) {

		$form        = get_post( $form_data['form-id'] );
		$form_schema = json_decode( $form->post_content, true );
		$consent_checked = ! empty( $form_data['sg-marketing-form-checkbox'] ) ? true: false;

		if ( empty( $form_schema ) ) {
			return array( 'general_error' => __( 'The form schema is broken', 'siteground-email-marketing' ) );
		}

		$errors = array();
		$data = array();

		foreach ( $form_schema['fields'] as $field ) {
			$field_value = isset( $form_data[ $field['sg-form-type'] ] ) ? trim( $form_data[ $field['sg-form-type'] ] ) : null; //phpcs:ignore

			if ( 'email' === $field['type'] && false === filter_var( $field_value, FILTER_VALIDATE_EMAIL ) ) {
				$errors[ $field['sg-form-type'] ] = __( 'Please provide a valid email address', 'siteground-email-marketing' );
				continue;
			}

			if ( intval( $field['required'] ) === 1 && empty( $field_value ) ) {
				$errors[ $field['sg-form-type'] ] = printf( esc_attr__( 'The field "%s" is required!', 'siteground-email-marketing' ), esc_attr( $field['label'] ) );
				continue;
			}

			$data[ lcfirst(str_replace( '-', '', ucwords( $field['sg-form-type'], '-' ) )) ] = $field_value;
		}

		// Custom fields logic here.
		if ( isset( $form_schema['custom-fields'] ) && is_array( $form_schema['custom-fields'] ) ) {
			$data['contactCustomFields'] = $this->process_custom_fields( $form_schema['custom-fields'], $form_data );
		}

		// Consent logic here.
		if ( ! empty( $form_schema['consent']['consent_visible'] )) {
			if ( ! empty( $form_schema['consent'] ) && ! empty( $form_schema['consent']['consent_checkbox'] ) && ! $consent_checked ) {
				return;
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		$labels = array_map( function( $label ) {
			return $label['id'];
		}, $form_schema['settings']['labels'] );

		$data = array_merge( $data, array(
			'timestamp' => time(),
			'ip'        => $this->get_current_user_ip(),
			'labels'    => $labels,
		));

		try {
			$this->mailer_api->send_data( array( $data ) );
		} catch ( \Exception $e ) {
			return array( 'general_error' => $e->getMessage() );
		}
	}

	/**
	 * Extract and prepare the custom fields data.
	 *
	 * @param array $custom_fields The custom field schema.
	 *
	 * @param array $form_data The submitted form data.
	 *
	 * @return array The structured custom fields data.
	 */
	public function process_custom_fields( $custom_fields, $form_data ) {
		$custom_fields_data = array();

		foreach ( $custom_fields as $field ) {
			// Generate the field names, based on the render's structure.
			$field_hidden_name = $field['sg-form-type'] . '-' . $field['id'];
			$field_input_name = $field['type'] . '-' . $field['id'];

			if ( 'dropdown' === $field['type'] ) {
				$custom_dropdown_id = isset( $form_data[ $field_hidden_name ] ) ? trim( $form_data[ $field_hidden_name ] ) : null;
				$option_id = isset( $form_data[ $field_input_name ] ) ? trim( $form_data[ $field_input_name ] ) : null;

				$custom_fields_data[] = array(
					'customField'       => $custom_dropdown_id,
					'customFieldOption' => $option_id,
				);
			}

			if ( 'text' === $field['type'] ) {
				$custom_text_id = isset( $form_data[ $field_hidden_name ] ) ? trim( $form_data[ $field_hidden_name ] ) : null;
				$text = isset( $form_data[ $field_input_name ] ) ? trim( $form_data[ $field_input_name ] ) : null;

				$custom_fields_data[] = array(
					'customField'           => $custom_text_id,
					'customFieldOptionText' => $text,
				);
			}
		}

		return $custom_fields_data;
	}
}
