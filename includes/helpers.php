<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function woolab_icdic_verify_rc( $rc )
{
    // be liberal in what you receive
    if (!preg_match('#^\s*(\d\d)(\d\d)(\d\d)[ /]*(\d\d\d)(\d?)\s*$#', $rc, $matches)) {
        return FALSE;
    }

    list(, $year, $month, $day, $ext, $c) = $matches;

    if ($c === '') {
        $year += $year < 54 ? 1900 : 1800;
    } else {
        // controll number
        $mod = ($year . $month . $day . $ext) % 11;
        if ($mod === 10) $mod = 0;
        if ($mod !== (int) $c) {
            return FALSE;
        }

        $year += $year < 54 ? 2000 : 1900;
    }

    // there can be added 20, 50 or 70 to the month
    if ($month > 70 && $year > 2003) {
        $month -= 70;
    } elseif ($month > 50) {
        $month -= 50;
    } elseif ($month > 20 && $year > 2003) {
        $month -= 20;
    }

    // check date
    if (!checkdate($month, $day, $year)) {
        return FALSE;
    }

    return TRUE;
}

// https://phpfashion.com/jak-overit-platne-ic-a-rodne-cislo
function woolab_icdic_verify_ic( $ic )
{
    // be liberal in what you receive
    $ic = preg_replace('#\s+#', '', $ic);

    // check required format
    if (!preg_match('#^\d{8}$#', $ic)) {
        return FALSE;
    }

    // controll sum
    $a = 0;
    for ($i = 0; $i < 7; $i++) {
        $a += $ic[$i] * (8 - $i);
    }

    $a = $a % 11;
    if ($a === 0) {
        $c = 1;
    } elseif ($a === 1) {
        $c = 0;
    } else {
        $c = 11 - $a;
    }

    return (int) $ic[7] === $c;
}

/**
 * Verify DIC/VAT
 *
 * @see https://gist.githubusercontent.com/svschannak/e79892f4fbc56df15bdb5496d0e67b85/raw/5283c4a8056dad3d789e0eb77b2ce2e89a5b1f2c/vat_validation.js
 * @param string|int $dic 
 * @return bool 
 */
function woolab_icdic_verify_dic( $dic )
{
    // be liberal in what you receive
    $dic = preg_replace('#\s+#', '', $dic);

    $total = 0;
    $multipliers = [8,7,6,5,4,3,2];

    $czexp = array ();
    $czexp[0] = '/^\d{8}$/';                                       //  8 digit legal entities
    $czexp[1] = '/^[0-5][0-9][0|1|5|6][0-9][0-3][0-9]\d{3}$/';     //  9 digit individuals
    $czexp[2] = '/^6\d{8}$/';                                      //  9 digit individuals (Special cases)
    $czexp[3] = '/^\d{2}[0-3|5-8][0-9][0-3][0-9]\d{4}$/';          // 10 digit individuals
    $i = 0;

    // Legal entities
    if (preg_match($czexp[0], $dic)) {
        // Extract the next digit and multiply by the counter.
        for ($i = 0; $i < 7; $i++) {
            $total += intval(substr($dic, $i, 1)) * $multipliers[$i];
        }

        // Establish check digit.
        $total = 11 - $total % 11;
        if ($total == 10) $total = 0; 
        if ($total == 11) $total = 1; 

        // Compare it with the last character of the VAT number. If it's the same, then it's valid.
        if ($total == substr($dic, 7, 1)) {
            return true;
        }

        return false;
    }

    // Individuals type 1 (Standard) - 9 digits without check digit
    elseif (preg_match($czexp[1], $dic)) {
        if (intval(substr($dic, 0, 2)) > 62) {
            return false;
        }

        return true;
    }

    // Individuals type 2 (Special Cases) - 9 digits including check digit
    elseif (preg_match($czexp[2], $dic)) {
        // Extract the next digit and multiply by the counter.
        for ($i = 0; $i < 7; $i++) {
            $total += intval(substr($dic, $i+1, 1)) * $multipliers[$i];
        }

        // Establish check digit pointer into lookup table
        if ($total % 11 == 0) {
            $a = $total + 11;
        } else {
            $a = ceil($total/11) * 11;
        }
        $pointer = $a - $total;

        // Convert calculated check digit according to a lookup table;
        $lookup  = [8,7,6,5,4,3,2,1,0,9,8];
        if ($lookup[$pointer-1] == substr($dic, 8, 1)) {
            return true;
        }

        return false;
    }

    // Individuals type 3 - 10 digits
    elseif (preg_match($czexp[3], $dic)) {
        $temp = intval(substr($dic, 0, 2)) + intval(substr($dic, 2, 4)) + intval(substr($dic, 4, 6)) + intval(substr($dic, 6, 8)) + intval(substr($dic, 8));
        if ($temp % 11 == 0 && intval($dic) % 11 == 0) {
            return true;
        }

        return false;
    }

    // else error
    return false;
}

function woolab_icdic_verify_dic_sk( $dic )
{
    // be liberal in what you receive
    $dic = preg_replace('#\s+#', '', $dic);

    // check required format
    if (!preg_match('#^\d{10}$#', $dic)) {
        return FALSE;
    }

    // TODO check the sum for Slovak DIC

    return (int) $dic;
}

/**
 * Verify ICDPH/VAT
 * 
 * @see https://gist.githubusercontent.com/svschannak/e79892f4fbc56df15bdb5496d0e67b85/raw/5283c4a8056dad3d789e0eb77b2ce2e89a5b1f2c/vat_validation.js
 * @param string|int $dic_dph
 * @return bool 
 */
function woolab_icdic_verify_dic_dph_sk( $dic_dph )
{
    // be liberal in what you receive
    $dic_dph = preg_replace('#\s+#', '', $dic_dph);

    // check required format
    if (!preg_match('#^SK\d{10}$#', $dic_dph)) {
        return false;
    }

    $dic_dph = substr($dic_dph, 2);
    if ( $dic_dph % 11 === 0 ) {
        return true;
    }

    return false;
}

function woolab_icdic_add_after_company( $fields, $additional, $type = 'both' )
{
    if ( $type == 'both' ) {
        // add fields after "billing_company" field
        foreach ($fields as $key => $value) {
            if ( $key == 'billing' ) {
                foreach ( $value as $_key => $_value ) {
                    $key_fields[$_key] = $_value;
                    if ( $_key == 'billing_company') {
                        // add passed values
                        foreach ( $additional as $__key => $__value ) {
                            $key_fields[$__key] = $__value;
                        }
                    }
                }
                $fields_new[$key] = $key_fields;
            } else {
                $fields_new[$key] = $value;
            }
        }
    } else {
        foreach ( $fields as $key => $value ) {
            $fields_new[$key] = $value;
            if ( $key == 'billing_company') {
                // add passed values
                foreach ( $additional as $_key => $_value ) {
                    $fields_new[$_key] = $_value;
                }
            }
        }
    }

    return $fields_new;
}

/**
 * Get VAT number country code from VAT number and match it to WooCommerce countr field code.
 * 
 * @param string $vat_number VAT number
 * @return string
 */
function woolab_icdic_get_vat_number_country_code($vat_number) {
    $country_code = substr( $vat_number, 0, 2);
    if ($country_code === 'EL') {
        return 'GR';
    }
    return $country_code;
}