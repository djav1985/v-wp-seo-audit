# Update Button Testing Checklist

## Test Environment Setup

### Prerequisites
- WordPress installation with v-wpsa plugin activated
- Page with `[v_wpsa]` shortcode
- Browser with developer tools enabled
- Test domains available (e.g., google.com, example.com)

## Test Cases

### Test 1: Basic Update Button Functionality
**Objective:** Verify the update button triggers a force re-analysis

**Steps:**
1. Navigate to the page with the shortcode
2. Enter a domain (e.g., "google.com")
3. Click "Analyze" button
4. Wait for report to appear
5. Note the "Generated on" timestamp
6. Click the "UPDATE" button in the report header
7. Observe the behavior

**Expected Results:**
- ✅ Progress bar appears immediately
- ✅ Report is regenerated (may take 5-30 seconds)
- ✅ New "Generated on" timestamp is displayed (should be current time)
- ✅ Page automatically scrolls to the top of the report
- ✅ No page reload occurs
- ✅ No JavaScript errors in console
- ✅ All report sections are refreshed with new data

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 2: Data Deletion Verification
**Objective:** Verify old data is deleted before re-analysis

**Steps:**
1. Analyze a domain and wait for report
2. Check database for website record:
   ```sql
   SELECT id, domain, modified FROM wp_ca_website WHERE domain = 'example.com';
   ```
3. Note the website ID and modified timestamp
4. Check for PDF files in uploads directory:
   ```bash
   ls -la wp-content/uploads/seo-audit/pdf/example.com*
   ```
5. Check for thumbnail:
   ```bash
   ls -la wp-content/uploads/seo-audit/thumbnails/
   ```
6. Click UPDATE button
7. Check database again during/after update
8. Check file system again

**Expected Results:**
- ✅ New database record created (different ID) OR existing record updated with new modified timestamp
- ✅ Old PDF files deleted and recreated
- ✅ Old thumbnail deleted and recreated
- ✅ All data is fresh (not cached)

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 3: Network Request Verification
**Objective:** Verify correct AJAX parameters are sent

**Steps:**
1. Open browser Developer Tools (F12)
2. Go to Network tab
3. Analyze a domain
4. Wait for report
5. Click UPDATE button
6. In Network tab, find the request to `admin-ajax.php`
7. Click on the request and check:
   - Request Method
   - Form Data
   - Response

**Expected Results:**
- ✅ Request Method: POST
- ✅ Form Data includes:
  - `action=v_wpsa_generate_report`
  - `domain=example.com`
  - `force=1` ← **This is the key parameter**
  - `nonce=xxxxx`
- ✅ Response is JSON with:
  - `success: true`
  - `data.html: "<div>...</div>"`
  - `data.nonce: "xxxxx"`
- ✅ No 4xx or 5xx errors

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 4: JavaScript Console Verification
**Objective:** Verify no JavaScript errors during update

**Steps:**
1. Open browser Developer Tools (F12)
2. Go to Console tab
3. Clear console
4. Analyze a domain
5. Click UPDATE button
6. Watch console for errors

**Expected Results:**
- ✅ No error messages (red text)
- ✅ No "Uncaught" exceptions
- ✅ No "TypeError" or "ReferenceError"
- ℹ️ Info/debug messages are okay
- ℹ️ Warnings (yellow) are okay if not critical

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 5: Multiple Domains Test
**Objective:** Verify update works for different domains

**Steps:**
1. Analyze first domain (e.g., google.com)
2. Click UPDATE button
3. Verify it works
4. Analyze second domain (e.g., github.com)
5. Click UPDATE button
6. Verify it works
7. Go back to first domain's report (if cached)
8. Click UPDATE button
9. Verify it works

**Expected Results:**
- ✅ Update works for all domains
- ✅ Correct domain is updated (not mixed up)
- ✅ Each domain gets its own fresh data

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 6: Update Without Form Present
**Objective:** Verify fallback when form is not visible

**Steps:**
1. Analyze a domain normally
2. Wait for report to appear
3. Scroll down so the form is not visible
4. Click UPDATE button
5. Observe behavior

**Expected Results:**
- ✅ Update still works (uses fallback code path)
- ✅ Domain is correctly identified
- ✅ Report is regenerated
- ✅ No errors in console

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 7: Error Handling
**Objective:** Verify proper error handling during update

**Steps:**
1. Analyze a valid domain
2. Wait for report
3. Disconnect network or use browser DevTools to block network
4. Click UPDATE button
5. Observe behavior

**Expected Results:**
- ✅ Progress bar shows during request
- ✅ Progress bar hides after timeout/error
- ✅ Error message displayed to user
- ✅ Page doesn't break or hang
- ✅ Submit button is re-enabled

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 8: Server-Side Logs
**Objective:** Verify no PHP errors during update

