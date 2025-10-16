# v-wpsa WordPress Plugin

WordPress SEO Audit plugin - Analyze your website's SEO performance

## Description

This plugin provides comprehensive SEO audit functionality for WordPress. It analyzes websites for SEO issues, performance, meta tags, links, and more.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/v-wpsa` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add the shortcode `[v_wpsa]` to any page or post where you want to display the SEO audit tool

## Usage

To display the SEO audit tool on your website, simply add the following shortcode to any page or post:

```
[v_wpsa]
```

The plugin will display on the front-end where the shortcode is placed.

## Features

- Website SEO analysis
- Meta tags verification
- Link extraction and analysis
- Content analysis
- Performance testing
- PageSpeed Insights integration
- **Force update / re-analysis of reports** - Remove cached data and generate fresh reports
- Front-end display via shortcode
- AJAX-based form submission for seamless user experience
- Client-side form validation
- Dynamic content updates without page redirects
- **REST API for programmatic access** - Internal API for AI chatbots and integrations
- **PHP helper function** - Direct function calls for custom integrations

## Technical Architecture

### AJAX Implementation

This plugin uses WordPress's admin-ajax.php for all AJAX communication instead of direct PHP file access. This provides:

1. **Better Security**: All requests go through WordPress's authentication system
2. **WordPress Integration**: Leverages WordPress hooks and filters
3. **No Direct File Access**: Eliminates "Direct access not allowed" errors
4. **Single Page Application Feel**: Content updates without page reloads

### AJAX Endpoints

The plugin registers the following AJAX actions:

1. **v_wpsa_validate**: Validates domain input
   - Action: `v_wpsa_validate`
   - Method: POST
   - Parameters: `domain`, `nonce`
   - Response: Success with validated domain or error message

2. **v_wpsa_generate_report**: Generates SEO audit report
   - Action: `v_wpsa_generate_report`
   - Method: POST
   - Parameters: `domain`, `nonce`
   - Response: HTML content of the audit report

3. **v_wpsa_download_pdf**: Downloads PDF version of report
   - Action: `v_wpsa_download_pdf`
   - Method: POST
   - Parameters: `domain`, `nonce`
   - Response: PDF file download

**Note:** The `v_wpsa_generate_report` endpoint supports a `force` parameter (set to `'1'`) to force deletion of cached data and re-analysis. This is used by the UPDATE button in generated reports.

### REST API Endpoints

The plugin provides a REST API for programmatic access to SEO audit reports. This is useful for AI chatbots, custom integrations, and automation tools.

#### POST /wp-json/v-wpsa/v1/report

Generate an SEO audit report for a domain.

**Authentication:** Requires admin privileges (`manage_options` capability by default). The required capability can be customized using the `v_wpsa_rest_api_capability` filter.

**Request Body:**
```json
{
  "domain": "example.com",
  "force": false
}
```

**Parameters:**
- `domain` (string, required): Domain to analyze
- `force` (boolean, optional): Force re-analysis even if cached data exists. Default: `false`

**Response (Success):**
```json
{
  "domain": "example.com",
  "idn": "example.com",
  "score": 85,
  "cached": false,
  "pdf_url": "https://yoursite.com/wp-content/uploads/seo-audit/pdf/example.com.pdf",
  "pdf_cached": false,
  "generated": {
    "time": "2 minutes ago",
    "seconds": 120,
    "A": "PM",
    "Y": "2025",
    "M": "Oct",
    "d": "16",
    "H": "14",
    "i": "30"
  },
  "report": {
    "website": {
      "id": 123,
      "score": 85,
      "score_breakdown": {...}
    },
    "content": {...},
    "document": {...},
    "links": {...},
    "meta": {...},
    "w3c": {...},
    "cloud": {...},
    "misc": {...},
    "thumbnail": {...}
  }
}
```

**Response (Error):**
```json
{
  "code": "invalid_domain",
  "message": "Invalid domain format",
  "data": {
    "status": 400
  }
}
```

**Example Usage:**

```bash
# Using curl
curl -X POST https://yoursite.com/wp-json/v-wpsa/v1/report \
  -H "Content-Type: application/json" \
  -u admin:your-application-password \
  -d '{"domain":"example.com"}'

# Force re-analysis
curl -X POST https://yoursite.com/wp-json/v-wpsa/v1/report \
  -H "Content-Type: application/json" \
  -u admin:your-application-password \
  -d '{"domain":"example.com","force":true}'
```

### PHP Helper Function

For custom integrations within WordPress (other plugins, themes, or custom code), you can use the `v_wpsa_get_report_data()` helper function:

```php
/**
 * Get SEO audit report data for a domain.
 *
 * @param string $domain Domain to analyze.
 * @param array  $args   Optional arguments.
 *                       - 'force' (bool): Force re-analysis. Default: false.
 * @return array|WP_Error Report data array or WP_Error on failure.
 */
$report = v_wpsa_get_report_data( 'example.com' );

if ( is_wp_error( $report ) ) {
    // Handle error
    echo 'Error: ' . $report->get_error_message();
} else {
    // Use report data
    echo 'Score: ' . $report['score'];
    echo 'PDF URL: ' . $report['pdf_url'];
    
    // Access detailed report sections
    $meta_tags = $report['report']['meta'];
    $links = $report['report']['links'];
}

