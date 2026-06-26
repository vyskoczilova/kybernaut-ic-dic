# Plan — Deepen the checkout validation orchestrator (Candidate 1)

**Status:** designed, ready to implement
**Date:** 2026-06-26
**Source:** architecture review `~/Development/_ai/architecture-review/kybernaut-ic-dic-20260626.html`, candidate 1
**Recommendation strength:** Strong

## Goal

`woolab_icdic_checkout_field_process()` (`includes/filters-actions.php:153–376`) is the plugin's
central business logic — all CZ/SK/EU validation — fused into one `woocommerce_checkout_process`
hook callback with no return value, driven off `$_POST` / `wc_add_notice()` / `WC()->session`.
It has **zero tests** and is the single largest untested risk surface.

**Deepen it:** extract the decision into a pure module behind one interface; leave the hook as a
thin adapter. Same frontend behavior, but the branching becomes testable through its interface.

This is a **behavior-preserving refactor**. No frontend/notice changes.

## Scope

- **In:** `woolab_icdic_checkout_field_process()` only.
- **Out (later candidates):**
  - The two VAT-exempt twins (`set_vat_exempt_for_customer`, `validate_vat_exempt_for_company`) →
    **candidate 3**. Design the VIES seam here so candidate 3 can reuse it.
  - `ares.php` parse/transport split and its pre-translated error strings → **candidate 5**.
    Leave `ares.php` untouched; pass its `error` string through verbatim as `data`.

## Design decisions (from grilling)

1. **Output = structured error codes**, not translated strings. The pure module returns codes;
   the adapter renders them to `wc_add_notice()`. Tests assert on codes, never English text.

2. **One pure function**, not interface classes (proportionate to a procedural PHP 7.3 plugin):

   ```php
   woolab_icdic_validate_checkout( array $input ): array
   ```

3. **Network crosses the seam as plain closures** passed in `$input` — no DI container, no
   interface classes. Real closure in prod, fake closure in tests (= two adapters, real seam).

4. **Config resolved by the adapter, passed in as plain booleans.** The pure function reads no
   settings/filters/options. Every `apply_filters` / `get_option` / settings helper fires **from
   the adapter, unchanged** — same hook names, defaults, args, WordPress context. Chosen
   **Option A** (resolve filters once up front); the zero-arg config filters make this
   behaviorally identical.

5. **New file `includes/validation.php`**, added to the test bootstrap alongside `helpers.php`.
   Keeps pure orchestration distinct from the math primitives in `helpers.php`.

6. **Error entries carry data:** `['code' => '...', 'data' => [...]]`; `data` omitted when unused.

