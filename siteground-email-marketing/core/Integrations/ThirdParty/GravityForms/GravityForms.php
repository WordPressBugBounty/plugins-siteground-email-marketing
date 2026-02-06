<?php
namespace SG_Email_Marketing\Integrations\ThirdParty\GravityForms;

use SG_Email_Marketing\Integrations\Integrations;
use SG_Email_Marketing\Integrations\ThirdParty\GravityForms\SGWPMAIL_Field;
use SG_Email_Marketing\Loader\Loader;
use SG_Email_Marketing\Integrations\ThirdParty\Form_Parser;
use SG_Email_Marketing\Traits\Ip_Trait;

/**
 * Class managing all Ninja Forms integrations.
 */
class GravityForms extends Integrations {
	use Ip_Trait;
	/**
	 * The integration id.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $id = 'gravity_forms';

	/**
	 * Fetch integration's settings
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function fetch_settings() {
		$settings = get_option(
			$this->prefix . $this->id,
			array(
				'enabled'       => class_exists( '\GFForms' ) ? 1 : 2,
				'labels'        => array(),
				'checkbox_text' => 'Sign me up for the newsletter!',
				'system'        => 1,
				'name'          => $this->id,
			)
		);

		$settings['title']       = 'Gravity Forms';
		$settings['description'] = 'Add an optional checkbox to any form created with Gravity Forms, enabling users to sign up for your mailing list. Enable this feature by adding the SG Email Marketing building block to any form created with Gravity Forms.';

		return $settings;
	}

	/**
	 * Check if integration is active or inactive.
	 *
	 * @since 1.5.0
	 *
	 * @return int If integration is active or inactive.
	 */
	public function is_active() {
		// Return 1 if the class exists.
		if ( class_exists( '\GFForms' ) ) {
			return 1;
		}

		// Get the integration data.
		$settings = $this->fetch_settings();

		// Return the status of the integration.
		return intval( $settings['enabled'] );
	}

	/**
	 * Load the assets for the editor.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		wp_enqueue_style(
			'sg-email-marketing-gravity-forms-integration-styles',
			\SG_Email_Marketing\URL . '/assets/css/integrations/gravity-forms/gravity-forms.css',
			array(),
			\SG_Email_Marketing\VERSION,
			'all'
		);
	}

	/**
	 * Constructor for the class.
	 *
	 * @since 1.5.0
	 *
	 * @param SG_Email_Marketing\Services\Mailer_Api\Mailer_Api $mailer_api Mailer API instance.
	 */
	public function __construct( $mailer_api ) {
		if ( ! class_exists( '\GFForms' ) ) {
			return;
		}

		$field = new SGWPMAIL_Field();
		parent::__construct( $mailer_api );
	}

	/**
	 * Render of the settings in the field options.
	 *
	 * @since 1.5.0
	 *
	 * @param int $position Position in the field options.
	 * @param int $form_id  ID of the form.
	 * @return void
	 */
	public function sgwpmail_settings( $position, $form_id ) {
		include \SG_Email_Marketing\DIR . '/templates/GF_Integration_Field_Settings.php';

	}
	/**
	 * Editor scripts for the settings in the field options.
	 *
	 * @since 1.5.0
	 *
	 * @return void
	 */
	public function editor_script() {
		include_once \SG_Email_Marketing\DIR . '/templates/GF_Integration_Field_Scripts.php';
	}

