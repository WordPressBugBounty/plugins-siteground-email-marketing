<div class="sgwpmail-cf7-integration">
	<h3 class="sgwpmail-cf7-page-heading"> <?php esc_html_e( 'SiteGround Email Marketing', 'siteground-email-marketing' ); ?> </h3>
	<span class="sgwpmail-cf7-enable-checkbox">
		<h4 class="sgwpmail-cf7-page-label"> <?php esc_html_e( 'Enable SG Email Marketing', 'siteground-email-marketing' ); ?> </h4>
		<input type="checkbox" <?php echo esc_attr( $is_integration_enabled ); ?> id="sgwpmail-cf7-enable" name="sgwpmail-cf7-enable"/>
		<label for="sgwpmail-cf7-enable">
			<?php esc_html_e( 'Enable people filling this form to be added as subscribers to SiteGround Email Marketing', 'siteground-email-marketing' ); ?>
		</label>
	</span>
	<span class="sgwpmail-cf7-checkbox-toggle">
		<h4 class="sgwpmail-cf7-page-label"> <?php esc_html_e( 'Manage Consent', 'siteground-email-marketing' ); ?></h4>
		<input type="checkbox" <?php echo esc_attr( $is_checkbox_enabled ); ?> id="sgwpmail-cf7-checkbox-toggle" name="sgwpmail-cf7-checkbox-toggle"/>
		<label for="sgwpmail-cf7-checkbox-toggle">
			<?php esc_html_e( 'Display consent checkbox. (Recommended if subscription is not the main purpose of the form)', 'siteground-email-marketing' ); ?>
		</label>
	</span>
	<span class="sgwpmail-cf7-checkbox-label-input">
		<label class="sgwpmail-cf7-page-label" for="sgwpmail-cf7-checbkox-label">
			<?php esc_html_e( 'Consent checkbox text.', 'siteground-email-marketing' ); ?>
		</label>
		<input id="sgwpmail-cf7-checkbox-label" name="sgwpmail-cf7-checkbox-label" value="<?php echo esc_attr( $checkbox_label ); ?>"/>
	</span>
	<span class=sgwpmail-cf7-labels-dropdown>
		<label class="sgwpmail-cf7-page-label" for="sgwpmail-cf7-labels">
			<?php esc_html_e( 'Groups', 'siteground-email-marketing' ); ?>
		</label>
		<select multiple id="sgwpmail-cf7-labels" name="sgwpmail-cf7-labels[]">
			<?php
				foreach ( $labels_list['data'] as $label ) {
					if ( 'array' === gettype( $saved_labels ) && \in_array( $label['name'], $saved_labels ) ) {
						echo '<option selected value="' . esc_attr( $label['name'] ) . '">' . esc_html( $label['name'] ) . '</option>';
						continue;
					}
					echo '<option value="' . esc_attr( $label['name'] ) . '">' . esc_html( $label['name'] ) . '</option>';
				}
			?>
		</select>
		<span class="sgwpmail-description">
			<?php esc_html_e( 'People subscribing through this form will be added to the selected groups', 'siteground-email-marketing' ); ?>
		</span>
	</span>
</div>

