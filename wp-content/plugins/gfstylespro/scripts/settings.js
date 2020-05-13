// Reset Theme Settings
function resetToDefaults (theme, label) {
  var res = confirm('Reset ' + label + ' Theme to default settings?');
    if (res == true) {
    jQuery('.thm.' + theme + ' input[type=text], .thm.' + theme + ' select').each( function () { jQuery(this).val( jQuery(this).data('default')) } );
    jQuery('.thm.' + theme + ' input[type="checkbox"]').each( function () { if (   jQuery(this).prop('checked') == true ) { jQuery(this).trigger('click') } });
    jQuery('.thm.' + theme + ' .background').each( function () { if (jQuery(this).val() == 'default' ) { jQuery(this).trigger('click') } });

    // Advanced Settings

    jQuery('.thm.' + theme + ' .adv_field_options_wrapper').find('input, select').trigger('blur').css('background-color', '')
    
    // Fix for Color Picker
    if (typeof lastColorPicker !== 'undefined') {
        jQuery(lastColorPicker).trigger('change');
    }
  }
}


/* Hide all theme and Show @param: showTheme */
function toggleTheme (showTheme) {
  jQuery('.thm').slideUp();
  jQuery('.' + showTheme).slideDown();
}


/* Show and hide background options */
function toggleBgOption(theme, bg_type) {
    jQuery('#gaddon-setting-row-' + theme + '_bg_color, #gaddon-setting-row-' + theme + '_bg_image').hide();

    if (bg_type == 'color') {
        jQuery('#gaddon-setting-row-' + theme + '_bg_color').slideDown();
    }

    if (bg_type == 'image') {
        jQuery('#gaddon-setting-row-' + theme + '_bg_image').slideDown();
    }
}


// Media uploader
var gk_media_init = function(selector, button_selector)  {
    var clicked_button = false;

    jQuery(selector).each(function (i, input) {
        var button = jQuery(input).next(button_selector);
        button.click(function (event) {
            event.preventDefault();
            var selected_img;
            clicked_button = jQuery(this);

            // check for media manager instance
            if(wp.media.frames.gk_frame) {
                wp.media.frames.gk_frame.open();
                return;
            }
            // configuration of the media manager new instance
            wp.media.frames.gk_frame = wp.media({
                title: 'Select image',
                multiple: false,
                library: {
                    type: 'image'
                },
                button: {
                    text: 'Use selected image'
                }
            });

            // Function used for the image selection and media manager closing
            var gk_media_set_image = function() {
                var selection = wp.media.frames.gk_frame.state().get('selection');

                // no selection
                if (!selection) {
                    return;
                }

                // iterate through selected elements
                selection.each(function(attachment) {
                    var url = attachment.attributes.url;
                    clicked_button.prev(selector).val(url);
                });
            };

            // closing event for media manger
            wp.media.frames.gk_frame.on('close', gk_media_set_image);
            // image selection event
            wp.media.frames.gk_frame.on('select', gk_media_set_image);
            // showing media manager
            wp.media.frames.gk_frame.open();
        });
});
};
// $(document).on('change', maybeShowFontOptions.bind(this));


function maybeShowFontOptions( e ) {

	var el = jQuery(e.target);
	var thm = el.closest('.thm').attr('id');

    var maybe_label = el.hasClass('label_font') ? "_label" : "" ;

    var font = el.val().split("/");
	var $theme_options = el.closest('tbody');
	
	if ( font.length > 1 && font[1] == "Native" ) {
	
	    if ( font[0] == "custom" ) {
			// Custom Font. Show only Custon Font option
            font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_custom"]').fadeIn();
            font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_load_cb"]').fadeOut();
        } else {
			// Native Font. Hide both options
            font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_custom"]').fadeOut();
            font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_load_cb"]').fadeOut();
			
		}
	}

	else {
        // Google Font. Show only Dont Load G Font option
        font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_custom"]').fadeOut();
        font_custom = $theme_options.find('tr[id*="'+thm+maybe_label+'_font_load_cb"]').fadeIn();
	}
}




/**
 * 
 * @param {string} settingName  CSS classname for the setting field
 * @param {string} property     Name of the CSS property to create rule with
 * @param {string} suffix       Unit for the value, or value itself in case of a checkbox
 * 
 * @returns {string} CSS delaration
 */