// Force re-analysis
$fresh_report = v_wpsa_get_report_data( 'example.com', array( 'force' => true ) );
```

**Use Cases:**
- AI chatbot integrations
- Custom admin dashboards
- Automated reporting systems
- Bulk domain analysis scripts
- Integration with CRM or project management tools

### Form Workflow

1. User enters domain in the form
2. Client-side validation checks for valid domain format
3. AJAX request to `v_wpsa_validate` validates the domain
4. If valid, AJAX request to `v_wpsa_generate_report` generates the report
5. Report HTML is injected into the page without reload
6. Page scrolls to the report section automatically

### Update Button Workflow

1. User clicks UPDATE button on an existing report
2. JavaScript fills domain input and sets force-update flag
3. AJAX request to `v_wpsa_generate_report` with `force=1` parameter
4. Server deletes all cached data (database records, PDFs, thumbnails)
5. Fresh analysis is performed
6. New report HTML replaces old report on the page
7. Page scrolls to the updated report

For detailed information about the update button functionality, see [UPDATE_BUTTON_FUNCTIONALITY.md](UPDATE_BUTTON_FUNCTIONALITY.md).

## PHP_CodeSniffer (phpcs) Setup

This project uses [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for code linting.

### Installation

Install globally (recommended):

```
composer global require squizlabs/php_codesniffer
```

Or install locally in your project:

```
composer require --dev squizlabs/php_codesniffer
```

### Configuration

Rules are defined in `phpcs.xml` in the project root. It uses PSR12 and WordPress standards, excluding asset and static folders.

### Usage

Run phpcs from the project root:

```
phpcs .
```

To auto-fix issues (where possible):

```
phpcbf .
```

### VS Code Integration

Install the following extension for linting in VS Code:

```vscode-extensions
wongjn.php-sniffer
```

Configure the extension to use your global or local phpcs path if needed.

## Plugin Conversion

This plugin was converted from a standalone Yii PHP application to a WordPress plugin. The conversion is happening in phases to gradually reduce Yii dependencies while maintaining functionality.

### Phase 4 (October 2025) - Autoloader Fix & Utils Migration

**Critical Bug Fix:**
- ✅ Fixed Helper.php autoloader issue that prevented form submission
- ✅ Changed class loading order to avoid triggering Yii autoloader prematurely

**WordPress-Native Conversions:**
- ✅ `Utils::deletePdf()` → `V_WPSA_Helpers::delete_pdf()` (uses `wp_upload_dir()`, `wp_delete_file()`)
- ✅ `Utils::getLocalConfigIfExists()` → `V_WPSA_Helpers::load_config_file()` (uses plugin constants)
- ✅ `Yii::app()->params['analyzer.cache_time']` → WordPress filter `v_wpsa_cache_time`

**Files Modified:**
- `v-wpsa.php` - Added 3 new WordPress-native helper functions
- `protected/models/WebsiteForm.php` - Updated to use WordPress functions

**Documentation:**
- [ISSUE_RESOLUTION.md](ISSUE_RESOLUTION.md) - Summary of fixes and testing instructions
- [PHASE4_MIGRATION.md](PHASE4_MIGRATION.md) - Detailed technical documentation

### Phase 3 (Completed)
- ✅ Website analysis migrated to WordPress-native
- ✅ Database operations via V_WPSA_DB class

### Phase 2 (Completed)
- ✅ WordPress Cron for cleanup
- ✅ WordPress-native domain validation

### Phase 1 (Completed)

**Removed unused code:**
- ✅ CLI/Console commands (7 files) - Not needed for WordPress plugin
- ✅ Management controller (1 file) - Not integrated with WordPress auth  
- ✅ Utility files (2 files) - WordPress has its own equivalents
- ✅ Configuration files (1 file) - Deprecated standalone routing

**See detailed documentation:**
- [CONVERSION_NOTES.md](CONVERSION_NOTES.md) - What was removed and why
- [ARCHITECTURE.md](ARCHITECTURE.md) - Plugin architecture and data flow
- [TESTING_GUIDE.md](TESTING_GUIDE.md) - Complete testing procedures

### Removed Features
- Command-line interface (CLI) commands for batch operations
- Cron job utilities (use WordPress cron instead)
- Yii requirements checker (WordPress handles plugin requirements)
- Management controller (not integrated with WordPress auth)

## Testing

### Manual Testing

To test the plugin functionality:

1. **Install and Activate**: 
   - Upload the plugin to WordPress
   - Activate it through the Plugins menu

2. **Create a Test Page**:
   - Create a new page in WordPress
   - Add the shortcode `[v_wpsa]`
   - Publish the page

3. **Test Form Submission**:
   - Visit the page with the shortcode
   - Enter a domain name (e.g., "google.com")
   - Click "Analyze"
   - Verify that:
     - No "Helper.php" error appears
     - Progress bar shows while processing
     - No page redirect occurs
     - Report appears on the same page
     - Page scrolls to the report

4. **Test Validation**:
   - Try submitting without a domain - should show error
   - Try invalid domain format - should show error
   - Try valid domain - should show report

5. **Browser Console Check**:
   - Open browser developer tools
   - Check Console tab for JavaScript errors
   - Check Network tab to verify AJAX calls to admin-ajax.php
   - Verify no calls to index.php (old behavior)

### Expected Behavior

**Before Changes (Problem)**:
- Form would redirect to URLs like `http://localhost/wp-content/plugins/v-wpsa/index.php/www/example.com`
- "Direct access not allowed" error
- **"Helper.php not found" fatal error** (Phase 4 fix)
- Page reloads on every action

**After Changes (Fixed)**:
- Form uses AJAX calls to admin-ajax.php
- No page redirects
- **Helper.php loads correctly** (Phase 4 fix)
- Content updates dynamically on the same page
- Smooth user experience with progress indicators
