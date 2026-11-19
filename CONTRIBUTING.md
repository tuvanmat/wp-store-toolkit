# Contributing

Thanks for your interest in WP Store Toolkit!

## Ground rules

- **English first.** Code, comments, commit messages, issues and
  documentation are written in English. User-facing strings are
  wrapped in translation functions with the `wp-store-toolkit` text
  domain so they can be translated (Vietnamese ships in `languages/`).
- **One module, one job.** New features belong in a module. A module
  must work — and cost nothing — when disabled: disabled modules are
  never instantiated.
- **WordPress standards.** Follow the WordPress Coding Standards,
  check capabilities (`current_user_can`), use nonces for forms, and
  sanitise input / escape output.

## Workflow

1. Open an issue describing the change before starting larger work.
2. Work on a branch, keep commits small and messages descriptive.
3. Open a pull request; reference the issue it closes.

## Reporting bugs

Please include your WordPress, WooCommerce (if relevant) and PHP
versions, the enabled modules, and steps to reproduce.
