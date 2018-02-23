# Kybernaut IC DIC (WordPress plugin)

[![plugin version](https://img.shields.io/wordpress/plugin/v/woolab-ic-dic.svg)](https://wordpress.org/plugins/woolab-ic-dic)

Adds Czech Company & VAT numbers (IČO & DIČ) to WooCommerce billing fields and verifies if data are correct.
Download here: https://wordpress.org/plugins/woolab-ic-dic/

## Unreleased changes
* Fix: Display "VAT reg. no." field in Order Billing-edit.
* Performance: CSS in admin.
* Feature: Validation for Czech Business ID (via ARES)
* Feature: Autofill for fields such as Company, VAT number, Address, City, and Postcode based on Czech Business ID (via ARES)
* Feature: Validation of VAT (via VIES)
* Added: Plugin settings to `WooCommerce->Settings->General`

## Filters

### Custom class for fields: `woolab_icdic_class_{field_name}`
If you need to modify class of outputed fields. For example you want to have *billing_ic* and *billing_dic* in one row.


*Example:*

    add_filter( 'woolab_icdic_class_billing_ic', 'my_theme_class_billing_ic', 10, 1 );
    function my_theme_class_billing_ic ( $class ) {
      return array('form-row-first');
    }

    add_filter( 'woolab_icdic_class_billing_dic', 'my_theme_class_billing_dic', 10, 1 );
    function my_theme_class_billing_dic ( $class ) {
      return array('form-row-last');
    }

    add_filter( 'woolab_icdic_class_billing_dic_dph', 'my_theme_class_billing_dic_dph', 10, 1 );
    function my_theme_class_billing_dic_dph ( $class ) {
      return array('form-row-last');
    }

### Manipulate the plugin settings: 
If you need to set it up in your theme or plugin, you can use following filters to do so:


*Example:*

    add_filter( 'woolab_icdic_ares_check', '__return_true' );

    add_filter( 'woolab_icdic_ares_fill', '__return_true' );

    add_filter( 'woolab_icdic_vies_check', '__return_true' );

### Update user meta while edition order details: `woolab_icdic_update_user_meta`
By default, if you edit order details, user profile is not touched. If you want to update user details when you add or edit ICO or DIC value, use this filter.


*Example:*

    add_filter( 'woolab_icdic_update_user_meta', '__return_true' );