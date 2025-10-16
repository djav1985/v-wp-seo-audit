# Implementation Verification Checklist

Use this checklist to verify that the internal domain analysis API implementation is working correctly.

## ✅ Installation Verification

- [ ] Plugin is activated in WordPress admin
- [ ] No PHP errors in WordPress debug log
- [ ] Composer autoloader is up to date (`composer dump-autoload`)
- [ ] All required files exist:
  - [ ] `includes/class-v-wpsa-report-service.php`
  - [ ] `includes/class-v-wpsa-rest-api.php`
  - [ ] Modified: `includes/class-v-wpsa-ajax-handlers.php`
  - [ ] Modified: `v-wp-seo-audit.php`

## ✅ PHP Helper Function Test

Test in a temporary plugin or `functions.php`:

```php
// Test 1: Basic call
$report = v_wpsa_get_report_data('google.com');
if (is_wp_error($report)) {
    error_log('Error: ' . $report->get_error_message());
} else {
    error_log('Success! Score: ' . $report['score']);
}

// Test 2: Verify response structure
var_dump(array_keys($report));
// Expected: domain, idn, score, cached, pdf_url, pdf_cached, generated, report

// Test 3: Force refresh
$fresh = v_wpsa_get_report_data('google.com', array('force' => true));

// Test 4: Invalid domain
$error = v_wpsa_get_report_data('');
// Expected: WP_Error with code 'empty_domain'
```

**Verification Points:**
- [ ] Function exists and is callable
- [ ] Returns array on success
- [ ] Returns WP_Error on failure
- [ ] Response has all expected keys
- [ ] Score is between 0-100
- [ ] PDF URL is accessible

## ✅ REST API Test

### Setup
- [ ] Create Application Password:
  - [ ] Go to Users → Profile
  - [ ] Scroll to "Application Passwords"
  - [ ] Create new password
  - [ ] Copy password for testing

### Test 1: Basic Request
```bash
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -u "admin:YOUR-APP-PASSWORD" \
  -d '{"domain":"google.com"}'
```

**Expected Response:**
```json
{
  "domain": "google.com",
  "score": 85,
  "pdf_url": "https://...",
  "cached": false,
  "report": { ... }
}
```

- [ ] Request succeeds (HTTP 200)
- [ ] Response is valid JSON
- [ ] Contains all expected fields
- [ ] PDF URL is valid and accessible

### Test 2: Force Refresh
```bash
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -u "admin:YOUR-APP-PASSWORD" \
  -d '{"domain":"google.com","force":true}'
```

- [ ] Request succeeds
- [ ] `cached` field is `false`
- [ ] New analysis is performed

### Test 3: Invalid Domain
```bash
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -u "admin:YOUR-APP-PASSWORD" \
  -d '{"domain":""}'
```

**Expected Response:**
```json
{
  "code": "empty_domain",
  "message": "Domain is required",
  "data": {
    "status": 400
  }
}
```

- [ ] Request returns error (HTTP 400)
- [ ] Error message is clear
- [ ] Error code is correct

### Test 4: Unauthorized Access
```bash
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -H "Content-Type: application/json" \
  -d '{"domain":"google.com"}'
```

**Expected Response:**
```json
{
  "code": "rest_forbidden",
  "message": "Sorry, you are not allowed to do that.",
  "data": {
    "status": 401
  }
}
```

- [ ] Request is rejected (HTTP 401)
- [ ] Error indicates permission issue

## ✅ Backward Compatibility Test

### Test Existing AJAX Functionality

1. [ ] Create a page with `[v_wpsa]` shortcode
2. [ ] Visit the page in browser
3. [ ] Enter a domain (e.g., "google.com")
4. [ ] Click "Analyze"
5. [ ] Verify:
   - [ ] Form submits via AJAX (no page reload)
   - [ ] Report displays on same page
   - [ ] PDF download button works
   - [ ] No JavaScript errors in console
   - [ ] No PHP errors in error log

### Test Force Update Button

1. [ ] Generate a report for a domain
2. [ ] Click the "UPDATE" button
3. [ ] Verify:
   - [ ] Fresh analysis is triggered
   - [ ] Report updates on page
   - [ ] No errors occur

## ✅ Example Plugin Test

### Install Example Dashboard Widget

1. [ ] Copy `examples/seo-dashboard-widget.php` to `wp-content/plugins/`
2. [ ] Activate the plugin
3. [ ] Go to WordPress Dashboard
4. [ ] Verify "SEO Audit Reports" widget appears
5. [ ] Enter a domain and generate report
6. [ ] Verify:
   - [ ] Report displays correctly
   - [ ] Score shows with color
   - [ ] PDF link works
   - [ ] Recent reports history updates

## ✅ Documentation Test

Verify all documentation files are present and readable:

- [ ] `README.md` - Updated with REST API sections
- [ ] `CHANGELOG.md` - Contains this implementation
- [ ] `REST_API_TESTING.md` - Complete testing guide
- [ ] `IMPLEMENTATION_SUMMARY.md` - Technical overview
- [ ] `QUICK_REFERENCE.md` - Quick start guide
- [ ] `ARCHITECTURE_DIAGRAM.md` - Architecture visuals
- [ ] `examples/README.md` - Integration patterns
- [ ] `examples/seo-dashboard-widget.php` - Working example

