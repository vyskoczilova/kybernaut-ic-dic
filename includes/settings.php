<?php

add_filter('woocommerce_general_settings', 'woolab_icdic_icdic_general_settings');
function woolab_icdic_icdic_general_settings($settings) {
        
    $settings[] = array( 'title' => __( 'Kybernaut IČO DIČ options', 'woolab-ic-dic' ), 'type' => 'title', 'desc' => __( 'The following options affect how Business ID and VAT number behaves.', 'woolab-ic-dic' ), 'id' => 'woolab_icdic_options' );
    $settings[] = array(
        'title'   => __( 'CZ: Validate Business ID in ARES', 'woolab-ic-dic' ),
        'desc'    => __( 'Enable validation of Business ID in Czech database ARES.', 'woolab-ic-dic' ),
        'id'      => 'woolab_icdic_ares_check',
        'default' => 'yes',
        'type'    => 'checkbox',
    );
    $settings[] = array(
        'title'   => __( 'CZ: Validate and autofill based on ARES', 'woolab-ic-dic' ),
        'desc'    => __( 'Enable autofill and validation for Company, VAT number, Address, City, and Postcode fields based on Czech database ARES. Requires checked the option above.', 'woolab-ic-dic' ),
        'id'      => 'woolab_icdic_ares_fill',
        'default' => 'false',
        'type'    => 'checkbox',
    );
    $settings[] = array(
        'title'   => __( 'EU: Validate VAT number in VIES', 'woolab-ic-dic' ),
        'desc'    => __( 'Enable validation of VAT number in EU database VIES.', 'woolab-ic-dic' ),
        'id'      => 'woolab_icdic_vies_check',
        'default' => 'yes',
        'type'    => 'checkbox',
    );
    $settings[] = array( 'type' => 'sectionend', 'id' => 'woolab_icdic_options' );

    return $settings;
}