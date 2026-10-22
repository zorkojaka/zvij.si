# Age confirmation at checkout — 22 October 2026

Requested by the owner: remove the entrance age dialog; require checkout confirmation only for CBD/CBG-flower carts, not accessory-only orders.

Implementation: shared predicate checks the actual product category cbd-cbg-vrsicki, resolving variations to their parent. Rendering, server-side validation and unsaved order metadata all use the scoped rule. Mixed carts require confirmation. Removing the last flower item removes the requirement. AJAX checkout fragments preserve a checked value. The initial dialog and code creating the zvij_age cookie were removed. Historical confirmation metadata is preserved. Existing age cookies are ignored; no cookie clearing or personal-data deletion was performed.

Public production FAQ, terms and story copy plus the About template now describe this as shop policy. This change is not a conclusion that flowers can legally be sold as food. UVHVVR notices dated 3 March and 6 July 2026 identify hemp tops as unauthorised novel food. THC analysis alone does not establish food-market eligibility. The owner was informed and asked for supplier/product documentation. No product status, price, stock or classification was changed.

Validation: PHP syntax; isolated in-memory fixture carts for all three published flower products and purchasable variants, accessory-only, mixed, empty and removed-flower cases; missing/invalid confirmation validation; metadata scope; AJAX checked-state preservation; independent refusal at order-creation hook. WC_Order fixtures were never saved or submitted. Public home has no age dialog. Current checkout is the classic WooCommerce checkout; migration to Store API / Checkout Blocks needs separate integration.

Backup: /home/jaka/backups/zvij-age-checkout-20261022 (two original PHP files and three public post records). Staging/migration inputs: /tmp/zvij-age-fix.
