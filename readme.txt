=== Kybernaut IC DIC ===
Contributors: vyskoczilova
Tags: woocommerce, DIČ, IČO, IČ, IČ DPH, česky, česká, české, cz, Czech, zobrazení, úprava, VAT, number, Company, identification, tax, eshop, e-shop, ecommerce, e-commerce, commerce, woothemes, wordpress woocommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, additional, fields, variable, download, downloadable, digital, inventory, fakturační, billing, shipping, adresa, address, woo commerce, order, objednávka, admin, backend
Requires at least: 4.6
Tested up to: 4.7.5
Stable tag: /trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds Czech Company & VAT numbers (IČO & DIČ) to WooCommerce billing fields and verifies if data are correct.


== Description ==

* supports WooCommerce 3.0
* adds **Czech IČO - Company number, DIČ - VAT number** too WooCommerce
* **validates its value** if added and billing country is set to CZ
* **compatible with [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)**
* **compatible with plugins of Vladislav Musilek (Toret)** - Woo Doprava, Woo GoPay etc.
* possible edit of IČO and DIČ at "My Account" page.
* support for editing IČO, DIČ in the administration (backend): 
  * `Users -> Joe Doe (Edit) -> Billing address of the customer` 
  * `E-shop-WooCommerce -> Orders-> Order (show(edit)) -> Billing Information (edit)`

If you want to help, join the [Github](https://github.com/vyskoczilova/kybernaut-ic-dic).


== Installation ==

Just follow the standard [WordPress plugin installation procedere](http://codex.wordpress.org/Managing_Plugins).

1. Upload the plugin to your web site or install via plugin management.
1. Check whether the WooCommerce plugin is installed and active.
1. Activate the plugin through the `/Plugins/` menu in WordPress administration
1. Done!


== Frequently asked questions ==

= I want to display fields in the same row, one besides other =

You can use this snippet to modify the classes of outputed fileds, just add them to your functions.php

`/
add_filter( 'woolab_icdic_class_billing_ic', 'my_theme_class_billing_ic', 10, 1 );
function my_theme_class_billing_ic ( $class ) {
	return array('form-row-first');
}

add_filter( 'woolab_icdic_class_billing_dic', 'my_theme_class_billing_dic', 10, 1 );
function my_theme_class_billing_dic ( $class ) {
	return array('form-row-last');
}
/`

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

1. Checkout validation of ICO.



== Changelog ==

= 1.1.0 = (2017-04-05) =
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