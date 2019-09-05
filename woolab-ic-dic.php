<?php
/*
 Plugin Name:       Kybernaut IC DIC
 Plugin URI:		https://kybernaut.cz/pluginy/kybernaut-ic-dic
 Description:       Adds Czech Company & VAT numbers (IČO & DIČ) to WooCommerce billing fields and verifies if data are correct. 
 Version:           1.4.0
 Author:            Karolína Vyskočilová
 Author URI:        https://kybernaut.cz
 Text Domain:       woolab-ic-dic
 License:           GPLv3
 License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 Domain Path:       /languages
 Donate link: 	    https://paypal.me/KarolinaVyskocilova/
 Requires PHP: 	    5.6
 WC requires at least: 	2.6
 WC tested up to: 		3.7.0
 Copyright: © 2009-2015 Karolína Vyskočilová.
 License: GNU General Public License v3.0
 License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WOOLAB_IC_DIC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOOLAB_IC_DIC_ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WOOLAB_IC_DIC_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOLAB_IC_DIC_VERSION', '1.4.0' );

// Check if WooCommerce active
function woolab_icdic_init() {

	// Localize
	load_plugin_textdomain( 'woolab-ic-dic', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	
	// If Parent Plugin is NOT active
	if ( current_user_can( 'activate_plugins' ) && !class_exists( 'woocommerce' ) ) {
		
		add_action( 'admin_init', 'woolab_icdic_plugin_deactivate' );
		add_action( 'admin_notices', 'woolab_icdic_plugin_admin_notice' );
		
		// Deactivate the Child Plugin
		function woolab_icdic_plugin_deactivate() {
		  deactivate_plugins( plugin_basename( __FILE__ ) );
		}
		
		// Throw an Alert to tell the Admin why it didn't activate
		function woolab_icdic_plugin_admin_notice() {
			$dpa_child_plugin = __( 'Kybernaut IČ DIČ', 'woolab-ic-dic' );
            $dpa_parent_plugin = __( 'WooCommerce', 'woolab-ic-dic' );
            		
            		echo '<div class="error"><p>'					
                		. sprintf( __( '%1$s requires %2$s to function. Please activate %2$s before you activate %1$s. This plugin has been deactivated.', 'woolab-ic-dic' ), '<strong>' . esc_html( $dpa_child_plugin ) . '</strong>', '<strong>' . esc_html( $dpa_parent_plugin ) . '</strong>' )
                		. '</p></div>';
                
		   if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		   }
		}
		
	} else {			

		// Create option for admin notice
		if( ! get_option('woolab_icdic_notice_settings')){
			add_option('woolab_icdic_notice_settings', true);
		}

		// Load additional sources
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/admin-notice.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/ares.php');		
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/helpers.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/filters-actions.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/settings.php');
		// https://github.com/dannyvankooten/vat.php
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/vat/Vies/Client.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/vat/Vies/ViesException.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/vat/Countries.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/vat/Validator.php');
		
		add_filter( 'woocommerce_billing_fields' , 'woolab_icdic_billing_fields', 10, 2 );
		add_filter( 'woocommerce_checkout_fields', 'woolab_icdic_checkout_fields', 10, 2);				
		add_action( 'woocommerce_checkout_process', 'woolab_icdic_checkout_field_process', 10, 0);	
		add_filter( 'woocommerce_my_account_my_address_formatted_address', 'woolab_icdic_my_address_formatted_address', 10, 3 );
		add_filter( 'woocommerce_localisation_address_formats', 'woolab_icdic_localisation_address_formats' );		
		add_filter( 'woocommerce_formatted_address_replacements', 'woolab_icdic_formatted_address_replacements', 10, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', 'woolab_icdic_order_formatted_billing_address', 10, 2 );
		add_filter( 'woocommerce_customer_meta_fields', 'woolab_icdic_customer_meta_fields' );
		add_filter( 'woocommerce_admin_billing_fields', 'woolab_icdic_admin_billing_fields' );		
		add_action( 'woocommerce_process_shop_order_meta', 'woolab_icdic_process_shop_order', 10, 2 );
		
		if ( version_compare( WC_VERSION, '2.7', '<' )) { 
			add_filter( 'woocommerce_found_customer_details', 'woolab_icdic_ajax_get_customer_details_old_woo', 10, 1 );
		} else { 
			add_filter( 'woocommerce_ajax_get_customer_details', 'woolab_icdic_ajax_get_customer_details', 10, 3 );
		} 
		
		add_filter( "plugin_row_meta", 'woolab_icdic_plugin_row_meta', 10, 2 );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', 'woolab_icdic_admin_scripts' );
			add_filter( 'plugin_action_links_' . WOOLAB_IC_DIC_PLUGIN_BASENAME, 'woolab_icdic_plugin_action_links' );
		} else {
			add_action( 'wp_enqueue_scripts', 'woolab_icdic_enqueue_scripts' );						
		}

		add_action('wp_ajax_nopriv_ajaxAres', 'woolab_icdic_ares_ajax');
		add_action('wp_ajax_ajaxAres', 'woolab_icdic_ares_ajax');

	}
}
add_action( 'plugins_loaded', 'woolab_icdic_init' );    

function woolab_icdic_enqueue_scripts() {
	$suffix = SCRIPT_DEBUG ? '' : '.min';
	if( is_checkout() ){
		wp_enqueue_script( 'woolab-icdic-public-js', WOOLAB_IC_DIC_URL . '/assets/js/public'.$suffix.'.js', array( 'jquery' ), WOOLAB_IC_DIC_URL );
		wp_localize_script( 'woolab-icdic-public-js', 'woolab', array(									
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'l18n_not_valid' => __('Business ID is invalid.', 'woolab-ic-dic'),
			'l18n_error' => __('Unexpected error occurred. Try it again.', 'woolab-ic-dic'),
			'l18n_ok' => __('Information loaded succesfully from ARES.', 'woolab-ic-dic'),
			'l18n_validating' => __('Validating data in ARES.', 'woolab-ic-dic'),
			'ares_check' => woolab_icdic_ares_check(), 
			'ares_fill' => woolab_icdic_ares_fill(),
		));
	}
}

function woolab_icdic_ares_check() {
	$option = woolab_icdic_get_option( 'woolab_icdic_ares_check', 'yes' );
	return apply_filters( 'woolab_icdic_ares_check', $option );	
}

function woolab_icdic_ares_fill() {
	$option = woolab_icdic_get_option( 'woolab_icdic_ares_fill', 'no' );
	return apply_filters( 'woolab_icdic_ares_fill', $option );
}

function woolab_icdic_vies_check() {
	
	if ( ! class_exists('SoapClient') ) {
        return false;
	}
	
	$option = woolab_icdic_get_option( 'woolab_icdic_vies_check', 'yes' );
	return apply_filters( 'woolab_icdic_vies_check', $option );
	
}

function woolab_icdic_get_option( $name, $default = 'yes' ) {

	$option = get_option( $name, $default );
	if ( $option == 'yes' ) {
		return true;
	} else {
		return false;
	}	

}

function woolab_icdic_admin_scripts( $hook ) {
	$suffix = SCRIPT_DEBUG ? '' : '.min';
    if ( 'post.php' === $hook  || 'post-new.php' === $hook ) {
		wp_enqueue_style( 'woolab-ic-dic-admin', WOOLAB_IC_DIC_URL . 'assets/css/admin.css', WOOLAB_IC_DIC_URL );		
		wp_enqueue_script( 'woolab-ic-dic-admin', WOOLAB_IC_DIC_URL . 'assets/js/admin-edit'.$suffix.'.js', array('jquery') );	
	} 
	if ( 'woocommerce_page_wc-settings' === $hook || current_user_can('manage_woocommerce') && get_option( 'woolab_icdic_notice_settings', true ) ) {
		wp_enqueue_script( 'woolab-ic-dic-admin', WOOLAB_IC_DIC_URL . 'assets/js/admin'.$suffix.'.js', array('jquery') );		
        wp_localize_script( 'woolab-ic-dic-admin', 'woolab', array(									
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'soap' => class_exists('SoapClient'),
		));
    }
}

function woolab_icdic_ares_ajax(){
	if ( isset($_REQUEST) ) {
		
		$value = woolab_icdic_ares( $_REQUEST['ico'] );
		if ( $value ) {
			echo json_encode( $value );
		} else {
			echo null;
		}

	}
	die();
};