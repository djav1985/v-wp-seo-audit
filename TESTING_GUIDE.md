# Testing Guide - WordPress Plugin Conversion

This guide helps you test the V-WP-SEO-Audit plugin after the WordPress conversion cleanup.

## Prerequisites

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher
- Active WordPress installation

## Installation Testing

### 1. Plugin Installation

```bash
# Upload to WordPress plugins directory
wp-content/plugins/v-wp-seo-audit/

# Or use WordPress admin interface
Plugins > Add New > Upload Plugin
```

### 2. Activation Test

1. Navigate to **Plugins** in WordPress admin
2. Find "V-WP-SEO-Audit" in the list
3. Click **Activate**
4. Verify no PHP errors appear
5. Check database for new tables (wp_ca_*)

**Expected Tables Created:**
- wp_ca_cloud
- wp_ca_content
- wp_ca_document
- wp_ca_issetobject
- wp_ca_links
- wp_ca_metatags
- wp_ca_misc
- wp_ca_pagespeed
- wp_ca_w3c
- wp_ca_website

**Verify with SQL:**
```sql
SHOW TABLES LIKE 'wp_ca_%';
```

## Functional Testing

### 3. Shortcode Display Test

1. Create a new WordPress page
2. Title: "SEO Audit Tool"
3. Content: `[v_wp_seo_audit]`
4. Publish the page
5. Visit the page on frontend

**Expected Result:**
- Form with domain input field
- "Analyze" button
- No PHP errors or warnings
- CSS and JavaScript loaded properly

**Verify in Browser Console:**
```javascript
// Should see global variable defined
console.log(_global);
// Output: {baseUrl: "...", ajaxUrl: "...", nonce: "..."}
```

### 4. Domain Validation Test

**Test Cases:**

**A. Empty Domain**
1. Leave domain field empty
2. Click "Analyze"
3. Expected: Error message "Domain is required"

**B. Invalid Domain Format**
1. Enter: "not-a-domain"
2. Click "Analyze"
3. Expected: Validation error

**C. Valid Domain - New Analysis**
1. Enter: "google.com"
2. Click "Analyze"
3. Expected:
   - Progress bar appears
   - AJAX request to admin-ajax.php
   - Report loads without page refresh
   - Page scrolls to report

**D. Valid Domain - Cached Analysis**
1. Enter: "google.com" (same as previous test)
2. Click "Analyze"
3. Expected:
   - Faster response (from cache)
   - Report loads without page refresh

### 5. AJAX Endpoint Tests

**Verify Network Requests:**

Open Browser Developer Tools > Network tab

**Test Validation Endpoint:**
```javascript
// In browser console
jQuery.ajax({
    url: _global.ajaxUrl,
    method: 'POST',
    data: {
        action: 'v_wp_seo_audit_validate',
        domain: 'google.com',
        nonce: _global.nonce
    },
    success: function(response) {
        console.log('Validation:', response);
    }
});
```

Expected Response:
```json
{
    "success": true,
    "data": {
        "domain": "google.com"
    }
}
```

**Test Report Generation Endpoint:**
```javascript
// In browser console
jQuery.ajax({
    url: _global.ajaxUrl,
    method: 'POST',
    data: {
        action: 'v_wp_seo_audit_generate_report',
        domain: 'google.com',
        nonce: _global.nonce
    },
    success: function(response) {
        console.log('Report:', response);
    }
});
```

Expected Response:
```json
{
    "success": true,
    "data": {
        "html": "...HTML content of report..."
    }
}
```

### 6. Database Operations Test

**Check Website Record:**
```sql
SELECT * FROM wp_ca_website WHERE domain = 'google.com';
```

Expected:
- Record exists after first analysis
- `last_update` timestamp within last few minutes
- `expired` should be 0

**Check Associated Data:**
```sql
-- Content analysis
SELECT * FROM wp_ca_content WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'google.com');

-- Meta tags
SELECT * FROM wp_ca_metatags WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'google.com');

-- Links
SELECT * FROM wp_ca_links WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'google.com');
```

### 7. Cache Expiration Test

**Test Expired Cache:**
```sql
-- Manually expire a domain
UPDATE wp_ca_website 
SET expired = 1 
WHERE domain = 'google.com';
```

Then:
1. Enter "google.com" in the form
2. Click "Analyze"
3. Expected: New analysis is performed (not from cache)

### 8. PDF Generation Test

1. Complete a domain analysis
2. Look for "Download PDF" button in report
3. Click the button
4. Expected:
   - PDF file downloads
   - Filename: `domain-name-timestamp.pdf`
   - PDF contains full report

### 9. Error Handling Tests

**A. Invalid API Key (if using PageSpeed API)**
```php
// Temporarily modify protected/config/config.php
'googleApiKey' => 'invalid-key-for-testing'
```
Expected: Graceful error message, no PHP fatal errors

**B. Unreachable Domain**
1. Enter: "thisdomaindoesnotexist123456789.com"
2. Click "Analyze"
3. Expected: User-friendly error message

**C. Server Timeout**
Test with very slow domain or network issues
Expected: Timeout handled gracefully with error message

## Security Testing

### 10. Nonce Verification Test

