<?php

/**
 * Compatibility with PDF Invoices and Packing Slips for WooCommerce by Acowebs 
 * https://wordpress.org/plugins/pdf-invoices-and-packing-slips-for-woocommerce/
 */

/**
 * Render custom billing fields for packing slip
 * in PDF Invoices and Packing Slips for WooCommerce (doesn't seem to be super stable filter)
 * @param string $html Html output.
 * @param int $order_id Order ID.
 * @return string
 */
function woolab_icdic_apifw_ps_custom_billing_fields($html, $order_id) {

    // Before there can be output ending by "<br/>" if there is no phone number in the order, otherwise no "<br/>" is added.
    $order = wc_get_order($order_id);

    $phone = $order->get_billing_phone();
    $dic = $order->get_meta('_billing_dic');
    $ic = $order->get_meta('_billing_ic');
    $ic_dph = $order->get_meta('_billing_dic_dph');

    if ($phone) {
        $html .= '<br/>';
    }

    if ($ic) {
        $html .= __('Business ID', 'woolab-ic-dic') . ': ' . $ic . '<br/>';
    }
    if ($dic) {
        $html .= __('Tax ID', 'woolab-ic-dic') . ': ' . $dic . '<br/>';
    }
    if ($ic_dph) {
        $html .= __('VAT reg. no.', 'woolab-ic-dic') . ': ' . $ic_dph . '<br/>';
    }

    // Remove the last <br/> to keep the same look.
    $html = preg_replace('/<br\/>$/', '', $html);

    return $html;
}
// add_filter('apifw_ps_custom_billing_fields', 'woolab_icdic_apifw_ps_custom_billing_fields', 10, 2);
add_filter('apifw_invoice_custom_billing_fields', 'woolab_icdic_apifw_ps_custom_billing_fields', 10, 2);

