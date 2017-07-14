<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// add plugin links
function woolab_icdic_plugin_row_meta( $links, $file ) {
		
	if ( WOOLAB_IC_DIC_PLUGIN_BASENAME == $file ) {
		
		$row_meta = array(
			'github'    => '<a href="https://github.com/vyskoczilova/kybernaut-ic-dic" target="_blank" aria-label="' . esc_attr__( 'View GitHub', 'woolab-ic-dic' ) . '">' . esc_html__( 'GitHub', 'woolab-ic-dic' ) . '</a>',
			'review' => '<a href="http://wordpress.org/support/view/plugin-reviews/woolab-ic-dic" target="_blank" aria-label="' . esc_attr__( 'Write a Review', 'woolab-ic-dic' ) . '">' . esc_html__( 'Write a Review', 'woolab-ic-dic' ) . '</a>',				
		);

		return array_merge( $links, $row_meta );
	}

	return (array) $links;
}

// add checkout fields
function woolab_icdic_checkout_fields( $fields ) {
	
	 $fields['billing']['billing_ic'] = array(
		'label'     => __('BI', 'woolab-ic-dic'),
		'placeholder'   => _x('BI', 'placeholder', 'woolab-ic-dic'),
		'required'  => false,
		'class'     => apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide') ),
		'clear'     => true
	 );
 
	 $fields['billing']['billing_dic'] = array(
		'label'     => __('VAT No.', 'woolab-ic-dic'),
		'placeholder'   => _x('VAT No.', 'placeholder', 'woolab-ic-dic'),
		'required'  => false,
		'class'     => apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide') ),
		'clear'     => true
	 );

	 $fields['billing']['billing_dic_dph'] = array(
		'label'     => __('VAT DPH No.', 'woolab-ic-dic'),
		'placeholder'   => _x('VAT DPH No.', 'placeholder', 'woolab-ic-dic'),
		'required'  => false,
		'class'     => apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide') ),
		'clear'     => true
	 );

	return $fields;

}

