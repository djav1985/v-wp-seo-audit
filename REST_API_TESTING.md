# REST API Testing Guide

This guide provides examples for testing the v-wpsa REST API endpoint.

## Prerequisites

- WordPress installation with v-wpsa plugin activated
- Admin access to WordPress
- Application password or authentication method configured

## Creating Application Passwords (WordPress 5.6+)

1. Go to **Users** → **Profile** in WordPress admin
2. Scroll down to **Application Passwords** section
3. Enter a name (e.g., "SEO Audit API")
4. Click **Add New Application Password**
5. Copy the generated password (you'll use this for API authentication)

## Example 1: Basic Report Request

### Using cURL

```bash
# Replace with your actual values
SITE_URL="https://yoursite.com"
USERNAME="admin"
APP_PASSWORD="xxxx xxxx xxxx xxxx xxxx xxxx"
DOMAIN="example.com"

# Make the request
curl -X POST "${SITE_URL}/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -u "${USERNAME}:${APP_PASSWORD}" \
  -d "{\"domain\":\"${DOMAIN}\"}"
```

### Using JavaScript (Browser/Node.js)

```javascript
const siteUrl = 'https://yoursite.com';
const username = 'admin';
const appPassword = 'xxxx xxxx xxxx xxxx xxxx xxxx';
const domain = 'example.com';

// Create base64 encoded credentials
const credentials = btoa(`${username}:${appPassword}`);

fetch(`${siteUrl}/wp-json/v-wpsa/v1/report`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Basic ${credentials}`
  },
  body: JSON.stringify({ domain: domain })
})
.then(response => response.json())
.then(data => {
  console.log('SEO Score:', data.score);
  console.log('PDF URL:', data.pdf_url);
  console.log('Report Data:', data.report);
})
.catch(error => console.error('Error:', error));
```

### Using Python

```python
import requests
import json

site_url = "https://yoursite.com"
username = "admin"
app_password = "xxxx xxxx xxxx xxxx xxxx xxxx"
domain = "example.com"

response = requests.post(
    f"{site_url}/wp-json/v-wpsa/v1/report",
    json={"domain": domain},
    auth=(username, app_password)
)

if response.status_code == 200:
    data = response.json()
    print(f"SEO Score: {data['score']}")
    print(f"PDF URL: {data['pdf_url']}")
    print(f"Cached: {data['cached']}")
else:
    print(f"Error: {response.status_code}")
    print(response.json())
```

## Example 2: Force Re-analysis

To force a fresh analysis even if cached data exists:

```bash
curl -X POST "${SITE_URL}/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -u "${USERNAME}:${APP_PASSWORD}" \
  -d "{\"domain\":\"${DOMAIN}\",\"force\":true}"
```

## Example 3: AI Chatbot Integration

Here's an example of integrating with an AI chatbot:

```javascript
// Example for a chatbot that responds to user queries
async function getSEOReport(domain) {
  const response = await fetch('https://yoursite.com/wp-json/v-wpsa/v1/report', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Basic ' + btoa('admin:your-app-password')
    },
    body: JSON.stringify({ domain })
  });
  
  if (!response.ok) {
    throw new Error(`API error: ${response.statusText}`);
  }
  
  return await response.json();
}

