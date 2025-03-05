<?php

// If this file is called directly, abort.

use KybernautIcDicDeps\Ibericode\Vat\Countries;
use KybernautIcDicDeps\Ibericode\Vat\Validator;
use KybernautIcDicDeps\Ibericode\Vat\Vies\ViesException;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// add plugin links
function woolab_icdic_plugin_row_meta( $links, $file ) {

	if ( WOOLAB_IC_DIC_PLUGIN_BASENAME == $file ) {

		$row_meta = array(
			'github' => '<a href="https://github.com/vyskoczilova/kybernaut-ic-dic" target="_blank" aria-label="' . esc_attr__( 'View GitHub', 'woolab-ic-dic' ) . '">' . esc_html__( 'GitHub', 'woolab-ic-dic' ) . '</a>',
			'review' => '<a href="http://wordpress.org/support/view/plugin-reviews/woolab-ic-dic" target="_blank" aria-label="' . esc_attr__( 'Write a Review', 'woolab-ic-dic' ) . '">' . esc_html__( 'Write a Review', 'woolab-ic-dic' ) . '</a>',
		);

		return array_merge( $links, $row_meta );
	}

	return (array) $links;
}

// add checkout fields
function woolab_icdic_checkout_fields( $fields ) {

	$fields['billing']['billing_ic'] = array(
		'label'       => __('Business ID', 'woolab-ic-dic'),
		'placeholder' => _x('Business ID', 'placeholder', 'woolab-ic-dic'),
		'required'    => false,
		'class'       => apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
		'clear'       => true,
		'priority'    => 31, // @since 1.3.3 && WC 3.5.1
	);
	$fields['billing']['billing_dic'] = array(
		'label'       => __('Tax ID', 'woolab-ic-dic'),
		'placeholder' => _x('Tax ID', 'placeholder', 'woolab-ic-dic'),
		'required'    => false,
		'class'       => apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
		'clear'       => true,
		'priority'    => 31, // @since 1.3.3 && WC 3.5.1
	);
	$fields['billing']['billing_dic_dph'] = array(
		'label'       => __('VAT reg. no.', 'woolab-ic-dic'),
		'placeholder' => _x('VAT reg. no.', 'placeholder', 'woolab-ic-dic'),
		'required'    => false,
		'class'       => apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
		'clear'       => true,
		'priority'    => 31, // @since 1.3.3 && WC 3.5.1
	);

	/**
	 * Enable/Disable fields toggle
	 * @since 1.5.0
	 */
	$woolabToggle = apply_filters( 'woolab_icdic_toggle', get_option('woolab_icdic_toggle_switch', 'no') );
	if($woolabToggle !== 'no') {
		$fields['billing']['billing_iscomp'] = array(
			'type'        => 'checkbox',
			'label_class' => apply_filters( 'woolab_icdic_label_class_billing_iscomp', array('woocommerce-form__label', 'woocommerce-form__label-for-checkbox', 'checkbox') ),
			'label'       => '<span>' . __('Buying as a company', 'woolab-ic-dic') . '</span>',
			'required'    => false,
			'input_class' => apply_filters( 'woolab_icdic_class_billing_iscomp', array('woocommerce-form__input', 'woocommerce-form__input-checkbox', 'form-row-wide') ),
			'clear'       => true,
			'priority'    => 29
		);
		array_push($fields['billing']['billing_ic']['class'], 'woolab-ic-dic-toggle');
		array_push($fields['billing']['billing_dic']['class'], 'woolab-ic-dic-toggle');
		array_push($fields['billing']['billing_dic_dph']['class'], 'woolab-ic-dic-toggle');

		if ( is_array($fields['billing']['billing_company']['class'])) {
			array_push($fields['billing']['billing_company']['class'], 'woolab-ic-dic-toggle');
		} else {
			$fields['billing']['billing_company']['class'] = ['woolab-ic-dic-toggle'];
		}
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
		$fields['billing']['billing_company']['label']   .= ' <abbr class="required" title="'.__('required', 'woocommerce').'">*</abbr>';
		$fields['billing']['billing_ic']['label']        .= ' <abbr class="required" title="'.__('required', 'woocommerce').'">*</abbr>';
		$fields['billing']['billing_dic']['label']        = __('Tax ID (optional, enter only if you are a VAT payer)', 'woolab-ic-dic');
		$fields['billing']['billing_dic']['label_class']  = array('woolab-ic-dic-not-optional');
	}

	// Why there is no placeholder for Company field by default ?!
	$fields['billing']['billing_company']['placeholder'] = __( 'Company', 'woocommerce' );

	return $fields;

}

