# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-05-13

### Added

- `AdminAjax::endpoint()` gains an `$auth_only` parameter; when `true`, the `wp_ajax_nopriv_` hook is not registered, restricting the action to authenticated users only.
- `AdminAjax::verify_nonce()` static helper — verifies a nonce from `$_POST` and calls `wp_die()` on failure.
- `WPModelFactory::wrap_models()` and `WPModelFactory::apply_meta_fields()` as base implementations, eliminating duplicated code across all four factory subclasses. `wrap()` is now abstract.

### Changed

- `WPModelFactory::wrap()` is now `abstract` — subclasses that previously relied on the no-op base must implement it explicitly.
- `WPMeta` interface method signatures updated to use strict PHP 8.1 types (`?string $key`, union return types).
- `WPTaxonomyTerms` interface method signatures updated to use strict PHP 8.1 types.
- PHP 8.1 typed properties, parameter types, and return types added throughout the framework.
- All model classes (`GenericPostModel`, `UserModel`, `TaxonomyModel`, `TaxonomyTermModel`, `CommentModel`) annotated with `#[\AllowDynamicProperties]` for intentional dynamic property use.
- `gettype()` checks replaced with `instanceof` across all model classes and factories.
- `isset`/ternary patterns replaced with null coalescing (`??`) throughout.

### Fixed

- `CommentModelFactory`: fixed typo `$commend` → `$comment` (caused undefined variable on lookup failure); `create_comment()` now passes the `$comment` argument; `delete_comment()` now uses `comment_ID`; `update_comment()` captures the return value.
- `Config::get()` no longer overwrites `$default` before it is used; `glob()` calls fall back to `[]` instead of `false`.
- `Route`: `REQUEST_URI` sanitised via `sanitize_url(wp_unslash(...))` and parsed with `parse_url()` instead of `strtok()`; match check corrected from `!empty($matches)` to checking `$match` directly.
- `REST`: `extract()` calls removed and replaced with explicit key access; `_doing_it_wrong()` is now emitted when `permission_callback` is absent, discouraging open endpoints.
- `EmailView`: `$content_templater->subject` dynamic property assignment replaced — subject is now passed through `$content_params` so it is available inside the email body template.
- `Templater`: constructor bug where `$this->params['template_slug']` referenced a removed local variable; corrected to `$this->slug`.

### Security

- `REQUEST_URI` in `Route` sanitised with `sanitize_url(wp_unslash(...))` before use.
- `WP_ENV` in `Config` restricted to `[a-zA-Z0-9_-]` via `preg_replace` to prevent path traversal in config file lookups.
- `EmailView::shortcodes()` switched from `preg_replace` with `$1`/`$2` backreferences to `preg_replace_callback`, eliminating potential backreference injection via user-controlled param values.
- `REST` emits `_doing_it_wrong()` when `permission_callback` is not provided, discouraging inadvertently public endpoints.

## [1.0.0-alpha] - 2024-11-01

### Added

- Initial package release v1.0.0-alpha.


[unreleased]: https://github.com/TheCodeCompany/wpmvc/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/TheCodeCompany/wpmvc/compare/v1.0.0-alpha...v1.1.0
[1.0.0-alpha]: https://github.com/TheCodeCompany/wpmvc/releases/tag/v1.0.0-alpha
