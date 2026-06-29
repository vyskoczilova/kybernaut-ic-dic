# ADR 0001 — Extract the checkout validation orchestrator behind a pure seam

- **Status:** Accepted — implemented 2026-06-26
- **Deciders:** Karolína Vyskočilová
- **Related:** `docs/plans/candidate-1-validation-orchestrator.md` (design), architecture
  review `kybernaut-ic-dic-20260626` candidate 1

## Context

`woolab_icdic_checkout_field_process()` was the plugin's central business logic: all
CZ/SK/EU company-identifier validation (Business ID, Tax ID, VAT/VIES, ARES) fused into a
single ~220-line `woocommerce_checkout_process` hook callback. It read `$_POST`, settings,
`apply_filters`, `get_option`, and `WC()->session` directly, performed network I/O (ARES +
VIES) inline, and emitted `wc_add_notice()` strings — all interleaved.

Consequences of that shape:

- **Zero test coverage.** The branching could not be exercised without a full WordPress +
  WooCommerce + network stack, so it had no unit tests despite being the highest-risk code in
  the plugin.
- **Hard to reason about / change.** Country rules, VIES quirks, and ARES cross-checks were
  entangled with i18n, session, and transport concerns.

## Decision

Split the hook into two pieces with a single seam:

1. **A pure decision module** — `includes/validation.php`,
   `woolab_icdic_validate_checkout( array $input ): array`. No WordPress, no WooCommerce, no
   network, no i18n. It returns **structured error codes**
   (`['errors' => [['code', 'data']], 'check_fail_ignored' => bool]`), never translated
   strings. It calls the existing pure math helpers in `helpers.php` directly; only the
   genuinely impure collaborators cross the seam.

2. **A thin adapter** — the rewritten hook in `includes/filters-actions.php`. It cleans
   `$_POST` into values, resolves every setting/filter/option into plain booleans up front
   ("Option A"), builds the two network closures, calls the pure function, maps each returned
   code back to its **exact** original notice via `woolab_icdic_checkout_error_message()`, and
   writes the session flag.

Key design choices (see the plan for the full rationale):

- **Network crosses the seam as plain closures**, not DI/interfaces — proportionate to a
  procedural PHP 7.3 plugin. `verify_vat` returns a four-state string
  (`valid|bad_format|invalid|unverifiable`), keeping ibericode (`Validator`, `ViesException`)
  100% behind the seam; `lookup_ares` is the bare `woolab_icdic_ares` callable.
- **Structured codes, not strings.** Tests assert on codes; the adapter owns the
  code→string/text-domain/context mapping. Codes are split by *originating branch* even when
  the English text is identical (`invalid_vat` / `invalid_dic_cz` / `invalid_vat_dph`) so tests
  can tell paths apart and traceability to the original is preserved.
- **Behavior-preserving.** The branching was ported verbatim in logic and emission order.
  Pre-existing quirks were deliberately kept, not "fixed": the early `return` (which skips
  writing the session flag) when `billing_country` is unset; the malformed-EU-VAT case that
  emits **both** `vat_format` and `invalid_vat`; and the `isset($_POST['billing_dic'])` guard
  on the ARES Tax-ID mismatch (carried as the explicit `dic_present` flag).

## Consequences

**Positive**

- The decision logic is now unit-testable with zero infrastructure. `tests/unit/ValidationTest.php`
  covers every branch with fake closures (28 tests); full suite is **93 tests / 105 assertions, green**.
- The adapter is small and obvious; the pure module is navigable and side-effect-free.
- The four-state `verify_vat` seam and the SK `dic_dph`-else-`dic` selection are reusable by the
  next refactors (notably the VAT-exempt logic).

**Negative / trade-offs**

- The adapter resolves all flags/closures eagerly even when the relevant field is absent (the
  original resolved some lazily). On a per-request hook this is microseconds and dominated by
  the ARES/VIES network calls — judged negligible.
- The seam introduces an internal contract (the `$input` array). It is documented in the
  function docblock but not type-enforced (procedural PHP, by choice).
- A `bad_format` result is interpreted differently per call site (DIČ branch re-expands to two
  codes; IČ-DPH branch folds to one). This asymmetry is inherited from the original two-vs-one
  validator-call behavior and is documented at both sites.

## Verification

- `composer test:unit` — green (new `ValidationTest` + existing `Helpers`/`Ares`).
- No build step applies (gulp builds only CSS/JS; PHP is not bundled).
- **Outstanding manual step:** a live-checkout smoke test confirming identical message text,
  order, and count for: CZ bad IČ; CZ ARES mismatch (ares_fill on); SK mismatched DIČ/IČ-DPH;
  EU malformed VAT (expect both `vat_format` + `invalid_vat`); EU VIES-unverifiable with
  ignore-check-fail on/off. Not runnable in CI (needs WooCommerce + network).

## Notes for future work

- This was **candidate 1** of the architecture review. Candidate 3 (the two VAT-exempt twins,
  `set_vat_exempt_for_customer` / `validate_vat_exempt_for_company`) should reuse the
  `verify_vat` seam established here. Candidate 5 (`ares.php` parse/transport split and its
  pre-translated error strings) remains untouched — its `error` string is passed through
  verbatim as `data` for now.
