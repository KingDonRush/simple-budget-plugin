# Simple Budget Plugin

Simple Budget Plugin is a WordPress plugin for lightweight quote-request flows
inside Elementor-based sites. It lets visitors add posts or custom post type
items to a local budget cart, review the selected items, remove entries, and
send a prebuilt quote request through WhatsApp.

## Why It Exists

Many catalog and service websites need quotation requests, not a full
WooCommerce checkout. This plugin keeps the site builder workflow intact while
adding a focused budget/cart layer for products, services, or any configured
custom post type.

## Current Capabilities

- Native Elementor widgets:
  - **Budget Button** for add, toggle, context-aware remove, open cart, close
    cart, and WhatsApp send actions.
  - **Budget Quantity** for an accessible numeric input or stepper inside item
    templates.
  - **Budget Listing** for built-in or Elementor-templated items, optional
    calculated summaries, quantity controls, empty state, and item removal.
  - Editor-only **Design Preview** controls in Budget Listing to emulate cart
    items while building Elementor templates.
- Contextual Elementor template builder:
  - Create cart modal, budget item, and budget summary templates in **Simple
    Budget > Templates**.
  - Compose image, title, description, price, subtotal, adjustment, range, and
    estimate status with contextual dynamic tags and native Elementor widgets.
  - Select the template directly in a **Budget Button** configured as
    **Open budget popup**.
  - Configure the opener shell as centered modal, side drawer, or bottom sheet.
- Aggregate estimates for fixed, from, range, and hidden prices, including
  fixed or percentage adjustments and mixed-currency protection.
- Internal setup prompt when an open-cart button has no template selected.
- Configurable allowed post types.
- WhatsApp quote URL generation.
- Modular PHP structure with namespaces and a small autoloader.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Elementor Free 4.0.8 is the compatibility target; Elementor Pro is not required

## Setup

1. Copy `simple-budget-plugin` into `wp-content/plugins/`.
2. Activate **Simple Budget Plugin** in WordPress.
3. Open **Simple Budget > Configurações SBP** and configure the WhatsApp number.
4. Optionally restrict the accepted post types.
5. In Elementor, add **Budget Button** inside the item/card template.
6. Add **Budget Listing** where the visitor should review selected items and
   choose its item and summary modes.
7. Open **Simple Budget > Templates** to create reusable cart, item, and summary
   templates, then select them from the corresponding widgets.
8. Add a separate **Budget Button** configured as **Send WhatsApp budget**
   wherever the composition should expose its final action.

## Elementor-Only Mode

Version 4 keeps the Elementor-only surface introduced in version 3. The supported
surface is now the native Elementor widget flow: **Budget Button**, **Budget
Listing**, **Budget Quantity**, and contextual templates managed in **Simple
Budget > Templates**.

If an open-cart button has no template selected, the popup shows a setup prompt
that points implementers back to the template builder instead of rendering a
legacy cart.

## 4.0 Migration

The inline WhatsApp submit previously owned by **Budget Listing** was removed.
Existing Listings keep their built-in item layout, but documents that saved
`show_submit` are reported in **Simple Budget > Templates** until a standalone
**Budget Button** with the **Send WhatsApp budget** action is added. The plugin
does not rewrite Elementor JSON during upgrade.

## Repository Status

Version `4.0.0` introduces contextual item and summary templates, dynamic tags,
the **Budget Quantity** widget, aggregate cent-based calculation, composable
Listing summaries, stale AJAX response protection, and the explicit inline-send
migration described above.

## Verification

Run the local verification script before releases:

```bash
bash scripts/verify.sh
```

Set `SBP_SKIP_WP_SMOKE=1` to run only syntax and whitespace checks when the
local Docker WordPress runtime is unavailable.

## License

GPLv2 or later.
