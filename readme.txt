=== Kybernaut IČO DIČ ===
Contributors: vyskoczilova
Tags: woocommerce, DIČ, IČO, IČ, IČ DPH, česky, česká, české, cz, Czech, zobrazení, úprava, VAT, number, Company, identification, tax, eshop, e-shop, ecommerce, e-commerce, commerce, woothemes, wordpress woocommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, additional, fields, variable, download, downloadable, digital, inventory, fakturační, billing, shipping, adresa, address, woo commerce, order, objednávka, admin, backend
Requires at least: 4.6
Tested up to: 6.4
Stable tag: 1.8.0
Requires PHP: 7.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Donate link: https://www.paypal.me/KarolinaVyskocilova

Adds Company & VAT numbers (IČO & DIČ & IČ DPH) to WooCommerce billing fields and verifies if data are correct.

== Description ==

Adds Company & VAT numbers (IČO & DIČ & IČ DPH) to WooCommerce billing fields and verifies if data are correct. Verification is based either on ARES and VIES database or only on mathematics. When billing to Czech republic, you can autofill fields Company, VAT number, Address, City, and Postcode based on IČO.

Supports both PHP 7.1+ & PHP 8.0+.

=== Main functionality ===

* for CZ as billing country
    * ARES and VIES verification (or mathematically verifies IČO and DIČ)
    * ARES autofill (fields Company, VAT number, Address, City, and Postcode) based on IČO
* for SK as billing country
    * VIES DIČ validation (or just validate the format of values)
* for EU countries as billing country
    * VIES DIČ validation
* VAT extempt feature
* adds fields to IČO & DIČ & IČ DPH WooCommerce frontend: Checkout and My Account page
* allows edits from administration (backend):
  * `Users -> Joe Doe (Edit) -> Billing address of the customer`
  * `E-shop-WooCommerce -> Orders-> Order (show(edit)) -> Billing Information (edit)`
* Enable toggle switch to show/hide input fields ("Buying as a company?")
* Move Country field above the "Buying as a company?" toggle

