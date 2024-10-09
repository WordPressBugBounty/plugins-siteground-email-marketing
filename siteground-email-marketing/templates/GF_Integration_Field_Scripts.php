<script type='text/javascript'>
    jQuery(document).on('gform_load_field_settings', function(event, field, form){
        jQuery( '#field_sgwpmail_consent_toggle' ).prop( 'checked', Boolean( rgar( field, 'sgwpmailConsentToggle' ) ) );
        jQuery( '#field_sgwpmail_consent_text').val(rgar( field, 'sgwpmailConsentText' ))
        jQuery("select#sgwpmail-gf-groups").selectize({
            plugins: ["restore_on_backspace", "clear_button"],
            delimiter: " - ",
            persist: false,
            maxItems: null,
        });
    });

    function sgwpmail_change_consent_checkbox( $element ) {
        SetFieldProperty('sgwpmailConsentToggle', $element.checked);
        if( jQuery('#field_sgwpmail_consent_toggle').is(':checked') ) {
            jQuery('.sg_email_marketing_checkbox_label').show();
            jQuery('.sgwpmail_consent_text.field_setting').show();
            jQuery('.sg-email-marketing-checkbox-enabled').show();
            jQuery('.sg-email-marketing-checkbox-disabled').hide();
        } else {
            jQuery('.sg_email_marketing_checkbox_label').hide();
            jQuery('.sgwpmail_consent_text.field_setting').hide();
            jQuery('.sg-email-marketing-checkbox-enabled').hide();
            jQuery('.sg-email-marketing-checkbox-disabled').show();
        }
    }
    function sgwpmail_change_labels( $element ) {
        SetFieldProperty('sgwpmailGroups', jQuery($element).val());
    }

    function sgwpmail_change_consent_text( $text ) {
        SetFieldProperty('sgwpmailConsentText', $text);
        jQuery( '.sg_email_marketing_field_preview_label').text($text);
    }
    jQuery(document).on("gform_load_form_settings", function(event, form){
        //do something specific to this form
        var field = []; 
        form.fields.forEach( function(e) {
            if( e.type === 'sgwpmail' ){
                field = e;
            }
        })
        if ( field.sgwpmailConsentToggle === true ) {
            jQuery('.sgwpmail_consent_text.field_setting').show();
            jQuery('.sg-email-marketing-checkbox-enabled').show();
            jQuery('.sg-email-marketing-checkbox-disabled').hide();
        } else {
            jQuery('.sgwpmail_consent_text.field_setting').hide();
            jQuery('.sg-email-marketing-checkbox-enabled').hide();
            jQuery('.sg-email-marketing-checkbox-disabled').show();
        }
        jQuery( '.sg_email_marketing_field_preview_label').text(field.sgwpmailConsentText);

    });

    gform.addAction( 'gform_post_set_field_property', function ( name, field, value, previousValue ) {
        if ( field.type !== 'sgwpmail' ) {
            return;
        }
        if ( field.sgwpmailConsentToggle === true ) {
            jQuery('.sgwpmail_consent_text.field_setting').show();
            jQuery('.sg-email-marketing-checkbox-enabled').show();
            jQuery('.sg-email-marketing-checkbox-disabled').hide();
        } else {
            jQuery('.sgwpmail_consent_text.field_setting').hide();
            jQuery('.sg-email-marketing-checkbox-enabled').hide();
            jQuery('.sg-email-marketing-checkbox-disabled').show();
        }
    });
    jQuery(document).on( 'gform_field_added', function( event, form, field ) {
        if ( field.type !== 'sgwpmail' ) {
            return;
        }
        jQuery( '.sg_email_marketing_field_preview_label').text(field.sgwpmailConsentText);

    })

</script>