**Invalid Nonce:**
```javascript
// In browser console - try with invalid nonce
jQuery.ajax({
    url: _global.ajaxUrl,
    method: 'POST',
    data: {
        action: 'v_wp_seo_audit_validate',
        domain: 'google.com',
        nonce: 'invalid-nonce-123'
    },
    error: function(xhr) {
        console.log('Error (expected):', xhr.responseText);
    }
});
```

Expected: 403 Forbidden or nonce verification error

### 11. XSS Protection Test

**Test Output Escaping:**
1. Enter domain with special chars: `<script>alert('xss')</script>.com`
2. Submit form
3. Check browser console and page source
4. Expected: No JavaScript execution, characters escaped

### 12. SQL Injection Test

**Test Input Sanitization:**
```javascript
jQuery.ajax({
    url: _global.ajaxUrl,
    method: 'POST',
    data: {
        action: 'v_wp_seo_audit_validate',
        domain: "'; DROP TABLE wp_ca_website; --",
        nonce: _global.nonce
    }
});
```

Expected: 
- No database damage
- Input properly sanitized
- Error message or safe handling

## Performance Testing

### 13. Load Testing

**Multiple Simultaneous Requests:**
```javascript
// In browser console
for (let i = 0; i < 5; i++) {
    setTimeout(() => {
        jQuery.ajax({
            url: _global.ajaxUrl,
            method: 'POST',
            data: {
                action: 'v_wp_seo_audit_validate',
                domain: 'example' + i + '.com',
                nonce: _global.nonce
            }
        });
    }, i * 100);
}
```

Expected:
- All requests complete successfully
- No server errors
- Reasonable response times

### 14. Cache Performance Test

**First Load vs Cached Load:**
```javascript
// Measure time for first analysis
console.time('First Analysis');
// ... perform analysis for new domain
console.timeEnd('First Analysis');

// Measure time for cached analysis
console.time('Cached Analysis');
// ... perform analysis for same domain again
console.timeEnd('Cached Analysis');
```

Expected: Cached analysis should be significantly faster

## Cleanup Testing

### 15. Deactivation Test

1. Go to **Plugins** in WordPress admin
2. Deactivate "V-WP-SEO-Audit"
3. Verify:
   - No PHP errors
   - Database tables remain intact
   - Shortcode no longer renders on frontend

### 16. Uninstallation Test

**WARNING: This will delete all plugin data!**

1. Deactivate plugin first
2. Click "Delete" on the plugin
3. Verify:
   - Plugin files removed
   - Database tables dropped (wp_ca_*)
   - No orphaned data

**Verify Tables Deleted:**
```sql
SHOW TABLES LIKE 'wp_ca_%';
-- Should return empty result
```

## Regression Testing Checklist

After any code changes, verify:

- [ ] Plugin activates without errors
- [ ] Shortcode displays form correctly
- [ ] Domain validation works
- [ ] Report generation works
- [ ] AJAX endpoints respond correctly
- [ ] Database operations succeed
- [ ] No JavaScript console errors
- [ ] No PHP errors in debug.log
- [ ] CSS styles load properly
- [ ] PDF generation works
- [ ] Cache system functions
- [ ] Security measures active (nonces, sanitization)

## Automated Testing (Future Enhancement)

Consider implementing:
- PHPUnit tests for WordPress hooks
- Selenium tests for frontend interaction
- API endpoint tests with WP REST API Test Framework
- Database integrity tests

## Known Limitations After Cleanup

The following features were removed during WordPress conversion:

### No Longer Available:
- ❌ CLI commands (`php yiic.php parse`, `php yiic.php clear`)
- ❌ Batch domain import from text file
- ❌ Management controller endpoints (`/manage/clear`)
- ❌ Direct Yii routing (all routes go through WordPress)

### Still Available:
- ✅ Web-based domain analysis
- ✅ Report generation and viewing
- ✅ PDF downloads
- ✅ Database caching
- ✅ All frontend features

## Troubleshooting

### Issue: "Application not initialized" error

**Solution:**
1. Check that shortcode is placed on page
2. Verify Yii framework files exist in `framework/` directory
3. Check file permissions
4. Review WordPress debug.log

### Issue: AJAX requests fail with 404

**Solution:**
1. Verify plugin is activated
2. Check that admin-ajax.php URL is correct
3. Test with browser console network tab
4. Verify nonce is being passed

### Issue: Reports not loading

**Solution:**
1. Check database connection
2. Verify tables were created on activation
3. Test with simple domain like "google.com"
4. Check for JavaScript errors in console

### Issue: PDF generation fails

**Solution:**
1. Check write permissions on `pdf/` directory
2. Verify TCPDF library exists in `protected/extensions/`
3. Check PHP memory limit (increase if needed)
4. Review server error logs

## Support Resources

- Plugin Documentation: See README.md
- Conversion Notes: See CONVERSION_NOTES.md
- Security Review: See SECURITY_REVIEW.md
- Code Standards: See PHPCS_CLEANUP_SUMMARY.md

---

**Last Updated**: 2025-10-14
**Plugin Version**: 1.0.0
**Test Environment**: WordPress 6.0+, PHP 7.4+
