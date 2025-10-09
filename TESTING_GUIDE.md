# Testing Guide for V-WP-SEO-Audit Plugin

## Overview

This guide provides manual testing steps to verify the plugin works correctly after the recent fixes.

## Recent Fixes

1. **Removed wp-db-config.php dependency**: The plugin now uses WordPress database constants directly
2. **Fixed "array offset on null" error**: Enhanced null value handling in the WebsitestatController
3. **Fixed PHPCS issues**: Resolved PHP_CodeSniffer vsprintf errors

## Prerequisites

- WordPress installation (5.0 or higher recommended)
- PHP 7.4 or higher
- MySQL or MariaDB database
- Plugin installed and activated in WordPress

## Installation Steps

1. Upload the plugin to `/wp-content/plugins/v-wp-seo-audit/`
2. Activate the plugin through the WordPress admin panel
3. No additional configuration needed - the plugin will use your WordPress database settings automatically

## Testing Scenarios

### Test 1: Basic Form Display

**Objective**: Verify the form loads correctly on a page

**Steps**:
1. Create a new page in WordPress
2. Add the shortcode `[v_wp_seo_audit]` to the page content
3. Publish the page
4. View the page on the front-end

**Expected Result**:
- The SEO audit form should display
- Form should have a domain input field and submit button
- No PHP errors or warnings should appear

### Test 2: Domain Validation

**Objective**: Verify domain validation works correctly

**Steps**:
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter an invalid domain (e.g., "invalid..domain")
3. Click Submit

**Expected Result**:
- Error message should appear explaining the domain is invalid
- Form should not submit

**Steps (Valid Domain)**:
1. Enter a valid domain (e.g., "google.com")
2. Click Submit

**Expected Result**:
- Progress indicator should appear
- Domain should be validated
- Report generation should start

### Test 3: New Domain Analysis

**Objective**: Verify a new domain can be analyzed and report is displayed

**Steps**:
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter a domain that has never been analyzed before (e.g., "example.com")
3. Click Submit
4. Wait for analysis to complete (may take 30-60 seconds)

**Expected Result**:
- Progress bar should show during analysis
- No "array offset on null" error should occur
- Report should be displayed with sections including:
  - Domain URL
  - Generated date/time
  - SEO score
  - Title analysis
  - Description analysis
  - Meta tags
  - Content analysis
  - Links analysis
  - Tag cloud
- All sections should show data or appropriate "No data" messages
- No PHP errors or warnings should appear

### Test 4: Cached Domain Report

**Objective**: Verify cached reports are displayed correctly

**Steps**:
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter a domain that was previously analyzed (from Test 3)
3. Click Submit immediately

**Expected Result**:
- Report should be displayed quickly (within 1-2 seconds)
- Same report data should be shown as before
- "Generated on" date should match the previous analysis
- Message indicating report is from cache (if configured to show)

### Test 5: Database Integration

**Objective**: Verify the plugin correctly uses WordPress database settings

**Steps**:
1. Check that no `wp-db-config.php` file is required
2. Verify the plugin works with standard WordPress database configuration
3. Check database tables exist:
   - `{prefix}ca_website`
   - `{prefix}ca_content`
   - `{prefix}ca_links`
   - `{prefix}ca_metatags`
   - `{prefix}ca_cloud`
   - `{prefix}ca_document`
   - `{prefix}ca_issetobject`
   - `{prefix}ca_misc`
   - `{prefix}ca_w3c`
   - `{prefix}ca_pagespeed`

**Expected Result**:
- All tables should exist in the WordPress database
- Tables should use the configured WordPress table prefix
- Plugin should work without any external configuration files

### Test 6: Error Handling

**Objective**: Verify error handling for unreachable domains

**Steps**:
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter a non-existent domain (e.g., "thisdomaindoesnotexist12345.com")
3. Click Submit

**Expected Result**:
- Appropriate error message should appear
- No PHP fatal errors or warnings
- User should be able to try again with a different domain

### Test 7: Multiple Analyses

**Objective**: Verify multiple domains can be analyzed in sequence

**Steps**:
1. Analyze domain A (e.g., "google.com")
2. Wait for report to display
3. Scroll back to form
4. Enter domain B (e.g., "github.com")
5. Submit and wait for report

**Expected Result**:
- Both analyses should complete successfully
- Second report should replace the first report
- No JavaScript errors in browser console
- No PHP errors

## Verification Checklist

- [ ] Form displays correctly
- [ ] Domain validation works
- [ ] New domain analysis completes without errors
- [ ] Report displays all sections
- [ ] No "array offset on null" errors
- [ ] Cached reports load quickly
- [ ] Database integration works (no wp-db-config.php needed)
- [ ] Error handling works for invalid domains
- [ ] Multiple analyses work in sequence
- [ ] No PHP warnings or errors in WordPress debug.log
- [ ] No JavaScript errors in browser console

## Troubleshooting

### Issue: Plugin doesn't initialize

**Possible Causes**:
- Database connection issues
- Missing WordPress constants

**Solution**:
- Verify WordPress database connection is working
- Check that DB_NAME, DB_USER, DB_PASSWORD, and DB_HOST constants are defined in wp-config.php

### Issue: "array offset on null" error

**Possible Causes**:
- Database records with NULL values in JSON fields
- Analysis didn't complete properly

**Solution**:
- This should be fixed by the recent changes
- If it still occurs, check the error log for specific field names
- Verify the database tables have proper structure

### Issue: Analysis times out

**Possible Causes**:
- Server timeout limits
- Domain takes too long to respond
- Heavy server load

**Solution**:
- Increase PHP max_execution_time
- Try a different domain
- Check server resources

## CLI Testing (Optional)

If you need to test the plugin from command line:

```bash
# Navigate to WordPress root
cd /path/to/wordpress

# Run console command
php wp-content/plugins/v-wp-seo-audit/command.php parse insert --domain=example.com --idn=example.com --ip=93.184.216.34
```

This should create database records for analysis.

## Browser Console Testing

Open browser developer tools (F12) and check:

1. **Network Tab**: Verify AJAX requests go to `/wp-admin/admin-ajax.php`
2. **Console Tab**: Verify no JavaScript errors
3. **Network Tab**: Check AJAX responses are valid JSON

## Database Testing

Connect to your WordPress database and verify:

```sql
-- Check if website record exists
SELECT * FROM {prefix}ca_website WHERE domain = 'example.com';

-- Check if all related records exist
SELECT * FROM {prefix}ca_content WHERE wid = {website_id};
SELECT * FROM {prefix}ca_links WHERE wid = {website_id};
SELECT * FROM {prefix}ca_metatags WHERE wid = {website_id};
```

All records should exist after a successful analysis.

## Support

If you encounter issues not covered in this guide, please check:

1. WordPress debug.log file
2. PHP error logs
3. Browser console errors
4. Network requests in browser developer tools
