# Quick Reference: v-wpsa API Integration

## TL;DR

```php
// PHP Helper (within WordPress)
$report = v_wpsa_get_report_data('example.com');
echo $report['score']; // 85
echo $report['pdf_url']; // PDF download link
```

```bash
# REST API (external/authenticated)
curl -X POST https://site.com/wp-json/v-wpsa/v1/report \
  -u admin:app-password \
  -d '{"domain":"example.com"}'
```

## Installation

No installation needed - automatically available after plugin activation.

## Quick Start

### Option 1: PHP Function (Internal)

Use this when calling from WordPress PHP code (plugins, themes, functions.php).

```php
$report = v_wpsa_get_report_data('example.com', array('force' => false));

if (is_wp_error($report)) {
    // Handle error
    error_log($report->get_error_message());
} else {
    // Use report
    $score = $report['score'];
    $pdf = $report['pdf_url'];
}
```

### Option 2: REST API (External)

Use this when calling from outside WordPress (AI bots, external services, JavaScript).

**Endpoint:** `POST /wp-json/v-wpsa/v1/report`

**Authentication:** Required (admin user with Application Password)

**Request:**
```json
{
  "domain": "example.com",
  "force": false
}
```

**Response:**
```json
{
  "domain": "example.com",
  "score": 85,
  "pdf_url": "https://site.com/uploads/seo-audit/pdf/example.com.pdf",
  "cached": false,
  "report": { /* full data */ }
}
```

## Parameters

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| domain | string | Yes | - | Domain to analyze (without http://) |
| force | boolean | No | false | Force fresh analysis (ignore cache) |

## Response Fields

| Field | Type | Description |
|-------|------|-------------|
| domain | string | Normalized domain |
| idn | string | Internationalized domain name |
| score | integer | Overall SEO score (0-100) |
| cached | boolean | Whether result is from cache |
| pdf_url | string | Direct link to PDF report |
| pdf_cached | boolean | Whether PDF is from cache |
| generated | object | Timestamp information |
| report | object | Full report data with all sections |

## Report Sections

```php
$report['report']['website']   // Site info and overall score
$report['report']['meta']      // Meta tags (title, description, keywords)
$report['report']['content']   // Content analysis (word count, headings)
$report['report']['links']     // Link analysis (internal, external)
$report['report']['document']  // Document structure (lang, charset)
$report['report']['w3c']       // HTML validation
$report['report']['cloud']     // Keyword cloud
$report['report']['misc']      // Analytics, sitemap, etc.
$report['report']['thumbnail'] // Site screenshot
```

## Common Patterns

### Pattern 1: Simple Score Display

```php
$report = v_wpsa_get_report_data('example.com');
echo '<h2>SEO Score: ' . $report['score'] . '/100</h2>';
echo '<a href="' . $report['pdf_url'] . '">Download Report</a>';
```

### Pattern 2: Check Multiple Domains

```php
$domains = ['site1.com', 'site2.com', 'site3.com'];
foreach ($domains as $domain) {
    $report = v_wpsa_get_report_data($domain);
    if (!is_wp_error($report)) {
        echo "$domain: {$report['score']}/100\n";
    }
}
```

### Pattern 3: Force Fresh Analysis

```php
// Use when you need latest data
$report = v_wpsa_get_report_data('example.com', array('force' => true));
```

### Pattern 4: AI Chatbot Response

```javascript
async function analyzeDomain(domain) {
  const response = await fetch('/wp-json/v-wpsa/v1/report', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Basic ' + btoa('admin:app-password')
    },
    body: JSON.stringify({ domain })
  });
  
  const data = await response.json();
  return `Score: ${data.score}/100. Download: ${data.pdf_url}`;
}
```

### Pattern 5: Scheduled Reports

```php
// In functions.php or plugin
add_action('my_daily_reports', 'generate_daily_reports');

function generate_daily_reports() {
    $report = v_wpsa_get_report_data('mysite.com', array('force' => true));
    if (!is_wp_error($report)) {
        wp_mail(
            'admin@mysite.com',
            'Daily SEO Report',
            "Score: {$report['score']}/100\nPDF: {$report['pdf_url']}"
        );
    }
}

wp_schedule_event(time(), 'daily', 'my_daily_reports');
```

## Error Codes

| Code | Description |
|------|-------------|
| empty_domain | Domain parameter is missing or empty |
| invalid_domain | Domain format is invalid |
| analysis_failed | Website analysis failed |
| report_data_not_found | Report data not available |
| report_generation_failed | Error during report generation |

## Error Handling

```php
$report = v_wpsa_get_report_data($domain);

if (is_wp_error($report)) {
    switch ($report->get_error_code()) {
        case 'invalid_domain':
            // Handle invalid domain
            break;
        case 'analysis_failed':
            // Handle analysis failure
            break;
        default:
            // Handle other errors
            error_log($report->get_error_message());
    }
}
```

## Authentication Setup (REST API)

### Step 1: Create Application Password

1. Go to WordPress Admin → Users → Profile
2. Scroll to "Application Passwords"
3. Enter name (e.g., "SEO API")
4. Click "Add New"
5. Copy the generated password

### Step 2: Use in Requests

```bash
curl -u "username:password" https://site.com/wp-json/v-wpsa/v1/report
```

or

```javascript
const auth = btoa('username:password');
headers: { 'Authorization': `Basic ${auth}` }
```

## Customization

### Change Required Capability

```php
// Allow editors to access API
add_filter('v_wpsa_rest_api_capability', function() {
    return 'edit_posts';
});
```

### Change Cache Time

```php
// Cache for 12 hours instead of 24
add_filter('v_wpsa_cache_time', function() {
    return 12 * HOUR_IN_SECONDS;
});
```

## Troubleshooting

### REST API not working?
- Flush permalinks: Settings → Permalinks → Save
- Check if plugin is activated
- Verify authentication credentials
- Check PHP error logs

### Empty response?
- Domain may be unreachable
- Analysis may have failed (check logs)
- Insufficient memory/time limits

### Old cached data?
- Use `force => true` parameter
- Or wait for cache to expire (24h default)

## Performance Tips

1. **Use caching** - Don't set `force => true` unnecessarily
2. **Batch at off-peak** - Run bulk operations during low traffic
3. **Store results** - Cache responses in your own system
4. **Monitor limits** - Watch memory/execution time for large sites

## Links

- Full Documentation: [README.md](README.md)
- Testing Guide: [REST_API_TESTING.md](REST_API_TESTING.md)
- Examples: [examples/](examples/)
- Changelog: [CHANGELOG.md](CHANGELOG.md)
- Implementation Details: [IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)

## Support

Questions? Check the documentation or open an issue on GitHub.

---

**Remember:** This is an internal API requiring admin authentication. Never expose credentials publicly.
