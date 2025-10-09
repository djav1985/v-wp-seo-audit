# V-WP-SEO-Audit Testing Guide

This guide provides step-by-step instructions for testing the WordPress SEO Audit plugin after AJAX implementation fixes.

## Prerequisites

- WordPress installation (local or development environment)
- Plugin activated in WordPress
- A test page with the `[v_wp_seo_audit]` shortcode

## Test Scenarios

### 1. Form Submission Test

**Objective**: Verify that form submission uses AJAX and does not redirect

**Steps**:
1. Navigate to the page with the `[v_wp_seo_audit]` shortcode
2. Open browser Developer Tools (F12)
3. Go to the Network tab
4. Enter a domain name (e.g., `example.com`)
5. Click the "Analyze" button

**Expected Results**:
- ✅ No page reload or redirect occurs
- ✅ Progress bar appears during processing
- ✅ Network tab shows POST requests to `admin-ajax.php`
- ✅ Network tab shows NO requests to `index.php`
- ✅ Report appears on the same page below the form
- ✅ Page smoothly scrolls to the report results

**Common Issues**:
- ❌ If page redirects to URL containing `index.php`, AJAX is not working
- ❌ If "Direct access not allowed" error appears, check AJAX configuration

### 2. Domain Validation Test

**Objective**: Verify proper validation and error handling

**Steps**:

**Test 2a: Empty Domain**
1. Leave the domain field empty
2. Click "Analyze"

**Expected**: Error message "Please enter a domain name" appears (no AJAX call)

**Test 2b: Invalid Domain**
1. Enter an invalid domain (e.g., `not..valid..domain`)
2. Click "Analyze"

**Expected**: Client-side validation error appears immediately

**Test 2c: Valid Domain**
1. Enter a valid domain (e.g., `google.com`)
2. Click "Analyze"

**Expected**: 
- ✅ Domain is validated via AJAX
- ✅ Report generation begins
- ✅ Results display without page reload

### 3. Security Test

**Objective**: Verify nonce verification is working

**Steps**:
1. Open browser Developer Tools
2. Go to Console tab
3. Run this command to see the nonce:
   ```javascript
   console.log(_global.nonce);
   ```
4. In the Network tab, inspect an AJAX request
5. Check the "Form Data" or "Payload" section

**Expected Results**:
- ✅ Nonce value is present in `_global.nonce`
- ✅ Nonce is sent with each AJAX request
- ✅ Requests without valid nonce are rejected (security check failed)

### 4. Direct Access Test

**Objective**: Verify index.php cannot be accessed directly

**Steps**:
1. Navigate directly to `http://your-site.com/wp-content/plugins/v-wp-seo-audit/index.php`

**Expected Results**:
- ✅ Friendly error page appears
- ✅ Error message explains: "Direct Access Not Allowed"
- ✅ Instructions to use shortcode are provided
- ✅ Link to return to homepage is shown
- ❌ Should NOT show a blank page or fatal PHP error

### 5. Thumbnail Test

**Objective**: Verify thumbnails load correctly

**Steps**:
1. Generate a report for a domain
2. Check if thumbnail/screenshot appears in the report
3. Inspect the thumbnail image source

**Expected Results**:
- ✅ Thumbnail loads from thum.io service or cached version
- ✅ No errors in console related to thumbnail loading
- ✅ If thumbnail fails, fallback "not available" image appears

### 6. Browser Console Test

**Objective**: Verify no JavaScript errors occur

**Steps**:
1. Open Developer Tools (F12)
2. Go to Console tab
3. Perform all above tests
4. Monitor for errors

**Expected Results**:
- ✅ No JavaScript errors appear
- ✅ No warnings about missing _global object
- ✅ No AJAX errors (400, 404, 500 status codes)

### 7. Multiple Submission Test

**Objective**: Verify subsequent searches work correctly

**Steps**:
1. Analyze a domain (e.g., `example.com`)
2. Wait for results to appear
3. Enter a different domain (e.g., `google.com`)
4. Click "Analyze" again

**Expected Results**:
- ✅ Previous report is replaced with new report
- ✅ No duplicate content appears
- ✅ Form remains functional after first use
- ✅ Progress indicators work correctly on each submission

## AJAX Endpoints Verification

The plugin should have these three AJAX endpoints registered:

1. **v_wp_seo_audit_validate** - Domain validation
2. **v_wp_seo_audit_generate_report** - Report generation
3. **v_wp_seo_audit_pagepeeker** - Thumbnail proxy (legacy, returns message about thum.io)

To verify they're registered, you can check WordPress hooks:
```php
// In WordPress, run this in a test script or plugin:
var_dump(has_action('wp_ajax_v_wp_seo_audit_validate'));
var_dump(has_action('wp_ajax_v_wp_seo_audit_generate_report'));
var_dump(has_action('wp_ajax_v_wp_seo_audit_pagepeeker'));
```

All should return a priority number (typically 10), not false.

## Troubleshooting

### Issue: "Direct access not allowed" error
**Solution**: Ensure you're using the shortcode `[v_wp_seo_audit]` and not accessing index.php directly

### Issue: Reports not loading
**Solution**: 
1. Check browser console for JavaScript errors
2. Check Network tab for failed AJAX requests
3. Verify WordPress AJAX URL is correct (`/wp-admin/admin-ajax.php`)

### Issue: Nonce verification failed
**Solution**:
1. Clear browser cache
2. Reload the page to get a fresh nonce
3. Check that `_global.nonce` is defined in page source

### Issue: Thumbnails not loading
**Solution**: 
1. Check internet connectivity (thum.io is external service)
2. Verify uploads directory is writable for caching
3. Check browser console for CORS errors

## Test Checklist

Use this checklist to track your testing progress:

- [ ] Form submission uses AJAX (no page reload)
- [ ] Domain validation works (empty, invalid, valid)
- [ ] Security nonces are present and verified
- [ ] index.php shows friendly error when accessed directly
- [ ] Thumbnails load or show fallback
- [ ] No JavaScript errors in console
- [ ] Multiple submissions work correctly
- [ ] All three AJAX endpoints are registered
- [ ] Network tab shows admin-ajax.php calls only
- [ ] Report displays correctly on the same page

## Reporting Issues

If you encounter issues, please provide:

1. WordPress version
2. PHP version
3. Browser and version
4. Screenshot of error (if visual)
5. Browser console errors (if any)
6. Network tab showing failed request (if AJAX related)

## Performance Notes

- First domain analysis may take 10-30 seconds depending on site size
- Subsequent analyses of the same domain use cached data
- Thumbnails are cached for 7 days
- AJAX responses may vary based on target website performance
