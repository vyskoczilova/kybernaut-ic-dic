# Kybernaut IC DIC (WordPress plugin)

[![plugin version](https://img.shields.io/wordpress/plugin/v/woolab-ic-dic.svg)](https://wordpress.org/plugins/woolab-ic-dic)

Přidá IČO a DIČ do formuláře s fakturační adresou ve WooCommerce a rovnou ověří, jestli jsou zadané hodnoty skutečné.
Download here: https://wordpress.org/plugins/woolab-ic-dic/

## Description
* supports WooCommerce 3.0+
* adds **Czech IČO - Company number, DIČ - VAT number** too WooCommerce
* **validates its value** if added and billing country is set to CZ
* **compatible with [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)**
* **compatible with plugins of Vladislav Musilek (Toret)** - Woo Doprava, Woo GoPay etc.
* possible edit of IČO and DIČ at "My Account" page.
* support for editing IČO, DIČ in the administration (backend): 
  * `Users -> Joe Doe (Edit) -> Billing address of the customer` 
  * `E-shop-WooCommerce -> Orders-> Order (show(edit)) -> Billing Information (edit)`


## Unreleased changes
* Texts in plugin only in English (Czech as a translation)
* Feature: Added `woolab_icdic_class_{field_name}` filters to customize class of added billing input fields


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