// Chatbot handler
async function handleUserQuery(userMessage) {
  // Extract domain from user message
  const domainMatch = userMessage.match(/analyze\s+([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i);
  
  if (domainMatch) {
    const domain = domainMatch[1];
    
    try {
      const report = await getSEOReport(domain);
      
      return `
        SEO Audit Results for ${domain}:
        
        Overall Score: ${report.score}/100
        Status: ${report.cached ? 'Cached' : 'Fresh Analysis'}
        
        Key Metrics:
        - Meta Title: ${report.report.meta.title || 'Not set'}
        - Meta Description: ${report.report.meta.description || 'Not set'}
        - Total Links: ${report.report.links.internal + report.report.links.external_dofollow + report.report.links.external_nofollow}
        
        Download full report: ${report.pdf_url}
      `;
    } catch (error) {
      return `Sorry, I couldn't analyze ${domain}. Error: ${error.message}`;
    }
  }
  
  return "Please ask me to analyze a domain, for example: 'analyze example.com'";
}
```

## Example 4: WordPress Plugin Integration

From within another WordPress plugin or theme:

```php
<?php
/**
 * Example: Get SEO report in a WordPress plugin
 */
function my_plugin_get_seo_report() {
    $domain = 'example.com';
    
    // Use the helper function
    $report = v_wpsa_get_report_data( $domain );
    
    if ( is_wp_error( $report ) ) {
        error_log( 'SEO report error: ' . $report->get_error_message() );
        return false;
    }
    
    // Process the report
    echo '<h2>SEO Report for ' . esc_html( $domain ) . '</h2>';
    echo '<p>Score: ' . absint( $report['score'] ) . '/100</p>';
    echo '<p><a href="' . esc_url( $report['pdf_url'] ) . '">Download PDF</a></p>';
    
    // Display detailed data
    if ( isset( $report['report']['meta'] ) ) {
        echo '<h3>Meta Tags</h3>';
        echo '<p>Title: ' . esc_html( $report['report']['meta']['title'] ) . '</p>';
        echo '<p>Description: ' . esc_html( $report['report']['meta']['description'] ) . '</p>';
    }
    
    return true;
}

// Schedule a weekly report update
function my_plugin_schedule_report_update() {
    if ( ! wp_next_scheduled( 'my_plugin_update_reports' ) ) {
        wp_schedule_event( time(), 'weekly', 'my_plugin_update_reports' );
    }
}
add_action( 'init', 'my_plugin_schedule_report_update' );

function my_plugin_update_all_reports() {
    $domains = array( 'example.com', 'mysite.com' );
    
    foreach ( $domains as $domain ) {
        // Force fresh analysis
        $report = v_wpsa_get_report_data( $domain, array( 'force' => true ) );
        
        if ( ! is_wp_error( $report ) ) {
            // Store or process the report
            update_option( 'my_plugin_report_' . md5( $domain ), $report );
        }
    }
}
add_action( 'my_plugin_update_reports', 'my_plugin_update_all_reports' );
```

## Response Structure

### Success Response

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
      "score_breakdown": {
        "total": 85,
        "categories": {
          "meta": 20,
          "content": 15,
          "links": 10
        }
      }
    },
    "content": {
      "word_count": 1500,
      "headings": {...}
    },
    "document": {
      "title": "Example Domain",
      "lang": "en"
    },
    "links": {
      "internal": 10,
      "external_dofollow": 5,
      "external_nofollow": 2
    },
    "meta": {
      "title": "Example Domain",
      "description": "This domain is for examples",
      "keyword": ""
    },
    "w3c": {...},
    "cloud": {...},
    "misc": {...},
    "thumbnail": {...}
  }
}
```

### Error Response

```json
{
  "code": "invalid_domain",
  "message": "Invalid domain format",
  "data": {
    "status": 400
  }
}
```

## Testing with WordPress Plugin

If you prefer to test within WordPress itself, you can use the REST API test plugin:

1. Create a new plugin file: `wp-content/plugins/test-v-wpsa-api.php`

```php
<?php
/**
 * Plugin Name: Test v-wpsa API
 * Description: Simple test for v-wpsa REST API
 */

add_action( 'admin_menu', 'test_vpsa_api_menu' );

function test_vpsa_api_menu() {
    add_menu_page(
        'Test v-wpsa API',
        'Test SEO API',
        'manage_options',
        'test-vpsa-api',
        'test_vpsa_api_page'
    );
}

function test_vpsa_api_page() {
    if ( isset( $_POST['test_domain'] ) && check_admin_referer( 'test_api' ) ) {
        $domain = sanitize_text_field( $_POST['test_domain'] );
        $report = v_wpsa_get_report_data( $domain );
        
        echo '<div class="wrap">';
        echo '<h1>SEO Report Test</h1>';
        
        if ( is_wp_error( $report ) ) {
            echo '<div class="error"><p>' . esc_html( $report->get_error_message() ) . '</p></div>';
        } else {
            echo '<div class="notice notice-success"><p>Report generated successfully!</p></div>';
            echo '<h2>Results for ' . esc_html( $domain ) . '</h2>';
            echo '<p><strong>Score:</strong> ' . absint( $report['score'] ) . '/100</p>';
            echo '<p><strong>PDF URL:</strong> <a href="' . esc_url( $report['pdf_url'] ) . '" target="_blank">' . esc_html( $report['pdf_url'] ) . '</a></p>';
            echo '<p><strong>Cached:</strong> ' . ( $report['cached'] ? 'Yes' : 'No' ) . '</p>';
            echo '<h3>Raw Data:</h3>';
            echo '<pre>' . esc_html( print_r( $report, true ) ) . '</pre>';
        }
        
        echo '</div>';
    }
    
    ?>
    <div class="wrap">
        <h1>Test v-wpsa API</h1>
        <form method="post">
            <?php wp_nonce_field( 'test_api' ); ?>
            <table class="form-table">
                <tr>
                    <th><label for="test_domain">Domain to Test:</label></th>
                    <td><input type="text" id="test_domain" name="test_domain" value="example.com" class="regular-text"></td>
                </tr>
            </table>
            <?php submit_button( 'Generate Report' ); ?>
        </form>
    </div>
    <?php
}
```

2. Activate the plugin
3. Go to **Test SEO API** in the WordPress admin menu
4. Enter a domain and click "Generate Report"

## Security Considerations

1. **Authentication Required**: The REST API endpoint requires admin privileges by default
2. **Rate Limiting**: Consider implementing rate limiting for production use
3. **Capability Filtering**: You can customize the required capability:

```php
add_filter( 'v_wpsa_rest_api_capability', function() {
    return 'edit_posts'; // Allow editors to access the API
});
```

## Troubleshooting

### Error: "rest_forbidden"
- Ensure you're authenticated with admin credentials
- Check that the user has `manage_options` capability

### Error: "invalid_domain"
- Verify the domain format is correct
- Domain should not include protocol (http://) or path

### Error: "rest_no_route"
- Ensure the v-wpsa plugin is activated
- Try flushing permalinks: **Settings** → **Permalinks** → **Save Changes**

### Empty or Incomplete Response
- Check PHP error logs for any issues during analysis
- Verify the domain is accessible from your server
- Ensure sufficient memory and execution time limits
