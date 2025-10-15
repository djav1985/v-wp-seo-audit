# Copilot instructions — v-wp-seo-audit

Purpose: give an AI coding agent the minimum, precise context needed to be productive in this repository.

Big picture
- Hybrid WordPress plugin that is being migrated away from Yii. The plugin now runs WordPress-native code for most flows but selectively boots Yii for legacy analysis/PDF paths. Key boundaries:
  - WP bootstrap & UI: `v-wp-seo-audit.php`, `includes/`, `templates/`, `assets/js/` (shortcode, enqueues, `_global` JS injection).
  - AJAX entrypoints: `includes/class-v-wpsa-ajax-handlers.php` (registers `v_wpsa_*` actions; always use `check_ajax_referer`).
  - Data & persistence: `includes/class-v-wpsa-db.php` (WP-native wrapper for `ca_*` tables) and legacy `protected/` (Yii models/vendors).
  - Report & PDF generation: `includes/class-v-wpsa-report-generator.php` (currently still calls into Yii controllers); TCPDF lives under `protected/extensions/tcpdf`.

Must-know conventions (follow exactly)
- All AJAX POST handlers must call `check_ajax_referer('v_wpsa_nonce','nonce')` and return `wp_send_json_success()` / `wp_send_json_error()`.
- Sanitize inputs with `wp_unslash()` then `sanitize_text_field()` (see `V_WPSA_Ajax_Handlers::validate_domain`).
- Preserve the JSON response shape expected by `assets/js/base.js`: `{ success: boolean, data: { ... } }`.
- Use `V_WPSA_DB` for DB access where possible (keeps `ca_` prefix and prepared statements patterns).
- Do not initialize Yii during normal page loads. The global `$v_wpsa_app` is only allowed to be created inside tightly-scoped AJAX handlers; avoid adding `require_once 'framework/yii.php'` in code paths executed for page views.
- When you must use legacy vendor classes, prefer explicit `require_once` of the specific files (this repo does that to avoid triggering Yii's autoloader; see `V_WPSA_DB::analyze_website`).

JS & DOM expectations
- `assets/js/base.js` is the single-page UX controller. It expects:
  - form inputs with `#domain` and `#submit` selectors;
  - container element `.v-wpsa-container` that may contain a `data-nonce` attribute;
  - download links with `.v-wpsa-download-pdf` and `data-domain` attributes; XHR expects a PDF blob (`application/pdf`) or a JSON error.
- If you change a template in `templates/`, update the selectors in `assets/js/base.js` or preserve the DOM IDs/classes.

Key integration points & gotchas
- TCPDF: bundled at `protected/extensions/tcpdf`. New WP-native PDF handlers should prefer composer/autoload or require the TCPDF library directly; watch remote images and memory/time limits.
- Logging & fatal-handling: AJAX handlers already register shutdown handlers and `error_log()` messages prefixed with `v-wpsa:` — use those for debugging.
- Avoid relying on `Website::model()` or other CActiveRecord classes for new code; instead add methods to `V_WPSA_DB` so migration can proceed incrementally.

Search heuristics (quick grep tokens)
- To locate remaining Yii dependencies search for: `Yii::`, `WebsitestatController`, `ParseController`, `Website::model(`, `createPdfFromHtml`, `framework/yii.php`, `CActiveRecord`, `CHtml::`.

Developer workflows (practical steps)
- Lint: run `phpcs .` (config in `phpcs.xml`) and fix with `phpcbf .` where safe. See `composer.json` dev deps for PHPCS & WPCS.
- Manual smoke test: install plugin, add `[v_wpsa]` shortcode to a page, open DevTools → Network → watch POSTs to `admin-ajax.php` for `v_wpsa_validate` and `v_wpsa_generate_report` and verify responses follow the JSON shape.
- Heavy tasks (PDF/report generation): prefer running via WP-CLI or feature-flagged AJAX endpoints (handlers already call `wp_raise_memory_limit('admin')` and `set_time_limit(0)`).

Fast migration checklist for AI tasks
1. Find all `Yii::` / controller usages and create per-file TODOs (search tokens above).
2. Replace `V_WPSA_Report_Generator::generate_html_report` data collection (currently creates `WebsitestatController`) with a WP-native data assembler that uses `V_WPSA_DB`.
3. Replace `generate_pdf_report` to render HTML → TCPDF in WP context and stream a blob (maintain current XHR contract used by `assets/js/base.js`).
4. Move model logic from `protected/models/` into `V_WPSA_DB` or small repository classes; keep `ca_*` schema.
5. Add feature flags (option or transient) to switch endpoints from legacy Yii → WP-native incrementally.

If anything above is ambiguous or you want the recommended first PR scaffolded (e.g., migrate `generate_report` handler first), tell me which endpoint to target and I will produce a precise migration plan.