**Steps:**
1. Enable WordPress debug mode:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```
2. Clear `wp-content/debug.log`
3. Analyze a domain
4. Click UPDATE button
5. Check `wp-content/debug.log` for errors

**Expected Results:**
- ✅ No PHP Fatal errors
- ✅ No PHP Warnings related to v-wpsa
- ℹ️ Info messages prefixed with "v-wpsa:" are okay
- ✅ No database query errors

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


### Test 9: Performance Test
**Objective:** Verify update completes in reasonable time

**Steps:**
1. Analyze a domain
2. Note the start time
3. Click UPDATE button
4. Note when new report appears
5. Calculate elapsed time

**Expected Results:**
- ✅ Update completes in under 60 seconds for typical websites
- ✅ Progress bar provides feedback during the wait
- ✅ No browser timeout errors

**Status:** ⬜ Pass ⬜ Fail

**Elapsed Time:** _________ seconds

**Notes:**
_________________________________


### Test 10: File Permissions
**Objective:** Verify proper file permissions for deletion/creation

**Steps:**
1. Check upload directory permissions:
   ```bash
   ls -la wp-content/uploads/seo-audit/
   ```
2. Analyze a domain
3. Click UPDATE button
4. Check if old files were deleted
5. Check if new files were created

**Expected Results:**
- ✅ Upload directory is writable by web server
- ✅ PDF directory: `wp-content/uploads/seo-audit/pdf/`
- ✅ Thumbnail directory: `wp-content/uploads/seo-audit/thumbnails/`
- ✅ Old files successfully deleted
- ✅ New files successfully created

**Status:** ⬜ Pass ⬜ Fail

**Notes:**
_________________________________


## Browser Compatibility Testing

Test the update button in multiple browsers:

### Chrome/Chromium
- **Version:** __________
- **Status:** ⬜ Pass ⬜ Fail
- **Notes:** _________________________________

### Firefox
- **Version:** __________
- **Status:** ⬜ Pass ⬜ Fail
- **Notes:** _________________________________

### Safari
- **Version:** __________
- **Status:** ⬜ Pass ⬜ Fail
- **Notes:** _________________________________

### Edge
- **Version:** __________
- **Status:** ⬜ Pass ⬜ Fail
- **Notes:** _________________________________

### Mobile Browsers
- **Device:** __________
- **Browser:** __________
- **Status:** ⬜ Pass ⬜ Fail
- **Notes:** _________________________________


## Database Verification Queries

### Check Website Record
```sql
SELECT id, domain, md5domain, modified, created
FROM wp_ca_website
WHERE domain = 'example.com';
```

### Check Related Records
```sql
-- Count all related records
SELECT 
    'website' as table_name, COUNT(*) as count FROM wp_ca_website WHERE domain = 'example.com'
UNION ALL
SELECT 'content', COUNT(*) FROM wp_ca_content WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'example.com')
UNION ALL
SELECT 'document', COUNT(*) FROM wp_ca_document WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'example.com')
UNION ALL
SELECT 'links', COUNT(*) FROM wp_ca_links WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'example.com')
UNION ALL
SELECT 'metatags', COUNT(*) FROM wp_ca_metatags WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'example.com')
UNION ALL
SELECT 'w3c', COUNT(*) FROM wp_ca_w3c WHERE wid = (SELECT id FROM wp_ca_website WHERE domain = 'example.com');
```

### Verify Data Was Deleted and Recreated
```sql
-- Run this BEFORE clicking update
SELECT id, modified FROM wp_ca_website WHERE domain = 'example.com';
-- Note the ID and timestamp

-- Run this AFTER clicking update
SELECT id, modified FROM wp_ca_website WHERE domain = 'example.com';
-- If ID changed, it's a new record
-- If ID same but modified changed, it's an update
```


## File System Verification Commands

### Check PDF Files
```bash
# List PDF files for a domain
ls -lh wp-content/uploads/seo-audit/pdf/example.com*

# Check file timestamps
stat wp-content/uploads/seo-audit/pdf/example.com.pdf
```

### Check Thumbnails
```bash
# List thumbnail files (uses MD5 hash)
ls -lh wp-content/uploads/seo-audit/thumbnails/

# Find specific thumbnail (MD5 of domain)
echo -n "example.com" | md5sum
# Then look for file with that MD5 name + .jpg
```


## Common Issues and Solutions

### Issue: Update button doesn't respond
**Check:**
- JavaScript console for errors
- jQuery is loaded
- No JavaScript conflicts with other plugins

**Solution:**
- Disable other plugins temporarily
- Check for JavaScript errors
- Verify `window.vWpSeoAudit` object exists

---

### Issue: Old data not deleted
**Check:**
- File/directory permissions
- Database query errors in debug.log
- `delete_website()` return value

**Solution:**
- Set proper permissions (755 for dirs, 644 for files)
- Check database user has DELETE permission
- Review error logs

---

### Issue: Update takes too long
**Check:**
- Server timeout settings
- Website being analyzed is slow to respond
- Server resources (CPU, memory)

**Solution:**
- Increase PHP max_execution_time
- Increase PHP memory_limit
- Use faster web server/hosting

---

### Issue: Update fails silently
**Check:**
- Network tab in browser DevTools
- WordPress debug.log
- PHP error logs

**Solution:**
- Enable WP_DEBUG and check logs
- Look for AJAX errors in network tab
- Verify nonce is valid


## Test Summary

**Date Tested:** __________________

**Tester Name:** __________________

**WordPress Version:** __________________

**Plugin Version:** __________________

**PHP Version:** __________________

**Overall Status:** ⬜ All Tests Pass ⬜ Some Tests Fail

**Critical Issues Found:**
_________________________________
_________________________________
_________________________________

**Minor Issues Found:**
_________________________________
_________________________________
_________________________________

**Recommendations:**
_________________________________
_________________________________
_________________________________

**Sign-off:**
_________________________________
