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

    function woolab_icdic_ares( $ico = '' ) {

        if ( $ico == '' ) {
            return array( 'error' => __('Business ID not set.', 'woolab-ic-dic'));
        }

        // Check format before asking ARES.
        if ( ! is_numeric( $ico ) || strlen( $ico ) != 8) {
            return array( 'error' => __('Business ID must be a number and 8 digits long.', 'woolab-ic-dic'));
        }

        $url = 'https://ares.gov.cz/ekonomicke-subjekty-v-be/rest/ekonomicke-subjekty/' . $ico;
        $response = wp_remote_get( $url );

        if ( ! is_wp_error( $response ) ) {

            $status_code = wp_remote_retrieve_response_code( $response );
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if ( $status_code == 200 && $data ) {

                $return = array( 'error' => false );
                $return['spolecnost'] = $data->obchodniJmeno ?? '';
                $return['ico'] = $data->ico ?? '';
                $return['dic'] = $data->dic ?? '';

                $cislo_orientacni = $data->sidlo->cisloOrientacni ?? '';
                $cislo_domovni = $data->sidlo->cisloDomovni ?? '';
                $pismeno_orientacni = $data->sidlo->cisloOrientacniPismeno ?? ''; // TEST
                $cp = ($cislo_orientacni !== "" ? $cislo_domovni . "/".$cislo_orientacni . $pismeno_orientacni : $cislo_domovni);
                $ulice  = $data->sidlo->nazevUlice ?? $data->sidlo->nazevObce;

                $return['adresa'] = sprintf( '%s %s', $ulice, $cp );
                $return['psc'] = $data->sidlo->psc;
                $return['mesto'] = $data->sidlo->nazevMestskehoObvodu ?? $data->sidlo->nazevObce;

            } elseif ( $status_code == 404 ) {
                $return = array( 'error' => __('Entity doesn\'t exist in ARES.', 'woolab-ic-dic'));
            } else {
                $return = array(
					'error'          => __('ARES is not responding.', 'woolab-ic-dic'),
	                'internal_error' => true,
                );
            }

        } else {
            $return = array(
				'error'          => __('An error occured while connecting to ARES, try it again later.', 'woolab-ic-dic'),
				'internal_error' => true,
            );
        }

        return $return;

    }
}
