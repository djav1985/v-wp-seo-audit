# WordPress Hooks Reference for V-WP-SEO-Audit

This document lists all WordPress actions and filters provided by the plugin.

## Actions

### `v_wp_seo_audit_assets_loaded`
Fired after plugin assets are enqueued.

**Use Case**: Add custom CSS/JS to the plugin frontend.

**Example**:
```php
add_action( 'v_wp_seo_audit_assets_loaded', function() {
    wp_enqueue_style( 'my-custom-seo-styles', get_stylesheet_directory_uri() . '/seo-custom.css' );
} );
```

---

### `v_wp_seo_audit_before_generate_html`
Fired before HTML report generation starts.

**Parameters**:
- `$domain` (string) - Domain being analyzed

**Use Case**: Log report generation, check permissions, modify settings.

**Example**:
```php
add_action( 'v_wp_seo_audit_before_generate_html', function( $domain ) {
    error_log( "Generating SEO report for: $domain" );
    // Could also check user permissions, rate limits, etc.
} );
```

---

### `v_wp_seo_audit_after_generate_html`
Fired after HTML report generation completes.

**Parameters**:
- `$domain` (string) - Domain that was analyzed
- `$result` (array) - Result array with 'success', 'html', or 'error'

**Use Case**: Log completion, send notifications, cache results.

**Example**:
```php
add_action( 'v_wp_seo_audit_after_generate_html', function( $domain, $result ) {
    if ( $result['success'] ) {
        // Send admin notification
        wp_mail( get_option( 'admin_email' ), 
                "SEO Report Generated for $domain", 
                "Report is ready to view." );
    }
}, 10, 2 );
```

---

### `v_wp_seo_audit_daily_cleanup`
Runs daily via WordPress Cron to cleanup old data.

**Use Case**: Add custom cleanup tasks.

**Example**:
```php
add_action( 'v_wp_seo_audit_daily_cleanup', function() {
    // Custom cleanup logic
    delete_expired_transients();
} );
```

---

## Filters

### `v_wp_seo_audit_cache_time`
Filter the cache time for analysis results.

**Parameters**:
- `$cache_time` (int) - Cache time in seconds (default: 86400 = 24 hours)

**Returns**: (int) Modified cache time

**Use Case**: Adjust how long analysis data is considered fresh.

**Example**:
```php
add_filter( 'v_wp_seo_audit_cache_time', function( $cache_time ) {
    // Extend cache to 7 days
    return 7 * 24 * 60 * 60;
} );
```

---

### `v_wp_seo_audit_shortcode_content`
Filter the shortcode output before it's returned.

**Parameters**:
- `$content` (string) - HTML content to display
- `$atts` (array) - Shortcode attributes

**Returns**: (string) Modified HTML content

**Use Case**: Wrap content, add headers/footers, inject ads.

**Example**:
```php
add_filter( 'v_wp_seo_audit_shortcode_content', function( $content, $atts ) {
    // Add custom header
    $header = '<div class="seo-audit-header"><h2>SEO Analysis</h2></div>';
    return $header . $content;
}, 10, 2 );
```

---

### `v_wp_seo_audit_html_result`
Filter the HTML generation result.

**Parameters**:
- `$result` (array) - Result array with 'success', 'html', or 'error'
- `$domain` (string) - Domain that was analyzed

**Returns**: (array) Modified result array

**Use Case**: Modify report HTML, add watermarks, filter content.

**Example**:
```php
add_filter( 'v_wp_seo_audit_html_result', function( $result, $domain ) {
    if ( $result['success'] ) {
        // Add watermark
        $result['html'] .= '<div class="watermark">Powered by My SEO Service</div>';
    }
    return $result;
}, 10, 2 );
```

---

## Database Hooks

The plugin uses standard WordPress database operations, so you can use core WordPress hooks:

### `wpdb_query` filter
Intercept all database queries.

### `pre_get_posts` action
Modify database queries before execution.

---

## AJAX Hooks

All AJAX handlers use standard WordPress AJAX actions:

