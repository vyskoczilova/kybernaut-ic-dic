<?php

/**
 * MFCR INFO ARES API
 * http://wwwinfo.mfcr.cz/ares/ares_xml.html.cz#k3
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

        $url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=' . $ico;
        $response = wp_remote_get( $url );

        if ( ! is_wp_error( $response ) ) {
            
            $body = wp_remote_retrieve_body($response);
            $xml  = simplexml_load_string($body);

            if ( $xml ) {

                $ns = $xml->getDocNamespaces(); 
                $data = $xml->children($ns['are']);
                $data = $data->children($ns['D'])->VBAS;

                if ( $data ) {

                    $return = array( 'error' => false );
                    $return['spolecnost'] = $data->OF->__toString();
                    $return['ico'] = $data->ICO->__toString();
                    $return['dic'] = $data->DIC->__toString();

                    $cp_1 = $data->AA->CD->__toString();
                    $cp_2 = $data->AA->CO->__toString();
                    $cp = ( $cp_2 != "" ? $cp_1."/".$cp_2 : $cp_1 );
                    $cp = (empty($cp)?$data->AA->CA->__toString():$cp);
                    $return['adresa'] = $data->AA->NU->__toString() . ' ' . $cp;

                    $return['psc'] = $data->AA->PSC->__toString();
                    $return['mesto'] = $data->AA->N->__toString();

                } else {
                    
                    $return = array( 'error' => __('Entity doesn\'t exist in ARES.', 'woolab-ic-dic'));
                    
                }

            } else {
                $return = array( 'error' => __('ARES is not responding', 'woolab-ic-dic'));

            }
            
        } else {
            $return = array( 'error' => __('WP ERROR, can\'t connect.', 'woolab-ic-dic'));
        }

        return $return;

    }
}