function getDeclaration(settingName, property, suffix='' ) {

    $settingEl = $el.find('.' + settingName );

    if ($settingEl.length == 0) {
        console.log("Setting " + settingName + " not found;");
        return '';
    }
    
    // if checkbox is selected
	if ( $settingEl.attr('type') == "checkbox" ) {

        if ( $settingEl.is(':checked') == false ) {
            return "";
        } else {
            // if checked
            return property + ":" + suffix + reinforce;
        }


    // if not a checkbox, get value from the field
    } else {
        value = $settingEl.val();
    }

    if ( value != '') {
        return property + ":" + value + suffix + reinforce;
    }

    // Default
    return '';

}



jQuery(document).ready(function() {

    jQuery(".thm").css('display', 'none');
    jQuery("." + jQuery('#theme').val() ).slideDown();

    jQuery('body').addClass('loading_completed');

    // Resets Additonal Script values in case they've been changed
    jQuery('.additional_scripts').each( function() {
        jQuery(this).val( jQuery(this).data('value') );
    });

    // Bind font options to font select boxes
    // immediately trigger after
    jQuery('.font, .label_font').on('change', maybeShowFontOptions.bind(this) )
    .trigger('change');


    /* Generate CSS from settings and submit form */
    jQuery('#gform-settings').submit(function() {

        // e.preventDefault();
        var arThm = new Array();
        window.reinforce = jQuery('#reinforce_styles').is(':checked') ? ' !important;' : ';';

        jQuery('.thm').each(function () {
            // assign values for theme options to vairables
            $el = jQuery(this);
            theme = jQuery(this).attr('id');
            themeClass = '.' + theme;

            font_get = jQuery(this).find('.font').val();

            rule_font_bold =     getDeclaration('font_bold', 'font-weight', 'bold');
            rule_font_italic =   getDeclaration('font_italic', 'font-style', 'italic');
            rule_font_underline= getDeclaration('font_underline', 'text-decoration', 'underline');
            rule_font_size =     getDeclaration('font_size', 'font-size');
            rule_font_color =    getDeclaration('font_color', 'color');
            // Placeholder values
            font_ph_color = jQuery(this).find('.font_color').data('ph-color');
            rule_font_ph_color = "color:" + font_ph_color + reinforce;

            rule_font_desc_size =       getDeclaration('adv_desc_font_size', 'font-size');
            rule_font_desc_color =      getDeclaration('adv_desc_color', 'color');
            rule_font_desc_bg_color=    getDeclaration('adv_desc_bg_color', 'background-color');
            rule_font_desc_bold =       getDeclaration('adv_desc_font_bold', 'font-weight', 'bold');
            rule_font_desc_italic =     getDeclaration('adv_desc_font_italic', 'font-style', 'italic');
            rule_font_desc_underline=   getDeclaration('adv_desc_font_underline', 'text-decoration', 'underline');


            o_custom_bg = jQuery(this).find('.o_custom_bg').val();
            field_icon_color = jQuery(this).find('.field_icon_color').val();
            rule_field_margin_bottom = getDeclaration('field_margin_bottom', 'margin-bottom');

            rule_btn_color = getDeclaration('btn_color', 'color');
            rule_btn_bg =    getDeclaration('btn_bg', 'background');

            label_font_get = jQuery(this).find('.label_font').val();

            rule_label_font_bold =      getDeclaration('label_font_bold', 'font-weight', 'bold');
            rule_label_font_italic =    getDeclaration('label_font_italic', 'font-style', 'italic');
            rule_label_font_underline = getDeclaration('label_font_underline', 'text-decoration', 'underline');
            rule_label_font_size =      getDeclaration('label_font_size', 'font-size');
            rule_label_font_color =     getDeclaration('label_font_color', 'color');

            font = font_get.split('/');
            label_font = label_font_get.split('/');

            label_font_load = jQuery(this).find('.label_font_load').is(':checked');
            font_load = jQuery(this).find('.font_load').is(':checked');

            label_font_custom = jQuery(this).find('.label_font_custom').val();
            font_custom = jQuery(this).find('.font_custom').val();

            bg_type = jQuery(this).find('.background:checked').val();
            bg_color = jQuery(this).find('.bg_color').val();
            bg_image = jQuery(this).find('.bg_image').val();

            adv_border_width = jQuery(this).find('.adv_border_width').val();

            rule_adv_bg_color =             getDeclaration('adv_bg_color', 'background-color');
            rule_adv_v_padding =            getDeclaration('adv_v_padding', 'padding-top', 'px') + getDeclaration('adv_v_padding', 'padding-bottom', 'px');
            rule_adv_border_width =         getDeclaration('adv_border_width', 'border-width', 'px');
            rule_adv_inset_margin =         getDeclaration('adv_border_width', 'margin-top', 'px'); // **The same as border width
            rule_adv_border_radius =        getDeclaration('adv_border_radius', 'border-radius', 'px');
            rule_adv_border_style =         getDeclaration('adv_border_style', 'border-style');
            rule_adv_border_color =         getDeclaration('adv_border_color', 'border-color');
            rule_adv_focus_bg_color =       getDeclaration('adv_focus_bg_color', 'backrgound-color');
            rule_adv_focus_border_color =   getDeclaration('adv_focus_border_color', 'border-color');

            rule_adv_btn_v_padding =                getDeclaration('adv_btn_v_padding', 'padding-top', 'px') + getDeclaration('adv_btn_v_padding', 'padding-bottom', 'px');
            rule_adv_btn_h_padding =                getDeclaration('adv_btn_h_padding', 'padding-left', 'px') + getDeclaration('adv_btn_h_padding', 'padding-right', 'px');
            rule_adv_btn_border_width =             getDeclaration('adv_btn_border_width', 'border-width', 'px');
            rule_adv_btn_border_radius =            getDeclaration('adv_btn_border_radius', 'border-radius', 'px');
            rule_adv_btn_border_style =             getDeclaration('adv_btn_border_style', 'border-style');
            rule_adv_btn_border_color =             getDeclaration('adv_btn_border_color', 'border-color');
            rule_adv_btn_hover_color =              getDeclaration('adv_btn_hover_color', 'color');
            rule_adv_btn_hover_border_color =       getDeclaration('adv_btn_hover_border_color', 'border-color');
            rule_adv_btn_hover_bg_color =           getDeclaration('adv_btn_hover_bg_color', 'background-color');
            
            
            // generate CSS from options to enqueue
            enq = '';

                if (font[1] != 'Native' && font_load != true) {
                    // enq += '@import url(https://fonts.googleapis.com/css?family=' + encodeURIComponent(font[0]) + '); ';
                }

                if (label_font[1] != 'Native' && label_font_load != true) {
                    // enq += '@import url(https://fonts.googleapis.com/css?family=' + encodeURIComponent(label_font[0]) + '); ';
                }

                if ( bg_type == 'color' ) {
                    enq += themeClass + '_wrapper{background-color:' + bg_color + '} ';
                }

                if ( bg_type == 'image' ) {
                    enq += themeClass + '_wrapper{background:url(' + bg_image + ')} ';
                }

                if ( bg_type == 'none' ) {
                    enq += themeClass + '_wrapper{background: none}';
                }

                if (font[1] != 'Native') {
                    // font[0] = '"' + font[0] + '"';
                    rule_font = 'font-family: "'+font[0]+'"'+ reinforce;
                }
                // If Native and also inherit, no need to add this rule
                else if (font[0] == "inherit") {
                    rule_font = '';
                }
                else if (font[0] == "custom") {
                    rule_font = 'font-family: '+font_custom + reinforce;
                }
                else {
                    rule_font = 'font-family: '+font[0] + reinforce;
                }



                if (label_font[1] != 'Native') {
                    // label_font[0] = '"' + label_font[0] + '"';
                    rule_label_font = 'font-family: "'+label_font[0]+'"'+ reinforce;
                }
                else if (label_font[0] == "inherit") {
                    // If Native and also inherit, no need to add this rule
                    rule_label_font = '';
                }
                else if (label_font[0] == "custom") {
                    rule_label_font = 'font-family: '+label_font_custom + reinforce;
                }
                else {
                    rule_label_font = 'font-family: '+label_font[0] + reinforce;
                }


enq += '.gf_stylespro'+themeClass+' input,\
.gf_stylespro'+themeClass+' select,\
.gf_stylespro'+themeClass+' textarea,\
.gf_stylespro'+themeClass+' .ginput_total,\
.gf_stylespro'+themeClass+' .ginput_product_price,\
.gf_stylespro'+themeClass+' .ginput_shipping_price,\
'+themeClass+' .gfsp_icon,\
.gf_stylespro'+themeClass+' input[type=checkbox]:not(old) + label,\
.gf_stylespro'+themeClass+' input[type=radio   ]:not(old) + label,\
.gf_stylespro'+themeClass+' .ginput_container {\
' + rule_font + rule_font_color + rule_font_size + rule_font_bold + rule_font_italic + rule_font_underline +' }\
.gf_stylespro'+themeClass+' *::-webkit-input-placeholder {' + rule_font + rule_font_ph_color + ' }\
.gf_stylespro'+themeClass+' *:-moz-placeholder {' + rule_font +  reinforce + rule_font_ph_color + ' }\
.gf_stylespro'+themeClass+' *::-moz-placeholder {' + rule_font + reinforce + rule_font_ph_color + ' }\
.gf_stylespro'+themeClass+' *:-ms-input-placeholder {' + rule_font + reinforce + rule_font_ph_color + ' }\
.gf_stylespro'+themeClass+' placeholder, .gf_stylespro'+themeClass+' .gf_placeholder {' + rule_font + rule_font_ph_color + ' }\
.gf_stylespro'+themeClass+' {\
' + rule_label_font + rule_label_font_color + rule_label_font_size + ' }\
.gf_stylespro'+themeClass+' .button,\
.gf_stylespro'+themeClass+' .gfield_label {\
' + rule_label_font + rule_label_font_color + rule_label_font_size + rule_label_font_bold + rule_label_font_italic + rule_label_font_underline +' }\
.gf_stylespro'+themeClass+' .ginput_complex label {\
' + rule_label_font + rule_label_font_color+ ' }\
'+themeClass+' .gfield_description,\
.gf_stylespro'+themeClass+' .ginput_counter {\
' + rule_label_font  + rule_label_font_color +'}'

if ( rule_field_margin_bottom ) {
    enq += '.gf_stylespro'+themeClass+' .gfield { '+ rule_field_margin_bottom +'}';
}
if (field_icon_color != '') {
    enq+= themeClass+' .gfsp_icon { color: '+ field_icon_color +  reinforce +'}';    
}
if (o_custom_bg != '') {
enq += themeClass +' .o-custom-bg input:checked + label,\
'+themeClass+' .o-custom-bg li:not(.gfsp_choice_icn):not(.gfsp_choice_img) :checked + label { background: '+ o_custom_bg + '}\
'+themeClass+' .o-custom-bg input:checked + label:after{ color: '+ o_custom_bg +'}\
'+themeClass+' .o-custom-border input:checked + label,\
'+themeClass+' .o-custom-border li:not(.gfsp_choice_icn):not(.gfsp_choice_img) :checked + label { border-color: '+ o_custom_bg +' }';
}

adv_inset_margin = '';

/* Advanced Field */

if (  rule_adv_bg_color
    + rule_adv_v_padding
    + rule_adv_border_width
    + rule_adv_border_radius
    + rule_adv_border_style
    + rule_adv_border_color != ''
) {
    enq += themeClass+' input,'+themeClass+' select,'+themeClass+' textarea,'+themeClass+' input[type="text"],'+themeClass+' input[type="tel"],'+themeClass+' input[type="email"],\
'+themeClass+' input[type="url"],'+themeClass+' input[type="password"],'+themeClass+' input[type="search"],'+themeClass+' input[type="number"],'+themeClass+' .chosen-choices {'
+ rule_adv_bg_color + rule_adv_v_padding + rule_adv_border_width + rule_adv_border_radius + rule_adv_border_style + rule_adv_border_color + '}';
}

if ( rule_adv_v_padding + rule_adv_border_width + rule_adv_border_color != '' ) {
    enq += themeClass+' .gfsp_icon {'+rule_adv_v_padding+rule_adv_border_width+rule_adv_border_color+'}';
}

if ( rule_adv_border_width != ''){
    enq += themeClass+' .gf_icn_inset .gfsp_icon{'+rule_adv_inset_margin+'}';
}

if ( rule_adv_focus_bg_color + rule_adv_focus_border_color != '' ) {
    enq += themeClass+' input:focus,'+themeClass+' select:focus,'+themeClass+' textarea:focus,'+themeClass+' input[type="text"]:focus,'+themeClass+' input[type="tel"]:focus,'+themeClass+' input[type="email"]:focus,\
'+themeClass+' input[type="url"]:focus,'+themeClass+' input[type="password"]:focus,'+themeClass+' input[type="search"]:focus,'+themeClass+' input[type="number"]:focus{'+rule_adv_focus_bg_color+rule_adv_focus_border_color+'}';
}


/* Advanced Button Options */

/* Button Options */

if (  rule_btn_color 
    + rule_btn_bg
    + rule_adv_btn_v_padding
    + rule_adv_btn_h_padding
    + rule_adv_btn_border_width
    + rule_adv_btn_border_radius
    + rule_adv_btn_border_style
    + rule_adv_btn_border_color != ''
) {
    enq += '.gf_stylespro'+themeClass+' .button {' + rule_btn_bg + rule_btn_color + rule_adv_btn_v_padding + rule_adv_btn_h_padding + rule_adv_btn_border_width + rule_adv_btn_border_radius + rule_adv_btn_border_style + rule_adv_btn_border_color + '}';
}

if ( rule_adv_btn_hover_color + rule_adv_btn_hover_border_color + rule_adv_btn_hover_bg_color != '' ) {
    enq += themeClass + ' .button:hover, '+themeClass+' input[type=button]:hover,'+themeClass+' input[type=submit]:hover {' + rule_adv_btn_hover_color + rule_adv_btn_hover_border_color + rule_adv_btn_hover_bg_color + '}';
}

if (   rule_font_desc_size
     + rule_font_desc_color
     + rule_font_desc_bg_color
     + rule_font_desc_bold
     + rule_font_desc_italic
     + rule_font_desc_underline != ''
) {
    enq += themeClass + ' .gfield_description{' + rule_font_desc_size + rule_font_desc_color + rule_font_desc_bg_color + rule_font_desc_bold + rule_font_desc_italic + rule_font_desc_underline + '}';
}

            jQuery( this ).find( '.save_css' ).val( enq );

            // Disable empty fields to exclude from saving
            jQuery( 'input, select').each( function() {
                if (jQuery(this).val() == '') {
                    jQuery(this).attr('disabled', 'disabled')
                }
            });

            arThm.push( jQuery( this ) );

        });
    });

    /* Initiate color picker */
    myColorPicker = jQuery(".color").colorPicker({
        opacity: true,

        convertCallback: function(colors, type) {
            rgb = colors.RND.rgb;
            placeholder_color = 'rgba(' + rgb['r'] + ', ' + rgb['g'] + ', ' + rgb['b'] + ', ' + (colors.alpha*0.47).toFixed(2) + ')';
        },
        renderCallback: function($elm, toggled) {
            if (typeof placeholder_color !== undefined && placeholder_color != '' && placeholder_color != null) {
                $elm.attr('data-ph-color', placeholder_color);
            }
            window.lastColorPicker = $elm;
        }
    });

    /* Calculate placeholder colors and save to the field's data-ph-color */
    jQuery(".font_color").each( function() {

        rgb = jQuery(this).colorPicker().colorPicker.color.colors.RND.rgb;
        alpha = jQuery(this).colorPicker().colorPicker.color.colors.alpha;
        placeholder_color = 'rgba(' + rgb['r'] + ', ' + rgb['g'] + ', ' + rgb['b'] + ', ' + (alpha*0.47).toFixed(2) + ')';

        jQuery(this).attr('data-ph-color', placeholder_color);
    });


    /* Apply Media Uploader to upload background image fields */
    gk_media_init('.bg_image', '.media-button');


    /* Set background options per saved or default settings */
    jQuery('.background:checked').each ( function() {
        toggleBgOption(jQuery( this ).data('theme'), jQuery( this ).val())
    });

    /* Empty Fields Styles */
    
    jQuery("input, select").each( function() {
        if (jQuery(this).val() == '')
            jQuery(this).css('opacity', '.7');
    });
    
    jQuery("input, select").on('blur', function() {
        if (jQuery(this).val() == '')
            jQuery(this).css('opacity', '.7');
    });
    
    jQuery("input, select").on('focus', function() {
            jQuery(this).css('opacity', '1');
    });

    /* If any field has value; Show Advanced Fields */
    adv_has_vals = [];
    jQuery('.adv_field_options_wrapper').each( function(i, $wrapper){
        jQuery(this).hide();
        jQuery.each(jQuery($wrapper).find('input:not([type=checkbox]), select'), function() {

            // Checkbox field store values in hidden fields
            if ( jQuery(this).attr('type') == "hidden" ) {
                if ( jQuery(this).val() == "1" ) {
                    adv_has_vals.push( $wrapper );
                    return false;
                }
            }
            
            // All other types of fields (text and select)
            else if ( jQuery(this).val() != '' ){
                adv_has_vals.push( $wrapper );
                return false;
            }
        })
    });
    
    jQuery(adv_has_vals).each(function() {
        jQuery(this).slideDown();
        jQuery(this).siblings('.button').slideUp();
    });

    // Hide Gravity Flow setting if Gravity Flow is not available
    $gflow_field_wrapper = jQuery('#gaddon-setting-row-gravity_flow_form_style');
    if ( $gflow_field_wrapper.find('select').hasClass('hide_field') ) {
        $gflow_field_wrapper.css('display','none')
    }
}); // Document ready ends
