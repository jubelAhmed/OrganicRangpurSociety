
function maybeFloatLabel(field, event) {
    if (event == 'focus' || field.val() != '' ) {
        field.closest('.ginput_container').addClass('float_label')		
    } else {
        field.closest('.ginput_container').removeClass('float_label')
    }
}

function maybeFloatComplexLabel(field, event) {
    if (event == 'focus' || field.val() != '' ) {
        field.closest('span').addClass('float_label')		
    } else {
        field.closest('span').removeClass('float_label')
    }
}

/* jQuery(document).bind('gform_post_render', function(){  
    jQuery('.ginput_container_text, .ginput_container_number').each(function() {
        if ( jQuery(this).prev().hasClass('gfield_label') ) {
            console.log ( jQuery(this).prev('.gfield_label').text() )
            jQuery(this).prev('.gfield_label').prependTo ( jQuery(this) );
            jQuery(this).addClass('floatable_label');
    
            field = jQuery(this).find('input');
            maybeFloatLabel(field);
            field.on('focus blur', function(event) { maybeFloatLabel( jQuery(this), event.type ) } )
        }
    });
}); // gform_post_render
 */

jQuery(document).bind('gform_post_render', function() {
    
    jQuery('.ginput_container_text, .ginput_container_number, .ginput_container_textarea, .ginput_container_select, .ginput_container_date, .ginput_container_address, .ginput_container_email, .ginput_container_name')
    .each( function() {


        if ( jQuery(this).hasClass('ginput_complex') ) {

            // console.log(this);
            
            jQuery(this).find('span')
            .each( function( i, field_container ) {

                // console.log ( jQuery(field_container).find('label').text() );
                // console.log ( field_container );

                field = jQuery(field_container).find('input:not([readonly]), select:not([readonly])');

                field_type = field.is("select") ? "select" : field.attr('type');

                console.log(field_container)
                console.log ( field ) ;

                switch ( field_type ) {
                    case "text":
                    case "type":
                    case "number":
                    case "phone":
                    case "email":
                        
                        jQuery(field_container).addClass('floatable_label');

                        maybeFloatComplexLabel(field);
                        field.on('focus blur', function(event) { maybeFloatComplexLabel( jQuery(this), event.type ) } )

                        break;
                    case "select":

                        if ( field.find(":selected").text() == "" ) {
                            jQuery(field_container).addClass('floatable_label');

                            maybeFloatComplexLabel(field);
                            field.on('focus blur', function(event) { maybeFloatComplexLabel( jQuery(this), event.type ) } )
                                
                        }

                        break;
                
                    default:
                        break;
                }
                
                
            } );


        }


        else if ( jQuery(this).prev().hasClass('gfield_label') ) {
            // console.log ( jQuery(this).prev('.gfield_label').text() );

            // field = jQuery(this).find('input:not([readonly])');

            field = jQuery(this).find('input:not([readonly]), select:not([readonly]), textarea:not([readonly])');

            // Get nodeName if select or textarea, otherwise it's input, get its type
            field_type = ( field.is("select") || field.is("textarea") ) ? field.get(0).nodeName.toLowerCase() : field.attr('type');
            // field_type = field.is("select") ? "select" : field.attr('type');

            switch ( field_type ) {
                case "text":
                case "type":
                case "number":
                case "phone":
                case "email":
                case "textarea":
                    
                jQuery(this).prev('.gfield_label').prependTo ( jQuery(this) );
                jQuery(this).addClass('floatable_label');

                maybeFloatLabel(field);
                field.on('focus blur', function(event) { maybeFloatLabel( jQuery(this), event.type ) } )

                    break;
                case "select":

                console.log(field)
                    if ( field.find(":selected").text() == "" ) {

                        jQuery(this).prev('.gfield_label').prependTo ( jQuery(this) );
                        jQuery(this).addClass('floatable_label');

                        maybeFloatLabel(field);
                        field.on('focus blur', function(event) { maybeFloatLabel( jQuery(this), event.type ) } )
                            
                    }

                    break;
            
                default:
                    break;
            }


			// if ( field.attr('type') == "text" || field.attr('type') == "number" ) {

            //     jQuery(this).prev('.gfield_label').prependTo ( jQuery(this) );
            //     jQuery(this).addClass('floatable_label');

            //     maybeFloatLabel(field);
            //     field.on('focus blur', function(event) { maybeFloatLabel( jQuery(this), event.type ) } )

            // }
        }
    });
}); // gform_post_render