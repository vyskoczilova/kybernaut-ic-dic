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
    if (!checkdate(intval($month), intval($day), intval($year))) {
        return FALSE;
    }

    return TRUE;
}

function woolab_icdic_verify_ic($ic)
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

function woolab_icdic_verify_ic_sk( $ic ){

    // be liberal in what you receive
    $ic = preg_replace('#\s+#', '', $ic);

    // check required format
    if (!preg_match('#^\d{8}$#', $ic)) {
        return FALSE;
    }

    // TODO check the sum for Slovak IC

    return (int) $ic;
}

function woolab_icdic_verify_dic_sk( $dic ){

    // be liberal in what you receive
    $dic = preg_replace('#\s+#', '', $dic);

    // check required format
    if (!preg_match('#^\d{10}$#', $dic)) {
        return FALSE;
    }

    // TODO check the sum for Slovak DIC

    return (int) $dic;
}

function woolab_icdic_verify_dic_dph_sk( $dic_dph ){

    // be liberal in what you receive
    $dic_dph = preg_replace('#\s+#', '', $dic_dph);

    // check required format
    if (!preg_match('#^SK\d{10}$#', $dic_dph)) {
        return FALSE;
    }

    // TODO check the sum for Slovak DIC

    return (string) $dic_dph;
}

function woolab_icdic_add_after_company( $fields, $additional, $type = 'both' ) {
    
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

function woolab_icdic_send_error ( $body, $response, $add_to_subject = "" ) {
	
	$to = array( 'karolina@kybernaut.cz' );
	$subject = 'Error KBNT ICO DIC' . ( !empty( $add_to_subject ) ? ' - ' . $add_to_subject : '' );
	$body = "<b>Page</b>: ".md5( get_bloginfo('home') )."<br><br><b>Data sent</b>: ".var_export($body, true)."<br><br><b>Answer</b>: ".var_export($response, true)."<br><br><b>Browser</b>: ".$_SERVER['HTTP_USER_AGENT']." (<a href='https://developers.whatismybrowser.com/useragents/parse/#parse-useragent'>parse it here</a>)<BR><BR><b>Plugin version</b>: " . WOOLAB_IC_DIC_VERSION;
	$headers = array('Content-Type: text/html; charset=UTF-8');
	
	wp_mail( $to, $subject, $body, $headers );
}