- `wp_ajax_v_wp_seo_audit_validate`
- `wp_ajax_nopriv_v_wp_seo_audit_validate`
- `wp_ajax_v_wp_seo_audit_generate_report`
- `wp_ajax_nopriv_v_wp_seo_audit_generate_report`
- `wp_ajax_v_wp_seo_audit_download_pdf`
- `wp_ajax_nopriv_v_wp_seo_audit_download_pdf`
- `wp_ajax_v_wp_seo_audit_pagepeeker`
- `wp_ajax_nopriv_v_wp_seo_audit_pagepeeker`

You can use `add_action()` with priority to intercept or extend these.

---

## Security Hooks

All AJAX requests use nonces. You can validate with:

```php
add_action( 'wp_ajax_v_wp_seo_audit_validate', function() {
    // Additional security checks
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Insufficient permissions' );
    }
}, 5 ); // Priority 5 runs before main handler
```

---

## Example Use Cases

### 1. Add Google Analytics Tracking

```php
add_action( 'v_wp_seo_audit_after_generate_html', function( $domain, $result ) {
    if ( $result['success'] && function_exists( 'gtag' ) ) {
        gtag( 'event', 'seo_report_generated', [
            'event_category' => 'SEO Audit',
            'event_label' => $domain,
        ] );
    }
}, 10, 2 );
```

### 2. Restrict to Logged-In Users

```php
add_filter( 'v_wp_seo_audit_shortcode_content', function( $content, $atts ) {
    if ( ! is_user_logged_in() ) {
        return '<p>Please <a href="' . wp_login_url( get_permalink() ) . '">log in</a> to use this tool.</p>';
    }
    return $content;
}, 10, 2 );
```

### 3. Add Custom CSS

```php
add_action( 'v_wp_seo_audit_assets_loaded', function() {
    wp_enqueue_style( 
        'my-seo-audit-custom', 
        get_stylesheet_directory_uri() . '/seo-audit-custom.css',
        array( 'v-wp-seo-audit-app' ),
        '1.0'
    );
} );
```

### 4. Email Reports

```php
add_action( 'v_wp_seo_audit_after_generate_html', function( $domain, $result ) {
    if ( $result['success'] ) {
        $to = get_option( 'admin_email' );
        $subject = "SEO Report Ready: $domain";
        $message = "Your SEO audit for $domain is complete.\n\nView it at: " . home_url();
        wp_mail( $to, $subject, $message );
    }
}, 10, 2 );
```

### 5. Rate Limiting

```php
add_action( 'v_wp_seo_audit_before_generate_html', function( $domain ) {
    $transient_key = 'seo_audit_' . md5( $domain );
    if ( get_transient( $transient_key ) ) {
        wp_die( 'Please wait before running another analysis for this domain.' );
    }
    set_transient( $transient_key, true, 300 ); // 5 minutes
} );
```

---

## Best Practices

1. **Always check return values** - Hooks may modify or cancel operations
2. **Use appropriate priorities** - Lower numbers run first (default is 10)
3. **Return correct types** - Filters must return the same type they receive
4. **Check capabilities** - Verify user permissions in your hooks
5. **Handle errors gracefully** - Don't break the plugin with your custom code
6. **Document your hooks** - Help other developers understand your modifications

---

## Future Hooks

The following hooks are planned for future releases:

- `v_wp_seo_audit_before_analyze` - Before domain analysis starts
- `v_wp_seo_audit_after_analyze` - After domain analysis completes
- `v_wp_seo_audit_pdf_generated` - After PDF generation
- `v_wp_seo_audit_validate_domain` - Filter domain validation
- `v_wp_seo_audit_analyzer_result` - Filter analysis results

---

## Support

For questions or issues with hooks:

1. Check the source code in `v-wp-seo-audit.php`
2. Review the wrapper classes in `includes/`
3. See `PHASE3_MIGRATION.md` for architecture details
4. Open an issue on GitHub

---

## Contributing

To add new hooks:

1. Add the hook in the appropriate location
2. Document it in this file
3. Add usage examples
4. Update the "Future Hooks" list if needed
5. Submit a pull request
