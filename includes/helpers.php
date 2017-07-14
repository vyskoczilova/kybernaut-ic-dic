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