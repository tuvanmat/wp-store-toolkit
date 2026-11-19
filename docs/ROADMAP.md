# Roadmap

WP Store Toolkit is developed in small, regular steps over 13 weeks.
Each module is ported from a small production plugin that already
runs on real stores, rewritten to the toolkit's standards (English
first, Settings API, capability checks, nonces, escaped output,
sanitised input) before it lands. Commits are small and follow the
Conventional Commits style.

## Weeks 1–4 — Framework and the Read More module

- **Week 1** — Public repository, GPL-2.0 license, English README,
  plugin bootstrap and `includes/class-plugin.php` (singleton).
- **Week 2** — Settings page via the WordPress Settings API, under
  the WooCommerce menu (with a fallback when WooCommerce is
  inactive), with per-module enable/disable checkboxes.
- **Week 3** — Module: Read More for Product Descriptions. Collapse
  heights (250px long / 120px short) and toggle labels move from
  hardcoded values into settings; new setting for asset scope
  (product pages only, or site-wide for sticky TOC compatibility).
- **Week 4** — Read More front-end fixes: consistent expanded state
  classes, no fixed pixel height after expansion, safe anchor lookup
  for UTF-8 heading ids, `window.scrollY`, colours as CSS variables.

## Weeks 5–8 — Quick Order module and mobile polish

- **Week 5** — Module: Quick Order Popup. No global state, Flatsome
  Quick View exclusion kept, legacy `[dathangnhanh]` shortcode kept
  working next to the new `[wpst_quick_order]` shortcode; default
  form ID, heading and button label become settings.
- **Week 6** — Assets load only on pages that actually render the
  quick order button; multiple distinct popups per page; custom form
  shortcodes (WPForms, Contact Form 7, Fluent Forms).
- **Week 7** — Popup transition fix (visibility/opacity instead of
  `display` toggling), dead code removal, colours as CSS variables,
  fewer `!important` rules.
- **Week 8** — Mobile and accessibility pass for both modules:
  scrollable popup content, close button kept in viewport, body
  scroll lock, Escape to close, semantic `<button>` triggers,
  `aria-expanded`, `role="dialog"`, `aria-modal`,
  `prefers-reduced-motion` support.

## Weeks 9–13 — Extensions, standards, v1.0.0

- **Week 9** — Optional auto-injected Quick Order button via
  WooCommerce single-product hooks; Read More passes labels,
  selectors and offsets to its script via `wp_localize_script()`
  instead of hardcoded selectors.
- **Week 10** — Read More height recalculation when lazy-loaded
  images finish or the viewport resizes (ResizeObserver).
- **Week 11** — Internationalisation audit, translation template
  (.pot) and Vietnamese translation (.po/.mo).
- **Week 12** — GitHub Actions: PHP syntax check and PHPCS
  (WordPress Coding Standards) on every push and pull request;
  release .zip build on version tags.
- **Week 13** — Complete README (installation, shortcode and hook
  reference, CSS variables), CHANGELOG.md, tagged release **v1.0.0**.

## Maintenance

After v1.0.0 the toolkit is maintained continuously: compatibility
checks with new WordPress/WooCommerce releases, small fixes, and new
modules when a real store need appears. Releases are tagged as they
ship.
