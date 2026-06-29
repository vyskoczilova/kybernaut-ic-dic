# Plan — One settings module, one read seam (Candidate 2)

**Status:** designed, ready to implement (after candidates 1 & 3)
**Date:** 2026-06-26
**Source:** architecture review `kybernaut-ic-dic-20260626` candidate 2
**Recommendation strength:** Strong

## Goal

`settings.php` defines 8 option keys, but they are read in **two incompatible styles**: four go
through helper accessors (`woolab_icdic_ares_check/ares_fill/vies_check/ignore_check_fail` in
`woolab-ic-dic.php:157–182`, each normalizing via `woolab_icdic_get_option()` = `option == 'yes'`),
while four more are read raw with the `'yes'/'no'` truthiness and the `wc_tax_enabled()` clause
**hand-copied at each call site**. `woolab_icdic_vat_exempt_switch` is byte-identical at two sites.

Promote the four raw reads into named accessors so every settings read crosses one interface and
the normalization lives in exactly one place per option.

## The four raw reads (current)

| Option | Site(s) | Current expression | Filter arg shape |
|--------|---------|--------------------|------------------|
| `vat_exempt_switch` | `filters-actions.php:398`, `:430` | `apply_filters('woolab_icdic_vat_exempt_enabled', get_option(..,'no') !== 'no' && wc_tax_enabled())` | **bool** |
| `disable_dic_dicdph_match` | `filters-actions.php:223` | `apply_filters('woolab_icdic_enable_dic_dicdph_match_check', get_option(..,'no') !== 'yes')` | **bool** |
| `toggle_switch` | `filters-actions.php:62` (fields), `woolab-ic-dic.php:152` (css) | `apply_filters('woolab_icdic_toggle', get_option(..,'no'))` then `!== 'no'` *vs* `=== 'yes'` | **string** |
| `country_switch` | `filters-actions.php:88` | `apply_filters('woolab_icdic_country_ontop', get_option(..,'no'))` then `!== 'no'` | **string** |

## Decision — preserve filter contracts; centralize only normalization

New accessors (placed next to the existing four helpers in `woolab-ic-dic.php`), each returning
`bool` and keeping the **existing filter name and argument shape** so no public filter breaks:

```php
woolab_icdic_vat_exempt_enabled()         // apply_filters('woolab_icdic_vat_exempt_enabled', get_option('woolab_icdic_vat_exempt_switch','no') !== 'no' && wc_tax_enabled())
woolab_icdic_dic_dicdph_match_enabled()   // apply_filters('woolab_icdic_enable_dic_dicdph_match_check', get_option('woolab_icdic_disable_dic_dicdph_match','no') !== 'yes')
woolab_icdic_toggle_enabled()             // apply_filters('woolab_icdic_toggle', get_option('woolab_icdic_toggle_switch','no')) !== 'no'
woolab_icdic_country_ontop_enabled()      // apply_filters('woolab_icdic_country_ontop', get_option('woolab_icdic_country_switch','no')) !== 'no'
```

The `vat_exempt`/`dicdph` filters keep receiving a **bool** (unchanged); the `toggle`/`country`
filters keep receiving the **string** option and the `!== 'no'` normalization happens after the
filter (unchanged for those two functional sites).

### One flagged nuance (behavior-identical for every settings-UI value)

The toggle option is currently read with two different comparisons:
- `filters-actions.php:62` (adds the company checkbox + toggle field classes): `!== 'no'`
- `woolab-ic-dic.php:152` (enqueues `style.css`): `=== 'yes'`

Both accessor call sites will use `woolab_icdic_toggle_enabled()` = `!== 'no'`. This standardizes
the CSS-enqueue site from `=== 'yes'` to `!== 'no'`. The settings checkbox only ever stores
`'yes'` or `'no'`, and the `woolab_icdic_toggle` filter defaults to passing that through, so the
two are **identical for every configuration the UI can produce**. They diverge only if a
third-party filter returns some other truthy string — in which case the CSS now loads whenever the
toggle fields are shown, which is the consistent (and arguably correct) outcome. Documented here
and in the commit so it is not a silent change.

## Implementation steps

1. Add the four accessors in `woolab-ic-dic.php`, immediately after
   `woolab_icdic_ignore_check_fail()`.
2. Replace the call sites:
   - `filters-actions.php:62` → `if ( woolab_icdic_toggle_enabled() ) {`  (drop `$woolabToggle`)
   - `filters-actions.php:88` → `if ( woolab_icdic_country_ontop_enabled() ) {`  (drop `$countryFirst`)
   - `filters-actions.php:223` → `'check_dic_dph_match' => woolab_icdic_dic_dicdph_match_enabled(),`
   - `filters-actions.php:398` & `:430` → `$enabled = woolab_icdic_vat_exempt_enabled();`
   - `woolab-ic-dic.php:152` → `if ( woolab_icdic_toggle_enabled() ) { wp_enqueue_style(...) }`
3. `composer test:unit` green (no test depends on these; existing suite must stay green).
4. Manual smoke: with the company toggle ON, confirm the checkout still shows the
   company checkbox + IČ/DIČ fields and the toggle CSS still loads; confirm VAT exemption and the
   DIČ/IČ-DPH match check still behave as before.

## Scope

- **In:** the four raw settings reads above and their five call sites.
- **Out:** the existing four helpers (already correct), the `woolab_icdic_notice_settings` admin
  notice option (unrelated dismissal flag), the field registry (candidate 4), `ares.php`
  (candidate 5). No settings UI / option storage changes.

## Risks / guardrails

- **Public filters unchanged** — same names, same argument types; extensions keep working.
- The single nuance (CSS-enqueue comparison) is behavior-identical for all UI-producible values
  and documented above.
- No unit-test surface (these are WordPress option reads); covered by the existing suite staying
  green plus the manual toggle smoke test.
```
