jQuery(document).ready(function() {
    var myCustomFieldController = Marionette.Object.extend( {
        initialize: function() {
            this.listenTo( Backbone.Radio.channel( "fields" ), 'add:field', this.addField );
            this.listenTo( Backbone.Radio.channel( "fields" ), 'delete:field', this.removeField );
        },
        addField: function( view ) {
            if( view.attributes.type === "sgwpmail_checkbox" ) {
                var model = Backbone.Radio.channel( 'app' ).request("get:formModel");
                model.attributes.actions.models.forEach( function(action) {
                    if( action.attributes.hasOwnProperty( "sgwpmail_consent_checkbox_status" ) ) {
                        action.attributes.sgwpmail_consent_checkbox_status = 1;
                        action.trigger('change:sgwpmail_consent_checkbox_status')
                    }
                })
            }
        },

        removeField: function ( view ) {
            if( view.attributes.type === "sgwpmail_checkbox" ) {
                var model = Backbone.Radio.channel( 'app' ).request("get:formModel");
                model.attributes.actions.models.forEach( function(action) {
                    if( action.attributes.hasOwnProperty( "sgwpmail_consent_checkbox_status" ) ) {
                        action.attributes.sgwpmail_consent_checkbox_status = 0;
                        action.trigger('change:sgwpmail_consent_checkbox_status')
                    }
                })
            }
        }
    });

    var myCustomActionController = Marionette.Object.extend( {
        initialize: function() {
            this.listenTo( Backbone.Radio.channel( "drawer" ), 'before:open', this.addField );
        },
        addField: function( view ) {
            if( ! Backbone.Radio.channel( 'app' ).request('get:drawerEl').html().includes('sgwpmail-help') ){
                return;
            }
            var selected = [];
            jQuery("select#sgwpmail_groups\\[\\]").children('option[selected]').each( function( index, element ) {
                selected.push( jQuery( element ).html() );
            })

            var model = Backbone.Radio.channel( "app" ).request("get:formModel");

            model.attributes.fields.models.forEach( function( e ) {
                if( e.attributes.type === "sgwpmail_checkbox" ) {
                    model.attributes.actions.models.forEach( function(action) {
                        if( action.attributes.hasOwnProperty( "sgwpmail_consent_checkbox_status" ) ) {
                            action.attributes.sgwpmail_consent_checkbox_status = 1;
                            action.trigger("change:sgwpmail_consent_checkbox_status")
                        }
                    })
                }
            } );

            jQuery("select#sgwpmail_groups\\[\\]").selectize({
                plugins: ['restore_on_backspace', 'clear_button'],
                delimiter: ' - ',
                persist: false,
                maxItems: null,
                items: getSettingGroups(),
                onChange: function( value ) {
                    updateSettingGroups( value )
                }
            })
        },

    });

    function updateSettingGroups( value ) {
        var model = Backbone.Radio.channel( 'app' ).request("get:formModel");
        model.attributes.actions.models.forEach( function(action) {
            if( action.attributes.hasOwnProperty( "sgwpmail_groups" ) ) {
                action.attributes.sgwpmail_groups_value = JSON.stringify(value);
                action.trigger('change:sgwpmail_groups_value')

            }
        })

    }

    function getSettingGroups() {
        var groups = [];
        var model = Backbone.Radio.channel( 'app' ).request("get:formModel");
        var decoded = '';
        model.attributes.actions.models.forEach( function(action) {
            if( action.attributes.hasOwnProperty( "sgwpmail_groups_value" ) ) {
                var elem = document.createElement('textarea');
                elem.innerHTML = action.attributes.sgwpmail_groups_value;
                decoded = elem.value;
            }
        })
        return  decoded.length == 0 ? [] : JSON.parse( decoded ) ;
    }

    new myCustomFieldController();
    new myCustomActionController();

    var changed = 0;
    nfDashInlineVars.preloadedFormData.fields.forEach( function( e ) {
        if( e.type === "sgwpmail_checkbox" ) {
            nfDashInlineVars.preloadedFormData.actions.forEach( function(action) {
                if( action.type === "sgwpmail_subscribe" ) {
                    action.sgwpmail_consent_checkbox_status = 1;
                }
            })
            changed = 1;
        }
    } );

    if ( changed !== 1 ) {
        nfDashInlineVars.preloadedFormData.actions.forEach( function(action) {
            if( action.type === "sgwpmail_subscribe" ) {
                action.sgwpmail_consent_checkbox_status = 0;
            }
        })
    }
})