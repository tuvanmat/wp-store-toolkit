# WP Store Toolkit

A lightweight, modular toolkit for WordPress and WooCommerce stores.

WP Store Toolkit collects the small, single-purpose utilities that a
store usually ends up installing as separate plugins — and puts them
behind one settings page. Every utility is a **module**: enable only
the ones you need, and disabled modules are never loaded at all.

## Why this exists

This project started as a set of small private plugins written for
real WooCommerce stores (Flatsome-based shops running WPForms). Each
one solved exactly one problem, but together they meant several
plugins to update, several settings screens, and several slightly
different code styles. WP Store Toolkit is the clean-room rewrite of
those utilities as modules of a single, well-structured plugin —
developed in the open, in English first, with Vietnamese translations
included.

## Modules

| Module | Status | What it does |
| --- | --- | --- |
| Read More for Product Descriptions | Available | Collapses long WooCommerce product descriptions behind a "Read more" toggle, with table-of-contents (EZ TOC) compatibility |
| Quick Order Popup | Planned — see the roadmap | A "Quick order" button that opens a popup with a WPForms order form; Flatsome Quick View aware |

More modules are added one at a time. See
[docs/ROADMAP.md](docs/ROADMAP.md) for the current plan.

## Read More module settings

Once enabled, the Read More module adds its own section to the
settings page:

- **Long description height** (default 250px) — collapsed height of
  the full product description.
- **Short description height** (default 120px) — collapsed height of
  the short product description.
- **Expand / Collapse label** — text of the toggle while the
  description is collapsed or expanded.
- **Asset scope** — load the module's CSS and JavaScript on product
  pages only, or on the entire site when a sticky table of contents
  must reach headings inside collapsed descriptions on pages and
  posts.

Descriptions shorter than the configured height show no toggle.

## Installation

1. Download the latest release (or clone this repository) into
   `wp-content/plugins/wp-store-toolkit`.
2. Activate **WP Store Toolkit** in WordPress → Plugins.
3. Go to **WooCommerce → Store Toolkit** (or **Settings → Store Toolkit**
   if WooCommerce is not active) and enable the modules you want.

## Requirements

- WordPress 5.8 or later
- PHP 7.4 or later
- WooCommerce is required only by the WooCommerce-specific modules;
  each such module checks for it and stays inactive without it.

## Development

- All code, comments, commit messages and documentation are in
  English. Translations live in `languages/` (Vietnamese included).
- Code follows the WordPress Coding Standards; a PHPCS workflow is
  planned in the roadmap and will run on every push.
- Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

WP Store Toolkit is free software, licensed under the GNU General
Public License v2 or later — see [LICENSE](LICENSE). This is the
standard license for WordPress plugins and is fully compatible with
WordPress and WooCommerce.
