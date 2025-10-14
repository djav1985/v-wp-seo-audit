# Developer Guide: Using the Refactored Classes

This guide explains how to use the newly organized classes in the V-WP-SEO-Audit plugin.

## Quick Reference

### Class Overview

| Class | Purpose | Methods |
|-------|---------|---------|
| `V_WP_SEO_Audit_Yii_Integration` | Yii framework initialization | `configure_yii_app()`, `init()` |
| `V_WP_SEO_Audit_Validation` | Domain validation | `validate_domain()`, `sanitize_domain()`, `encode_idn()`, `is_valid_domain_format()`, `check_banned_domain()` |
| `V_WP_SEO_Audit_Helpers` | Utility functions | `delete_pdf()`, `get_config()`, `analyze_website()` |
| `V_WP_SEO_Audit_Ajax_Handlers` | AJAX endpoints | `init()`, `validate_domain()`, `generate_report()`, `pagepeeker_proxy()`, `download_pdf()` |

## Usage Examples

### 1. Using the Validation Class

#### Validate a Domain
```php
// Validate a domain input
$result = V_WP_SEO_Audit_Validation::validate_domain( 'example.com' );

if ( $result['valid'] ) {
    echo "Domain: " . $result['domain']; // ASCII/punycode
    echo "IDN: " . $result['idn'];       // Unicode
    echo "IP: " . $result['ip'];         // IP address
} else {
    echo "Errors: " . implode( ', ', $result['errors'] );
}
```

#### Sanitize a Domain
```php
// Clean up user input
$clean = V_WP_SEO_Audit_Validation::sanitize_domain( 'https://www.example.com/' );
// Result: "example.com"
```

#### Encode IDN Domain
```php
// Convert internationalized domain to punycode
$punycode = V_WP_SEO_Audit_Validation::encode_idn( 'münchen.de' );
// Result: "xn--mnchen-3ya.de"
```

#### Check Domain Format
```php
// Validate domain format
$valid = V_WP_SEO_Audit_Validation::is_valid_domain_format( 'example.com' );
// Returns: true or false
```

### 2. Using the Helpers Class

#### Delete PDF for a Domain
```php
// Delete all PDF files for a domain (all languages)
$success = V_WP_SEO_Audit_Helpers::delete_pdf( 'example.com' );
// Returns: true on success
```

#### Get Configuration Value
```php
// Get cache time configuration
$cache_time = V_WP_SEO_Audit_Helpers::get_config( 'analyzer.cache_time' );
// Default: 86400 (24 hours), filterable with 'v_wp_seo_audit_cache_time'

// Get pagination size
$per_page = V_WP_SEO_Audit_Helpers::get_config( 'param.rating_per_page' );
// Default: 12, filterable with 'v_wp_seo_audit_rating_per_page'
```

#### Analyze a Website (Yii Bridge)
```php
// Trigger website analysis
$result = V_WP_SEO_Audit_Helpers::analyze_website( 
    'example.com',    // domain (punycode)
    'example.com',    // idn (unicode)
    '93.184.216.34',  // ip address
    null              // website ID (null for new)
);

if ( ! is_wp_error( $result ) ) {
    echo "Website analyzed successfully";
} else {
    echo "Error: " . $result->get_error_message();
}
```

### 3. Using the Yii Integration Class

#### Initialize Yii Framework
```php
// Automatically called by 'wp' action hook
// Manual initialization (if needed):
V_WP_SEO_Audit_Yii_Integration::init();
```

#### Configure Yii App
```php
global $v_wp_seo_audit_app;

// Configure Yii app for WordPress environment
V_WP_SEO_Audit_Yii_Integration::configure_yii_app( $v_wp_seo_audit_app );
```

### 4. Using the AJAX Handlers Class

#### Register AJAX Endpoints
```php
// Already called in main plugin file
// All endpoints registered automatically:
V_WP_SEO_Audit_Ajax_Handlers::init();
```

The AJAX handlers are registered for these actions:
- `wp_ajax_v_wp_seo_audit_validate` / `wp_ajax_nopriv_v_wp_seo_audit_validate`
- `wp_ajax_v_wp_seo_audit_generate_report` / `wp_ajax_nopriv_v_wp_seo_audit_generate_report`
- `wp_ajax_v_wp_seo_audit_pagepeeker` / `wp_ajax_nopriv_v_wp_seo_audit_pagepeeker`
- `wp_ajax_v_wp_seo_audit_download_pdf` / `wp_ajax_nopriv_v_wp_seo_audit_download_pdf`

## Backward Compatibility

