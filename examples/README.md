# v-wpsa Integration Examples

This directory contains practical examples demonstrating how to integrate with the v-wpsa plugin using both the REST API and PHP helper functions.

## Available Examples

### 1. SEO Dashboard Widget (`seo-dashboard-widget.php`)

A complete WordPress plugin that adds an SEO report widget to the WordPress admin dashboard.

**Features:**
- Generate SEO reports from the dashboard
- View recent report history
- Force refresh for fresh analysis
- Visual score indicators with color coding
- Display key metrics (meta tags, links, word count, etc.)
- Automated batch reporting with scheduled tasks
- Email alerts when scores drop below threshold

**Installation:**
1. Copy `seo-dashboard-widget.php` to `wp-content/plugins/`
2. Activate the plugin in WordPress admin
3. View the "SEO Audit Reports" widget on your dashboard

**Use Cases:**
- Quick SEO checks from the dashboard
- Monitor multiple domains
- Automated daily/weekly report generation
- SEO score monitoring and alerts

## REST API Examples

For REST API usage examples, see [REST_API_TESTING.md](../REST_API_TESTING.md) which includes:

- cURL examples
- JavaScript/Node.js integration
- Python integration
- AI chatbot integration patterns
- WordPress plugin integration via REST

## PHP Helper Function

The `v_wpsa_get_report_data()` function provides a simple PHP interface:

```php
// Basic usage
$report = v_wpsa_get_report_data( 'example.com' );

if ( is_wp_error( $report ) ) {
    echo 'Error: ' . $report->get_error_message();
} else {
    echo 'Score: ' . $report['score'];
    echo 'PDF: ' . $report['pdf_url'];
}

// Force fresh analysis
$report = v_wpsa_get_report_data( 'example.com', array( 'force' => true ) );
```

## Common Use Cases

### 1. Custom Admin Dashboard

Use the PHP helper to create custom admin pages that display SEO reports with your own styling and layout.

### 2. Client Reporting

Generate automated reports for clients using scheduled tasks and email them the PDF links.

### 3. Multi-site Monitoring

Monitor multiple websites and track their SEO scores over time, sending alerts when scores drop.

### 4. AI Chatbot Integration

Use the REST API to allow AI chatbots to analyze domains and provide SEO insights to users.

### 5. CRM Integration

Integrate with your CRM to automatically generate SEO reports for new leads or clients.

### 6. Custom Widgets

Create custom widgets for the WordPress dashboard, sidebar, or front-end displaying SEO scores.

## Report Data Structure

Both the REST API and PHP helper return the same data structure:

```php
array(
    'domain'      => 'example.com',
    'idn'         => 'example.com',
    'score'       => 85,
    'cached'      => false,
    'pdf_url'     => 'https://site.com/wp-content/uploads/seo-audit/pdf/example.com.pdf',
    'pdf_cached'  => false,
    'generated'   => array(
        'time'    => '2 minutes ago',
        'seconds' => 120,
        // ... date components
    ),
    'report'      => array(
        'website'   => array( /* site data */ ),
        'content'   => array( /* content analysis */ ),
        'document'  => array( /* document structure */ ),
        'links'     => array( /* link analysis */ ),
        'meta'      => array( /* meta tags */ ),
        'w3c'       => array( /* validation */ ),
        'cloud'     => array( /* keyword cloud */ ),
        'misc'      => array( /* miscellaneous */ ),
        'thumbnail' => array( /* site thumbnail */ ),
    ),
);
```

## Error Handling

Both interfaces return `WP_Error` objects on failure:

```php
$report = v_wpsa_get_report_data( 'invalid' );

if ( is_wp_error( $report ) ) {
    $error_code = $report->get_error_code();    // e.g., 'invalid_domain'
    $error_msg  = $report->get_error_message(); // e.g., 'Invalid domain format'
}
```

## Security Considerations

### REST API
- Requires admin authentication (`manage_options` capability)
- Use Application Passwords for secure API access
- Can customize required capability via filter

### PHP Helper
- No authentication required (use with caution)
- Should only be called in trusted contexts
- Validate input before calling

## Performance Tips

1. **Use Caching**: Reports are cached by default. Only use `force => true` when necessary.
2. **Batch Processing**: Use WP-Cron for batch report generation rather than real-time.
3. **Transient Storage**: Cache report data in transients for frequently accessed reports.
4. **Async Processing**: Consider using Action Scheduler for heavy batch operations.

## Contributing Examples

Have a useful integration example? Please contribute!

1. Create a new PHP file in this directory
2. Include thorough inline comments
3. Add a description to this README
4. Submit a pull request

## Support

For questions or issues:
- Check the main [README.md](../README.md)
- Review [REST_API_TESTING.md](../REST_API_TESTING.md)
- Open an issue on GitHub
