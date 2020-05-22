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
		'label'			=> __('Business ID', 'woolab-ic-dic'),
		'placeholder'	=> _x('Business ID', 'placeholder', 'woolab-ic-dic'),
		'required'		=> false,
		'class'			=> apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
		'clear'			=> true,
		'priority'		=> 31, // @since 1.3.3 && WC 3.5.1
	);
	$fields['billing']['billing_dic'] = array(
		'label'			=> __('Tax ID', 'woolab-ic-dic'),
		'placeholder'	=> _x('Tax ID', 'placeholder', 'woolab-ic-dic'),
		'required'		=> false,
		'class'			=> apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
		'clear'			=> true,
		'priority'		=> 31, // @since 1.3.3 && WC 3.5.1
	);
	$fields['billing']['billing_dic_dph'] = array(
		'label'			=> __('VAT reg. no.', 'woolab-ic-dic'),
		'placeholder'	=> _x('VAT reg. no.', 'placeholder', 'woolab-ic-dic'),
		'required'		=> false,
		'class'			=> apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide', 'woolab-ic-dic-no_spaces', 'woolab-ic-dic-toggle') ),
		'clear'			=> true,
		'priority'		=> 31, // @since 1.3.3 && WC 3.5.1
	);

	/**
	 * Enable/Disable fields toggle
	 * @since 1.5.0
	 */
	$woolabToggle = apply_filters( 'woolab_icdic_toggle', get_option('woolab_icdic_toggle_switch', 'no') );
	if($woolabToggle !== 'no') {
		$fields['billing']['billing_iscomp'] = array(
			'type'			=> 'checkbox',
			'label_class'	=> apply_filters( 'woolab_icdic_label_class_billing_iscomp', array('woocommerce-form__label', 'woocommerce-form__label-for-checkbox', 'checkbox') ),
			'label'			=> '<span>' . __('Buying as a company', 'woolab-ic-dic') . '</span>',
			'required'		=> false,
			'class'			=> apply_filters( 'woolab_icdic_class_billing_iscomp', array('woocommerce-form__input', 'woocommerce-form__input-checkbox', 'input-checkbox') ),
			'clear'			=> true,
			'priority'		=> 29
		);
		array_push($fields['billing']['billing_ic']['class'], 'woolab-ic-dic-toggle');
		array_push($fields['billing']['billing_dic']['class'], 'woolab-ic-dic-toggle');
		array_push($fields['billing']['billing_dic_dph']['class'], 'woolab-ic-dic-toggle');
		array_push($fields['billing']['billing_company']['class'], 'woolab-ic-dic-toggle');
	}

	/**
	 * Move Country above the toggle, makes more sense when filling in VAT / TAX ID
	 * @since 1.5.0
	 */
	$countryFirst = apply_filters( 'woolab_icdic_country_ontop', get_option('woolab_icdic_country_switch', 'no') );
	if($countryFirst !== 'no') {
		$fields['billing']['billing_country']['priority'] = 28;
	}

	/**
	 * Optionally show "required" asterisk for Company and Business ID
	 * as these are always required for companies
	 * @since 1.5.0
	 */
	$fakeRequired = apply_filters( 'woolab_icdic_fake_required', false);
	if($fakeRequired) {
		$fields['billing']['billing_company']['label'] .= ' <abbr class="required" title="'.__('required', 'woocommerce').'">*</abbr>';
		$fields['billing']['billing_ic']['label'] .= ' <abbr class="required" title="'.__('required', 'woocommerce').'">*</abbr>';
		$fields['billing']['billing_dic']['label'] = __('Tax ID (optional, enter only if you are a VAT payer)', 'woolab-ic-dic');
		$fields['billing']['billing_dic']['label_class'] = array('woolab-ic-dic-not-optional');
	}
	
	// Why there is no placeholder for Company field by default ?!
	$fields['billing']['billing_company']['placeholder'] = __( 'Company', 'woocommerce' );

	return $fields;

}

