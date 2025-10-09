# Copilot Instructions for V-WP-SEO-Audit Plugin

## Project Overview
- This is a WordPress plugin for SEO auditing. Users submit a domain via a form; the plugin analyzes the domain and displays a report. If the domain exists in the DB and is not expired, the cached report is shown.
- The codebase was converted from a standalone Yii PHP app and now runs solely as a WordPress plugin. Direct file access is forbidden; all logic is routed through WordPress hooks and AJAX endpoints.

## Key Architecture & Data Flow
- **Entry Point:** Use the `[v_wp_seo_audit]` shortcode to render the audit form on any page/post.
- **AJAX Endpoints:** All client-server communication uses WordPress's `admin-ajax.php`:
  - `v_wp_seo_audit_validate`: Validates domain input
  - `v_wp_seo_audit_generate_report`: Generates and returns the SEO report
  - `v_wp_seo_audit_pagepeeker`: (Legacy) Thumbnail proxy, now disabled; uses thum.io for screenshots
- **Security:** All AJAX handlers require nonce verification (`check_ajax_referer`). Inputs are sanitized using WordPress functions.
- **Database:** Custom tables (e.g., `ca_website`, `ca_content`, etc.) store analysis results. If a domain is already present and not expired, the cached result is used.
- **Framework:** Relies on Yii (see `framework/` and `protected/` folders) for legacy model/controller logic. Do not bypass WordPress hooks.

## Developer Workflows
- **Linting:** Use PHP_CodeSniffer (`phpcs .`) with rules in `phpcs.xml` (PSR12 + WordPress standards, assets/static excluded).
- **Auto-fix:** Use `phpcbf .` to auto-correct coding standard violations.
- **Manual Testing:** No automated tests. Use `TESTING_GUIDE.md` for step-by-step manual QA in a WordPress environment.
- **Debugging:** Check browser console for AJAX requests; ensure all go to `admin-ajax.php` (never direct PHP files).

## Project-Specific Patterns
- **Never access PHP files directly** (e.g., `index.php`). All logic must be routed via WordPress AJAX and hooks.
- **AJAX responses** must be JSON and include `success` and `data` keys for client-side handling.
- **Shortcode is the only supported entry point** for the UI.
- **Legacy Yii code** is only invoked via WordPress AJAX handlers, not directly.
- **Thumbnails:** Use thum.io for screenshots; PagePeeker proxy is disabled by default.

## Key Files & Directories
- `v-wp-seo-audit.php`: Main plugin logic, AJAX handlers, shortcode registration
- `index.php`: Exists only for WordPress plugin structure; shows error if accessed directly
- `js/base.js`: Handles form submission, client-side validation, AJAX requests
- `framework/`, `protected/`: Yii framework and legacy app code
- `TESTING_GUIDE.md`: Manual QA steps
- `phpcs.xml`: Coding standards

## Example: AJAX Workflow
1. User submits domain via form
2. JS validates and sends AJAX to `admin-ajax.php?action=v_wp_seo_audit_validate`
3. If valid, JS sends AJAX to `admin-ajax.php?action=v_wp_seo_audit_generate_report`
4. Server returns HTML report in JSON; JS injects into page

## Integration Points
- **WordPress hooks**: All plugin logic must use hooks and AJAX endpoints
- **Yii framework**: Used for legacy model/controller logic, but only via WordPress AJAX
- **Thum.io**: Used for domain screenshots

---

For unclear or incomplete sections, please provide feedback to improve these instructions.