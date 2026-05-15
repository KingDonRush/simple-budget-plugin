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
  - **Budget Button** for add, remove, toggle, open cart, and send actions.
  - **Budget Listing** for cart display, empty state, item removal, and submit.
- Elementor cart template builder:
  - Create cart modal templates in **Simple Budget > Templates**.
  - Edit the modal content with Elementor free.
  - Select the template directly in a **Budget Button** configured as
    **Open budget popup**.
- Legacy Elementor mode with a regular Button widget using the
  `add-to-cart-button` CSS ID.
- Shortcode fallback through `[sbp_cart]`.
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

## Legacy Mode

Existing Elementor pages can continue using a normal Button widget with the CSS
ID `add-to-cart-button`. The plugin still injects the current post ID into the
button wrapper and the frontend script keeps the original add-to-budget flow.

## Repository Status

Version `2.2.0` adds an Elementor-based cart template builder for customizable
modal content while preserving the older shortcode and CSS-ID integration.

## License

GPLv2 or later.