All existing function names still work as wrapper functions:

```php
// Old style (still works)
$result = v_wp_seo_audit_validate_domain( 'example.com' );

// New style (recommended for new code)
$result = V_WP_SEO_Audit_Validation::validate_domain( 'example.com' );

// Both produce identical results
```

### Available Wrapper Functions

```php
// Yii Integration
v_wp_seo_audit_configure_yii_app( $app )
v_wp_seo_audit_init()

// Validation
v_wp_seo_audit_validate_domain( $domain )
v_wp_seo_audit_sanitize_domain( $domain )
v_wp_seo_audit_encode_idn( $domain )
v_wp_seo_audit_is_valid_domain_format( $domain )
v_wp_seo_audit_check_banned_domain( $domain )

// Helpers
v_wp_seo_audit_delete_pdf( $domain )
v_wp_seo_audit_get_config( $config_name )
v_wp_seo_audit_analyze_website( $domain, $idn, $ip, $wid )
```

## WordPress Filters

The new classes support WordPress filters for customization:

### Cache Time Filter
```php
// Customize cache time (default 24 hours)
add_filter( 'v_wp_seo_audit_cache_time', function( $seconds ) {
    return 3600; // 1 hour
});
```

### Pagination Filter
```php
// Customize items per page (default 12)
add_filter( 'v_wp_seo_audit_rating_per_page', function( $count ) {
    return 20;
});
```

### Index Website Count Filter
```php
// Customize website count on index (default 30)
add_filter( 'v_wp_seo_audit_index_website_count', function( $count ) {
    return 50;
});
```

## Error Handling

The classes use WordPress error patterns:

### Validation Errors
```php
$result = V_WP_SEO_Audit_Validation::validate_domain( 'invalid' );

if ( ! $result['valid'] ) {
    // Array of error messages
    foreach ( $result['errors'] as $error ) {
        echo $error;
    }
}
```

### Analysis Errors
```php
$result = V_WP_SEO_Audit_Helpers::analyze_website( $domain, $idn, $ip );

if ( is_wp_error( $result ) ) {
    echo "Error Code: " . $result->get_error_code();
    echo "Error Message: " . $result->get_error_message();
}
```

## Best Practices

### 1. Use Static Methods
Classes use static methods for ease of use without instantiation:
```php
// Good
V_WP_SEO_Audit_Validation::validate_domain( $domain );

// No need to instantiate
$validator = new V_WP_SEO_Audit_Validation(); // Not needed
```

### 2. Type Checking
Always check return types:
```php
// Validation returns array
$result = V_WP_SEO_Audit_Validation::validate_domain( $domain );
if ( is_array( $result ) && $result['valid'] ) { }

// Analysis returns array or WP_Error
$result = V_WP_SEO_Audit_Helpers::analyze_website( ... );
if ( ! is_wp_error( $result ) ) { }
```

### 3. Use WordPress Standards
The classes follow WordPress coding standards:
- Use `sanitize_text_field()` for user input
- Use `wp_unslash()` before sanitization
- Use `__()` for translatable strings
- Use `wp_send_json_success()` / `wp_send_json_error()` for AJAX

## Testing

### Manual Testing
```php
// Test validation
$test_domains = [ 'example.com', 'invalid..domain', 'münchen.de', 'localhost' ];
foreach ( $test_domains as $test_domain ) {
    $result = V_WP_SEO_Audit_Validation::validate_domain( $test_domain );
    var_dump( $result );
}

// Test helpers
$cache_time = V_WP_SEO_Audit_Helpers::get_config( 'analyzer.cache_time' );
echo "Cache time: " . $cache_time . " seconds\n";

// Test PDF deletion
$deleted = V_WP_SEO_Audit_Helpers::delete_pdf( 'example.com' );
echo "PDF deleted: " . ( $deleted ? 'Yes' : 'No' ) . "\n";
```

## Migration Guide

If you have custom code using the old functions, no changes are needed. However, for new code:

### Before (Old Style)
```php
function my_custom_validation( $domain ) {
    $result = v_wp_seo_audit_validate_domain( $domain );
    return $result['valid'];
}
```

### After (New Style - Recommended)
```php
function my_custom_validation( $domain ) {
    $result = V_WP_SEO_Audit_Validation::validate_domain( $domain );
    return $result['valid'];
}
```

Both styles work identically, but the new style:
- Makes dependencies explicit
- Improves IDE autocomplete
- Easier to mock for testing
- Clearer code organization

## Questions?

See `REFACTORING_SUMMARY.md` for more details on the refactoring approach and architecture.
