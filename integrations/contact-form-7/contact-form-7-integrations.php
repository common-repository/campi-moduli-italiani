<?php
/**
 * Adds files required for Contact Form 7 integration
 *
 * @link https://wordpress.org/plugins/campi-moduli-italiani/
 *
 * @package    campi-moduli-italiani
 * @subpackage integrations/contact-form-7
 * @since      2.1.0
 */

if ( GCMI_USE_COMUNE === true ) {
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/comune/class-gcmi-comune-wpcf7-formtag.php';
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/comune/wpcf7-comune-formtag.php';
}

if ( GCMI_USE_CF === true ) {
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/cf/class-gcmi-codicefiscale.php';
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/cf/class-gcmi-cf-wpcf7-formtag.php';
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/cf/wpcf7-cf-formtag.php';
}

if ( GCMI_USE_STATO === true ) {
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/stato/wpcf7-stato-formtag.php';
}

if ( GCMI_USE_FORMSIGN === true ) {
	require_once plugin_dir_path( GCMI_PLUGIN ) . 'modules/formsign/wpcf7-formsign-formtag.php';
}
