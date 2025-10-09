# PDF Download Testing Guide

## Overview
This document provides instructions for testing the new AJAX-based PDF download functionality.

## Changes Made
1. **New AJAX Handler**: Added `v_wp_seo_audit_ajax_download_pdf()` in `v-wp-seo-audit.php`
2. **Updated View**: Modified `protected/views/websitestat/index.php` to use AJAX button instead of direct link
3. **JavaScript Handler**: Added PDF download handler in `js/base.js` that submits a form to trigger file download

## Testing Steps

### Prerequisites
- WordPress installation with the V-WP-SEO-Audit plugin activated
- At least one domain already analyzed (e.g., google.com)
- Use the shortcode `[v_wp_seo_audit]` on a WordPress page

### Test Case 1: Download PDF from Existing Report
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter a domain (e.g., "google.com") in the form and submit
3. Wait for the report to be generated and displayed
4. Locate the "Download PDF version" button in the report
5. Click the button
6. **Expected Result**: 
   - Button should change text to "Generating PDF..." briefly
   - A PDF file should download with the domain name as filename (e.g., "google.com.pdf")
   - The PDF should contain the SEO audit report
   - No "Direct Access Not Allowed" error should appear

### Test Case 2: Download PDF from Cached Report
1. Navigate to a page with the `[v_wp_seo_audit]` shortcode
2. Enter a domain that was previously analyzed (within cache expiry time)
3. Wait for the cached report to be displayed
4. Click the "Download PDF version" button
5. **Expected Result**: Same as Test Case 1

### Test Case 3: Verify AJAX Request
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Follow steps from Test Case 1
4. **Expected Result**:
   - A POST request to `/wp-admin/admin-ajax.php` with action `v_wp_seo_audit_download_pdf`
   - The request should include `nonce` and `domain` parameters
   - Response should be a PDF file with `Content-Type: application/pdf`
   - Status code should be 200

### Test Case 4: Test Without Domain
1. Inspect the download button element
2. Remove the `data-domain` attribute using browser DevTools
3. Click the button
4. **Expected Result**: An alert should appear saying "Domain is required to download PDF"

### Test Case 5: Test Nonce Verification
1. Open browser Developer Tools
2. Go to Console tab
3. Manually trigger AJAX request without nonce or with invalid nonce
4. **Expected Result**: Request should fail with proper error message

## Troubleshooting

### Issue: "Direct Access Not Allowed" Error
- **Cause**: Old cached page or direct URL access
- **Solution**: Clear browser cache and reload page

### Issue: PDF Not Downloading
- **Cause**: Missing domain data or AJAX handler not registered
- **Solution**: 
  - Check browser console for JavaScript errors
  - Verify plugin is activated
  - Check WordPress debug log for PHP errors

### Issue: Button Stays Disabled
- **Cause**: JavaScript error during download process
- **Solution**: Check browser console for errors

## Technical Details

### AJAX Endpoint
- **Action**: `v_wp_seo_audit_download_pdf`
- **Method**: POST
- **Parameters**:
  - `action`: "v_wp_seo_audit_download_pdf"
  - `domain`: The domain to generate PDF for
  - `nonce`: Security nonce
- **Response**: PDF file download

### Security
- CSRF protection via WordPress nonce verification
- Input sanitization using `sanitize_text_field()`
- WordPress authentication system integration

## Notes
- The PDF is generated on-the-fly if it doesn't exist in cache
- Cached PDFs are served directly if they exist
- The download uses form submission to trigger browser download dialog
- The button provides visual feedback during PDF generation
