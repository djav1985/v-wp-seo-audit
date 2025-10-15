# Migration Changes - WordPress Standards Implementation

This document describes the changes made to migrate the plugin to WordPress standards.

## Changes Made

### 1. Removed pagepeeker_proxy() Function
- **Removed**: `pagepeeker_proxy()` AJAX handler from `includes/class-v-wpsa-ajax-handlers.php`
- **Removed**: AJAX action registrations for `v_wpsa_pagepeeker`
- **Updated**: Comment in `v-wp-seo-audit.php` to reflect removal
- **Impact**: Thumbnails are now served directly from thum.io with local caching (already implemented in `V_WPSA_Thumbnail` class)

### 2. Implemented Composer Autoloading
- **Added**: Autoload configuration to `composer.json`
- **Replaced**: Manual `require_once` statements in `v-wp-seo-audit.php` with single autoloader require
- **Benefit**: Classes are loaded on-demand, improving performance and following modern PHP standards
- **Classes autoloaded**: All classes in `includes/class-v-wpsa-*.php`

### 3. Moved Unused Protected Files to old/ Folder
- **Moved to old/protected/**:
  - `protected/models/` (DownloadPdfForm.php, Website.php)
  - `protected/components/` (Controller.php, LinkPager.php, UrlManager.php, Utils.php, WebsiteThumbnail.php)
  - `protected/config/` (badwords.php, config.php, domain_restriction.php, main.php)
  - `protected/data/` (dump.sql)
- **Remaining in protected/**: 
  - `extensions/` (TCPDF library - still needed for PDF generation)
  - `vendors/` (AnalyticsFinder - still used in templates)
- **Updated**: File paths in code to reference `old/protected/` where legacy files are still needed

### 4. Enhanced PDF Report Caching
- **Added**: Cache check in `generate_pdf_report()` to return existing PDF if fresh
- **Cache duration**: Uses `v_wpsa_cache_time` filter (default 24 hours)
- **Benefit**: Reduces server load by avoiding unnecessary PDF regeneration
- **Response**: Now includes 'cached' flag to indicate if PDF was retrieved from cache

### 5. Updated .gitignore
- **Added**: `/vendor/` directory (Composer dependencies)
- **Added**: `/old/` directory (archived legacy files)
- **Benefit**: Keeps repository clean and excludes generated/archived files from version control

### 6. Error Handling Improvements
- **Fixed**: Empty catch blocks now include error_log statements
- **Standard**: All error logging uses 'v-wpsa:' prefix for easy filtering
- **Compliance**: Already using WordPress standards (wp_send_json_error, wp_send_json_success)

## Thumbnail Handling

The plugin already uses thum.io with local caching:
- **Service**: https://image.thum.io/
- **Cache location**: `{uploads}/seo-audit/thumbnails/`
- **Cache duration**: 7 days
- **Implementation**: `V_WPSA_Thumbnail` class in `includes/class-v-wpsa-thumbnail.php`

## Cleanup Cron

The cleanup cron (`v_wpsa_cleanup`) is already implemented and handles:
- **Database records**: Removes entries older than cache time
- **PDF files**: Removes both simplified and legacy nested PDF layouts
- **Thumbnails**: Removes cached thumbnail images
- **Frequency**: Daily via WordPress cron

## Usage

### Installing Dependencies
```bash
composer install
```

### Running Linter
```bash
./vendor/bin/phpcs --standard=phpcs.xml .
```

### Running Fixer
```bash
./vendor/bin/phpcbf --standard=phpcs.xml .
```

## Notes

- The autoloader must be regenerated after adding new classes: `composer dump-autoload`
- Legacy files in `old/` are kept for reference but excluded from version control
- TCPDF and vendor libraries remain in `protected/` as they are actively used
