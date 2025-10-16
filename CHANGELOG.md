# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Internal Function for AI Integrations**: New `V_WPSA_external_generation()` function for internal use with AI chatbots and function calling
- **Report Service Layer**: New `V_WPSA_Report_Service` class that provides unified interface for report generation
- **Flexible Return Options**: Function can return full JSON report or just PDF download link based on parameter

### Function Signature
```php
V_WPSA_external_generation( string $domain, bool $report = true )
```

**Parameters:**
- `$domain` (string, required): Domain to analyze (without http://)
- `$report` (bool, optional): If `true` returns full JSON report, if `false` returns only PDF URL. Default: `true`

**Returns:**
- When `$report` is `true`: JSON string with complete report data including domain, score, PDF URL, and all report sections
- When `$report` is `false`: String with PDF download URL
- On error: `WP_Error` object

**Usage Examples:**
```php
// Get full report as JSON string
$json_report = V_WPSA_external_generation( 'example.com', true );

// Get only PDF download link
$pdf_url = V_WPSA_external_generation( 'example.com', false );

// Error handling
$result = V_WPSA_external_generation( 'example.com', true );
if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
}

// Wrapper pattern for AI function calling
function get_seo_report( $domain ) {
    $result = V_WPSA_external_generation( $domain, true );
    
    if ( is_wp_error( $result ) ) {
        return json_encode( array( 'error' => $result->get_error_message() ) );
    }
    
    // Result is already JSON, return directly
    return $result;
}
```

### Technical Details
- Function can be called from anywhere in WordPress (themes, plugins, functions.php)
- Uses existing analysis engine and database
- Reports are cached for 24 hours by default
- PDF files are automatically generated and cached
- Full backward compatibility maintained with existing AJAX endpoints

## [1.0.0] - Previous Releases
See README.md for earlier version history and migration notes.
