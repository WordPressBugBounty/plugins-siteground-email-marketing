<?php
if ( 0 === $position ) {
	$labels_list = $this->get_labels();
	$form        = \GFAPI::get_form( $form_id );

	foreach ( $form['fields'] as $field ) {
		if ( 'sgwpmail' === $field->type ) {
			$field_values = $field;
		}
	}

	$saved_labels = ! empty( $field_values['sgwpmailGroups'] ) ? $field_values['sgwpmailGroups'] : array();
	?>
	<li class="sgwpmail_groups field_setting">
		<span class=sgwpmail-groups-dropdown>
			<label class="sgwpmail-page-label" for="sgwpmail-gf-groups">
				<?php esc_html_e( 'Groups', 'siteground-email-marketing' ); ?>
				<?php \gform_tooltip( 'sgwpmail_groups' ); ?>
			</label>
			<select multiple id="sgwpmail-gf-groups" name="sgwpmail-gf-groups[]" onchange="sgwpmail_change_labels(this);">
				<?php
				foreach ( $labels_list as $label ) {
					if ( 'array' === gettype( $saved_labels ) && \in_array( $label, $saved_labels ) ) {
						echo '<option selected value="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</option>';
						continue;
					}
					echo '<option value="' . esc_attr( $label ) . '">' . esc_html( $label ) . '</option>';
				}
				?>
			</select>
		</span>
	</li>
	<li class="sgwpmail_consent_toggle field_setting">
		<label> <?php esc_html_e( 'Manage Consent', 'siteground-email-marketing' ); ?> </label>
		<input type="checkbox" id="field_sgwpmail_consent_toggle" onclick="sgwpmail_change_consent_checkbox(this);" />
		<label for="field_sgwpmail_consent_toggle" style="display:inline;">
			<?php esc_html_e( "Display consent checkbox", "siteground-email-marketing" ); ?>
			<?php \gform_tooltip( 'sgwpmail_manage_consent' ); ?>
		</label>
	</li>
	<?php
}

if ( 10 === $position ) {
	?>
	<li class="sgwpmail_consent_text field_setting">
		<label for="field_sgwpmail_consent_text">
			<?php esc_html_e( "Consent Checkbox Text", "siteground-email-marketing" ); ?>
			<?php \gform_tooltip( 'sgwpmail_consent_label' ); ?>
		</label>
		<input type="text" value="Subscribe to our Newsletter" id="field_sgwpmail_consent_text" onchange="sgwpmail_change_consent_text(jQuery(this).val());" >
	</li>
	<?php
}