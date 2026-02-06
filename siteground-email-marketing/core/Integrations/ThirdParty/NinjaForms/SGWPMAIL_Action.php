<?php
namespace SG_Email_Marketing\Integrations\ThirdParty\NinjaForms;

use SG_Email_Marketing\Loader\Loader;
use SG_Email_Marketing\Traits\Ip_Trait;
use SG_Email_Marketing\Integrations\ThirdParty\Form_Parser;

/**
 * Class SGWPMAIL_Action
 */
final class SGWPMAIL_Action extends \NF_Abstracts_ActionNewsletter {
	use Ip_Trait;

	/**
	 * Name of the action.
	 *
	 * @var string
	 */
	protected $_name = 'sgwpmail_subscribe';

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();

		$this->_nicename = 'SiteGround Email Marketing';

		unset( $this->_settings['sgwpmail_subscribenewsletter_list'] );
		unset( $this->_settings['sgwpmail_subscribenewsletter_list_fields'] );
		unset( $this->_settings['sgwpmail_subscribenewsletter_list_groups'] );

		$this->_settings['sgwpmail_groups'] = array(
			'name'  => 'sgwpmail_groups',
			'type'  => 'html',
			'help'  => __( 'We recommend adding a consent checkbox if the main purpose of the form is not subscription. You can add a consent checkbox from "Form Fields", Type - SG Email Marketing Checkbox', 'siteground-email-marketing' ),
			'label' => __( 'Groups', 'siteground-email-marketing' ),
			'group' => 'primary',
			'width' => 'full',
			'value' => $this->get_groups_html( $this ),
		);

		$this->_settings['sgwpmail_groups_value'] = array(
			'name'  => 'sgwpmail_groups_value',
			'type'  => 'textbox',
			'group' => 'primary',
			'value' => '',
		);

		$enabled_value = '
		<span class="sgwpmail-enabled">' .
						__( 'Consent Checkbox is <span class="enabled">ADDED</span>', 'siteground-email-marketing' ) .
						'</span></br>' .
						'<span class="sgwpmail-help">' . __( 'Users submitting the form will be subscribed if they have provided consent.', 'siteground-email-marketing' ) . '</span>';

		$this->_settings['sgwpmail_consent_checkbox_enabled'] = array(
			'type'  => 'html',
			'value' => $enabled_value,
			'name'  => 'sgwpmail_consent_checkbox_enabled',
			'group' => 'primary',
			'width' => 'full',
			'deps'  => array(
				'sgwpmail_consent_checkbox_status' => 1,
			),
			'help'  => __( 'We recommend adding a consent checkbox if the main purpose of the form is not subscription. You can add a consent checkbox from "Form Fields", Type - SG Email Marketing Checkbox', 'siteground-email-marketing' ),
		);

		$disabled_value = '<span class="sgwpmail-disabled">' .
						__( 'Consent Checkbox is <span class="disabled">NOT ADDED</span>', 'siteground-email-marketing' ) .
						'</span></br>' .
						'<span class="sgwpmail-help">' . __( 'We recommend adding a consent checkbox if the main purpose of the form is not subscription. You can add a consent checkbox from "Form Fields", Type - SG Email Marketing Checkbox', 'siteground-email-marketing' ) . '</span>';

		$this->_settings['sgwpmail_consent_checkbox_disabled'] = array(
			'type'  => 'html',
			'value' => $disabled_value,
			'name'  => 'sgwpmail_consent_checkbox_disabled',
			'group' => 'primary',
			'width' => 'full',
			'deps'  => array(
				'sgwpmail_consent_checkbox_status' => 0,
			),
			'help'  => __( 'We recommend adding a consent checkbox if the main purpose of the form is not subscription. You can add a consent checkbox from "Form Fields", Type - SG Email Marketing Checkbox', 'siteground-email-marketing' ),
		);

