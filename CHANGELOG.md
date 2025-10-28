# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Fixed
- Fixed shortcode AJAX loading stuck issue by ensuring jQuery and base.js are properly enqueued before inline script execution
- Fixed "Container not found" error by using wp_add_inline_script for proper script timing and execution order
- Added better error handling and console logging for AJAX content loading
- Added 30-second timeout to prevent infinite loading states
- Ensured global JavaScript variables (_global) are available when shortcode loads via AJAX