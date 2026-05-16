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
  - **Budget Button** for add, toggle, open cart, close cart, and WhatsApp send
    actions.
  - **Budget Listing** for cart display, quantity controls, empty state, item
    removal, remove positioning, and submit.
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

Version `3.0.1` hardens the Elementor-only v3 flow. Template rendering now
checks template role, post status, and edit capability; cart payloads and
quantities are capped on both frontend and server; product validation is shared
by listing and WhatsApp rendering; and hook registration avoids duplicated asset
enqueue paths.

## License

GPLv2 or later.
