<?php
/**
 * WP Image Size Limit - Options
 *
 * @since Version 1.0
 */


/**
 * Register the form setting for our wpisl_options array.
 *
 * This function is attached to the admin_init action hook.
 *
 * @since Version 1.0
 */
function wpisl_options_init() {

	// If we have no options in the database, let's add them now.
	if ( false === wpisl_get_options() )
		add_option( 'wpisl_options', wpisl_get_default_options() );

	register_setting(
		'media',       // Options group
		'wpisl_options', // Database option, see wpisl_get_options()
		'wpisl_options_validate' // The sanitization callback, see wpisl_options_validate()
	);

	add_settings_section( 'image_size_limit', 'Image Size Limit', '__return_false', 'media' );

	add_settings_field( 
		'img_upload_limit', 
		'Maximum File Size for Images', 
		'wpisl_settings_field_img_upload_limit', 
		'media', 
		'image_size_limit' 
	);

	add_settings_field( 
		'img_upload_limit_exempt_roles', 
		'Exempt User Roles', 
		'wpisl_settings_field_img_upload_limit_exempt', 
		'media', 
		'image_size_limit' 
	);
}
add_action( 'admin_init', 'wpisl_options_init' );

function wpisl_settings_section() {

}


/**
 * Returns the default options.
 *
 * @since Version 1.0
 */
function wpisl_get_default_options() {
	$wpisl = new WP_Image_Size_Limit;
	$limit = $wpisl->wp_limit();
	$default_options = array(
		'img_upload_limit' => $limit,
		'img_upload_limit_exempt' => array()
	);

	return apply_filters( 'wpisl_default_options', $default_options );
}

/**
 * Returns the options array.
 *
 * @since Version 1.0
 */
function wpisl_get_options() {
	return get_option( 'wpisl_options', wpisl_get_default_options() );
}

/**
 * Renders the Maximum Upload Size setting field.
 *
 * @since Version 1.0
 *
 */

function wpisl_settings_field_img_upload_limit() {
	$options = wpisl_get_options();
	$wpisl = new WP_Image_Size_Limit;
	$limit = $wpisl->wp_limit();

		// Sanitize
		$id = 'img_upload_limit';

		if ( isset($options[$id]) && ($options[$id] < $limit) ) {
			$value = $options[$id];
		} 
		/*elseif  ( empty($options[$id])  )  {
			$value = '1000';
		} */
		else {
			$value = $limit;
		}

		$field = '<p>
			<input name="wpisl_options[' . $id . ']' . '" id="wpisl-limit" type="text" value="' . $value . '" size="4" maxlength="5" /> KB
			<br>
			<span class="description">Server maximum: '.$limit.' KB</span>
		</p>';

	echo $field;

}

/**
 * Renders the Exempt User Roles setting field.
 *
 * @since Version 1.1
 *
 */

function wpisl_settings_field_img_upload_limit_exempt() {
	$options = wpisl_get_options();
	$roles = get_editable_roles();
	$selected = array();
	if (!empty($options['img_upload_limit_exempt'])) {
		$selected = $options['img_upload_limit_exempt'];
	}

	$id = 'img_upload_limit_exempt';
	$field = '';

	if (!is_array($roles) || count($roles) < 1 ) return;

	foreach ($roles as $k => $v) {
		$name = $v['name'];
		$s = '';
		if (in_array($k, $selected)) $s = ' checked="checked"';

		$field .= '<input type="checkbox" name="wpisl_options[' . $id . '][]" value="'.$k.'"'.$s.'> '.$name.'&nbsp;&nbsp;&nbsp;';
	}

	$field .= '<br><span class="description">Users roles selected here will be exempt from the image size limit.</span>';

	echo $field;

}

/**
 * Sanitize and validate form input. Accepts an array, return a sanitized array.
 *
 * @see wpisl_options_init()
 * @since Version 1.0
 */
function wpisl_options_validate( $input ) {
	$output = $defaults = wpisl_get_default_options();
	$wpisl = new WP_Image_Size_Limit;
	$limit = $wpisl->wp_limit();

	$output['img_upload_limit'] = str_replace(',','', $input['img_upload_limit']);

	$output['img_upload_limit'] = absint( intval( $output['img_upload_limit'] ) );

	if (isset($input['img_upload_limit_exempt']))
		$output['img_upload_limit_exempt'] = $input['img_upload_limit_exempt'];

	if ( $output['img_upload_limit'] > $limit ) {
		$output['img_upload_limit'] = $limit;
	}

	return apply_filters( 'wpisl_options_validate', $output, $input, $defaults );
}

function unique_identifyer_admin_notices() {
     settings_errors( 'img_upload_limit' );
}
add_action( 'admin_notices', 'unique_identifyer_admin_notices' );