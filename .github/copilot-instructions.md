# Copilot Instructions for V-WP-SEO-AUDIT

## Project Overview

- **Type:** WordPress plugin for SEO analysis and reporting
- **Main Entry:** `v-wp-seo-audit.php`
- **Architecture:** Modular PHP, Composer autoloading, WordPress hooks/filters, template-based rendering

## Key Components

- **SEO Analysis:** `Webmaster/Source/`, `Webmaster/TagCloud/`, `Webmaster/Matrix/`, `Webmaster/Rates/`
- **Scoring Logic:** `Webmaster/Rates/RateProvider.php`, `Webmaster/Rates/rates.php`, `includes/class-v-wpsa-score-calculator.php`
- **Report Generation:** `includes/class-v-wpsa-report-generator.php`, `templates/report.php`, `templates/pdf.php`
- **AJAX/API:** `includes/class-v-wpsa-ajax-handlers.php`, `V_WPSA_external_generation()` (for AI/chatbot integration)
- **Config:** `config/main.php`, `config/config.php`
- **Assets:** `assets/` (CSS, JS, images for UI and PDF)

## Developer Workflows

- **Install:** Use Composer (`composer install`)
- **Activate:** Place in `wp-content/plugins/`, activate via WP admin
- **Lint:** Run PHPCS (`phpcs.xml` defines WordPress/PSR-12 rules)
- **Test:** PHPUnit tests in `/tests` (if present)
- **PDF Generation:** Uses TCPDF, now located at `tcpdf/` (not `tcpdf/tcpdf/`)
- **Build/Debug:** Most logic is PHP; use WordPress debug mode and direct PHP execution for isolated tests

## Project-Specific Patterns

- **Scoring:** All SEO scores are calculated via config-driven rules in `rates.php` and processed by `RateProvider.php`
- **Caching:** 24-hour cache for reports, managed in DB and via helper classes
- **Output Escaping:** All template output must use WordPress escaping functions (`esc_html`, `esc_attr`, etc.)
- **Logging:** Uses native WordPress `error_log()` controlled by `WP_DEBUG_LOG`; all calls include phpcs:ignore comments
- **Locale/Timezone:** Plugin reads locale via `get_locale()` and timezone via `wp_timezone()` directly from WordPress (not config params)
- **No Yii Framework:** Former Yii 1.x config params removed; plugin uses only native WordPress APIs and TCPDF (vendor references are test fixtures only)
- **AI Integration:** Use `V_WPSA_external_generation($domain, $report)` for programmatic report generation; returns JSON
- **PDF Path:** All references to TCPDF must use `tcpdf/` (not nested)
- **Domain Handling:** IDN support via `Webmaster/Utils/IDN.php`
- **CommonWords:** Language-specific stopword lists in `Webmaster/TagCloud/CommonWords/`

## Integration Points

- **Google PageSpeed:** `Webmaster/Google/PageSpeedInsights.php`
- **WordPress Hooks:** Plugin uses standard WP hooks for activation, AJAX, and shortcodes
- **External Calls:** AI/chatbot integrations should use the external generation function for best compatibility

## Conventions

- **Code Style:** PSR-12, WordPress standards, enforced by PHPCS
- **Documentation:** Update `README.md` and `CHANGELOG.md` for any notable changes
- **Testing:** Cover all new features and bug fixes with tests; place in `/tests`
- **Commit/PRs:** Clear messages, explain "why" and "what" in PRs

## Examples

- **Generate SEO report (AI):**
  ```php
  $json = V_WPSA_external_generation('example.com', true);
  $data = json_decode($json, true);
  ```
- **Add new scoring rule:** Edit `Webmaster/Rates/rates.php`, update logic in `RateProvider.php`
- **Escape output in templates:** Use `esc_html($var)` for all dynamic output

---

**If any section is unclear or missing, please provide feedback so this can be further refined for your team and AI agents.**
