<?php
/**
 * Plugin Name:       Kybernaut IC DIC
 * Plugin URI:		  http://kybernaut.cz/pluginy/kybernaut-ic-dic
 * Description:       Přidá IČO a DIČ do formuláře s fakturační adresou ve WooCommerce a rovnou ověří, jestli jsou zadané hodnoty skutečné.
 * Version:           1.0.2
 * Author:            Karolína Vyskočilová
 * Author URI:        http://www.kybernaut.cz
 * Text Domain:       woolab-ic-dic
 * License:           GPLv3
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Domain Path:       /languages
 */



// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Check if WooCommerce active
function woolab_icdic_init() {
	
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
			$dpa_child_plugin = __( 'Woolab IČ DIČ', 'woolab-ic-dic' );
            $dpa_parent_plugin = __( 'WooCommerce', 'woolab-ic-dic' );
            		
            		echo '<div class="error"><p>'
                		. sprintf( __( '%1$s vyžaduje %2$s, aby mohl fungovat. Prosím aktivujte %2$s předtím, než aktivujete %1$s. Tento plugin byl prozatím deaktivován.', 'woolab-ic-dic' ), '<strong>' . esc_html( $dpa_child_plugin ) . '</strong>', '<strong>' . esc_html( $dpa_parent_plugin ) . '</strong>' )
                		. '</p></div>';
                
		   if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		   }
		}
		
	} else {
			
		// load additional sources
		require_once('includes/helpers.php');
		require_once('includes/filters-actions.php');
		
		add_action( 'plugins_loaded', 'woolab_icdic_load_plugin_textdomain' );
		add_filter( 'woocommerce_checkout_fields', 'woolab_icdic_checkout_fields', 10, 2);				
		add_action('woocommerce_checkout_process', 'woolab_icdic_checkout_field_process', 10, 2);	
		add_filter( 'woocommerce_my_account_my_address_formatted_address', 'woolab_icdic_my_address_formatted_address', 10, 3 );
		add_filter( 'woocommerce_localisation_address_formats', 'woolab_icdic_localisation_address_formats' );		
		add_filter( 'woocommerce_formatted_address_replacements', 'woolab_icdic_formatted_address_replacements', 10, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', 'woolab_icdic_order_formatted_billing_address', 10, 2 );
		add_filter( 'woocommerce_customer_meta_fields', 'woolab_icdic_customer_meta_fields' );
		add_filter( 'woocommerce_admin_billing_fields', 'woolab_icdic_admin_billing_fields' );		
		
	}
}
add_action( 'plugins_loaded', 'woolab_icdic_init' );    