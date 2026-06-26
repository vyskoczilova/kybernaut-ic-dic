# Plan — Collapse the two VAT-exemption twins into one module (Candidate 3)

**Status:** designed, ready to implement (after candidate 1 — landed in `47818e8`)
**Date:** 2026-06-26
**Source:** architecture review `kybernaut-ic-dic-20260626` candidate 3
**Recommendation strength:** Strong

## Goal

`woolab_icdic_set_vat_exempt_for_customer()` (`includes/filters-actions.php:376–422`, hooked on
`init`) and `woolab_icdic_validate_vat_exempt_for_company()` (`:424–477`, hooked on
`woocommerce_checkout_update_order_review`) carry a **near-identical VIES + exemption body**. The
inner decision (`validateVatNumberFormat` → `validateVatNumber` → `ViesException` →
`ignore_check_fail ? true : false`) and the **SK→`dic_dph` else `dic`** number-selection rule are
duplicated between them — and the selection rule appears a *third* time in candidate 1.

A customer-facing VAT-exemption bug must currently be fixed in two places that can drift.

**Deepen it:** extract one pure exemption decision behind the **same `verify_vat` seam built in
candidate 1**; the two hooks stay as thin adapters that gate, source data, select the VAT number,
and apply the result. Behavior-preserving.

## What is actually shared vs different (verified against current source)

The review calls these "the same logic twice", but they are **near**-identical — the refactor must
preserve the differences, not erase them:

| Aspect | `set_..._customer` (init) | `validate_..._company` (order_review) |
|--------|---------------------------|----------------------------------------|
| Bail early | `wp_doing_ajax()` → return | — |
| Enabled gate | `vat_exempt_enabled` filter + `vat_exempt_switch != 'no'` + `wc_tax_enabled()` | **same** (byte-identical) |
| Data source | `$customer->get_meta('billing_*')` | `wp_parse_str($post_data)` → `$data['billing_*']` |
| Country gate | `billing_country` non-empty **and != base_country** | empty / == base / **not in `eu_vat`** → return |
| EU-membership check | **absent** | present (`get_european_union_countries('eu_vat')`) |
| `is_company` check | **absent** | `!isset(billing_iscomp) \|\| billing_iscomp == 1` |
| VAT selection | SK→`dic_dph` else `dic` | **same** |
| VIES decision body | shared (see below) | **same** |
| Logger message | `'Could not validate if VAT number is exempt: %s…'` | **same** |
| Result filter | `woolab_icdic_vat_exempt_customer` ($exempt, $vat, $customer) | `woolab_icdic_vat_exempt_company` ($exempt, $data) |
| Apply | `$customer->set_is_vat_exempt(...)` | `WC()->customer->set_is_vat_exempt(...)`; else-branch sets `false` |

**Genuinely shared core (the only thing to extract):**

```php
$is_vat_exempt = false;
if ( $validator->validateVatNumberFormat( $vat_num ) ) {
    try { $is_vat_exempt = $validator->validateVatNumber( $vat_num ); }
    catch ( ViesException $e ) { /* log */ $is_vat_exempt = $ignore_check_fail; }   // false otherwise
}
```

Mapped onto candidate 1's four-state `verify_vat`:
`valid → true`, `unverifiable → $ignore_check_fail`, `bad_format | invalid → false`.

## Design

1. **Reuse the candidate-1 VIES seam — land it once.** The inline `$verify_vat` closure currently
   built inside `woolab_icdic_checkout_field_process()` (`includes/filters-actions.php`) is
   promoted to a small factory so all three call sites share one construction:

   ```php
   // includes/validation.php (or a thin helper) — adapter-side, NOT pure
   function woolab_icdic_make_vies_verifier( string $log_message ): callable
   // returns fn(string $vat): string  // 'valid'|'bad_format'|'invalid'|'unverifiable'
   ```

   `$log_message` is the `sprintf` format the closure logs on `ViesException` — this is the one
   difference between candidate 1 (`'Could not validate VAT number: %s…'`) and the exemption path
   (`'Could not validate if VAT number is exempt: %s…'`), so it stays parameterized to preserve
   logs verbatim. Candidate 1's adapter switches to this factory (no behavior change).

2. **One pure decision** in `includes/validation.php`:

   ```php
   /** valid → true; unverifiable → $ignore_check_fail; bad_format|invalid → false */
   function woolab_icdic_vat_exempt_from_state( string $state, bool $ignore_check_fail ): bool
   ```

   Plus the shared selection rule (already implicit in candidate 1):

   ```php
   function woolab_icdic_select_vat_number( string $country, string $dic, string $dic_dph ): string
   // $country === 'SK' ? $dic_dph : $dic
   ```

   Both pure, no WP/network/i18n — unit-tested directly.

3. **The two hooks become adapters.** Each keeps its own gating (ajax bail / EU check /
   `is_company` / data source / result filter / apply target — all unchanged), and for the inner
   decision does:

   ```php
   $vat_num = woolab_icdic_select_vat_number( $country, $dic, $dic_dph );
   $is_vat_exempt = false;
   if ( ! empty( $vat_num ) ) {
       $state = $verify_vat( $vat_num );                       // shared factory closure
       $is_vat_exempt = woolab_icdic_vat_exempt_from_state( $state, $ignore_check_fail );
   }
   // ... existing apply_filters + set_is_vat_exempt unchanged
   ```

   The `! empty( $vat_num )` guard preserves the original "don't touch the validator when no VAT
   number" path (and avoids a wasted `Validator` construction).

## Scope

- **In:** `set_vat_exempt_for_customer`, `validate_vat_exempt_for_company`, and promoting the
  candidate-1 `verify_vat` closure to a shared factory.
- **Out:** the country-gating differences themselves (kept verbatim — this is not the place to
  reconcile whether the customer path *should* also check EU membership; that is a separate,
  behavior-changing decision). `ares.php` (candidate 5). The field registry (candidate 4).

## Implementation steps (TDD)

1. **Tests first** — extend `tests/unit/ValidationTest.php` (or a new `VatExemptTest.php`):
   - `woolab_icdic_vat_exempt_from_state`: `valid→true`, `invalid→false`, `bad_format→false`,
     `unverifiable` with `$ignore` true→true and false→false.
   - `woolab_icdic_select_vat_number`: SK→dic_dph, CZ/other→dic, empties.
2. **Add the two pure functions** to `includes/validation.php`.
3. **Add the verifier factory** `woolab_icdic_make_vies_verifier()`; point candidate 1's adapter at
   it (confirm `composer test:unit` still green — candidate 1 behavior unchanged).
4. **Rewrite both hooks** to use the factory + the two pure functions, preserving every gate,
   filter, log message, and apply target exactly.
5. **Verify:** `composer test:unit` green; manual smoke — set base country, then a B2B EU order
   with a valid VIES number (tax removed), an invalid one (tax kept), and VIES-down with
   ignore-check-fail on/off; confirm exemption state matches current on both the `init` and
   `update_order_review` paths.

## Risks / guardrails

- **Behavior drift between the twins is intentional and must survive** (EU check, `is_company`).
  The extracted core is only the VIES decision + selection; everything else stays in the adapter.
- **Log strings** differ from candidate 1 — preserved via the factory's `$log_message` param.
- **`ViesException` must stay swallowed** (never re-thrown): an uncaught exception on `init` /
  checkout AJAX would fatal the request when VIES is down. The factory keeps the catch internal.
- Shared verifier factory is built per-request inside each adapter (empty `use`), so no
  large-scope capture / memory concern.
```