function woolab_icdic_billing_fields( $fields, $country ) {

	$additional_fields = array(
		'billing_ic' => array(
			'label'       => __('Business ID', 'woolab-ic-dic'),
			'placeholder' => _x('Business ID', 'placeholder', 'woolab-ic-dic'),
			'required'    => false,
			'class'       => apply_filters( 'woolab_icdic_class_billing_ic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
			'clear'       => true,
			'priority'    => 31, // @since 1.3.3 && WC 3.5.1
		),
		'billing_dic' => array(
			'label'       => __('Tax ID', 'woolab-ic-dic'),
			'placeholder' => _x('Tax ID', 'placeholder', 'woolab-ic-dic'),
			'required'    => false,
			'class'       => apply_filters( 'woolab_icdic_class_billing_dic', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
			'clear'       => true,
			'priority'    => 31, // @since 1.3.3 && WC 3.5.1
		),
	);

	if ( $country == 'SK' ) {
		$additional_fields['billing_dic_dph'] = array(
			'label'       => __('VAT reg. no.', 'woolab-ic-dic'),
			'placeholder' => _x('VAT reg. no.', 'placeholder', 'woolab-ic-dic'),
			'required'    => false,
			'class'       => apply_filters( 'woolab_icdic_class_billing_dic_dph', array('form-row-wide', 'woolab-ic-dic-no_spaces') ),
			'clear'       => true,
			'priority'    => 31, // @since 1.3.3 && WC 3.5.1
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

	// Bail if form not fully filled.
	if (!isset($_POST['billing_country'])) {
		return false;
	}

	$country               = $_POST['billing_country'];
	$ignore_vat_check_fail = woolab_icdic_ignore_check_fail();

	// Flag to check if VAT check fail was ignored.
	// The information will be saved in the order meta in woocommerce_new_order hook.
	$vat_check_fail_ignored = false;

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
						$is_internal_error = ( ! empty( $ares['internal_error'] ) );

						if ( $is_internal_error && $ignore_vat_check_fail ) {
							$vat_check_fail_ignored = true;
						} else {
							wc_add_notice( __( 'Enter a valid Business ID', 'woolab-ic-dic' ) . ' ' . $ares['error'], 'error' );
						}
					} elseif ( woolab_icdic_ares_fill() ) {
						if ( isset( $_POST['billing_dic'] ) && wc_clean( wp_unslash($_POST['billing_dic'])) != $ares['dic'] ) {
							$missing_fields[] = __( 'Business ID', 'woocommerce' );
						}
						if ( wc_clean( wp_unslash($_POST['billing_company'])) != $ares['spolecnost'] ) {
							$missing_fields[] = __( 'Company', 'woocommerce' );
						}
						if ( wc_clean( wp_unslash($_POST['billing_postcode'])) != $ares['psc'] ) {
							$missing_fields[] = __( 'Postcode / ZIP', 'woocommerce' );
						}
						if ( wc_clean( wp_unslash($_POST['billing_city'])) != $ares['mesto'] ) {
							$missing_fields[] = __( 'Town / City', 'woocommerce' );
						}
						if ( wc_clean( wp_unslash($_POST['billing_address_1'])) != $ares['adresa'] ) {
							$missing_fields[] = __( 'Address', 'woocommerce' );
						}
						if ( isset( $missing_fields ) ) {
							wc_add_notice( sprintf( _n( '%s is not corresponding to ARES.', '%s are not corresponding to ARES.', count( $missing_fields ), 'woolab-ic-dic' ), wc_format_list_of_items( $missing_fields ) ), 'error' );
						}
					}
				} else {
					if ( $ignore_vat_check_fail ) {
						$vat_check_fail_ignored = true;
					} else {
						wc_add_notice( __( 'Unexpected error occurred. Try it again.', 'woolab-ic-dic' ), 'error' );
					}
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
		$countries = new Countries();


		// Check if in EU
		if ( $countries->isCountryCodeInEU( $country ) ) {

			// If Validate in VIES
			// Slovak DIC cannot (and shouldn't) be validated in VIES
			if ( woolab_icdic_vies_check() && $country != 'SK' ) {

				// Match VAT country prefix and country code.
				// @since 1.7.3.
				if ( apply_filters( 'woolab_icdic_check_billing_country_and_dic', true ) && woolab_icdic_get_vat_number_country_code($dic) !== $country ) {
					wc_add_notice( __( 'The billing country does not correspond to the country of the VAT number.', 'woolab-ic-dic' ), 'error' );
				}


				// Match VAT country prefix and shipping country code.
				// @since 1.9.2.
				if ( apply_filters( 'woolab_icdic_check_billing_country_and_dic', true ) && woolab_icdic_get_vat_number_country_code($dic) !== $_POST['shipping_country'] ) {
					wc_add_notice( __( 'The shipping country does not correspond to the country of the VAT number.', 'woolab-ic-dic' ), 'error' );
				}

				$validator = new Validator();

				if ( ! $validator->validateVatNumberFormat( $dic )) {
					wc_add_notice( __( 'VAT number has not correct format', 'woolab-ic-dic' ), 'error' );
				}

				try {
					$vat_number_valid = $validator->validateVatNumber( $dic );

					if ( ! $vat_number_valid ) {
						wc_add_notice( __( 'Enter a valid VAT number', 'woolab-ic-dic' ), 'error' );
					}
				} catch ( ViesException $exception ) {
					if ( $ignore_vat_check_fail ) {
						$vat_check_fail_ignored = true;
					} else {
						wc_add_notice( __( 'Could not validate VAT number.', 'woolab-ic-dic' ), 'error' );
					}
				}

			// Validate CZ and SK mathematicaly
			} else {
				if ( $country == "CZ" ) {
					if ( ! ( woolab_icdic_verify_rc( substr( $dic, 2 )) || woolab_icdic_verify_dic( substr( $dic, 2 ) ) ) || substr( $dic, 0, 2) != "CZ") {
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
		$required_ic_and_dic = apply_filters( 'woolab_icdic_sk_required_ic_and_dic', true );
		if( $required_ic_and_dic && !empty( $_POST['billing_ic'] ) && empty( $_POST['billing_dic'] ) && $country == 'SK' ) {
			wc_add_notice( __( 'Enter a valid Tax ID', 'woolab-ic-dic' ), 'error' );
		}
	}

	// IC DPH / DIC DPH
	if ( isset( $_POST['billing_dic_dph'] ) && $_POST['billing_dic_dph'] && $country == 'SK' ) {

		/**
		 * Remove white spaces
		 * @since 1.4.0
		 */
		$dic     = preg_replace('/\s+/', '', $_POST['billing_dic']);
		$dic_dph = preg_replace('/\s+/', '', $_POST['billing_dic_dph']);

		// Match VAT country prefix and country code.
		// @since 1.7.4.
		if ( apply_filters( 'woolab_icdic_check_billing_country_and_dic', true ) && woolab_icdic_get_vat_number_country_code($dic_dph) !== $country ) {
			wc_add_notice( __( 'The billing country does not correspond to the country of the VAT number.', 'woolab-ic-dic' ), 'error' );
		}

		// Verify IC DPH
		// If Validate in VIES
		if ( woolab_icdic_vies_check() ) {

			$validator = new Validator();

			try {
				$vat_number_valid = $validator->validateVatNumber( $dic_dph );

				if ( ! $vat_number_valid ) {
					wc_add_notice( _x( 'Enter a valid VAT number', 'IC DPH', 'woolab-ic-dic' ), 'error' );
				}
			} catch ( ViesException $exception ) {
				if ( $ignore_vat_check_fail ) {
					$vat_check_fail_ignored = true;
				} else {
					wc_add_notice( __( 'Could not validate VAT number.', 'woolab-ic-dic' ), 'error' );
				}
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

	// Set flag about Business ID or VAT number check fails.
	WC()->session->set( 'woolab_icdic_vat_check_fail_ignored', $vat_check_fail_ignored );

}

// My address formatted
function woolab_icdic_my_address_formatted_address( $fields, $customer_id, $name ) {
	$fields += array(
		'billing_ic'  => get_user_meta( $customer_id, $name . '_ic', true ),
		'billing_dic' => get_user_meta( $customer_id, $name . '_dic', true )
	);
	if ( get_user_meta( $customer_id, $name . '_country', '' ) == 'SK' ) {
		$fields += array(
			'billing_dic_dph' => get_user_meta( $customer_id, $name . '_dic_dph', true )
		);
	}

	return $fields;
}

/**
 * Add ICO, DIC fields to customers address for all EU countries including Monaco.
 * @param array $address_formats Address formats array.
 * @return array 
 * 
 * @updated 1.7.3
 */
function woolab_icdic_localisation_address_formats($address_formats) {

	$euvat = WC()->countries->get_european_union_countries( 'eu_vat' ); // Eu VAT Countries including Monaco.

	foreach ( $euvat as $country_code ) {
		if (isset($address_formats[$country_code])) {
			$address_formats[$country_code] .= "\n{billing_ic}\n{billing_dic}";
			if ( $country_code == 'SK' ) {
				$address_formats[$country_code] .= "\n{billing_dic_dph}";
			}
		}
	}
	return $address_formats;
}

// Formatting
function woolab_icdic_formatted_address_replacements( $replace, $args) {
	return $replace += array(
		'{billing_ic}'            => (isset($args['billing_ic']) && $args['billing_ic'] != '' ) ?  __('Business ID: ', 'woolab-ic-dic') .$args['billing_ic'] : '',
		'{billing_dic}'           => (isset($args['billing_dic']) && $args['billing_dic'] != '') ?  __('Tax ID: ', 'woolab-ic-dic') . $args['billing_dic'] : '',
		'{billing_dic_dph}'       => (isset($args['billing_dic_dph']) && $args['billing_dic_dph'] != '') ?  __('VAT reg. no.: ', 'woolab-ic-dic') . $args['billing_dic_dph'] : '',
		'{billing_ic_upper}'      => strtoupper((isset($args['billing_ic_upper']) && $args['billing_ic_upper'] != '') ?__('Business ID: ', 'woolab-ic-dic') . $args['billing_ic_upper'] : '' ),
		'{billing_dic_upper}'     => strtoupper((isset($args['billing_dic_upper']) && $args['billing_dic_upper'] != '') ? __('Tax ID: ', 'woolab-ic-dic') . $args['billing_dic_upper'] : ''),
		'{billing_dic_dph_upper}' => strtoupper((isset($args['billing_dic_dph_upper']) && $args['billing_dic_dph_upper'] != '') ? __('VAT reg. no.: ', 'woolab-ic-dic') . $args['billing_dic_dph_upper'] : ''),
	);
}

function woolab_icdic_order_formatted_billing_address($address, $order) {

	if ( version_compare( WC_VERSION, '2.7', '<' )) {
		return $address += array(
			'billing_ic'      => $order->billing_ic,
			'billing_dic'     => $order->billing_dic,
			'billing_dic_dph' => $order->billing_dic_dph
		);
	} else {
		return $address += array(
			'billing_ic'      => $order->get_meta('_billing_ic'),
			'billing_dic'     => $order->get_meta('_billing_dic'),
			'billing_dic_dph' => $order->get_meta('_billing_dic_dph')
		);
	}

}

function woolab_icdic_toggle_iscomp_field($value, $input) {
	$customer = WC()->customer;

	if ( empty($customer) ) {
		return $value;
	}

	if ( !empty($customer->get_meta('billing_company'))
		|| !empty($customer->get_meta('billing_ic'))
		|| !empty($customer->get_meta('billing_dic'))
		|| !empty($customer->get_meta('billing_dic_dph'))
		) {
		return true;
	}

	return $value;
}

function woolab_icdic_set_vat_exempt_for_customer() {
	if ( wp_doing_ajax() ) {
		return;
	}

	$customer      = WC()->customer;
	$enabled       = apply_filters( 'woolab_icdic_vat_exempt_enabled', ( get_option('woolab_icdic_vat_exempt_switch', 'no') !== 'no' && wc_tax_enabled() ) );

	if (empty($customer) || !$enabled) {
		return;
	}

	$vat_num               = null;
	$wc_countries          = new WC_Countries();
	$base_country          = $wc_countries->get_base_country();
	$base_country          = apply_filters( 'woolab_icdic_base_country', $base_country );
	$ignore_vat_check_fail = woolab_icdic_ignore_check_fail();
	$is_vat_exempt         = false;

	if (!empty($customer->get_meta('billing_country')) && $customer->get_meta('billing_country') !== $base_country) {
		$vat_num = $customer->get_meta('billing_country') == 'SK' ? $customer->get_meta('billing_dic_dph') : $customer->get_meta('billing_dic');
	}

	if (!empty($vat_num)) {
		$validator = new Validator();
		if ( $validator->validateVatNumberFormat( $vat_num ) ) {
			try {
				$is_vat_exempt = $validator->validateVatNumber( $vat_num );
			} catch ( ViesException $exception ) {
				if ( $ignore_vat_check_fail ) {
					$is_vat_exempt = true;
				} else {
					throw $exception;
				}
			}
		}
	}

	$is_vat_exempt = apply_filters( 'woolab_icdic_vat_exempt_customer', $is_vat_exempt, $vat_num, $customer );

	$customer->set_is_vat_exempt( $is_vat_exempt );
}

function woolab_icdic_validate_vat_exempt_for_company( $post_data ) {
	$enabled       = apply_filters( 'woolab_icdic_vat_exempt_enabled', ( get_option('woolab_icdic_vat_exempt_switch', 'no') !== 'no' && wc_tax_enabled() ) );

	if ( !$enabled ) {
		return;
	}

	$data          = array();
	wp_parse_str($post_data, $data);
	$wc_countries          = new WC_Countries();
	$base_country          = $wc_countries->get_base_country();
	$base_country          = apply_filters( 'woolab_icdic_base_country', $base_country );
	$country               = $data['billing_country'];
	$vat_countries         = $wc_countries->get_european_union_countries('eu_vat');
	$is_eu_country         = in_array($country, $vat_countries);
	$ignore_vat_check_fail = woolab_icdic_ignore_check_fail();

	if ($country === $base_country || !$is_eu_country) {
		// Skip check if company's billing country is the same as store's country or if company's billing country is not EU VAT country.
		return;
	}

	$vat_num = $country === 'SK' ? $data['billing_dic_dph'] : $data['billing_dic'];

	if ( !empty($vat_num) && isset($data['billing_iscomp']) && $data['billing_iscomp'] == 1 ) {
		$validator     = new Validator();
		$is_vat_exempt = false;

		if ( $validator->validateVatNumberFormat( $vat_num ) ) {
			try {
				$is_vat_exempt = $validator->validateVatNumber( $vat_num );
			} catch ( ViesException $exception ) {
				if ( $ignore_vat_check_fail ) {
					$is_vat_exempt = true;
				} else {
					throw $exception;
				}
			}
		}

		$is_vat_exempt = apply_filters( 'woolab_icdic_vat_exempt_company', $is_vat_exempt, $data );

		WC()->customer->set_is_vat_exempt( $is_vat_exempt );
	} else {
		WC()->customer->set_is_vat_exempt( false );
	}
}

/**
 * Saves order metadata on WooCommerce checkout.
 * @param int $order_id
 */
function woolab_icdic_save_order_metadata( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order instanceof WC_Order ) {
		return;
	}

	$vat_check_fail_ignored = WC()->session->get( 'woolab_icdic_vat_check_fail_ignored' );

	$order->update_meta_data(
		'woolab_icdic_vat_check_fail_ignored',
		$vat_check_fail_ignored ? 'yes' : 'no'
	);

	$order->save_meta_data();
}

// admin
function woolab_icdic_customer_meta_fields($fields) {
	$fields['billing']['fields'] += array(
		'billing_ic' => array(
			'label'       => __('Business ID', 'woolab-ic-dic'),
			'description' => ''
		),
		'billing_dic' => array(
			'label'       => __('Tax ID', 'woolab-ic-dic'),
			'description' => ''
		),
		'billing_dic_dph' => array(
			'label'       => __('VAT reg. no.', 'woolab-ic-dic'),
			'description' => ''
		)
	);
	return $fields;
}

// Add custom user fields to admin order screen
function woolab_icdic_admin_billing_fields ( $fields ) {

	global $pagenow;
	
	// Return empty fields everytime someone wants admin billing fields.
	$fields += array(
		'billing_ic' => array(
			'label' => __('Business ID', 'woolab-ic-dic'),
			'show'  => false,
		),
		'billing_dic' => array(
			'label' => __('Tax ID', 'woolab-ic-dic'),
			'show'  => false,
		),
		'billing_dic_dph' => array(
			'label' => __('VAT reg. no.', 'woolab-ic-dic'),
			'show'  => false,
			)
		);
		
	$order = null;

	// HPOS ready.
	if ( $pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'wc-orders' && isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id']) ) {
		$order = wc_get_order($_GET['id']);		
	} else { // either edit.php or post.php - double checked by the next conditional.
		
		global $post;
		if ($post === null) {
			return $fields;
		}

		$order = wc_get_order( $post->ID );
	}

	if ( $order instanceof \WC_Order ) {

		$country = $order->get_billing_country();

		$fields['billing_ic']['value'] = $order->get_meta( '_billing_ic', true );
		$fields['billing_dic']['value'] = $order->get_meta( '_billing_dic', true );
		$fields['billing_dic_dph']['value'] = $order->get_meta( '_billing_dic_dph', true );

		// Hide the VAT reg. no. field if not country SK.
		if ( ! $country || ($country && $country[0] !== 'SK') ) {
			$fields['billing_dic_dph']['show'] = false;
		}

	}

	return $fields;

}

// Load customer user fields via ajax on admin order screen from a customer record
// https://www.jnorton.co.uk/woocommerce-custom-fields
function woolab_icdic_ajax_get_customer_details_old_woo ( $customer_data ){

	$user_id = $_POST['user_id'];
	$country = get_user_meta( $user_id, 'billing_country', true );

	$customer_data['billing_ic']  = get_user_meta( $user_id, 'billing_ic', true );
	$customer_data['billing_dic'] = get_user_meta( $user_id, 'billing_dic', true );

	if ( $country == 'SK' ) {
		$customer_data['billing_dic_dph'] = get_user_meta( $user_id, 'billing_dic_dph', true );
	}

	return $customer_data;

}
function woolab_icdic_ajax_get_customer_details( $data, $customer, $user_id ){

	$country = get_user_meta( $user_id, 'billing_country', true );
	$data['billing']['billing_ic']  = get_user_meta( $user_id,  'billing_ic', true );
	$data['billing']['billing_dic'] = get_user_meta( $user_id,  'billing_dic', true );
	if ( $country == 'SK' ) {
		$data['billing']['billing_dic_dph'] = get_user_meta( $user_id, 'billing_dic_dph', true );
	}

	return $data;

}

/**
 * Save meta data / custom fields when editing order in admin screen
 *
 * @param int $post_id Post ID.
 * @param object $post Post object.
 *
 * @return void
 */
function woolab_icdic_process_shop_order ( $post_id, $post ) {

	if ( empty( $_POST['woocommerce_meta_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
		return;
	}

	$order = wc_get_order( $post_id );

	$update_user_meta = apply_filters( 'woolab_icdic_update_user_meta', false );
	$user_id          = $order->get_user_id();

	if ( isset($_POST['_billing_billing_ic']) ) {
		$order->update_meta_data( '_billing_ic', wc_clean( $_POST['_billing_billing_ic'] ) );
		if ( $update_user_meta && $user_id !== 0 ) { // Update if not guest.
			update_user_meta( $user_id, 'billing_ic', sanitize_text_field( $_POST['_billing_billing_ic'] ) );
		}
	}
	if ( isset($_POST['_billing_billing_dic']) ) {
		$order->update_meta_data( '_billing_dic', wc_clean( $_POST['_billing_billing_dic'] ) );
		if ( $update_user_meta && $user_id !== 0 ) { // Update if not guest.
			update_user_meta( $user_id, 'billing_dic', sanitize_text_field( $_POST['_billing_billing_dic'] ) );
		}
	}
	if ( isset($_POST['_billing_billing_dic_dph']) ) {
		$order->update_meta_data( '_billing_dic_dph', wc_clean( $_POST['_billing_billing_dic_dph'] ) );
		if ( $update_user_meta && $user_id !== 0 ) { // Update if not guest.
			update_user_meta( $user_id, 'billing_dic_dph', sanitize_text_field( $_POST['_billing_billing_dic_dph'] ) );
		}
	}

	if ( isset($_POST['_billing_billing_ic']) || isset($_POST['_billing_billing_dic']) || isset($_POST['_billing_billing_dic_dph']) ) {
		$order->save();
	}

}

/**
 * Show notice about Business ID or VAT number check error
 * below order number on orders table view.
 * @param string $column Column ID.
 * @param int $post_id	Post ID.
 */
function woolab_icdic_show_check_failed_notice_on_orders_table( $column, $post_id ) {
    if ( $column !== 'order_number' ) {
        return;
    }

    $order        = wc_get_order( $post_id );
	$check_failed = ( $order instanceof WC_Order )
        ? ( $order->get_meta( 'woolab_icdic_vat_check_fail_ignored' ) === 'yes' )
        : false;

    if ( ! $check_failed ) {
        return;
    }

    ?>

    <br>
    <strong style="color: #dba617;">
        <?php esc_html_e( 'Verification of VAT number has failed.', 'woolab-ic-dic' ) ?>
    </strong>

    <?php
}

/**
 * Show notice about Business ID or VAT number check error
 * below order number on orders table view.
 * @param string $column
 * @param WC_Order $order
 */
function woolab_icdic_show_check_failed_notice_on_orders_table_hpos( $column, $order ) {
    if ( $column !== 'order_number' ) {
        return;
    }

	$check_failed = ( $order instanceof WC_Order )
        ? ( $order->get_meta( 'woolab_icdic_vat_check_fail_ignored' ) === 'yes' )
        : false;

    if ( ! $check_failed ) {
        return;
    }

    ?>

	<strong style="color: #dba617;">
		<?php esc_html_e( 'Verification of VAT number has failed.', 'woolab-ic-dic' ) ?>
	</strong>
	<br>

    <?php
}

/**
 * Show notice about Business ID or VAT number check error
 * below billing number fields on order edit screen.
 * @param WC_Order $order
 */
function woolab_icdic_show_check_failed_notice_on_order_edit( $order ) {
	$check_failed = ( $order->get_meta( 'woolab_icdic_vat_check_fail_ignored' ) === 'yes' );

	if ( ! $check_failed ) {
		return;
	}

	?>

	<div class="notice notice-warning inline">
		<p>
			<strong><?php esc_html_e( 'Caution!', 'woolab-ic-dic' ) ?></strong><br>
			<?php esc_html_e( 'Verification of VAT number has failed.', 'woolab-ic-dic' ) ?>
			<?php esc_html_e( 'Please, make sure the VAT number is valid before processing the order.', 'woolab-ic-dic' ) ?>
		</p>
	</div>

	<?php
}

/**
 * Show notice about Business ID or VAT number check error
 * below billing number fields on order edit screen.
 * @param string $address_type 'billing' or 'shipping'.
 * @param WC_Order $order
 * @param bool $sent_to_admin
 * @param bool $plain_text
 */
function woolab_icdic_show_check_failed_notice_on_admin_email(
	$order,
	$sent_to_admin,
	$plain_text
) {
	if ( ! $order instanceof WC_Order || ! $sent_to_admin ) {
		return;
	}

	$check_failed = ( $order->get_meta( 'woolab_icdic_vat_check_fail_ignored' ) === 'yes' );

	if ( ! $check_failed ) {
		return;
	}

	ob_start();

	?>

	<p>
		<strong><?php esc_html_e( 'Caution!', 'woolab-ic-dic' ) ?></strong><br>
		<?php esc_html_e( 'Verification of VAT number has failed.', 'woolab-ic-dic' ) ?>
		<?php esc_html_e( 'Please, make sure the VAT number is valid before processing the order.', 'woolab-ic-dic' ) ?>
	</p>

	<?php

	$content = ob_get_clean();

	echo ( $plain_text )
		? strip_tags( $content )
		: $content;
}

// Add settings link
function woolab_icdic_plugin_action_links( $links ) {
	$action_links = array(
		'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '" aria-label="' . esc_attr__( 'View Kybernaut IČO DIČ settings', 'woolab-ic-dic' ) . '">' . esc_html__( 'Settings', 'woolab-ic-dic' ) . '</a>',
	);

	return array_merge( $action_links, $links );
}
