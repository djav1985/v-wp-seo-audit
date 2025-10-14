# Copilot Instructions for V-WP-SEO-Audit Plugin

```instructions
# Copilot Instructions for V-WP-SEO-Audit Plugin

Purpose (one-liner)
- Convert and maintain a legacy Yii SEO-audit app running as a WordPress plugin. UI is provided via a shortcode; heavy work is performed by legacy Yii models/controllers invoked from WP AJAX handlers.

Quick architecture summary
- Shortcode `[v_wp_seo_audit]` renders the UI. Client JS ( `js/base.js` ) uses `admin-ajax.php` to call plugin actions implemented in `v-wp-seo-audit.php`.
- Server-side analysis logic lives under `protected/` and `framework/` (Yii). AJAX handlers bootstrap Yii as needed and call controller actions (e.g., `WebsitestatController::actionGenerateHTML`, `actionGeneratePDF`).
- Persistent data uses custom `ca_*` tables created on activation (`v_wp_seo_audit_activate`).

Key files to inspect first
- `v-wp-seo-audit.php` — plugin entry, shortcode registration, AJAX handlers, enqueued assets, activation/uninstall hooks, WordPress Cron cleanup, and native validation functions.
- `js/base.js` — primary frontend logic, validation, AJAX calls, and the PDF downloader (uses XHR blob download).
- `protected/views/` — UI templates (Yii view files). These files are rendered by Yii controllers and injected into the page via AJAX.
- `protected/commands`, `protected/controllers`, `protected/models` — legacy logic used by the plugin; find commands invoked via `yiic` or programmatically.
- `framework/` — Yii framework bootstrap and `yiic` console wrappers.
- `TESTING_GUIDE.md` — manual QA instructions; useful to reproduce user flows.
- `CONVERSION_NOTES.md` — tracks conversion progress from Yii to WordPress native (Phase 1 & 2 complete).
- `ARCHITECTURE.md` — detailed plugin architecture including new WordPress Cron and validation features.

Developer workflows and commands
- Linting: `vendor\bin\phpcbf .` (auto-fix), then `vendor\bin\phpcs .` (report). The repo includes `phpcs.xml` configured for WordPress rules.
- Manual test flow: follow `TESTING_GUIDE.md` to validate AJAX flows (validation → generate_report → download PDF).
- Local debugging: use browser devtools to inspect XHR to `admin-ajax.php`. Check that each AJAX POST includes `action`, fields, and `nonce`.

Project-specific patterns and gotchas
- All client-server communication must go through WP AJAX endpoints (no direct file access). Handlers use `check_ajax_referer('v_wp_seo_audit_nonce','nonce')`.
- AJAX responses are JSON shaped as `{ success: bool, data: { ... } }` (client expects this exact shape).
- The plugin bootstraps Yii on demand. Many handlers do `require_once framework/yii.php` and `Yii::createWebApplication($config)` — be mindful of side effects and performance when calling from high-traffic pages.
- Domain validation now uses WordPress-native functions (`v_wp_seo_audit_validate_domain()` and helpers) and does NOT require Yii initialization. Only report generation requires Yii.
- Views under `protected/views` are Yii templates using `CHtml::encode()` / `Yii::t()` — when updating views, prefer escaping with `CHtml::encode()` or WordPress equivalents if you migrate to WP templating.
- PDF download: frontend sends XHR POST expecting a PDF blob. If the page injects HTML via AJAX, the global inline nonce may be missing; the generate_report handler was updated to return a fresh nonce. When modifying AJAX flows, ensure a valid nonce is available to the client.
- WordPress Cron: A daily cleanup job (`v_wp_seo_audit_cleanup`) runs automatically to remove old PDFs, thumbnails, and database records. This is registered on plugin activation and unregistered on deactivation.

Security and maintenance notes
- Nonces: use `wp_create_nonce('v_wp_seo_audit_nonce')` on the page and `check_ajax_referer()` server-side. When returning server-injected HTML via AJAX, include a fresh nonce (e.g., response.data.nonce) or add `data-nonce` on the container.
- Protect CLI scripts: `protected/yiic.php`, `protected/yiic` and `command.php` are leftovers from the standalone app. Either keep them for cron/CLI usage and guard against HTTP access, or migrate jobs to WP-Cron/WP-CLI.
- Sensitive output: `command.php` prints PHP binary and paths — treat it as informational only and do not expose in production.
- WordPress Cron: The daily cleanup job is scheduled automatically. To test manually, use WP-CLI: `wp cron event run v_wp_seo_audit_daily_cleanup`
- Form validation: Domain validation is now pure WordPress code and doesn't require Yii. Use `v_wp_seo_audit_validate_domain()` for standalone validation needs.

Examples (patterns to follow)
- AJAX handler skeleton (server): see `v_wp_seo_audit_ajax_generate_report` in `v-wp-seo-audit.php` — bootstraps Yii, validates input with `sanitize_text_field( wp_unslash() )`, runs controller action, and returns `wp_send_json_success( array( 'html' => $content ) )`.
- Frontend XHR blob download (client): see `js/base.js` — uses XMLHttpRequest with `responseType='blob'` and tests Content-Type for `application/pdf` before triggering a download.

When to refactor vs keep legacy
- Keep Yii code if the migration cost is high and behavior is well-tested. Wrap any new WP-native code behind the same AJAX endpoints to avoid breaking clients.
- Prefer incremental migration: add WP-CLI commands and WP-Cron wrappers for the main tasks (parse, sitemap, clear PDF), then replace `exec('yiic ...')` call sites with in-process calls or WP-CLI triggers.
- Phase 2 conversions completed: WordPress Cron for cleanup (replaces old CLI clear command), WordPress-native domain validation (replaces Yii WebsiteForm validation)
- Remaining Yii dependencies: Report generation, PDF creation, database operations (ActiveRecord), and view rendering still use Yii framework.

Where to ask for clarification
- If a controller/action is unclear, look in `protected/controllers/` for the implementation. If behavior depends on Yii params, inspect `protected/config/main.php` and `protected/config/console.php`.

If anything here is unclear or you want a follow-up (e.g., add WP-CLI skeletons, migrate one command to WP-Cron, or harden public files), tell me which area to expand.
```