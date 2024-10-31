<?php
/*
 * Plugin Name: Real Estate Manager Importer for WP All Import
 * Description: Import existing property listings using WP ALl Import.
 * Plugin URI: https://webcodingplace.com/real-estate-manager-wordpress-plugin/
 * Version: 1.0
 * Author: WebCodingPlace
 * Author URI: https://webcodingplace.com/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rem-importer-wp-all-import
 * Domain Path: /languages
*/


include "rapid-addon.php";

$rem_addon = new RapidAddon('Real Estate Manager Settings', 'rem_addon');

if (class_exists('WCP_Real_Estate_Management')) {
	global $rem_ob;

	$all_fields = array();

	if (method_exists($rem_ob, 'single_property_fields')) {
		$all_fields = $rem_ob->single_property_fields();
	}

	foreach ($all_fields as $field) {
		if ($field['key'] != '' && $field['key'] != 'file_attachments') {
			if ($field['key'] == 'property_price') {
				$rem_addon->add_field('rem_'.$field['key'], $field['title'], 'text', null, 'Only digits, example: 435000');
			} else {
				$rem_addon->add_field('rem_'.$field['key'], $field['title'], 'text', null, $field['help']);
			}
		}
	}
	$rem_addon->import_images( 'rem_set_property_images', 'Property Gallery Images' );


	$rem_addon->set_import_function('rem_addon_import_properties');

	$rem_addon->run();
} else {
	$rem_addon->admin_notice(
		'The Real Estate Manager Importer Add-On requires WP All Import <a href="http://wordpress.org/plugins/wp-all-import" target="_blank">Free</a> and the <a href="https://wordpress.org/plugins/real-estate-manager/">Real Estate Manager</a> plugin.'
	);
}

function rem_addon_import_properties($post_id, $data, $import_options) {

	global $rem_addon;
	global $rem_ob;
	$all_fields = $rem_ob->single_property_fields();
	foreach ($all_fields as $field) {
		if ($rem_addon->can_update_meta('rem_'.$field['key'], $import_options)) {
			update_post_meta($post_id, 'rem_'.$field['key'], $data['rem_'.$field['key']]);
		}
	}

}

function rem_set_property_images( $post_id, $attachment_id, $image_filepath, $import_options ) {
	$existing_images = get_post_meta( $post_id, 'rem_property_images' );
	if ($existing_images != '' && is_array($existing_images)) {
		$existing_images[] = $attachment_id;
	} else {
		$existing_images = array($attachment_id);
	}
	update_post_meta( $post_id, 'rem_property_images', $existing_images );
}
