# Simple Budget Plugin

Simple Budget Plugin is a WordPress plugin for lightweight quote-request flows
inside Elementor-based sites. It lets visitors add posts or custom post type
items to a local budget cart, review the selected items, remove entries, and
open a prebuilt WhatsApp quote request for the visitor to review and send.

## Why It Exists

Many catalog and service websites need quotation requests, not a full
WooCommerce checkout. This plugin keeps the site builder workflow intact while
adding a focused budget/cart layer for products, services, or any configured
custom post type.

**Independent plugin project.** No payment processing or adoption claim is implied.

## Current Capabilities

- Native Elementor widgets:
  - **Budget Button** for add, toggle, open cart, close cart, and WhatsApp send
    actions.
  - **Budget Listing** for cart display, quantity controls, empty state, item
    removal, remove positioning, and submit.
  - Editor-only **Design Preview** controls in Budget Listing to emulate cart
    items while building Elementor templates.
- Elementor cart template builder:
  - Create cart modal templates in **Simple Budget > Templates**.
  - Edit the modal content with Elementor free.
  - Select the template directly in a **Budget Button** configured as
    **Open budget popup**.
  - Configure the opener shell as centered modal, side drawer, or bottom sheet.
- Internal setup prompt when an open-cart button has no template selected.
- Configurable allowed post types.
- WhatsApp quote URL generation.
- Modular PHP structure with namespaces and a small autoloader.

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Elementor for widget-based usage

## Setup

1. Copy `simple-budget-plugin` into `wp-content/plugins/`.
2. Activate **Simple Budget Plugin** in WordPress.
3. Open **Simple Budget > Configurações SBP** and configure the WhatsApp number.
4. Optionally restrict the accepted post types.
5. In Elementor, add **Budget Button** inside the item/card template.
6. Add **Budget Listing** where the visitor should review selected items.
7. To customize the modal, open **Simple Budget > Templates**, create a cart
   template, edit it in Elementor, then select it in a **Budget Button** whose
   action is **Open budget popup**.

## Elementor-Only Mode

Version 3 removes the old shortcode and fixed CSS ID behavior. The supported
surface is now the native Elementor widget flow: **Budget Button**, **Budget
Listing**, and cart templates managed in **Simple Budget > Templates**.

If an open-cart button has no template selected, the popup shows a setup prompt
that points implementers back to the template builder instead of rendering a
legacy cart.

## Repository Status

Version `3.1.1` is a stabilization release guided by AI coding governance. It
adds reproducible verification, reduces renderer/widget drift, fixes responsive
panel-width number formatting, avoids write side effects while listing
templates, stops exposing the WhatsApp number to frontend JavaScript, and only
prints the popup shell when an open-cart button is present.

## Verification

Run the local verification script before releases:

```bash
bash scripts/verify.sh
```

Set `SBP_SKIP_WP_SMOKE=1` to run only syntax and whitespace checks when the
local Docker WordPress runtime is unavailable.

## Review the implementation

- [AJAX validation](includes/Ajax/Ajax.php)
- [Elementor widgets](includes/Elementor/Widgets)
- [Editable cart templates](includes/Templates/CartTemplateManager.php)
- [Browser cart behavior](assets/js/sbp-script.js)

Review on 2026-09-17: `SBP_SKIP_WP_SMOKE=1 bash scripts/verify.sh` passed syntax
and whitespace checks on revision `27c9f411f87d12026ef9fb85b4afe59845f0ad99`.
The WordPress integration smoke test was not run in this review.

## Development method and authorship

This is an independent project, not evidence of an employer or a client engagement.
The source was produced primarily or entirely by AI coding agents under Guilherme
Manoel da Silva's direction. His contribution includes product intent, requirements,
constraints, decomposition, product and architectural decisions through the agent
interface, iteration, validation and documentation. The repository demonstrates
the resulting system and process; it does not imply that he manually wrote every
component or can reproduce it unaided from memory.

## Em português

Plugin de solicitação de orçamento para WordPress/Elementor, com widgets nativos,
carrinho local e modelo de modal editável. Gera a URL do pedido para o visitante
revisar e enviar pelo WhatsApp; não processa pagamentos.

## License

GPLv2 or later.
