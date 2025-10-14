# WordPress-Native Classes for V-WP-SEO-Audit

This directory contains WordPress-native implementations that wrap or replace Yii framework components.

## File Overview

### Core Classes

#### `class-v-wp-seo-audit-db.php`
WordPress-native database operations class. Replaces Yii's CActiveRecord and CDbCommand.

**Purpose**: Provides `$wpdb`-based database access for plugin tables.

**Key Methods**:
- `get_website_by_domain()` - Get website record by domain
- `get_website_report_data()` - Get all related data for a report
- `delete_website()` - Delete website and all related records
- `upsert_pagespeed()` - Insert or update PageSpeed data

**Status**: ✅ Complete and functional

---

#### `class-v-wp-seo-audit-report.php`
WordPress-native wrapper for report generation.

**Purpose**: Provides WordPress interface for HTML and PDF report generation, wrapping Yii WebsitestatController.

**Key Methods**:
- `check_website_status()` - Check if domain exists and is fresh
- `generate_html()` - Generate HTML report
- `generate_pdf()` - Generate PDF report
- `get_website_data()` - Get all data for custom rendering

**Status**: ✅ Wrapper complete, delegates to Yii (Phase 3)

**Future Work**: Replace Yii rendering with WordPress templates.

---

#### `class-v-wp-seo-audit-analyzer.php`
WordPress-native wrapper for domain analysis.

**Purpose**: Provides WordPress interface for SEO audit analysis, intended to replace ParseCommand.

**Key Methods**:
- `analyze()` - Run domain analysis
- `for_insert()` - Factory for new domain analysis
- `for_update()` - Factory for updating existing domain
- `get_errors()` - Get error messages

**Status**: ⚠️ Wrapper complete but non-functional

**Issue**: ParseCommand was removed in Phase 1, analysis logic needs restoration or reimplementation.

**Future Work**: Implement WordPress-native analysis logic.

---

### Public API

#### `v-wp-seo-audit-api.php`
Public functions for theme/plugin developers.

**Purpose**: Provides convenient WordPress-style functions that wrap the classes above.

**Key Functions**:
- `v_wp_seo_audit_get_report( $domain )` - Get HTML report
- `v_wp_seo_audit_check_domain( $domain )` - Check if domain analyzed
- `v_wp_seo_audit_get_website_data( $domain )` - Get all domain data
- `v_wp_seo_audit_analyze_domain( $domain, $args )` - Trigger analysis
- `v_wp_seo_audit_delete_domain( $domain )` - Delete domain data

**Status**: ✅ Complete and documented

**Usage Example**:
```php
// Check if domain has been analyzed
$status = v_wp_seo_audit_check_domain( 'example.com' );
if ( $status['exists'] && $status['fresh'] ) {
    // Get the report
    $result = v_wp_seo_audit_get_report( 'example.com' );
    if ( $result['success'] ) {
        echo $result['html'];
    }
}
```

---

## Architecture

All classes use the **wrapper pattern** for incremental migration.

See `../PHASE3_MIGRATION.md` for complete architecture documentation.

## Standards

All files follow WordPress Coding Standards and Plugin Development Best Practices.