7. **`verify_vat` closure returns a four-state string**, keeping ibericode (`Validator`,
   `ViesException`) 100% behind the seam — the pure function never references it:
   - `valid` | `bad_format` | `invalid` | `unverifiable` (`unverifiable` = `ViesException` caught
     inside the closure; VIES logging stays in the adapter's closure).

8. **`lookup_ares` closure returns the raw `woolab_icdic_ares()` array**; the pure function owns
   all cross-checking (field mismatch → `missing_fields`) and the `internal_error`/`ignore` paths.

9. **Preserve existing quirks, do not "fix" them** as part of this refactor (e.g. the early
   `return false` at line 156 when `billing_country` is unset *skips* setting the session flag —
   keep it). Any fix is a separate change after the refactor lands.

## Interface

```php
/**
 * Pure validation of the company-identifier checkout fields.
 * No WordPress, no WooCommerce, no network, no i18n.
 */
woolab_icdic_validate_checkout( array $input ): array

// $input keys:
//   values:   country, ic, dic, dic_dph, company, postcode, city, address_1,
//             ship_to_different (bool), shipping_country
//   flags:    ares_check, ares_fill, vies_check, ignore_check_fail,
//             check_country_match, require_sk_ic_and_dic, check_dic_dph_match
//   closures: verify_vat  : fn(string $vat): string  // 'valid'|'bad_format'|'invalid'|'unverifiable'
//             lookup_ares  : fn(string $ico): array   // raw woolab_icdic_ares() result (or falsy)
//
// returns:
//   [
//     'errors' => [ ['code' => string, 'data' => array], ... ],  // in current emission order
//     'check_fail_ignored' => bool,
//   ]
```

### Error codes → existing notices (verbatim mapping, owned by the adapter)

| code | data | current string (text domain) | context |
|------|------|------------------------------|---------|
| `invalid_business_id` | `ares_message?` | `Enter a valid Business ID` (`woolab-ic-dic`) + ARES msg | IC block |
| `ares_unexpected` | – | `Unexpected error occurred. Try it again.` (`woolab-ic-dic`) | IC, ARES falsy |
| `ares_mismatch` | `fields[]` | `_n('%s is not corresponding to ARES.', '%s are not corresponding to ARES.', n, 'woolab-ic-dic')` via `wc_format_list_of_items` | IC, ares_fill |
| `vat_country_mismatch_billing` | – | `The billing country does not correspond to the country of the VAT number.` (`woolab-ic-dic`) | DIČ + IC DPH |
| `vat_country_mismatch_shipping` | – | `The shipping country does not correspond to the country of the VAT number.` (`woolab-ic-dic`) | DIČ |
| `vat_format` | – | `VAT number has not correct format` (`woolab-ic-dic`) | DIČ |
| `invalid_vat` | – | `Enter a valid VAT number` (`woolab-ic-dic`) | DIČ |
| `vat_unverifiable` | – | `Could not validate VAT number.` (`woolab-ic-dic`) | DIČ + IC DPH |
| `invalid_dic_cz` | – | `Enter a valid VAT number` (`woolab-ic-dic`) | DIČ, CZ math path |
| `invalid_tax_id_sk` | – | `Enter a valid Tax ID` (`woolab-ic-dic`) | DIČ SK math + SK required |
| `invalid_vat_dph` | – | `_x('Enter a valid VAT number', 'IC DPH', 'woolab-ic-dic')` | IC DPH |
| `dic_dph_mismatch` | – | `Tax ID or VAT number is not valid.` (`woolab-ic-dic`) | IC DPH match check |

> Codes split where the same English string is emitted from different branches with different
> contexts (`invalid_vat` vs `invalid_dic_cz` vs `invalid_vat_dph`) so tests can tell branches
> apart; the adapter maps each back to its exact original string + context.

## Implementation steps

1. **Characterization tests first.** New `tests/unit/ValidationTest.php`. Drive
   `woolab_icdic_validate_checkout()` across every branch (CZ ARES on/off, CZ math, SK IČ/DIČ,
   SK IC DPH VIES on/off, country-prefix mismatches, ignore-check-fail paths, DIČ/IČ-DPH match)
   asserting the returned `errors` codes + `check_fail_ignored`. Pure — **no WP_Mock needed**.
   Use fake closures: `fn($v) => 'unverifiable'`, `fn($ico) => [...]`.

2. **Write `includes/validation.php`** — the pure `woolab_icdic_validate_checkout()`. Port the
   branching from lines 168–371 verbatim in logic and order; replace `wc_add_notice(...)` with
   pushing `['code'=>..., 'data'=>...]`, replace settings/filter reads with `$input` flags,
   replace `Validator`/`woolab_icdic_ares()` calls with `$input['verify_vat']`/`['lookup_ares']`,
   replace the session write with the returned `check_fail_ignored`.

3. **Add `includes/validation.php` to the test bootstrap** (`tests/bootstrap.php`) next to
   `helpers.php` / `ares.php`.

4. **Rewrite the hook as the adapter** in `includes/filters-actions.php`:
   - clean `$_POST` into the values,
   - resolve every setting/filter/option into the flags (Option A, once up front),
   - build the two closures (the `verify_vat` closure logs `ViesException` then returns
     `unverifiable`; the `lookup_ares` closure wraps `woolab_icdic_ares()`),
   - call `woolab_icdic_validate_checkout()`,
   - map each returned code → exact original `wc_add_notice()` string/domain/context,
   - `WC()->session->set('woolab_icdic_vat_check_fail_ignored', $result['check_fail_ignored'])`,
   - preserve the early `return` (and its skipped-flag quirk) when `billing_country` is unset.

5. **Verify (build + behavior):**
   - `composer test:unit` green (new ValidationTest + existing Helpers/Ares).
   - Manual checkout smoke test: CZ bad IČ, CZ ARES mismatch, SK mismatched DIČ/IČ-DPH, EU VIES
     unverifiable with ignore on/off — confirm identical messages, order, and count to current.

## Risks / guardrails

- **Frontend:** only the adapter can break it (a notice not reproduced exactly). Mitigated by the
  verbatim mapping table above + characterization tests + manual smoke test.
- **`public.js` ARES autofill** is a separate path — untouched.
- **Extension filters** fire from the adapter, unchanged — no public API change.
- **Reuse:** the four-state `verify_vat` seam and the SK→`dic_dph`-else-`dic` selection are
  carried forward to candidate 3.
