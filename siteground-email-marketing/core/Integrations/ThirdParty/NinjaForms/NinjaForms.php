<?php
namespace SG_Email_Marketing\Integrations\ThirdParty\NinjaForms;

use SG_Email_Marketing\Integrations\Integrations;
use SG_Email_Marketing\Integrations\ThirdParty\NinjaForms\SGWPMAIL_Checkbox;

/**
 * Class managing all Ninja Forms integrations.
 */
class NinjaForms extends Integrations {

	/**
	 * The integration id.
	 *
	 * @since 1.3.0
	 *
	 * @var string
	 */
	public $id = 'ninja_forms';

	/**
	 * Fetch integration's settings
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function fetch_settings() {
		$settings = get_option(
			$this->prefix . $this->id,
			array(
				'enabled'       => class_exists( '\NF_Abstracts_ActionNewsletter' ) ? 1 : 2,
				'labels'        => array(),
				'checkbox_text' => 'Sign me up for the newsletter!',
				'system'        => 1,
				'name'          => $this->id,
			)
		);

		$settings['title']       = 'Ninja Forms';
		$settings['description'] = 'Add an optional checkbox to any form created with Ninja Forms, enabling users to sign up for your mailing list. Enable this integration by adding the action "SG Email Marketing" in Ninja Form settings.';

		return $settings;
	}

	/**
	 * Check if integration is active or inactive.
	 *
	 * @since 1.3.0
	 *
	 * @return boolean If integration is active or inactive.
	 */
	public function is_active() {
		// Bail if we do not have the ninjaforms plugin.
		if ( class_exists( '\NF_Abstracts_ActionNewsletter' ) ) {
			return true;
		}

		// Get the integration data.
		$settings = $this->fetch_settings();

		// Return the status of the integration.
		return intval( $settings['enabled'] );
	}

	/**
	 * Load the assets for the editor.
	 *
	 * @since 1.3.0
	 *
	 * @return void
	 */
	public function enqueue_editor_styles() {
		if ( empty( $_GET['page'] ) || $_GET['page'] !== 'ninja-forms' ) { //phpcs:ignore
			return;
		}
		wp_enqueue_style(
			'sg-email-marketing-ninja-forms-integration-styles',
			\SG_Email_Marketing\URL . '/assets/css/integrations/ninja-forms/ninja-forms.css',
			array(),
			\SG_Email_Marketing\VERSION,
			'all'
		);

		wp_enqueue_script(
			'sg-email-marketing-ninja-forms-design',
			\SG_Email_Marketing\URL . '/assets/js/integrations/ninja-forms/ninja-forms.js',
			array( 'jquery' ),
			\SG_Email_Marketing\VERSION,
			true
		);
	}

	/**
	 * Integrate the form.
	 *
	 * @since 1.3.0
	 *
	 * @param  array $actions Array of the actions for NinjaForms.
	 *
	 * @return array Modified array of the actions for NinjaForms.
	 */
	public function integrate_action( $actions ) {
		$actions['sgwpmail_subscribe'] = new SGWPMAIL_Action();
		return $actions;
	}

	/**
	 * Integrate the form.
	 *
	 * @since 1.3.0
	 *
	 * @param  array $fields List of all fields enabled in NinjaForms.
	 *
	 * @return array Modified list of fields enabled in NinjaForms.
	 */
	public function integrate_field( $fields ) {
		$fields['sgwpmail_checkbox'] = new SGWPMAIL_Checkbox();
		return $fields;
	}
}
