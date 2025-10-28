# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Added "Back" button at the top of report pages to return to main form
- Added Back button to error messages when report generation fails
- Added JavaScript function to automatically inject back button on all reports if missing

### Fixed
- Fixed shortcode AJAX loading stuck issue by ensuring jQuery and base.js are properly enqueued before inline script execution
- Fixed "Container not found" error by using wp_add_inline_script for proper script timing and execution order
- Fixed 504 Gateway Timeout errors during SEO analysis by increasing timeouts and resource limits
- Fixed delete report showing empty page by reloading main content via AJAX after deletion
- Fixed loader.gif failing to load in website thumbnails by using V_WPSA_PLUGIN_URL constant directly
- Fixed ERR_ADDRESS_INVALID errors in reports by replacing V_WPSA_Config::get_base_url() with V_WPSA_PLUGIN_URL constant for all image URLs
- Fixed PDF download 504 timeout by adding 5-minute timeout to XMLHttpRequest and proper timeout handler
- Added 60-second total timeout to cURL requests to prevent hanging on slow external APIs
- Increased server-side execution time limit to 5 minutes for analysis and PDF generation operations
- Increased client-side AJAX timeout to 5 minutes for report generation and PDF downloads
- Added better error handling and console logging for AJAX content loading
- Added 30-second timeout to prevent infinite loading states
- Ensured global JavaScript variables (_global) are available when shortcode loads via AJAX

### Changed
- Back button now removes hash from URL and reloads page for simpler, more reliable navigation
- Delete report now reloads the main form via AJAX instead of trying to show non-existent form elements
- Website thumbnail loader now uses V_WPSA_PLUGIN_URL constant for more reliable asset paths
- All report template images now use V_WPSA_PLUGIN_URL constant instead of V_WPSA_Config::get_base_url()
- Increased memory limit for analysis operations using wp_raise_memory_limit()
- Added CURLOPT_TIMEOUT to all cURL requests for better timeout control