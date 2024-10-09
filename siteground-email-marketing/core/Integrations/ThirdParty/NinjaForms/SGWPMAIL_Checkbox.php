<?php

namespace SG_Email_Marketing\Integrations\ThirdParty\NinjaForms;

/**
 * Class SGWPMAIL_Checkbox class.
 */
class SGWPMAIL_Checkbox extends \NF_Abstracts_Input {

	/**
	 * Name of the checkbox.
	 *
	 * @var string
	 */
	protected $_name = 'sgwpmail_checkbox';

	/**
	 * Nickname for the field, used for displaying.
	 *
	 * @var string
	 */
	protected $_nicename = 'SG Email Marketing Checkbox';

	/**
	 * Section of the field.
	 *
	 * @var string
	 */
	protected $_section = 'misc';

	/**
	 * Type of the field.
	 *
	 * @var string
	 */
	protected $_type = 'checkbox';

	/**
	 * Icon for the field.
	 *
	 * @var string
	 */
	protected $_icon = 'check-square-o';

	/**
	 * Template for the field.
	 *
	 * @var string
	 */
	protected $_templates = 'checkbox';

	/**
	 * Test value for the field.
	 *
	 * @var integer
	 */
	protected $_test_value = 0;

	/**
	 * Setting excluded for the field.
	 *
	 * @var array
	 */
	protected $_settings_exclude = array( 'default', 'placeholder', 'input_limit_set', 'checkbox_values', 'required', 'description' );

	/**
	 * SGWPMAIL_Checkbox constructor.
	 *
	 * @since 1.3.0
	 */
	public function __construct() {
		parent::__construct();
		$this->_nicename                       = __( 'SG Email Marketing Checkbox', 'siteground-email-marketing' );
		$this->_settings['label_pos']['value'] = 'right';
	}

	/**
	 * Admin Form Element for the field
	 *
	 * @since 1.3.0
	 *
	 * @param integer        $id    Field ID.
	 * @param string|integer $value Field value.
	 *
	 * @return string HTML to be displayed for the checkbox.
	 */
	public function admin_form_element( $id, $value ) {
		// Add 'checked' attr if the checkbox is checked.
		return "<input type='hidden' name='fields[$id]' value='0' ><input type='checkbox' name='fields[$id]' value='1' id='' " . 1 === (int) $value ? 'checked' : '' . ">";
	}
}