## ✅ Code Quality Test

Run code quality checks:

```bash
# PHP Syntax Check
find . -name "*.php" -path "*/includes/*" -exec php -l {} \;

# WordPress Coding Standards
vendor/bin/phpcs includes/class-v-wpsa-report-service.php
vendor/bin/phpcs includes/class-v-wpsa-rest-api.php
vendor/bin/phpcs includes/class-v-wpsa-ajax-handlers.php
vendor/bin/phpcs v-wp-seo-audit.php
```

- [ ] No syntax errors
- [ ] Passes PHPCS checks (or only minor warnings)

## ✅ Performance Test

### Test Caching

```bash
# First request (fresh analysis)
time curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -u "admin:PASSWORD" -d '{"domain":"google.com"}'

# Second request (cached)
time curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -u "admin:PASSWORD" -d '{"domain":"google.com"}'
```

- [ ] Second request is significantly faster
- [ ] Response includes `"cached": true` on second request

### Test PDF Caching

1. [ ] Generate report for a domain (creates PDF)
2. [ ] Check `wp-content/uploads/seo-audit/pdf/` for PDF file
3. [ ] Generate report again for same domain
4. [ ] Verify:
   - [ ] PDF file is reused (check file timestamp)
   - [ ] `pdf_cached` is `true` in response

## ✅ Security Test

### Test Authentication

1. [ ] Try REST API without credentials → Should fail with 401
2. [ ] Try with invalid credentials → Should fail with 401
3. [ ] Try with valid admin credentials → Should succeed with 200
4. [ ] Try with non-admin user credentials → Should fail with 403

### Test Input Sanitization

```bash
# Try SQL injection
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -u "admin:PASSWORD" \
  -d '{"domain":"'; DROP TABLE users--"}'

# Try XSS
curl -X POST "https://YOUR-SITE.com/wp-json/v-wpsa/v1/report" \
  -u "admin:PASSWORD" \
  -d '{"domain":"<script>alert(1)</script>"}'
```

- [ ] Invalid inputs are rejected gracefully
- [ ] No SQL errors in logs
- [ ] No XSS payloads in responses

## ✅ Integration Test Scenarios

### Scenario 1: AI Chatbot Workflow

1. [ ] User asks chatbot: "Analyze example.com"
2. [ ] Chatbot calls REST API with domain
3. [ ] Chatbot receives JSON response
4. [ ] Chatbot displays score to user
5. [ ] Chatbot provides PDF download link

### Scenario 2: Monitoring Dashboard

1. [ ] Dashboard calls helper for multiple domains
2. [ ] Scores displayed for all domains
3. [ ] Low scores trigger alerts
4. [ ] Recent reports shown in widget

### Scenario 3: Scheduled Reports

1. [ ] WP-Cron triggers daily job
2. [ ] Job analyzes monitored domains with `force => true`
3. [ ] Reports stored in database
4. [ ] Email sent with PDF links

## ✅ Edge Cases Test

- [ ] Empty domain parameter
- [ ] Invalid domain format (e.g., "not-a-domain")
- [ ] Domain with special characters
- [ ] International domain (IDN)
- [ ] Very long domain name
- [ ] Domain that doesn't exist/resolve
- [ ] Domain that returns 404
- [ ] Domain with redirect loops
- [ ] Concurrent requests for same domain

## ✅ Final Verification

- [ ] All core tests pass
- [ ] No PHP errors in logs
- [ ] No JavaScript errors in console
- [ ] Documentation is clear and accurate
- [ ] Example plugin works as expected
- [ ] Existing functionality unchanged
- [ ] Performance is acceptable
- [ ] Security measures working

## 🎉 Success Criteria

✅ **Ready for Production** when:
- All tests in this checklist pass
- No critical errors or warnings
- Documentation verified and accurate
- Example code works as shown
- Performance meets expectations
- Security verification complete

## 📝 Notes

Record any issues encountered during testing:

```
Issue 1: [Description]
Status: [Resolved/Pending]
Solution: [What fixed it]

Issue 2: [Description]
Status: [Resolved/Pending]
Solution: [What fixed it]
```

## 🆘 Troubleshooting

If tests fail, check:
1. WordPress version compatibility (5.6+ required for App Passwords)
2. Plugin is activated
3. Permalinks are flushed (Settings → Permalinks → Save)
4. PHP version compatibility (7.4+ recommended)
5. Database tables exist (`{prefix}_ca_*`)
6. File permissions for uploads directory
7. Error logs for detailed error messages

## 📞 Support

If you encounter issues not covered in this checklist:
1. Check PHP error logs
2. Check JavaScript console
3. Review documentation files
4. Check existing GitHub issues
5. Create new GitHub issue with details

---

**Remember:** This is an internal API. Keep authentication credentials secure and never expose them publicly.
