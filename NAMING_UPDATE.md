# Naming Convention and Widget Migration Update

## Overview
This update addresses feedback to standardize naming conventions, restructure templates, and migrate widgets to WordPress-native implementations.

## Changes Made

### 1. Naming Convention Updates

#### Changed `v_wp_seo_audit` to `v_wpsa` (90+ occurrences)

**PHP Functions:**
- `v_wp_seo_audit_validate()` → `v_wpsa_validate()`
- `v_wp_seo_audit_generate_report()` → `v_wpsa_generate_report()`
- `v_wp_seo_audit_download_pdf()` → `v_wpsa_download_pdf()`
- All helper functions updated

**WordPress Hooks:**
- `v_wp_seo_audit_daily_cleanup` → `v_wpsa_daily_cleanup`
- `v_wp_seo_audit_validate` → `v_wpsa_validate`
- `v_wp_seo_audit_generate_report` → `v_wpsa_generate_report`
- `v_wp_seo_audit_download_pdf` → `v_wpsa_download_pdf`
- `v_wp_seo_audit_pagepeeker` → `v_wpsa_pagepeeker`

**Constants:**
- `V_WP_SEO_AUDIT_VERSION` → (kept for backward compatibility)
- `V_WP_SEO_AUDIT_PLUGIN_DIR` → (kept for backward compatibility)
- `V_WP_SEO_AUDIT_PLUGIN_URL` → (kept for backward compatibility)

**Global Variables:**
- `$v_wp_seo_audit_app` → `$v_wpsa_app` (internally, kept for Yii bridge compatibility)

#### Changed `v-wp-seo-audit` to `v-wpsa`

**CSS Classes:**
- `.v-wp-seo-audit-container` → `.v-wpsa-container`
- `.v-wp-seo-audit-view-report` → `.v-wpsa-view-report`
- `.v-wp-seo-audit-download-pdf` → `.v-wpsa-download-pdf`

**Text Domains:**
- `v-wp-seo-audit` → `v-wpsa`

**Plugin Header:**
- Text Domain: `v-wpsa`
- Plugin Name: V-WP-SEO-Audit (kept for display)

### 2. Template Restructuring

#### Renamed Files
- `templates/request-form.php` → `templates/main.php`

#### Updated References
All references to `request-form.php` updated in:
- Main plugin file (`v-wp-seo-audit.php`)
- Documentation files
- Code comments

### 3. Widget Migration

#### Created `templates/widgets.php`

WordPress-native widget functions to replace Yii widget classes:

**Main Functions:**
```php
v_wpsa_render_website_list( $args )
```
Renders a paginated list of analyzed websites with thumbnails and scores.

**Arguments:**
- `order` (string): Order by clause (default: 't.added DESC')
- `page` (int): Current page number (default: 1)
- `per_page` (int): Number of items per page (default: 12)

**Supporting Functions:**
```php
v_wpsa_render_website_list_template( $websites, $thumbnail_stack, $args, $total )
v_wpsa_render_pagination( $current_page, $per_page, $total )
v_wpsa_get_website_thumbnail_url( $args )
v_wpsa_crop_domain( $domain, $max_length = 25 )
```

#### Database Support

Added methods to `V_WPSA_DB` class:

```php
get_websites( $args )
```
Query websites with pagination and ordering.

**Arguments:**
- `order` (string): ORDER BY clause
- `limit` (int): Number of results
- `offset` (int): Pagination offset
- `columns` (array): Columns to select

```php
count_websites( $where = array() )
```
Count total number of websites with optional where conditions.

#### Integration

Updated main plugin file to load widgets:
```php
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'templates/widgets.php';
```

### 4. Deprecated Widget Files

The following Yii widget files are now replaced by WordPress-native templates:
- `protected/widgets/WebsiteList.php` → `templates/widgets.php`
- `protected/widgets/views/website_list.php` → Rendered by `v_wpsa_render_website_list_template()`

## Benefits

### Consistency
- ✅ Shorter, more readable function names
- ✅ Consistent naming across PHP, JavaScript, CSS
- ✅ Better alignment with WordPress coding standards

### Maintainability
- ✅ Widgets now use pure WordPress functions
- ✅ No Yii dependencies for widget rendering
- ✅ Easier to extend and customize

### Performance
- ✅ Native WordPress database queries
- ✅ No overhead from Yii widget factory
- ✅ Optimized pagination rendering

## Migration Guide

### For Developers Using Widget Functions

**Old Yii Way:**
```php
$this->widget('application.widgets.WebsiteList', array(
    'config' => array(
        'pagination' => array('pageSize' => 12)
    )
));
```

**New WordPress Way:**
```php
echo v_wpsa_render_website_list(array(
    'per_page' => 12,
    'page' => 1
));
```

### For Developers Extending Widgets

**Old Way:** Extend `CWidget` class
```php
class MyWidget extends CWidget {
    public function run() {
        // Yii-specific code
    }
}
```

**New Way:** Create WordPress function
```php
function my_widget_render( $args ) {
    // WordPress-native code
    // Use $wpdb, WordPress escaping, etc.
}
```

## Backward Compatibility

### What's Maintained
- ✅ All AJAX endpoints still work
- ✅ Database schema unchanged
- ✅ Plugin constants kept
- ✅ Global Yii app variable kept (for bridge compatibility)

### What's Changed
- ⚠️ Function names (internal only, no public API)
- ⚠️ CSS class names (update your custom CSS)
- ⚠️ Widget implementation (use new functions)

## Testing Checklist

### Widget Testing
- [ ] Website list displays correctly
- [ ] Pagination works
- [ ] Thumbnails load properly
- [ ] "Review" buttons work
- [ ] Responsive layout intact

### Naming Convention Testing
- [ ] AJAX validation works
- [ ] Report generation works
- [ ] PDF download works
- [ ] CSS styling intact
- [ ] JavaScript handlers work

## Files Modified

**Total files changed:** 30+

**Key files:**
- `v-wp-seo-audit.php` - Added widgets.php include
- `templates/main.php` - Renamed from request-form.php
- `templates/widgets.php` - NEW WordPress-native widgets
- `includes/class-v-wpsa-db.php` - Added get_websites() and count_websites()
- All PHP, JavaScript, CSS files - Updated naming conventions
- All documentation files - Updated references

## Commit Reference

**Commit Hash:** `2870268`

**Commit Message:** "Rename v_wp_seo_ to v_wpsa_, v-wp-seo- to v-wpsa-, request-form.php to main.php, and add widgets template"

## Support

For questions or issues with the new naming convention or widgets:
1. Check this document for migration examples
2. Review `templates/widgets.php` for implementation details
3. See `VIEWS_MIGRATION.md` for complete migration guide
