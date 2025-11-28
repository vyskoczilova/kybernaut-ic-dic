<?php
/*
 Plugin Name:			Kybernaut IC DIC
 Plugin URI:			https://kybernaut.cz/pluginy/kybernaut-ic-dic
 Description:			Adds Czech Company & VAT numbers (IČO & DIČ) to WooCommerce billing fields and verifies if data are correct.
 Version:				1.10.2
 Author:				Karolína Vyskočilová
 Author URI:			https://kybernaut.cz
 Text Domain:			woolab-ic-dic
 License:				GPLv3
 License URI:			http://www.gnu.org/licenses/gpl-3.0.html
 Domain Path:			/languages
 Donate link:			https://paypal.me/KarolinaVyskocilova/
 Requires Plugins: 		woocommerce
 WC requires at least:	3.5.0
 WC tested up to:		10.3.5
 Copyright:				© 2016-2025 Karolína Vyskočilová.
 License:				GNU General Public License v3.0
 License URI:			http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Loads Composer dependencies.
 */
if ( file_exists( __DIR__ . '/deps/scoper-autoload.php' ) ) {
	require __DIR__ . '/deps/scoper-autoload.php';
}
if ( file_exists( __DIR__ . '/deps/autoload.php' ) ) {
	require __DIR__ . '/deps/autoload.php';
}
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

define( 'WOOLAB_IC_DIC_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WOOLAB_IC_DIC_ABSPATH', dirname( __FILE__ ) . '/' );
define( 'WOOLAB_IC_DIC_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOLAB_IC_DIC_VERSION', '1.10.1' );

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
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/logger.php');
		// Compatibility
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/compatibility/superfaktura.php');
		include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/compatibility/pdf-invoices-and-packing-slips-for-woocommerce.php');
		// include_once( WOOLAB_IC_DIC_ABSPATH . 'includes/compatibility/fluidcheckout.php'); don't apply globally.

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
		add_filter( 'default_checkout_billing_iscomp', 'woolab_icdic_toggle_iscomp_field', 10, 2 );
		add_action( 'init', 'woolab_icdic_set_vat_exempt_for_customer', 10, 1 );
		add_action( 'woocommerce_checkout_update_order_review', 'woolab_icdic_validate_vat_exempt_for_company', 10, 1 );
		add_action( 'woocommerce_checkout_update_order_meta', 'woolab_icdic_save_order_metadata' );
		add_action( 'manage_shop_order_posts_custom_column', 'woolab_icdic_show_check_failed_notice_on_orders_table', 20, 2 ); // HPOS not enabled.
		add_action( 'woocommerce_shop_order_list_table_custom_column', 'woolab_icdic_show_check_failed_notice_on_orders_table_hpos', 10, 2 ); // HPOS alternative of "manage_shop_order_posts_custom_column" above.
		add_action( 'woocommerce_admin_order_data_after_billing_address', 'woolab_icdic_show_check_failed_notice_on_order_edit' );
		add_action( 'woocommerce_email_order_details', 'woolab_icdic_show_check_failed_notice_on_admin_email', 5, 3 );

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
		wp_enqueue_script( 'woolab-icdic-public-js', WOOLAB_IC_DIC_URL . 'assets/js/public'.$suffix.'.js', array( 'jquery' ), WOOLAB_IC_DIC_VERSION );
		wp_localize_script( 'woolab-icdic-public-js', 'woolab', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'l18n_not_valid' => __('Business ID is invalid.', 'woolab-ic-dic'),
			'l18n_error' => __('Unexpected error occurred. Try it again.', 'woolab-ic-dic'),
			'l18n_ok' => __('Information loaded succesfully from ARES.', 'woolab-ic-dic'),
			'l18n_validating' => __('Validating data in ARES.', 'woolab-ic-dic'),
			'ares_check' => woolab_icdic_ares_check(),
			'ares_fill' => woolab_icdic_ares_fill(),
			'ignore_check_fail' => woolab_icdic_ignore_check_fail(),
		));
		if ( apply_filters( 'woolab_icdic_toggle', get_option('woolab_icdic_toggle_switch', 'no') ) === 'yes') {
			wp_enqueue_style( 'woolab-icdic-public-css', WOOLAB_IC_DIC_URL . 'assets/css/style.css', null, WOOLAB_IC_DIC_VERSION );
		}
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

function woolab_icdic_ignore_check_fail() {
	$option = woolab_icdic_get_option( 'woolab_icdic_ignore_check_fail', 'no' );
	return apply_filters( 'woolab_icdic_ignore_check_fail', $option );
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

/**
 * Declare WooCommerce HPOS compatibility.
 */
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
	}
} );