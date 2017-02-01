<?php

// load locale
function  woolab_icdic_load_plugin_textdomain() {
    load_plugin_textdomain( 'woolab-ic-dic', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}

// add plugin links
function woolab_icdic_plugin_action_links( $links ) {
	$settings_link = sprintf( '<a href="%s" target="_blank">%s</a>', 'https://github.com/vyskoczilova/kybernaut-ic-dic', __( 'GitHub', 'woolab-ic-dic' ) );
	array_unshift( $links, $settings_link );

	return $links;
}

// add checkout fields
function woolab_icdic_checkout_fields( $fields ) {
	
	 $fields['billing']['billing_ic'] = array(
		'label'     => __('IČO', 'woolab-ic-dic'),
		'placeholder'   => _x('IČO', 'placeholder', 'woolab-ic-dic'),
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	 );
 
	 $fields['billing']['billing_dic'] = array(
		'label'     => __('DIČ', 'woolab-ic-dic'),
		'placeholder'   => _x('DIČ', 'placeholder', 'woolab-ic-dic'),
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
	 );

	return $fields;

}

function woolab_icdic_billing_fields( $fields, $country ) {
		
		 $fields['billing_ic'] = array(
			'label'     => __('IČO', 'woolab-ic-dic'),
			'placeholder'   => _x('IČO', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true
		);
	
		$fields['billing_dic'] = array(
			'label'     => __('DIČ', 'woolab-ic-dic'),
			'placeholder'   => _x('DIČ', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true
		);
		
		return $fields;
	}		 
			
// check field on checkout
function woolab_icdic_checkout_field_process() {
	if ( ! empty( $_POST['_wpnonce'] ) || wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-process_checkout' ) ) {
		if ( $_POST['billing_country'] == "CZ" ) {
			if ( $_POST['billing_ic'] ) {		
				if ( ! woolab_icdic_verify_ic($_POST['billing_ic'])) {		
					wc_add_notice( __( 'Zadejte platnou hodnotu IČO.', 'woolab-ic-dic'  ), 'error' );
				}
			}
			if ( $_POST['billing_dic'] ) {						
				if ( ! ( woolab_icdic_verify_rc( substr( $_POST['billing_dic'],2 )) || woolab_icdic_verify_ic( substr( $_POST['billing_dic'],2) ) ) || substr($_POST['billing_dic'],0,2) != "CZ") {		
					wc_add_notice( __( 'Zadejte platnou hodnotu DIČ.', 'woolab-ic-dic' ), 'error' );
				}
			}
		}
	}
}

// my address formatted
function woolab_icdic_my_address_formatted_address( $fields, $customer_id, $name ) {
	return $fields += array(
		'billing_ic' => get_user_meta( $customer_id, $name . '_ic', true ),
		'billing_dic' => get_user_meta( $customer_id, $name . '_dic', true )
	);
}

function woolab_icdic_localisation_address_formats($address_formats) {
	$address_formats['CZ'] .= "\n{billing_ic}\n{billing_dic}";		
	return $address_formats;
}

// formatting
function woolab_icdic_formatted_address_replacements( $replace, $args) {
	return $replace += array(
		'{billing_ic}' => (isset($args['billing_ic']) && $args['billing_ic'] != '' ) ?  __('IČO: ', 'woolab-ic-dic') .$args['billing_ic'] : '',
		'{billing_dic}' => (isset($args['billing_dic']) && $args['billing_dic'] != '') ?  __('DIČ: ', 'woolab-ic-dic') . $args['billing_dic'] : '',				
		'{billing_ic_upper}' => strtoupper((isset($args['billing_ic_upper']) && $args['billing_ic_upper'] != '') ?__('IČO: ', 'woolab-ic-dic') . $args['billing_ic_upper'] : '' ),
		'{billing_dic_upper}' => strtoupper((isset($args['billing_dic_upper']) && $args['billing_dic_upper'] != '') ? __('DIČ: ', 'woolab-ic-dic') . $args['billing_dic_upper'] : ''),
	);
}

function woolab_icdic_order_formatted_billing_address($address, $order) {
	return $address += array(
				'billing_ic'	=> $order->billing_ic,
				'billing_dic'	=> $order->billing_dic);
}

// admin
function woolab_icdic_customer_meta_fields($fields) {
	$fields['billing']['fields'] += array(
		'billing_ic' => array(
			'label' => __('IČO', 'woolab-ic-dic'),
			'description' => ''
		),	
		'billing_dic' => array(
			'label' => __('DIČ', 'woolab-ic-dic'),
			'description' => ''
		));
	return $fields;
}
		
function woolab_icdic_admin_billing_fields ($fields) {
	return $fields += array(
		'billing_ic' => array(
			'label'     => __('IČO', 'woolab-ic-dic'),
			'show'   => false
		),
		'billing_dic' => array(
			'label'     => __('DIČ', 'woolab-ic-dic'),
			'show'   => false
		) );
			
}
