<?php

add_action('admin_notices', 'woolab_icdic_update_notice_settings');
function woolab_icdic_update_notice_settings() {
     
    if ( current_user_can('manage_woocommerce') && get_option( 'woolab_icdic_notice_settings', true ) ) {

        echo '<div class="notice notice-warning is-dismissible woolab-icdic-notice"><p>'; 
        printf( __('Kybernaut IČO DIČ has now settings, go to the %1$s page and check it out!', 'woolab-ic-dic'), '<a href="' . admin_url( 'admin.php?page=wc-settings' ) . '">'.__('Settings','woolab-ic-dic').get_user_meta( get_current_user_id(), 'woolab-icdic-notice-dismissed1243', '1' ).'</a>');
        echo "</p></div>";

    }

}

add_action('wp_ajax_nopriv_woolab_icdic_notice_dismiss', 'woolab_icdic_notice_dismiss');
add_action('wp_ajax_woolab_icdic_notice_dismiss', 'woolab_icdic_notice_dismiss');
function woolab_icdic_notice_dismiss() {
    update_option( 'woolab_icdic_notice_settings', false );
    die();
}