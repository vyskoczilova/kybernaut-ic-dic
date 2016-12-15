<?php

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
        // kontrolní číslice
        $mod = ($year . $month . $day . $ext) % 11;
        if ($mod === 10) $mod = 0;
        if ($mod !== (int) $c) {
            return FALSE;
        }

        $year += $year < 54 ? 2000 : 1900;
    }

    // k měsíci může být připočteno 20, 50 nebo 70
    if ($month > 70 && $year > 2003) {
        $month -= 70;
    } elseif ($month > 50) {
        $month -= 50;
    } elseif ($month > 20 && $year > 2003) {
        $month -= 20;
    }

    // kontrola data
    if (!checkdate($month, $day, $year)) {
        return FALSE;
    }

    return TRUE;
}

function woolab_icdic_verify_ic($ic)
{
    // be liberal in what you receive
    $ic = preg_replace('#\s+#', '', $ic);

    // má požadovaný tvar?
    if (!preg_match('#^\d{8}$#', $ic)) {
        return FALSE;
    }

    // kontrolní součet
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