<?php

function woolab_icdic_superfaktura_client_data( $client_data, $order ) {
    $client_data['dic'] = $order->get_meta('_billing_dic');
    $client_data['ico'] = $order->get_meta('_billing_ic');
    $client_data['ic_dph'] = $order->get_meta('_billing_dic_dph');

    return $client_data;
}
add_filter( 'sf_client_data', 'woolab_icdic_superfaktura_client_data', 10, 3 );
