<?php
namespace SG_Email_Marketing\Integrations\ThirdParty\GravityForms;

class SGWPMAIL_Field extends \GF_Field {
	/**
	 * Field type
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $type = 'sgwpmail';

	/**
	 * Checked indicator URL.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $checked_indicator_url = '';

	/**
	 * Checked indicator image markup.
	 *
	 * @since 1.5.0
	 *
	 * @var string
	 */
	public $checked_indicator_markup = '';

	/**
	 * Indicates if this field supports state validation.
	 *
	 * @since 1.5.0
	 *
	 * @var bool
	 */
	protected $_supports_state_validation = true;


	/**
	 * Returns field title
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'SiteGround Email Marketing', 'siteground-email-marketing' );
	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'Enables users filling this form to be added as subscribers to SiteGround Email Marketing', 'siteground-email-marketing' );
	}
	/**
	 * Returns the HTML tag for the field container.
	 *
	 * @since 1.5.0
	 *
	 * @param array $form The current Form object.
	 *
	 * @return string
	 */
	public function get_field_container_tag( $form ) {

		if ( \GFCommon::is_legacy_markup_enabled( $form ) ) {
			return parent::get_field_container_tag( $form );
		}

		return 'fieldset';

	}
	/**
	 * GF_Field_Consent constructor.
	 *
	 * @since 1.5.0
	 *
	 * @param array $data Data needed when initiate the class.
	 */
	public function __construct( $data = array() ) {
		parent::__construct( $data );

		/**
		 * Filters the consent checked indicator (image) URL.
		 *
		 * @since 1.5.0
		 *
		 * @param string $url Image URL.
		 */
		$this->checked_indicator_url = apply_filters( 'gform_consent_checked_indicator', \GFCommon::get_base_url() . '/images/tick.png' );

		/**
		 * Filters the consent checked indicator (image) element.
		 *
		 * @since 1.5.0
		 *
		 * @param string $tag Image tag.
		 */
		$this->checked_indicator_markup = apply_filters( 'gform_consent_checked_indicator_markup', '<img src="' . esc_url( $this->checked_indicator_url ) . '" />' );
	}
	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a gform-icon class.
	 *
	 * @since 1.5.0
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return 'gform-icon--mail';
	}

	/**
	 * Returns the field button properties for the form editor. The array contains two elements:
	 * 'group' => 'standard_fields' // or  'advanced_fields', 'post_fields', 'pricing_fields'
	 * 'text'  => 'Button text'
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return array(
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		);
	}

	/**
	 * Returns the class names of the settings which should be available on the field in the form editor.
	 *
	 * @since 1.5.0
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {
		return array(
			'sgwpmail_groups',
			'sgwpmail_consent_toggle',
			'label_setting',
			'description_setting',
			'sgwpmail_consent_text',
			'label_placement_setting',
			'description_placement_setting',
			'css_class_setting',
		);
	}

	/**
	 * Indicate if this field type can be used when configuring conditional logic rules.
	 *
	 * @since 1.5.0
	 *
	 * @return bool
	 */
	public function is_conditional_logic_supported() {
		return true;
	}

	/**
	 * Returns the field inner markup.
	 *
	 * @since 1.5.0
	 *
	 * @param array      $form  The Form Object currently being processed.
	 * @param array      $value The field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array $entry Null or the Entry Object currently being edited.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = array(), $entry = null ) {
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();
		$is_admin        = $is_form_editor || $is_entry_detail;

		$html_input_type = 'checkbox';

		$id                 = (int) $this->id;
		$tabindex           = $this->get_tabindex();
		$disabled_text      = $is_form_editor ? 'disabled="disabled"' : '';

		$target_input_id       = parent::get_first_input_id( $form );
		$for_attribute         = empty( $target_input_id ) ? '' : "for='{$target_input_id}'";
		$label_class_attribute = 'class="gform-field-label gform-field-label--type-inline gfield_consent_label"';
		$field_values          = array();

		foreach ( $form['fields'] as $field ) {
			if ( 'sgwpmail' === $field->type ) {
				$field_values = $field;
			}
		}
		$checkbox_label = ! empty( $field_values['sgwpmailConsentText'] ) ? $field_values['sgwpmailConsentText'] : '';

		if ( $is_admin ) {
			$html = '<h4 class="sg-email-marketing-checkbox-disabled"><i class="fa fa-eye-slash"></i> ' .
				__( 'Consent checkbox is NOT ADDED. We recommend adding a consent checkbox if the main purpose of the form is not subscription. To include a consent checkbox, simply click on this alert and activate the Consent Checkbox option from the settings menu.<br>Note: This message is for administrative purposes and will not be visible to users.', 'siteground-email-marketing' ) .
				'</h4>';
			$html .= sprintf( '<div class="sg-email-marketing-checkbox-enabled"><div class="ginput_container ginput_container_consent"><input style="margin-right: 10px" type="checkbox" disabled><span class="sg_email_marketing_field_preview_label">%s</span></div></div>', $checkbox_label );

		} else {
			if ( ! $field_values['sgwpmailConsentToggle'] ) {
				return '';
			}
			$html = sprintf( '<div class="ginput_container ginput_container_consent"><input id="input_1_%s" name="input_%s" type="checkbox"><label for="input_1_%s" class="gform-field-label gform-field-label--type-inline gfield_consent_label sg_email_marketing_field_preview_label">%s</span></div>', $field_values->id, $field_values->id, $field_values->id, $checkbox_label );
		}
		return $html;
	}

}
\GF_Fields::register( new SGWPMAIL_Field() );