function woolab_icdic_billing_fields( $fields, $country ) {
		
	$additional_fields = array(
		'billing_ic' => array(
			'label'     => __('Business ID', 'woolab-ic-dic'),
			'placeholder'   => _x('Business ID', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),		
			'clear'     => true,
			'priority'  => 31, // @since 1.3.3 && WC 3.5.1
		),
		'billing_dic' => array(
			'label'     => __('Tax ID', 'woolab-ic-dic'),
			'placeholder'   => _x('Tax ID', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
			'clear'     => true,
			'priority'  => 31, // @since 1.3.3 && WC 3.5.1
		),
	);
	
	if ( $country == 'SK' ) {
		$additional_fields['billing_dic_dph'] = array(
			'label'     => __('VAT reg. no.', 'woolab-ic-dic'),
			'placeholder'   => _x('VAT reg. no.', 'placeholder', 'woolab-ic-dic'),
			'required'  => false,
			'class'     => apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
			'clear'     => true,
			'priority'  => 31, // @since 1.3.3 && WC 3.5.1
		);
	}
	
	/**
	 * NOTE: since WC 3.5.1 which starts to use 'priority' parameter for fields, there would be enough just to assign right priority and append new fields to $fields array
	 * TODO: later drop support
	 */
	return woolab_icdic_add_after_company( $fields, $additional_fields, 'billing' );
} 
			
// check field on checkout
function woolab_icdic_checkout_field_process() {

	$country = $_POST['billing_country'];

	// BUSINESS ID
	if ( isset( $_POST['billing_ic'] ) && $_POST['billing_ic'] ) {

		/**
		 * Remove white spaces
		 * @since 1.4.0
		 */
		$ico = preg_replace('/\s+/', '', $_POST['billing_ic']); 

		// CZ
		if ( $country == "CZ" ) {												
			
			// ARES Check Enabled
			if ( woolab_icdic_ares_check() ) {					
			
				$ares = woolab_icdic_ares( $ico  );			
				if ( $ares ) {
					if ( $ares['error'] ) {
						wc_add_notice( __( 'Enter a valid Business ID', 'woolab-ic-dic'  ) . ' ' . $ares['error'], 'error' );
					} elseif ( woolab_icdic_ares_fill() ) {
						if ( isset( $_POST['billing_dic'] ) && $_POST['billing_dic'] != $ares['dic'] ) {
							$missing_fields[] = __( 'Business ID', 'woocommerce' );
						}
						if ( $_POST['billing_company'] != $ares['spolecnost'] ) {
							$missing_fields[] = __( 'Company', 'woocommerce' );
						}
						if ( $_POST['billing_postcode'] != $ares['psc'] ) {
							$missing_fields[] = __( 'Postcode / ZIP', 'woocommerce' );
						}
						if ( $_POST['billing_city'] != $ares['mesto'] ) {
							$missing_fields[] = __( 'Town / City', 'woocommerce' );
						}
						if ( $_POST['billing_address_1'] != $ares['adresa'] ) {
							$missing_fields[] = __( 'Address', 'woocommerce' );
						}
						if ( isset( $missing_fields ) ) {								
							wc_add_notice( sprintf( _n( '%s is not corresponding to ARES.', '%s are not corresponding to ARES.', count( $missing_fields ), 'woolab-ic-dic' ), wc_format_list_of_items( $missing_fields ) ), 'error' );
						}
					}
				} else {
					wc_add_notice( __( 'Unexpected error occurred. Try it again.', 'woolab-ic-dic'  ), 'error' );
				}					
			
			// ARES Check Disabled
			} elseif ( ! woolab_icdic_verify_ic( $ico )) {	
					wc_add_notice( __( 'Enter a valid Business ID', 'woolab-ic-dic'  ), 'error' );
			}

		// SK
		} elseif ( $country == "SK" ) {
			if ( $ico ) {		
				if ( ! woolab_icdic_verify_ic( $ico )) {		
					wc_add_notice( __( 'Enter a valid Business ID', 'woolab-ic-dic'  ), 'error' );
				}
			}
		}

	}

	// VAT / DIC
	if ( isset( $_POST['billing_dic'] ) && $_POST['billing_dic'] ) {

		/**
		 * Remove white spaces
		 * @since 1.4.0
		 */
		
		$dic = preg_replace('/\s+/', '', $_POST['billing_dic']); 
		$countries = new DvK\Vat\Countries();


		// Check if in EU
		if ( $countries->inEurope( $country ) ) {

			// If Validate in VIES
			// Slovak DIC cannot (and shouldn't) be validated in VIES
			if ( woolab_icdic_vies_check() && $country != 'SK' ) {
					
				$validator = new DvK\Vat\Validator();

				if ( ! $validator->validate( $dic )) {
					wc_add_notice( __( 'Enter a valid VAT number', 'woolab-ic-dic' ), 'error' );
				}

			// Validate CZ and SK mathematicaly
			} else {
				if ( $country == "CZ" ) {						
					if ( ! ( woolab_icdic_verify_rc( substr( $dic, 2 )) || woolab_icdic_verify_ic( substr( $dic, 2 ) ) ) || substr( $dic, 0, 2) != "CZ") {		
						wc_add_notice( __( 'Enter a valid VAT number', 'woolab-ic-dic' ), 'error' );
					}
				} elseif ( $country == "SK" ) {

					if ( ! woolab_icdic_verify_dic_sk( $dic ) ) {		
						wc_add_notice( __( 'Enter a valid Tax ID', 'woolab-ic-dic' ), 'error' );
					}
				}
			}

		}

	}
	// DIC is mandatory in Slovakia, this is not a VAT number
	else {
		// if IC is set, DIC must be set as well in Slovakia
		if( !empty( $_POST['billing_ic'] ) && empty( $_POST['billing_dic'] ) && $country == 'SK' ) {
			wc_add_notice( __( 'Enter a valid Tax ID', 'woolab-ic-dic' ), 'error' );
		}
	}

	// IC DPH / DIC DPH
	if ( isset( $_POST['billing_dic_dph'] ) && $_POST['billing_dic_dph'] && $country == 'SK' ) {

		/**
		 * Remove white spaces
		 * @since 1.4.0
		 */
		$dic_dph = preg_replace('/\s+/', '', $_POST['billing_dic_dph']); 

		// Verify IC DPH
		// If Validate in VIES
		if ( woolab_icdic_vies_check() && class_exists('SoapClient') ) {
					
			$validator = new DvK\Vat\Validator();
			
			if ( ! $validator->validate( $dic_dph )) {
				wc_add_notice( _x( 'Enter a valid VAT number', 'IC DPH', 'woolab-ic-dic' ), 'error' );
			}

		} else {

			if ( ! woolab_icdic_verify_dic_dph_sk( $dic_dph ) ) {		
				wc_add_notice( _x( 'Enter a valid VAT number', 'IC DPH', 'woolab-ic-dic' ), 'error' );
			}

		}
		// IC DPH has to match to Tax ID number without SK
		if ( $dic_dph && $dic ) {		

			if ( $dic != substr( $dic_dph, 2) ) {		
				wc_add_notice( __( 'Tax ID or VAT number is not valid.', 'woolab-ic-dic' ), 'error' );
			}

		}
	}

}

// My address formatted
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

// Formatting
function woolab_icdic_formatted_address_replacements( $replace, $args) {
	return $replace += array(
		'{billing_ic}' => (isset($args['billing_ic']) && $args['billing_ic'] != '' ) ?  __('Business ID: ', 'woolab-ic-dic') .$args['billing_ic'] : '',
		'{billing_dic}' => (isset($args['billing_dic']) && $args['billing_dic'] != '') ?  __('Tax ID: ', 'woolab-ic-dic') . $args['billing_dic'] : '',				
		'{billing_dic_dph}' => (isset($args['billing_dic_dph']) && $args['billing_dic_dph'] != '') ?  __('VAT reg. no.: ', 'woolab-ic-dic') . $args['billing_dic_dph'] : '',				
		'{billing_ic_upper}' => strtoupper((isset($args['billing_ic_upper']) && $args['billing_ic_upper'] != '') ?__('Business ID: ', 'woolab-ic-dic') . $args['billing_ic_upper'] : '' ),
		'{billing_dic_upper}' => strtoupper((isset($args['billing_dic_upper']) && $args['billing_dic_upper'] != '') ? __('Tax ID: ', 'woolab-ic-dic') . $args['billing_dic_upper'] : ''),
		'{billing_dic_dph_upper}' => strtoupper((isset($args['billing_dic_dph_upper']) && $args['billing_dic_dph_upper'] != '') ? __('VAT reg. no.: ', 'woolab-ic-dic') . $args['billing_dic_dph_upper'] : ''),
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
			'label' => __('Business ID', 'woolab-ic-dic'),
			'description' => ''
		),	
		'billing_dic' => array(
			'label' => __('Tax ID', 'woolab-ic-dic'),
			'description' => ''
		),
		'billing_dic_dph' => array(
			'label' => __('VAT reg. no.', 'woolab-ic-dic'),
			'description' => ''
		)
	);
	return $fields;
}

// Add custom user fields to admin order screen	
function woolab_icdic_admin_billing_fields ( $fields ) {
	
	global $post;

	$order_id = $post->ID;
	$country = get_post_meta( $order_id, '_billing_country', '' );

	$fields += array(
		'billing_ic' => array(
			'label'     => __('Business ID', 'woolab-ic-dic'),
			'show'   => false,
			'value'=> get_post_meta( $order_id, '_billing_ic', true ),
		),
		'billing_dic' => array(
			'label'     => __('Tax ID', 'woolab-ic-dic'),
			'show'   => false,
			'value'=> get_post_meta( $order_id, '_billing_dic', true ),
		) 
	);

	if ( $country && $country[0] == 'SK' || ! $country ) {
		$fields += array(			
			'billing_dic_dph' => array(
				'label'     => __('VAT reg. no.', 'woolab-ic-dic'),
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

// Add settings link
function woolab_icdic_plugin_action_links( $links ) {
	$action_links = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '" aria-label="' . esc_attr__( 'View Kybernaut IČO DIČ settings', 'woolab-ic-dic' ) . '">' . esc_html__( 'Settings', 'woolab-ic-dic' ) . '</a>',
	);

	return array_merge( $action_links, $links );
}