	/**
	 * Submission handling
	 *
	 * @since 1.5.0
	 *
	 * @param array $entry Array containing the submitted entry.
	 * @param array $form  Array containing the form and its' strucutre.
	 *
	 * @return void
	 */
	public function post_submission( $entry, $form ) {
		$decoded_entry            = array();
		$sgwpmail_consent_setting = false;
		$labels                   = array();
		$has_sgwpmail_integration = false;

		foreach ( $form['fields'] as $field ) {
			if ( 'sgwpmail' === $field->type ) {
				$has_sgwpmail_integration = true;
				$labels                   = $field->sgwpmailGroups;
				$sgwpmail_consent_setting = $field->sgwpmailConsentToggle;
			}

			$inputs = $field->get_entry_inputs();

			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					$decoded_entry[ $input['label'] ] = \rgar( $entry, (string) $input['id'] );
				}
			} else {
				$decoded_entry[ $field->type ] = \rgar( $entry, (string) $field->id );
			}
		}

		if ( ! $has_sgwpmail_integration ) {
			return;
		}

		if ( $sgwpmail_consent_setting && empty( $decoded_entry['sgwpmail'] ) ) {
			return;
		}

		$data = Form_Parser::extract_data( $decoded_entry );

		// do nothing if no email was found.
		if ( empty( $data['email'] ) ) {
			return;
		}

		$data = array(
			'labels'    => $this->get_label_ids( $labels ),
			'firstName' => $data['first_name'],
			'lastName'  => $data['last_name'],
			'email'     => $data['email'],
			'timestamp' => time(),
			'ip'        => $this->get_current_user_ip(),
		);

		$this->mailer_api->send_data( array( $data ) );

	}

	/**
	 * Retrieve the labels added in SG Email Marketing tool.
	 *
	 * @since 1.5.0
	 *
	 * @return array Array of labels.
	 */
	public function get_labels() {
		$labels_list = $this->mailer_api->get_labels();
		$labels      = array();

		if ( empty( $labels_list['data'] ) ) {
			return array();
		}
		foreach ( $labels_list['data'] as $label ) {
			$labels[] = $label['name'];
		}

		return $labels;

	}

	/**
	 * Get label ids
	 *
	 * @since 1.5.0
	 *
	 * @param  array $labels List of labels selected in the back-end.
	 * @return array         List of the label ids.
	 */
	public function get_label_ids ( $labels ) {
		$label_ids   = array();
		$labels_list = $this->mailer_api->get_labels();

		if ( empty( $labels ) ) {
			return $label_ids;
		}

		foreach ( $labels_list['data'] as $label ) {
			if ( in_array( $label['name'], $labels, true ) ) {
				$label_ids[] = $label['id'];
			}
		}

		return $label_ids;
	}

	/**
	 * Set tooltips for field options.
	 *
	 * @since 1.5.0
	 *
	 * @param  array $tooltips Tooltips array.
	 * @return array           Updated tooltips array.
	 */
	public function sgwpmail_tooltips ( $tooltips ) {
		$tooltips['sgwpmail_groups']         = '<h6>' . esc_html__( 'Groups', 'siteground-email-marketing' ) . '</h6>' . esc_html__( 'Eligible users submitting this form will be added to the selected groups.', 'siteground-email-marketing' );
		$tooltips['sgwpmail_manage_consent'] = '<h6>' . esc_html__( 'Manage Consent', 'siteground-email-marketing' ) . '</h6>' . esc_html__( 'Recommended to be switched on if subscription is not the main purpose of the form.', 'siteground-email-marketing' );
		$tooltips['sgwpmail_consent_label']  = '<h6>' . esc_html__( 'Checkbox Text', 'siteground-email-marketing' ) . '</h6>' . esc_html__( 'Text of the consent checkbox.', 'siteground-email-marketing' );

		return $tooltips;
	}

	/**
	 * Set default values for consent toggle, consent text and field label.
	 *
	 * @since 1.5.0
	 */
	public function sgwpmail_set_defaults() {
		?>
		case 'sgwpmail':
			field.inputs = null;
			if (!field.label)
				field.label = <?php echo wp_json_encode( esc_html__( 'Newsletter subscription', 'siteground-email-marketing' ) ); ?>;
			if( !field.sgwpmailConsentToggle)
				field.sgwpmailConsentToggle = true;
			if( !field.sgwpmailConsentText)
				field.sgwpmailConsentText = '<?php esc_html_e( 'Subscribe to our Newsletter', 'siteground-email-marketing' ); ?>';
				jQuery( '.sg_email_marketing_field_preview_label').text(field.sgwpmailConsentText);
			break;
		<?php
	}
}
