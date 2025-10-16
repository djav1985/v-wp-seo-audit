
# Copilot instructions — v-wp-seo-audit

Purpose: Give AI coding agents the essential, actionable context to be productive in this hybrid WordPress plugin migrated from Yii.

## Big Picture Architecture
- **WP-native boundaries:**
  - Bootstrap/UI: `v-wp-seo-audit.php`, `includes/`, `templates/`, `assets/js/`
  - AJAX: `includes/class-v-wpsa-ajax-handlers.php` (registers `v_wpsa_*` actions)
  - Data: `includes/class-v-wpsa-db.php` (WP wrapper for `ca_*` tables)
- **Legacy boundaries:**
  - Analysis/PDF: `protected/` (Yii models/controllers), TCPDF at `protected/extensions/tcpdf`

## Critical Conventions
- All AJAX POST handlers **must** call `check_ajax_referer('v_wpsa_nonce','nonce')` and return `wp_send_json_success()` / `wp_send_json_error()`.
- Sanitize all inputs: `wp_unslash()` then `sanitize_text_field()` (see `V_WPSA_Ajax_Handlers::validate_domain`).
- JSON response shape for JS: `{ success: boolean, data: { ... } }` (see `assets/js/base.js`).
- Use `V_WPSA_DB` for DB access (preserves `ca_*` prefix, uses prepared statements).
- Never initialize Yii except inside tightly-scoped AJAX handlers. Avoid `require_once 'framework/yii.php'` in page view code paths.
- For legacy vendor classes, use explicit `require_once` (see `V_WPSA_DB::analyze_website`).

## JS & DOM Patterns
- `assets/js/base.js` expects:
  - Form: `#domain`, `#submit`
  - Container: `.v-wpsa-container` (may have `data-nonce`)
  - Download: `.v-wpsa-download-pdf`, `data-domain` (XHR expects PDF blob or JSON error)
- If you change templates, update selectors in `assets/js/base.js` or preserve DOM IDs/classes.

## Integration Points & Gotchas
- **TCPDF**: Use bundled at `protected/extensions/tcpdf`. Prefer composer/autoload for new WP-native PDF handlers. Watch remote images, memory/time limits.
- **Logging/fatal handling**: AJAX handlers register shutdown handlers and use `error_log()` prefixed with `v-wpsa:`.
- **Migration**: Avoid `Website::model()`/CActiveRecord for new code; add logic to `V_WPSA_DB` for incremental migration.

## Developer Workflows
- **Lint:** `phpcs .` (config: `phpcs.xml`), auto-fix with `phpcbf .`. See `composer.json` dev deps for PHPCS/WPCS.
- **Manual smoke test:** Install plugin, add `[v_wpsa]` shortcode, use DevTools → Network to watch POSTs to `admin-ajax.php` for `v_wpsa_validate`/`v_wpsa_generate_report`. Verify JSON response shape.
- **Heavy tasks:** Run via WP-CLI or feature-flagged AJAX endpoints. Handlers call `wp_raise_memory_limit('admin')` and `set_time_limit(0)`.

## Migration Checklist (for AI agents)
1. Find all Yii/controller usages (`Yii::`, `WebsitestatController`, etc.) and create per-file TODOs.
2. Replace `V_WPSA_Report_Generator::generate_html_report` data collection with WP-native assembler using `V_WPSA_DB`.
3. Replace `generate_pdf_report` to render HTML → TCPDF in WP context and stream a blob (maintain XHR contract for JS).
4. Move model logic from `protected/models/` into `V_WPSA_DB` or small repository classes (keep `ca_*` schema).
5. Add feature flags (option/transient) to switch endpoints from legacy Yii → WP-native incrementally.

## Example: Force Update Workflow
- JS sets `force=1` on update button click
- AJAX handler deletes cached data, triggers fresh analysis
- New report HTML replaces old report in DOM

## Search Heuristics
- To locate remaining Yii dependencies, grep for: `Yii::`, `WebsitestatController`, `ParseController`, `Website::model(`, `createPdfFromHtml`, `framework/yii.php`, `CActiveRecord`, `CHtml::`

If anything above is unclear or you need a recommended PR scaffold (e.g., migrate `generate_report` handler), ask for a precise migration plan.