		$this->_settings['sgwpmail_consent_checkbox_status'] = array(
			'type'  => 'toggle',
			'value' => 0,
			'name'  => 'sgwpmail_consent_checkbox_status',
			'group' => 'primary',
			'class' => 'test',
			'width' => 'full',
		);
	}

	/**
	 * Echoes the HTML for the Groups selector.
	 *
	 * @since 1.3.0
	 *
	 * @param  array $action Current action.
	 * @return string HTML of the groups field.
	 */
	public function get_groups_html( $action ) {
		$form_id = isset( $_GET['form_id'] ) ? sanitize_text_field( wp_unslash( $_GET['form_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$form = \Ninja_Forms()->form( $form_id )->get();

		$selected = array();

		$actions = \Ninja_Forms()->form( $form_id )->get_actions();
		foreach ( $actions as $action ) {
			$settings = $action->get_settings();
			if ( array_key_exists( 'sgwpmail_groups_value', $settings ) ) {
				$selected = $settings['sgwpmail_groups_value'];
			}
		}
		if ( ! empty( $selected ) ) {
			$selected = json_decode( $selected );
		}
		$options       = '';
		$labels        = $this->get_labels();
		$selected_html = '';
		foreach ( $labels as $key => $label ) {
			if ( ! empty( $selected ) ) {
				$selected_html = in_array( $label['value'], $selected, true ) ? 'selected' : '';
			}

			$options .= '<option value="' . $label['value'] . '" ' . $selected_html . '>' . $label['label'] . '</option>';
		}
		return '<label for="sgwpmail_groups[]">' . __( 'Groups', 'siteground-email-marketing' ) . '</label><span class="sgwpmail-help">' . __( 'People subscribing through this form will be added to the selected groups', 'siteground-email-marketing' ) . '</span><select multiple name="sgwpmail_groups[]" id="sgwpmail_groups[]">' . $options . '</select>';
	}

	/**
	 * Save function
	 *
	 * @since 1.3.0
	 *
	 * @param  array $action_settings Action's settings array.
	 * @return void
	 */
	public function save( $action_settings ) {
	}

	/**
	 * Process the data from submitting the form.
	 *
	 * @since 1.3.0
	 *
	 * @param array $action_settings The action's settings.
	 * @param int   $form_id         The form id.
	 * @param array $data            The submitted data.
	 *
	 * @return boolean Returns true on success, false on fail.
	 */
	public function process( $action_settings, $form_id, $data ) {
		$field_values     = array();
		$checkbox_checked = false;

		foreach ( $data['fields'] as $field ) {
			if ( 'sgwpmail_checkbox' === $field['type'] && ! empty( $field['value'] ) ) {
				$checkbox_checked = true;
			}
			$field_values[ $field['key'] ] = $field['value'];
		}

		if (
			isset( $action_settings['sgwpmail_consent_checkbox_status'] ) &&
			'1' === $action_settings['sgwpmail_consent_checkbox_status'] &&
			! $checkbox_checked
			) {
			return false;
		}

		$data = Form_Parser::extract_data( $field_values );

		// Do nothing if no email was found.
		if ( empty( $data['email'] ) ) {
			return false;
		}

		$data = array(
			'labels'    => $this->get_label_ids( json_decode( $action_settings['sgwpmail_groups_value'] ) ),
			'firstName' => $data['first_name'],
			'lastName'  => $data['last_name'],
			'email'     => $data['email'],
			'timestamp' => time(),
			'ip'        => $this->get_current_user_ip(),
		);

		Loader::get_instance()->mailer_api->send_data( array( $data ) );
	}

	/**
	 * Get lists for the editor.
	 *
	 * @return void
	 */
	protected function get_lists() {
	}
	/**
	 * Retrieve the labels added in SG Email Marketing tool.
	 *
	 * @since 1.3.0
	 *
	 * @return array Array of labels.
	 */
	public function get_labels() {
		$labels_list = Loader::get_instance()->mailer_api->get_labels();
		$labels      = array();
		if ( empty( $labels_list['data'] ) ) {
			return array();
		}

		foreach ( $labels_list['data'] as $label ) {
			$labels[] = array(
				'value' => $label['name'],
				'label' => $label['name'],
			);
		}

		return $labels;

	}
	/**
	 * Get label ids from label names
	 *
	 * @since 1.3.0
	 *
	 * @param  array $label_names A list with the label names.
	 *
	 * @return array              A list with label ids.
	 */
	public function get_label_ids( $label_names ) {
		$labels_list = Loader::get_instance()->mailer_api->get_labels();

		$label_ids = array();
		foreach ( $labels_list['data'] as $label ) {
			if ( in_array( $label['name'], $label_names, true ) ) {
				$label_ids[] = $label['id'];
			}
		}
		return $label_ids;
	}
}
