<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Pure validation of the company-identifier checkout fields.
 *
 * No WordPress, no WooCommerce, no network, no i18n. The hook adapter in
 * filters-actions.php resolves all settings/filters/options into the flags,
 * builds the two network closures, and maps the returned error codes back to
 * the exact original wc_add_notice() strings.
 *
 * @param array $input {
 *     @type string      country            Billing country code (wc_clean'd).
 *     @type string      ic                 billing_ic, wc_clean'd (whitespace stripped here).
 *     @type string      dic                billing_dic, wc_clean'd (whitespace stripped here for VAT use).
 *     @type bool        dic_present        Whether billing_dic was present in the request (isset).
 *     @type string      dic_dph            billing_dic_dph, wc_clean'd (whitespace stripped here).
 *     @type string      company            billing_company, wc_clean'd.
 *     @type string      postcode           billing_postcode, wc_clean'd.
 *     @type string      city               billing_city, wc_clean'd.
 *     @type string      address_1          billing_address_1, wc_clean'd.
 *     @type bool        ship_to_different  Whether ship-to-different-address is checked.
 *     @type string|null shipping_country   Shipping country code, or null when not present.
 *     @type bool        ares_check
 *     @type bool        ares_fill
 *     @type bool        vies_check
 *     @type bool        ignore_check_fail
 *     @type bool        check_country_match
 *     @type bool        require_sk_ic_and_dic
 *     @type bool        check_dic_dph_match
 *     @type bool        country_in_eu      Resolved from ibericode Countries::isCountryCodeInEU().
 *     @type callable    verify_vat         fn(string $vat): string 'valid'|'bad_format'|'invalid'|'unverifiable'
 *     @type callable    lookup_ares        fn(string $ico): array   raw woolab_icdic_ares() result (or falsy)
 * }
 *
 * @return array {
 *     @type array $errors             List of ['code' => string, 'data' => array]; 'data' omitted when unused.
 *     @type bool  $check_fail_ignored Whether a VAT/ARES check failure was ignored.
 * }
 */
function woolab_icdic_validate_checkout( array $input ) {

	$country = $input['country'];

	$errors                 = array();
	$vat_check_fail_ignored = false;

	// Remove white spaces (matches the original preg_replace cleaning).
	$ico     = preg_replace( '/\s+/', '', $input['ic'] );
	$dic     = preg_replace( '/\s+/', '', $input['dic'] );
	$dic_dph = preg_replace( '/\s+/', '', $input['dic_dph'] );

	// BUSINESS ID
	if ( $input['ic'] ) {

		// CZ
		if ( $country == 'CZ' ) {

			// ARES Check Enabled
			if ( $input['ares_check'] ) {

				$ares = $input['lookup_ares']( $ico );
				if ( $ares ) {
					if ( $ares['error'] ) {
						$is_internal_error = ( ! empty( $ares['internal_error'] ) );

						if ( $is_internal_error && $input['ignore_check_fail'] ) {
							$vat_check_fail_ignored = true;
						} else {
							$errors[] = array( 'code' => 'invalid_business_id', 'data' => array( 'ares_message' => $ares['error'] ) );
						}
					} elseif ( $input['ares_fill'] ) {
						$missing_fields = array();
						if ( $input['dic_present'] && $input['dic'] != $ares['dic'] ) {
							$missing_fields[] = 'tax_id';
						}
						if ( $input['company'] != $ares['spolecnost'] ) {
							$missing_fields[] = 'company';
						}
						if ( $input['postcode'] != $ares['psc'] ) {
							$missing_fields[] = 'postcode';
						}
						if ( $input['city'] != $ares['mesto'] ) {
							$missing_fields[] = 'city';
						}
						if ( $input['address_1'] != $ares['adresa'] ) {
							$missing_fields[] = 'address';
						}
						if ( $missing_fields ) {
							$errors[] = array( 'code' => 'ares_mismatch', 'data' => array( 'fields' => $missing_fields ) );
						}
					}
				} else {
					if ( $input['ignore_check_fail'] ) {
						$vat_check_fail_ignored = true;
					} else {
						$errors[] = array( 'code' => 'ares_unexpected' );
					}
				}

			// ARES Check Disabled
			} elseif ( ! woolab_icdic_verify_ic( $ico ) ) {
				$errors[] = array( 'code' => 'invalid_business_id' );
			}

		// SK
		} elseif ( $country == 'SK' ) {
			if ( $ico ) {
				if ( ! woolab_icdic_verify_ic( $ico ) ) {
					$errors[] = array( 'code' => 'invalid_business_id' );
				}
			}
		}

	}

	// VAT / DIC
	if ( $input['dic'] ) {

		// Check if in EU
		if ( $input['country_in_eu'] ) {

			// If Validate in VIES
			// Slovak DIC cannot (and shouldn't) be validated in VIES
			if ( $input['vies_check'] && $country != 'SK' ) {

				// Match VAT country prefix and country code.
				if ( $input['check_country_match'] && woolab_icdic_get_vat_number_country_code( $dic ) !== $country ) {
					$errors[] = array( 'code' => 'vat_country_mismatch_billing' );
				}

				// Match VAT country prefix and shipping country code.
				if ( $input['check_country_match'] && $input['ship_to_different'] && $input['shipping_country'] !== null && woolab_icdic_get_vat_number_country_code( $dic ) !== $input['shipping_country'] ) {
					$errors[] = array( 'code' => 'vat_country_mismatch_shipping' );
				}

				$state = $input['verify_vat']( $dic );

				if ( $state === 'bad_format' ) {
					// Original emits BOTH notices: validateVatNumberFormat() false, then
					// validateVatNumber() short-circuits to false (no VIES call, no exception).
					$errors[] = array( 'code' => 'vat_format' );
					$errors[] = array( 'code' => 'invalid_vat' );
				} elseif ( $state === 'invalid' ) {
					$errors[] = array( 'code' => 'invalid_vat' );
				} elseif ( $state === 'unverifiable' ) {
					if ( $input['ignore_check_fail'] ) {
						$vat_check_fail_ignored = true;
					} else {
						$errors[] = array( 'code' => 'vat_unverifiable' );
					}
				}

			// Validate CZ and SK mathematicaly
			} else {
				if ( $country == 'CZ' ) {
					if ( ! ( woolab_icdic_verify_rc( substr( $dic, 2 ) ) || woolab_icdic_verify_dic( substr( $dic, 2 ) ) ) || substr( $dic, 0, 2 ) != 'CZ' ) {
						$errors[] = array( 'code' => 'invalid_dic_cz' );
					}
				} elseif ( $country == 'SK' ) {
					if ( ! woolab_icdic_verify_dic_sk( $dic ) ) {
						$errors[] = array( 'code' => 'invalid_tax_id_sk' );
					}
				}
			}

		}

	}
	// DIC is mandatory in Slovakia, this is not a VAT number
	else {
		// if IC is set, DIC must be set as well in Slovakia
		if ( $input['require_sk_ic_and_dic'] && ! empty( $input['ic'] ) && empty( $input['dic'] ) && $country == 'SK' ) {
			$errors[] = array( 'code' => 'invalid_tax_id_sk' );
		}
	}

	// IC DPH / DIC DPH
	if ( $input['dic_dph'] && $country == 'SK' ) {

		// Match VAT country prefix and country code.
		if ( $input['check_country_match'] && woolab_icdic_get_vat_number_country_code( $dic_dph ) !== $country ) {
			$errors[] = array( 'code' => 'vat_country_mismatch_billing' );
		}

		// Verify IC DPH
		// If Validate in VIES
		if ( $input['vies_check'] ) {

			$state = $input['verify_vat']( $dic_dph );

			if ( $state === 'unverifiable' ) {
				if ( $input['ignore_check_fail'] ) {
					$vat_check_fail_ignored = true;
				} else {
					$errors[] = array( 'code' => 'vat_unverifiable' );
				}
			} elseif ( $state !== 'valid' ) {
				// Original only calls validateVatNumber() here (no separate format
				// notice), so both 'bad_format' and 'invalid' yield a single notice.
				$errors[] = array( 'code' => 'invalid_vat_dph' );
			}

		} else {

			if ( ! woolab_icdic_verify_dic_dph_sk( $dic_dph ) ) {
				$errors[] = array( 'code' => 'invalid_vat_dph' );
			}

		}

		// IC DPH has to match to Tax ID number without SK
		if ( $input['check_dic_dph_match'] && $dic_dph && $dic ) {
			if ( $dic != substr( $dic_dph, 2 ) ) {
				$errors[] = array( 'code' => 'dic_dph_mismatch' );
			}
		}
	}

	return array(
		'errors'             => $errors,
		'check_fail_ignored' => $vat_check_fail_ignored,
	);
}

/**
 * Pure VAT-exemption decision from a four-state VIES result.
 *
 * Mirrors the original exemption body verbatim: a valid VAT number is exempt; a
 * VIES outage (ViesException, surfaced as 'unverifiable') falls back to the
 * ignore-check-fail setting; everything else (bad format, format-ok-but-invalid)
 * is not exempt. Shared by both VAT-exempt hooks.
 *
 * @param string $state             'valid'|'bad_format'|'invalid'|'unverifiable' from verify_vat.
 * @param bool   $ignore_check_fail Whether a VIES outage should still grant exemption.
 * @return bool
 */
function woolab_icdic_vat_exempt_from_state( $state, $ignore_check_fail ) {
	if ( $state === 'valid' ) {
		return true;
	}
	if ( $state === 'unverifiable' ) {
		return (bool) $ignore_check_fail;
	}
	return false;
}

/**
 * Pure selection of which company field carries the VIES-checkable VAT number.
 *
 * Slovakia uses the separate DIČ DPH (VAT registration) field; every other
 * country uses the DIČ field. This rule is shared by the checkout validation
 * and both VAT-exempt hooks.
 *
 * @param string $country Billing country code.
 * @param string $dic     DIČ value.
 * @param string $dic_dph DIČ DPH value.
 * @return string
 */
function woolab_icdic_select_vat_number( $country, $dic, $dic_dph ) {
	return $country === 'SK' ? $dic_dph : $dic;
}