function woolab_icdic_billing_fields( $fields, $country ) {
		
		 $fields['billing_ic'] = array(
			'label'     => __('BI', 'woolab-ic-dic'),
			'placeholder'   => _x('BI', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide') ),		
			'clear'     => true
		);
	
		$fields['billing_dic'] = array(
			'label'     => __('VAT No.', 'woolab-ic-dic'),
			'placeholder'   => _x('VAT No.', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide') ),
			'clear'     => true
		);

		if ( $country == 'SK' ) {
			$fields['billing_dic_dph'] = array(
				'label'     => __('VAT DPH No.', 'woolab-ic-dic'),
				'placeholder'   => _x('VAT DPH No.', 'placeholder', 'woolab-ic-dic'),
				'required'  => false,
				'class'     => apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide') ),
				'clear'     => true
			);
		}
		
		return $fields;
	}		 
			
// check field on checkout
function woolab_icdic_checkout_field_process() {
	if ( ! empty( $_POST['_wpnonce'] ) || wp_verify_nonce( $_POST['_wpnonce'], 'woocommerce-process_checkout' ) ) {
		if ( $_POST['billing_country'] == "CZ" ) {
			if ( $_POST['billing_ic'] ) {		
				if ( ! woolab_icdic_verify_ic($_POST['billing_ic'])) {		
					wc_add_notice( __( 'Enter a valid Company number (BI)', 'woolab-ic-dic'  ), 'error' );
				}
			}
			if ( $_POST['billing_dic'] ) {						
				if ( ! ( woolab_icdic_verify_rc( substr( $_POST['billing_dic'],2 )) || woolab_icdic_verify_ic( substr( $_POST['billing_dic'],2) ) ) || substr($_POST['billing_dic'],0,2) != "CZ") {		
					wc_add_notice( __( 'Enter a valid VAT number', 'woolab-ic-dic' ), 'error' );
				}
			}
		} elseif ( $_POST['billing_country'] == "SK" ) {
			if ( $_POST['billing_ic'] ) {		
				if ( ! woolab_icdic_verify_ic_sk($_POST['billing_ic'])) {		
					wc_add_notice( __( 'Enter a valid Company number (BI)', 'woolab-ic-dic'  ), 'error' );
				}
			}
			if ( $_POST['billing_dic'] ) {					
				if ( ! woolab_icdic_verify_dic_sk( $_POST['billing_dic'] ) ) {		
					wc_add_notice( __( 'Enter a valid VAT number', 'woolab-ic-dic' ), 'error' );
				}
			}
			if ( $_POST['billing_dic_dph'] ) {						
				if ( ! woolab_icdic_verify_dic_dph_sk( $_POST['billing_dic_dph'] ) ) {		
					wc_add_notice( __( 'Enter a valid VAT DPH number', 'woolab-ic-dic' ), 'error' );
				}
			}
			if ( $_POST['billing_dic_dph'] && $_POST['billing_dic'] ) {						
				if ( $_POST['billing_dic'] != substr( $_POST['billing_dic_dph'], 2) ) {		
					wc_add_notice( __( 'VAT number and VAT DPH number doesn\'t match', 'woolab-ic-dic' ), 'error' );
				}
			}
		}
	}
}

// my address formatted
function woolab_icdic_my_address_formatted_address( $fields, $customer_id, $name ) {
	$fields += array(
		'billing_ic' => get_user_meta( $customer_id, $name . '_ic', true ),
		'billing_dic' => get_user_meta( $customer_id, $name . '_dic', true )
	);
	if ( get_user_meta( $customer_id, $name . '_country', '' ) == 'SK' ) {
		$fields += array(
			'billing_dic_dph' => get_user_meta( $customer_id, $name . '_dic_dph', true )
		);
	}

	return $fields;
}

function woolab_icdic_localisation_address_formats($address_formats) {
	$address_formats['CZ'] .= "\n{billing_ic}\n{billing_dic}";	
	$address_formats['SK'] .= "\n{billing_ic}\n{billing_dic}\n{billing_dic_dph}";	
	return $address_formats;
}

// formatting
function woolab_icdic_formatted_address_replacements( $replace, $args) {
	return $replace += array(
		'{billing_ic}' => (isset($args['billing_ic']) && $args['billing_ic'] != '' ) ?  __('BI: ', 'woolab-ic-dic') .$args['billing_ic'] : '',
		'{billing_dic}' => (isset($args['billing_dic']) && $args['billing_dic'] != '') ?  __('VAT No.: ', 'woolab-ic-dic') . $args['billing_dic'] : '',				
		'{billing_dic_dph}' => (isset($args['billing_dic_dph']) && $args['billing_dic_dph'] != '') ?  __('VAT DPH No.: ', 'woolab-ic-dic') . $args['billing_dic_dph'] : '',				
		'{billing_ic_upper}' => strtoupper((isset($args['billing_ic_upper']) && $args['billing_ic_upper'] != '') ?__('BI: ', 'woolab-ic-dic') . $args['billing_ic_upper'] : '' ),
		'{billing_dic_upper}' => strtoupper((isset($args['billing_dic_upper']) && $args['billing_dic_upper'] != '') ? __('VAT No.: ', 'woolab-ic-dic') . $args['billing_dic_upper'] : ''),
		'{billing_dic_dph_upper}' => strtoupper((isset($args['billing_dic_dph_upper']) && $args['billing_dic_dph_upper'] != '') ? __('VAT DPH No.: ', 'woolab-ic-dic') . $args['billing_dic_dph_upper'] : ''),
	);
}

function woolab_icdic_order_formatted_billing_address($address, $order) {
	
	if ( version_compare( WC_VERSION, '2.7', '<' )) { 
		return $address += array(
			'billing_ic'	=> $order->billing_ic,
			'billing_dic'	=> $order->billing_dic,
			'billing_dic_dph'	=> $order->billing_dic_dph
			);
	} else { 
		return $address += array(
			'billing_ic'	=> $order->get_meta('_billing_ic'),
			'billing_dic'	=> $order->get_meta('_billing_dic'),
			'billing_dic_dph'	=> $order->get_meta('_billing_dic_dph')
			);
	} 

}

// admin
function woolab_icdic_customer_meta_fields($fields) {
	$fields['billing']['fields'] += array(
		'billing_ic' => array(
			'label' => __('BI', 'woolab-ic-dic'),
			'description' => ''
		),	
		'billing_dic' => array(
			'label' => __('VAT No.', 'woolab-ic-dic'),
			'description' => ''
		),
		'billing_dic_dph' => array(
			'label' => __('VAT DPH No.', 'woolab-ic-dic'),
			'description' => ''
		)
		);
	return $fields;
}

// Add custom user fields to admin order screen	
function woolab_icdic_admin_billing_fields ( $fields ) {
	
	global $post;

	$order = new WC_Order($post->ID);
	$order_id = trim( str_replace( '#', '', $order->get_order_number() ) );
	$country = get_post_meta( $order_id, '_country', true );

	$fields += array(
		'billing_ic' => array(
			'label'     => __('BI', 'woolab-ic-dic'),
			'show'   => false,
			'value'=> get_post_meta( $order_id, '_billing_ic', true ),
		),
		'billing_dic' => array(
			'label'     => __('VAT No.', 'woolab-ic-dic'),
			'show'   => false,
			'value'=> get_post_meta( $order_id, '_billing_dic', true ),
		) 
	);

	if ( $country == 'SK' ) {
		$fields += array(			
			'billing_dic_dph' => array(
				'label'     => __('VAT DPH No.', 'woolab-ic-dic'),
				'show'   => false,
				'value'=> get_post_meta( $order_id, '_billing_dic_dph', true ),
			) 
		);
	}

	return $fields;
			
}

// Load customer user fields via ajax on admin order screen from a customer record
// https://www.jnorton.co.uk/woocommerce-custom-fields
function woolab_icdic_ajax_get_customer_details_old_woo ( $customer_data ){

	$user_id = $_POST['user_id'];
	$country = get_user_meta( $user_id, 'billing_country', true );

	$customer_data['billing_ic'] = get_user_meta( $user_id, 'billing_ic', true );
	$customer_data['billing_dic'] = get_user_meta( $user_id, 'billing_dic', true );
	
	if ( $country == 'SK' ) {
		$customer_data['billing_dic_dph'] = get_user_meta( $user_id, 'billing_dic_dph', true );
	}
	
	return $customer_data;

}
function woolab_icdic_ajax_get_customer_details( $data, $customer, $user_id ){

	$country = get_user_meta( $user_id, 'billing_country', true );
	$data['billing']['billing_ic'] = get_user_meta( $user_id,  'billing_ic', true );
	$data['billing']['billing_dic'] = get_user_meta( $user_id,  'billing_dic', true );
	if ( $country == 'SK' ) {
		$data['billing']['billing_dic_dph']  = get_user_meta( $user_id, 'billing_dic_dph', true );
	}

	return $data;

}

// Save meta data / custom fields when editing order in admin screen
function woolab_icdic_process_shop_order ( $post_id, $post ) {

	if ( empty( $_POST['woocommerce_meta_nonce'] ) ) {
		return;
	}

	if(!wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' )){
		return;
	}

	$update_user_meta = apply_filters( 'woolab_icdic_update_user_meta', false );

	if(isset($_POST['_billing_billing_ic'])){
		update_post_meta( $post_id, '_billing_ic', wc_clean( $_POST[ '_billing_billing_ic' ] ) );
		if ( $update_user_meta ) {
			update_user_meta( $_POST['user_ID'], 'billing_ic', sanitize_text_field( $_POST['_billing_billing_ic'] ) );
		}
	}
	if(isset($_POST['_billing_billing_dic'])){
		update_post_meta( $post_id, '_billing_dic', wc_clean( $_POST[ '_billing_billing_dic' ] ) );
		if ( $update_user_meta ) {
			update_user_meta( $_POST['user_ID'], 'billing_dic', sanitize_text_field( $_POST['_billing_billing_dic'] ) );
		}
	}
	if(isset($_POST['_billing_billing_dic_dph'])){
		update_post_meta( $post_id, '_billing_dic_dph', wc_clean( $_POST[ '_billing_billing_dic_dph' ] ) );
		if ( $update_user_meta ) {
			update_user_meta( $_POST['user_ID'], 'billing_dic_dph', sanitize_text_field( $_POST['_billing_billing_dic_dph'] ) );
		}
	}

}