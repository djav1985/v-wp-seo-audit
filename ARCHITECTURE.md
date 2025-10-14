# Plugin Architecture Overview

This document provides an overview of the V-WP-SEO-Audit plugin architecture after conversion from a standalone Yii application to a WordPress plugin.

## Current Architecture (Hybrid WordPress + Yii)

### Layer 1: WordPress Integration Layer

**Entry Points:**
- `v-wp-seo-audit.php` - Main plugin file
  - Plugin registration and metadata
  - WordPress hook registration
  - AJAX endpoint handlers
  - Database table creation
  - Asset enqueuing

**WordPress Hooks Used:**
1. `wp` - Initialize Yii app when shortcode detected
2. `wp_enqueue_scripts` - Load CSS/JS assets
3. `wp_ajax_v_wp_seo_audit_validate` - Domain validation
4. `wp_ajax_nopriv_v_wp_seo_audit_validate` - Public validation
5. `wp_ajax_v_wp_seo_audit_generate_report` - Report generation
6. `wp_ajax_nopriv_v_wp_seo_audit_generate_report` - Public report
7. `wp_ajax_v_wp_seo_audit_pagepeeker` - Thumbnail proxy (deprecated)
8. `wp_ajax_nopriv_v_wp_seo_audit_pagepeeker` - Public proxy (deprecated)
9. Shortcode: `[v_wp_seo_audit]` - Display form

### Layer 2: Yii Framework Layer

**Directory Structure:**
```
framework/           - Yii 1.1.29 framework files
protected/
  ├── components/    - Yii application components
  ├── config/        - Configuration files (main.php)
  ├── controllers/   - MVC controllers
  │   ├── SiteController.php         - Main site controller
  │   ├── WebsitestatController.php  - Report generation
  │   ├── ParseController.php        - Domain parsing (legacy routes)
  │   └── PagePeekerProxyController.php - Thumbnail proxy (deprecated)
  ├── models/        - Data models
  │   ├── Website.php           - Website ActiveRecord model
  │   ├── WebsiteForm.php       - Domain validation form
  │   └── DownloadPdfForm.php   - PDF generation form
  ├── vendors/       - Third-party libraries
  ├── views/         - View templates
  ├── widgets/       - Reusable UI widgets
  └── extensions/    - Yii extensions (TCPDF, etc.)
```

**Yii Controllers:**
- `SiteController` - Renders the audit form (used by shortcode)
- `WebsitestatController` - Generates and displays audit reports
- `ParseController` - Legacy domain parsing routes (minimal usage)
- `PagePeekerProxyController` - Deprecated thumbnail proxy

### Layer 3: Data Layer

**Database Tables (WordPress DB):**
- `wp_ca_website` - Main website records (domain, IP, scores)
- `wp_ca_content` - Content analysis (headings, images, deprecated tags)
- `wp_ca_document` - Document structure (doctype, lang, charset)
- `wp_ca_issetobject` - Object existence checks
- `wp_ca_links` - Link analysis (internal, external, broken)
- `wp_ca_metatags` - Meta tags analysis
- `wp_ca_misc` - Miscellaneous data (sitemaps, robots.txt)
- `wp_ca_pagespeed` - PageSpeed Insights data
- `wp_ca_w3c` - W3C validation results
- `wp_ca_cloud` - Word cloud data

**Cache Strategy:**
- Reports cached in database with expiration flag
- Cache checked before running new analysis
- Expiration time configurable in config

## Data Flow

### 1. Shortcode Rendering Flow

```
User visits page with [v_wp_seo_audit]
    ↓
WordPress processes shortcode
    ↓
v_wp_seo_audit_shortcode() function called
    ↓
Check if Yii app initialized (v_wp_seo_audit_init)
    ↓
Initialize Yii application (Yii::createWebApplication)
    ↓
Configure Yii app for WordPress context
    ↓
Run Yii app ($v_wp_seo_audit_app->run())
    ↓
Yii routes to SiteController::actionIndex()
    ↓
Render form view (protected/views/site/index.php)
    ↓
Return HTML to WordPress
    ↓
WordPress displays content
```

### 2. Domain Analysis Flow

