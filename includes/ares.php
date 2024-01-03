<?php

/**
 * MFCR INFO ARES API
 * https://ares.gov.cz/stranky/vyvojar-info
 * 
 * ADDITIONAL CREDITS
 * https://github.com/svecon/web-utilities/blob/master/Ares/Ares.php
 * https://webtrh.cz/279860-script-nacitani-dat-ares-jquery
 * http://www.garth.cz/ostatni/ares-ziskani-dat-pomoci-php/
 */

if ( ! function_exists( 'woolab_icdic_ares') ) {

    function woolab_icdic_ares( $ico = NULL ) {

        if ( $ico == NULL ) {
            return array( 'error' => __('Business ID not set.', 'woolab-ic-dic'));
        }

        $url = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/' . $ico;
        $response = wp_remote_get( $url );

        if ( ! is_wp_error( $response ) ) {

            $status_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if ( $status_code == 200 && $data ) {

                $return = array( 'error' => false );
                $return['spolecnost'] = $data->obchodniJmeno;
                $return['ico'] = $data->ico;
                $return['dic'] = $data->dic;

                $address = $data->sidlo;
                $return['adresa'] = explode(',', $address->textovaAdresa)[0];
                $return['psc'] = $address->psc;
                $return['mesto'] = $address->nazevObce;

            } elseif ( $status_code == 404 ) {
                $return = array( 'error' => __('Entity doesn\'t exist in ARES.', 'woolab-ic-dic'));
            } else {
                $return = array( 'error' => __('ARES is not responding', 'woolab-ic-dic'));
            }

        } else {
            $return = array( 'error' => __('WP ERROR, can\'t connect.', 'woolab-ic-dic'));
        }

        return $return;

    }
}
