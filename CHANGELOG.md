# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Internal REST API for Domain Analysis**: New REST API endpoint at `v-wpsa/v1/report` for submitting domains for SEO analysis and receiving JSON reports with PDF links
- **Report Service Layer**: New `V_WPSA_Report_Service` class that provides a unified interface for report generation across AJAX handlers, REST API, and direct function calls
- **PHP Helper Function**: New `v_wpsa_get_report_data($domain, $args)` function for programmatic access to report data from other plugins or AI integrations
- **REST API Controller**: New `V_WPSA_Rest_API` class to handle REST API endpoints with admin authentication (`manage_options` capability by default)

### Changed
- **Refactored AJAX Handler**: Updated `V_WPSA_Ajax_Handlers::generate_report()` to use the new `V_WPSA_Report_Service` for consistency and code reuse
- **Report Data Structure**: Report data now includes structured JSON payload with domain, score, timestamps, PDF URL, and comprehensive report sections
- Reports generated via REST API or helper function exclude non-serializable objects (e.g., RateProvider) for JSON compatibility

### Technical Details
- All reports (AJAX, REST, direct calls) now go through the same validation and analysis pipeline
- REST API requires `manage_options` capability (admin access) by default, but can be customized via the `v_wpsa_rest_api_capability` filter
- PDF files are automatically generated and made accessible via a publicly accessible URL
- Cached reports are served when available (unless force flag is set)
- Full backward compatibility maintained with existing AJAX endpoints

## [1.0.0] - Previous Releases
See README.md for earlier version history and migration notes.
