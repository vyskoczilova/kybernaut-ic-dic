=== Kybernaut IC DIC ===
Contributors: vyskoczilova
Tags: woocommerce, DIČ, IČO, IČ, IČ DPH, česky, česká, české, cz, Czech, zobrazení, úprava, VAT, number, Company, identification, tax, eshop, e-shop, ecommerce, e-commerce, commerce, woothemes, wordpress woocommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, additional, fields, variable, download, downloadable, digital, inventory, fakturační, billing, shipping, adresa, address, woo commerce, order, objednávka, admin, backend
Requires at least: 4.6
Tested up to: 4.9.4
Stable tag: /trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds Company & VAT numbers (IČO & DIČ & IČ DPH) to WooCommerce billing fields and verifies if data are correct.


== Description ==

Adds Company & VAT numbers (IČO & DIČ & IČ DPH) to WooCommerce billing fields and verifies if data are correct. Verification is based either on ARES and VIES database or only on mathmeatics. When billing to Czech republic, you can autofill fields Company, VAT number, Address, City, and Postcode based on IČO.

* for CZ as billing country
    * ARES and VIES verification (or mathematicaly verifies IČO and DIČ)
    * ARES autofill (fields Company, VAT number, Address, City, and Postcode) based on IČO
* for SK as billing country
    * VIES DIČ valiadtion (or just validate format of values)
* for EU countries as billing country
    * VIES DIČ valiadtion
* adds fields to IČO & DIČ & IČ DPH WooCommerce frontend: Checkout and My Acount page
* allows edits from administration (backend): 
  * `Users -> Joe Doe (Edit) -> Billing address of the customer` 
  * `E-shop-WooCommerce -> Orders-> Order (show(edit)) -> Billing Information (edit)`

=== Compatibility ==
* WooCommerce 2.6 & 3.0+
* [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)
* plugins of Vladislav Musilek (Toret) - Woo Doprava, Woo GoPay etc.

=== Requirements ===
* PHP 5.4 and above
* Soap Client for VIES valiadtion (ask your hosting)

=== Credits ===
* [PHP library for dealing with European VAT](https://github.com/dannyvankooten/vat.php)


If you want to help, join the [Github](https://github.com/vyskoczilova/kybernaut-ic-dic).


== Installation ==

1. Just follow the standard [WordPress plugin installation procedere](http://codex.wordpress.org/Managing_Plugins).
1. Go to `WooCommerce->Settings->General` and scroll down for `Kybernaut IČO DIČ options`.


== Frequently asked questions ==

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

You can use this snippet to update customers data when you edit order, just add them to your functions.php

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