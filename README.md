# Kybernaut IC DIC (WordPress plugin)

[![plugin version](https://img.shields.io/wordpress/plugin/v/woolab-ic-dic.svg)](https://wordpress.org/plugins/woolab-ic-dic)

Adds Czech Company & VAT numbers (IČO & DIČ) to WooCommerce billing fields and verifies if data are correct.
Download here: https://wordpress.org/plugins/woolab-ic-dic/

**Note:** `vendor` folder is tracked because off PHP 7.1 compatibility which is unoficially still working on ibericode/vat package, but the minimum requirement was bumped in December 2020. Plan to bump accordingly soon.

## Unreleased changes

* none

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

    add_filter( 'woolab_icdic_vat_exempt_enabled', function(){
      return "no"; // or "yes"
    } );

    add_filter( 'woolab_icdic_base_country', function(){
      return "SK";
    } );

### Update user meta while edition order details: `woolab_icdic_update_user_meta`
By default, if you edit order details, user profile is not touched. If you want to update user details when you add or edit ICO or DIC value, use this filter.


*Example:*

    add_filter( 'woolab_icdic_update_user_meta', '__return_true' );

### Disable required DIC when ICO filled in SK

    add_filter( 'woolab_icdic_sk_required_ic_and_dic', '__return_false' );

## Credits

* 10up and their [WordPress.org Plugin Deploy](https://github.com/10up/action-wordpress-plugin-deploy) and [WordPress.org Plugin Readme/Assets Update](https://github.com/10up/action-wordpress-plugin-asset-update) Github Actions
