=== Kybernaut IC DIC ===
Contributors: vyskoczilova
Tags: woocommerce, DIČ, IČO, IČ, IČ DPH, česky, česká, české, cz, Czech, zobrazení, úprava, VAT, number, Company, identification, tax, eshop, e-shop, ecommerce, e-commerce, commerce, woothemes, wordpress woocommerce, store, sales, sell, shop, shopping, cart, checkout, configurable, additional, fields, variable, download, downloadable, digital, inventory, fakturační, billing, shipping, adresa, address, woo commerce, order, objednávka, admin, backend
Requires at least: 4.0
Tested up to: 4.7.2
Stable tag: /trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Přidá IČO a DIČ do formuláře s fakturační adresou ve WooCommerce a rovnou ověří, jestli jsou zadané hodnoty skutečné.

== Description ==
= CZ =

* přidává **políčka pro IČO a DIČ** do fakturační adresy WooCommerce
* pokud je IČO anebo DIČ zadáno a je fakturováno do ČR, tak **ověří jejich správnost** (algoritmem)
* **kompatibilita s pluginem [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)**
* **kompatibilita s pluginy Vladislava Musílka (Toret)** - Woo Doprava, Woo GoPay apod.
* možnost editace IČO a DIČ i z administrace:
  * `Uživatelé -> Jan Novák (Upravit) -> Fakturační adresa zákazníka`
  * `E-shop-WooCommerce -> Objednávky -> Objednávka (zobrazit(upravit)) -> Fakturační údaje (editace)`

Pokud mi chcete pomoci, přidejte se na [GitHubu](https://github.com/vyskoczilova/kybernaut-ic-dic).

= EN =

* adds **Czech IČO - Company number, DIČ - VAT number** too WooCommerce
* **validates its value** if added and billing country is set to CZ
* **compatible with [WooCommerce PDF Invoices & Packing Slips](https://cs.wordpress.org/plugins/woocommerce-pdf-invoices-packing-slips/)**
* **compatible with plugins of Vladislav Musilek (Toret)** - Woo Doprava, Woo GoPay etc.
* support for editing IČO, DIČ in the administration (backend): 
  * `Users -> Joe Doe (Edit) -> Billing address of the customer` 
  * `E-shop-WooCommerce -> Orders-> Order (show(edit)) -> Billing Information (edit)`

If you want to help, join the [Github](https://github.com/vyskoczilova/kybernaut-ic-dic).

== Installation ==
= CZ =
1. Stáhněte si poslední verzi a rozbalte ji do adresáře /wp-content/plugins/, nebo plugin nainstalujte přes menu Pluginy -> Instalace pluginů v administraci.
1. Ověřte, že máte nainstalované a aktivované WooCommerce
1. Aktivujte plugin přes menu Pluginy v administraci WordPressu.
1. Hotovo!

= EN =
1. Upload the plugin to your web site or install via plugin management.
1. Check whether the WooCommerce plugin is installed and active.
1. Activate the plugin through the \'Plugins\' menu in WordPress administration
1. Done!

== Screenshots ==

1. Checkout validation of ICO.

== Changelog ==

= 1.0.3 (2017-02-1) =
* CZ - Opraveno zobrazování polí v "Můj účet" (frontendová editace účtu zákazníkem).
* CZ - Podpora pro WooCommerce PDF Invoices & Packing Slips.
* EN - Fix display fields in My Acoount frontend page.
* EN - Add support for WooCommerce PDF Invoices & Packing Slips.

= 1.0.2.1 (2016-12-15) =
* CZ - Vytvořen repozitář GIT
* EN - Created the GIT repository

= 1.0.2 (2016-09-05) =
* CZ - Opraveno "IČ" na "IČO.
* EN - Fixed the term "IČ" to "IČO".

= 1.0.1 (2016-07-18) =
* CZ - Ověří fakturační zemi a validuje IČO a DIČ pouze, když je nastaveno CZ
* EN - Checks billing country and validates IC & DIC if is set to CZ

= 1.0.0 (2016-07-16) =
* CZ - První vydání
* EN - Initial version