=== Compatibility ===
* [Kybernaut Mailstep](https://kybernaut.cz/pluginy/kybernaut-mailstep/)
* [WooCommerce SuperFaktura](https://wordpress.org/plugins/woocommerce-superfaktura/)
* [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)
* [WooCommerce Sequential Order Numbers](https://cs.wordpress.org/plugins/woocommerce-sequential-order-numbers/)
* [Woo Checkout Field Editor Pro](https://cs.wordpress.org/plugins/woo-checkout-field-editor-pro/)
* [Fluid Checkout for WooCommerce - Lite](https://wordpress.org/plugins/fluid-checkout/)
* Toret - Woo iDoklad, Woo Doprava, Woo GoPay etc.

=== Requirements ===
* SoapClient PHP library for VIES validation (ask your hosting)

=== Credits ===
* [ibericode/vat](https://github.com/ibericode/vat)


If you want to help, join the [Github](https://github.com/vyskoczilova/kybernaut-ic-dic).


== Installation ==

1. Just follow the standard [WordPress plugin installation procedere](http://codex.wordpress.org/Managing_Plugins).
1. Go to `WooCommerce->Settings->General` and scroll down for `Kybernaut IČO DIČ options`.


== Frequently asked questions ==

= I want to display values in Woo iDoklad by Vladislav Musílek (Toret) =

Go to `Toret plugins -> Woo iDoklad` and scroll to `Přiřazení polí pro IČ a DIČ` and fill following values:
IČ: `_billing_ic`
DIČ: `_billing_dic`
SK DIČ: `_billing_dic_dph`

= I want to style ARES verified fields =
U can use css selectors `.kbnt-validating`, `.kbnt-ok`, and `.kbnt-wrong` for example:
`.kbnt-wrong input {
    color: #e2401c;
}
.kbnt-ok input {
    color: #0f834d;
}
.kbnt-validating input{
    color: #3d9cd2;
}`

= I want to display fields in the same row, one besides other (half width) =

You can use this snippet to modify the classes of outputed fileds, just add them to your functions.php

`add_filter( 'woolab_icdic_class_billing_ic', 'my_theme_class_billing_ic', 10, 1 );
function my_theme_class_billing_ic ( $class ) {
	return array('form-row-first');
}

add_filter( 'woolab_icdic_class_billing_dic', 'my_theme_class_billing_dic', 10, 1 );
function my_theme_class_billing_dic ( $class ) {
	return array('form-row-last');
}`

= I want to update customers meta when I change IČO or DIČ value within order edit. =

You can use this snippet to update customers data when you edit an order, just add them to your functions.php

`add_filter( 'woolab_icdic_update_user_meta', '__return_true' );`

= I want to use the latest files. How can I do this? =

Use the GitHub Repo rather than the WordPress Plugin. Do as follows:

1. If you haven't already done: [Install git](https://help.github.com/articles/set-up-git)

2. in the console cd into Your 'wp-content/plugins´ directory

3. type `git clone https://github.com/vyskoczilova/kybernaut-ic-dic` or better type `git fork https://github.com/vyskoczilova/kybernaut-ic-dic`

4. If you want to update to the latest files (be careful, might be untested on Your WP-Version) type `git pull´.

= I found a bug. Where should I post it? =

I personally prefer GitHub, to keep things straight. The plugin code is here: [GitHub](https://github.com/vyskoczilova/kybernaut-ic-dic)
But you may use the WordPress Forum as well.

= I found a bug and fixed it. How can I contribute? =

Either post it on [GitHub](https://github.com/vyskoczilova/kybernaut-ic-dic) or—if you are working on a cloned repository—send me a pull request.


== Screenshots ==

1. Checkout validation of IČO.


== Changelog ==

= 1.8.0 (2024-01-05) = 

* Fix: VAT exempt checkbox default to off.
* Fix: Work with new ARES API (the old in previous versions has been discontinued by the end of 2023). For initial solution and pointing to the thanks to [@lukas-tomoszek](https://github.com/lukas-tomoszek).
* Feature: Prefix dependencies to avoid conflicts (using [wpify/scoper](https://packagist.org/packages/wpify/scoper))
* Add test validating Ares REST API check and processing.

= 1.7.5 (2023-12-19) =

* Fix: Accidentally broken toggle switch in 1.7.4.
* Declare incompatibility with checkout blocks.

= 1.7.4 (2023-11-20) =

* Fix: Additional check - billing country and VAT country prefix must match for SK IC DPH.
* Feature: Added compatibility with [Fluid Checkout for WooCommerce - Lite](https://wordpress.org/plugins/fluid-checkout/).
* Feature: Added a filter `woolab_icdic_check_billing_country_and_dic` allowing to disable the feature introduced in 1.7.3.

= 1.7.3 (2023-10-08) =

* Feature: Localize address format for all EU countries.
* Feature: Additional check - billing country and VAT country prefix must match (paid by a supporter).

= 1.7.2 (2023-08-02) =

* Fix: non HPOS WooCommerce edit order - load IC DIC values [#60](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/60)
* Fix: ICO - load the city district (NCO) if the street (NO) is not filled in (case when the street = city name) [#62](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/62)
* Several code updates and cleanup [#61](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/61)

= 1.7.1 (2023-07-26) =

* Fix: Fatal error while updating manually order status [#59](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/59)

= 1.7.0 (2023-07-25) =

* Feature: HPOS support - together with [@morvy](https://github.com/morvy), [#55](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/55)
* Feature: VAT extempt - thanks to [@morvy](https://github.com/morvy), [#48](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/48)
* Feature: Math validation for Slovak IC DPH - thanks to [@morvy](https://github.com/morvy), [#56](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/56)
* Fix: Checked "Buying as a company" while any of Company related fields is filled - thanks to [@morvy](https://github.com/morvy), [#48](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/48)
* Fix: Validation of CZ DIC with different length than 10 numbers  - thanks to [@morvy](https://github.com/morvy), [#56](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/56)

= 1.6.7 (2022-01-12) =

* Fix: Checkbox buy as a company - data are sent even when unchecked - thanks to [@morvy](https://github.com/morvy)

= 1.6.6 (2021-11-05) =

* Fix: jQuery 3.x compatibility [#41](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/41) - thanks to [@morvy](https://github.com/morvy)
* Fix: Notice: Undefined variable: dic [#43](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/43) - thanks to [@morvy](https://github.com/morvy)

= 1.6.5 (2021-10-01) =

* Fix: VAT label CSS bug [#40](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/40) - thanks to [@morvy](https://github.com/morvy)

= 1.6.4 (2021-04-05) =
* Fix: prefix toggle CSS class.
* Fix: call an old function in SK DIČ validation.
* Improve: remove duplicate class on IČ DPH.
* Improve: validate ARES only when CZ selected (when the country is re-selected again).
* Load toggle CSS only when used.

= 1.6.3 (2021-02-24) =
* Feature: Add compatibility with PHP 8

= 1.6.2 (2021-02-24) =
* Fix: VAT Validation error.

= 1.6.1 (2021-02-24) =
* Bump the version after SVN issues with "vendor" file

= 1.6.0 (2021-02-24) =
* Fix: Add correct classes on checkbox input for "Buy as Company"
* Update dependencies: VAT composer library to 2.0.5
* Bump minimum requirements to 7.1 (due to Composer dependency)

= 1.5.4 (2020-12-02) =
* Fix: Don't validate without billing country [#27](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/27) - thanks to [@morvy](https://github.com/morvy)

= 1.5.3 (2020-11-09) =
* Compatibility with [WooCommerce SuperFaktura](https://wordpress.org/plugins/woocommerce-superfaktura/)
* Compatibility [Kybernaut Mailstep](https://kybernaut.cz/pluginy/kybernaut-mailstep/)

= 1.5.2 (2020-10-13) =
* Fix wrong assets path.
* Use more general CSS selectors for better compatibility.

= 1.5.1 (2020-09-17) =
* Fix issues with quotes - [#25](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/25)
* Add custom filter for disabling required DIC when ICO filled for SK `add_filter( 'woolab_icdic_sk_required_ic_and_dic', '__return_false' );` - [#26](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/26)

= 1.5.0 (2020-07-20) =
* Fix: Slovak DIC validation fix, [#22](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/22) - thanks to [@morvy](https://github.com/morvy)
* Feature: Show/Hide toggle functionality, [#24](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/24) - [@morvy](https://github.com/morvy)
* Maintenance: Update language files and dependencies

= 1.4.0 (2019-09-05) =
* Fix: Strip spaces from ICO, DIC, DIC DPH fields ([#8](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/8))
* Fix: Validation of Slovak DIČ in Vies ([#9](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/9))
* Fix: Trigger update_checkout JS when address loaded
* Performance: Don't validate IČO in ARES when the value has not been changed, called after 'donetyping'
* Maintenance: Update node_modules

= 1.3.3 (2018-11-18) =
* Compatibility with WC 3.5.1 which has "fixed" old ordering of checkout fields [#21763](https://github.com/woocommerce/woocommerce/pull/21763)

= 1.3.2 (2018-08-14) =
* Fix: Use number for address in ARES when no correct land registry and hous number are not filled ([@pryx](https://github.com/vyskoczilova/kybernaut-ic-dic/pull/6))

= 1.3.1 (2018-06-03) =
* Fix: Show "IČ DPH" on "Manually add new order" screen when "Slovakia" selected
* Fix: Correct validation "IČ DPH" (with SK prefix) and "DIČ" (without SK prefix)
* Fix: Remove the WC nonce check (already checked in WC itself)
* Fix: Problem with loading plugin options
* Added "How to" for Woo iDoklad to readme.txt
* Update .pot source file.

= 1.3.0 (2018-02-21) =
* Fix: Display "VAT reg. no." field in Order Billing-edit.
* Fix: Compatibility with WooCommerce Sequential Order Numbers ([#3](https://github.com/vyskoczilova/kybernaut-ic-dic/issues/3))
* Performance: CSS in admin.
* Feature: Validation for Czech Business ID (via ARES)
* Feature: Autofill for fields such as Company, VAT number, Address, City, and Postcode based on Czech Business ID (via ARES)
* Feature: Validation of VAT (via [VIES](https://github.com/dannyvankooten/vat.php))
* Added: Plugin settings to `WooCommerce->Settings->General`
* Fields moved after "company" field.

= 1.2.0 (2018-02-08) =
* Fixed: Editing Business ID and VAT values within order in admin backend
* Feature: Texts in plugin only in English (Czech as a translation)
* Feature: Added `woolab_icdic_class_{field_name}` filters to customize class of added billing input fields
* Feature: Added `woolab_icdic_update_user_meta` filter to enable updating user meta on order details edit
* Added: Links to GitHub and Write a review to plugins page.
* Added: Basic SK support (based on this [article](https://podnikam.webnoviny.sk/ico-dic-ic-dph-co-znamenaju-tieto-skratky-a-kde-ich-hladat/))

= 1.1.0 (2017-04-05) =
* Fix: "Order properties should not be accessed directly." Added support for WooCommerce 3.0.

= 1.0.3 (2017-02-1) =
* Fix: display fields in My Account frontend page.
* Add: support for WooCommerce PDF Invoices & Packing Slips.

= 1.0.2.1 (2016-12-15) =
* Feature: Created the GIT repository

= 1.0.2 (2016-09-05) =
* Fix: the term "IČ" to "IČO".

= 1.0.1 (2016-07-18) =
* Feature: Checks billing country and validates IC & DIC if is set to CZ

= 1.0.0 (2016-07-16) =
* Initial release