```
User submits domain form
    ↓
JavaScript (js/base.js) captures submit
    ↓
Client-side validation
    ↓
AJAX to admin-ajax.php?action=v_wp_seo_audit_validate
    ↓
WordPress calls v_wp_seo_audit_ajax_validate_domain()
    ↓
Create WebsiteForm model
    ↓
Validate domain format
    ↓
Return validation result
    ↓
If valid, AJAX to admin-ajax.php?action=v_wp_seo_audit_generate_report
    ↓
WordPress calls v_wp_seo_audit_ajax_generate_report()
    ↓
Create WebsiteForm model
    ↓
WebsiteForm::validate() calls tryToAnalyse()
    ↓
Check if domain exists in database
    ↓
If exists and not expired: Return cached data
If new or expired: Perform new analysis
    ↓
New analysis flow:
    - Fetch domain HTML
    - Parse meta tags, headings, images
    - Analyze links (internal, external)
    - Check W3C validation
    - Generate SEO score
    - Store in database tables
    ↓
Render report view (protected/views/websitestat/index.php)
    ↓
Return HTML to JavaScript
    ↓
JavaScript injects report into page
    ↓
Scroll to report
```

## Key Components

### Configuration

**Main Config:** `protected/config/main.php`
- Database connection (uses WordPress DB constants)
- Application parameters
- Component configuration
- URL manager settings

**Parameters:**
```php
'app.name' => 'V-WP-SEO-Audit'
'app.timezone' => 'UTC'
'app.base_url' => WordPress plugin URL
'cache.timeout' => Cache expiration time
'googleApiKey' => PageSpeed Insights API key
```

### Models

**Website Model (ActiveRecord):**
- Represents website records in database
- Handles CRUD operations
- Relationships with other tables

**WebsiteForm Model:**
- Validates domain input
- Triggers domain analysis
- Handles both new and cached reports

**DownloadPdfForm Model:**
- Generates PDF reports
- Uses TCPDF library
- Formats report for print

### Vendors (Third-party Libraries)

Located in `protected/vendors/Webmaster/`:
- `Source/` - Website fetching and parsing
- `TagCloud/` - Word cloud generation
- `Utils/` - Utility functions (IDN conversion, etc.)
- `Matrix/` - Data matrix operations
- `Rates/` - Scoring algorithms
- `Google/` - PageSpeed Insights integration

### Assets

**CSS Files:**
- `css/bootstrap.min.css` - Bootstrap framework
- `css/fontawesome.min.css` - Font Awesome icons
- `css/app.css` - Custom plugin styles

**JavaScript Files:**
- `js/jquery.flot.js` - Charting library
- `js/jquery.flot.pie.js` - Pie chart plugin
- `js/bootstrap.bundle.min.js` - Bootstrap JS
- `js/base.js` - Main plugin JavaScript (AJAX, validation)

## Security Measures

### WordPress Layer
1. **Nonce Verification**: All AJAX requests verified with `check_ajax_referer()`
2. **Input Sanitization**: Using `sanitize_text_field()`, `wp_unslash()`
3. **Output Escaping**: Using `esc_html()`, `esc_url()`, `esc_js()`
4. **Database Queries**: Using WordPress `$wpdb` prepared statements

### Yii Layer
1. **Input Validation**: Yii validation rules in models
2. **Output Encoding**: Using `CHtml::encode()`, `Yii::t()` with encoding
3. **Database Queries**: Yii ActiveRecord (parameterized queries)
4. **CSRF Protection**: Yii CSRF tokens (though bypassed for WordPress AJAX)

## Performance Optimizations

### Caching
- **Database Caching**: Reports cached with expiration flag
- **Yii Cache**: File cache for framework operations
- **Asset Versioning**: Cache busting with plugin version number

### Lazy Loading
- Yii app only initialized when shortcode present on page
- Assets only enqueued on pages with shortcode
- Database queries optimized with indexes

