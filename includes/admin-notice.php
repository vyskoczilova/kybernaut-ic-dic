<?php

add_action('admin_notices', 'woolab_icdic_update_notice_settings');
function woolab_icdic_update_notice_settings() {
     
    if ( current_user_can('manage_woocommerce') && get_option( 'woolab_icdic_notice_settings', true ) ) {

        echo '<div class="notice notice-warning is-dismissible woolab-icdic-notice"><p>'; 
        printf( __('Kybernaut IČO DIČ has now settings, go to the %1$s page and check it out!', 'woolab-ic-dic'), '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '">'.__('Settings','woolab-ic-dic').'</a>');
        echo "</p></div>";

    }

}

add_action('wp_ajax_woolab_icdic_notice_dismiss', 'woolab_icdic_notice_dismiss');
function woolab_icdic_notice_dismiss() {
    check_ajax_referer( 'woolab_icdic_notice_dismiss', 'nonce' );

    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_send_json_error( null, 403 );
    }

    update_option( 'woolab_icdic_notice_settings', false );
    wp_send_json_success();
}