### Asynchronous Operations
- Domain analysis runs via AJAX (doesn't block page load)
- Progress indicator shown during analysis
- Results loaded without page refresh

## Removed Components (After Cleanup)

### CLI/Console Layer (Removed)
- ❌ `protected/yiic.php` - Console bootstrap
- ❌ `protected/yiic` - Unix executable
- ❌ `protected/yiic.bat` - Windows executable
- ❌ `protected/config/console.php` - Console config
- ❌ `protected/commands/` - CLI commands directory

### Management Interface (Removed)
- ❌ `protected/controllers/ManageController.php` - Backend management
- ❌ Direct route access to `/manage/clear` endpoints

### Utilities (Removed)
- ❌ `command.php` - Cron job builder
- ❌ `requirements.php` - Yii requirements checker
- ❌ `.htaccess` - Standalone app routing rules

## Future Enhancement Opportunities

### Phase 1: Native WordPress Forms
**Current:** Yii forms with custom validation
**Future:** WordPress form API or popular form plugins
**Benefits:**
- Better integration with WordPress ecosystem
- Familiar interface for WordPress users
- Reduced dependency on Yii

### Phase 2: Custom Post Types
**Current:** Custom database tables (wp_ca_*)
**Future:** WordPress custom post types for reports
**Benefits:**
- Native WordPress data structure
- Built-in UI in WordPress admin
- Use WordPress search and filtering
- Better backup/export integration

### Phase 3: REST API
**Current:** Custom AJAX handlers
**Future:** WordPress REST API endpoints
**Benefits:**
- Standard WordPress API patterns
- Better documentation
- OAuth authentication support
- External application integration

### Phase 4: Admin Dashboard
**Current:** Front-end only (shortcode)
**Future:** WordPress admin page for reports
**Benefits:**
- Manage all reports from admin
- User permission controls
- Bulk operations
- Export capabilities

### Phase 5: Scheduled Tasks
**Current:** No scheduled tasks (removed with CLI)
**Future:** WordPress Cron for maintenance
**Benefits:**
- Automatic cache cleanup
- Periodic report updates
- Scheduled email reports

### Phase 6: Blocks Editor Support
**Current:** Classic shortcode only
**Future:** Gutenberg block
**Benefits:**
- Modern WordPress editing experience
- Live preview in editor
- Block-specific settings

## Development Guidelines

### Adding New Features

1. **WordPress-First Approach**: New features should use WordPress APIs
2. **Security**: Always validate input, sanitize, escape output
3. **Coding Standards**: Follow WordPress and PSR-12 standards
4. **Documentation**: Update this file and relevant docs
5. **Testing**: Add tests to TESTING_GUIDE.md

### Modifying Existing Code

1. **Minimal Changes**: Make smallest possible modifications
2. **Backward Compatibility**: Don't break existing functionality
3. **Test Thoroughly**: Run full test suite before commit
4. **Update Docs**: Reflect changes in documentation

### Deprecating Features

1. **Document Reason**: Add to CONVERSION_NOTES.md
2. **Graceful Degradation**: Return helpful error messages
3. **Migration Path**: Provide alternative if available

## Dependencies

### Required
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Yii Framework 1.1.29 (bundled)

### Optional
- Google PageSpeed Insights API key (for PageSpeed reports)
- Thum.io account (for domain thumbnails)

### PHP Extensions
- curl - HTTP requests
- json - JSON parsing
- mbstring - Multibyte string handling
- mysqli - Database connection
- openssl - Secure connections

## File Size Overview

```
Total plugin size: ~15 MB
- framework/: ~8 MB (Yii framework)
- protected/extensions/: ~5 MB (TCPDF)
- protected/vendors/: ~500 KB (Custom libraries)
- css/: ~300 KB (Bootstrap, FontAwesome)
- js/: ~200 KB (jQuery plugins)
- img/: ~150 KB (Images, icons)
- Core files: ~50 KB
```

## Browser Compatibility

- Chrome 90+ ✅
- Firefox 88+ ✅
- Safari 14+ ✅
- Edge 90+ ✅
- IE 11 ⚠️ (Limited support)

## Known Issues

1. **PagePeeker Proxy**: Deprecated, using Thum.io instead
2. **Direct Yii Routes**: Some legacy routes still accessible (not documented)
3. **Large Framework**: Yii framework adds significant size
4. **Mixed Architecture**: WordPress + Yii creates complexity

## Support

For issues and questions:
- GitHub Issues: [Repository URL]
- Documentation: See README.md, CONVERSION_NOTES.md, TESTING_GUIDE.md
- Code Review: See SECURITY_REVIEW.md, PHPCS_CLEANUP_SUMMARY.md

---

**Last Updated**: 2025-10-14
**Plugin Version**: 1.0.0
**Architecture Version**: Hybrid WordPress + Yii v1
