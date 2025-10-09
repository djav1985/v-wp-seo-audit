# Request Flow Comparison

## Before Changes (Problem)

```
User enters domain "example.com"
       ↓
JavaScript submits form
       ↓
Redirects to: /wp-content/plugins/v-wp-seo-audit/index.php?r=parse/index&Website[domain]=example.com
       ↓
index.php checks for 'r' parameter
       ↓
If missing: "Direct access not allowed" ❌
       ↓
If present: Yii application runs
       ↓
ParseController validates domain
       ↓
Redirects to: /index.php?r=websitestat/generateHTML&domain=example.com
       ↓
WebsitestatController generates HTML
       ↓
Full page reload with new URL ❌
```

### Problems:
- ❌ Malformed URLs with plugin path
- ❌ "Direct access not allowed" errors
- ❌ Multiple page redirects
- ❌ Poor user experience
- ❌ Not using WordPress standards

---

## After Changes (Solution)

```
User enters domain "example.com"
       ↓
JavaScript validates domain format (client-side)
       ↓
       ✓ Valid format
       ↓
AJAX POST to: /wp-admin/admin-ajax.php
  - action: v_wp_seo_audit_validate
  - domain: example.com
  - nonce: security_token
       ↓
WordPress hooks system
       ↓
v_wp_seo_audit_ajax_validate_domain() function
       ↓
Initializes Yii framework
       ↓
Creates WebsiteForm model
       ↓
Validates domain
       ↓
Returns JSON: { success: true, data: { domain: "example.com" } }
       ↓
JavaScript receives validation success
       ↓
AJAX POST to: /wp-admin/admin-ajax.php
  - action: v_wp_seo_audit_generate_report
  - domain: example.com
  - nonce: security_token
       ↓
WordPress hooks system
       ↓
v_wp_seo_audit_ajax_generate_report() function
       ↓
Initializes Yii framework
       ↓
Creates WebsitestatController
       ↓
Executes generateHTML()
       ↓
Returns JSON: { success: true, data: { html: "<div>...</div>" } }
       ↓
JavaScript receives HTML
       ↓
Injects HTML into page (no reload) ✓
       ↓
Scrolls to results ✓
       ↓
User sees report on same page ✓
```

### Benefits:
- ✅ Standard WordPress AJAX pattern
- ✅ No page redirects
- ✅ No direct file access
- ✅ Better security with nonces
- ✅ Smooth user experience
- ✅ Progressive loading states

---

## Technical Flow Diagram

### Component Interaction

```
┌─────────────────────────────────────────────────────────────┐
│                        WordPress                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │                  Plugin: v-wp-seo-audit               │  │
│  │                                                       │  │
│  │  ┌─────────────────────────────────────────────┐    │  │
│  │  │          v-wp-seo-audit.php                 │    │  │
│  │  │                                              │    │  │
│  │  │  • Registers AJAX handlers                  │    │  │
│  │  │  • Initializes Yii framework               │    │  │
│  │  │  • Enqueues JavaScript with config         │    │  │
│  │  │                                              │    │  │
│  │  │  Handlers:                                   │    │  │
│  │  │  1. v_wp_seo_audit_ajax_validate_domain()  │    │  │
│  │  │  2. v_wp_seo_audit_ajax_generate_report()  │    │  │
│  │  │  3. v_wp_seo_audit_ajax_pagepeeker_proxy() │    │  │
│  │  └─────────────────────────────────────────────┘    │  │
│  │                         ↕                            │  │
│  │  ┌─────────────────────────────────────────────┐    │  │
│  │  │              js/base.js                      │    │  │
│  │  │                                              │    │  │
│  │  │  • Form submission handler                  │    │  │
│  │  │  • Client-side validation                   │    │  │
│  │  │  • AJAX request management                  │    │  │
│  │  │  • Dynamic content injection                │    │  │
│  │  │  • PagePeeker helper                        │    │  │
│  │  └─────────────────────────────────────────────┘    │  │
│  └───────────────────────────────────────────────────────┘  │
│                         ↕                                    │
│  ┌───────────────────────────────────────────────────────┐  │
│  │              wp-admin/admin-ajax.php                  │  │
│  │         (WordPress AJAX endpoint)                     │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                          ↕
┌─────────────────────────────────────────────────────────────┐
│                    Yii Framework                             │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  • WebsiteForm (validation)                          │  │
│  │  • WebsitestatController (report generation)         │  │
│  │  • ParseController (domain processing)               │  │
│  │  • Database operations                               │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                          ↕
┌─────────────────────────────────────────────────────────────┐
│                    WordPress Database                        │
│  • ca_website                                               │
│  • ca_content                                               │
│  • ca_links                                                 │
│  • ca_metatags                                              │
│  • ... (other tables)                                       │
└─────────────────────────────────────────────────────────────┘
```

---

## Data Flow Example

### Successful Domain Analysis

**Step 1: User Input**
```javascript
Input: "google.com"
↓ Client-side cleaning
Output: "google.com" (validated)
```

**Step 2: Domain Validation**
```http
POST /wp-admin/admin-ajax.php
Content-Type: application/x-www-form-urlencoded

action=v_wp_seo_audit_validate
domain=google.com
nonce=abc123xyz
```

**Step 3: Server Response**
```json
{
  "success": true,
  "data": {
    "domain": "google.com"
  }
}
```

**Step 4: Report Generation Request**
```http
POST /wp-admin/admin-ajax.php
Content-Type: application/x-www-form-urlencoded

action=v_wp_seo_audit_generate_report
domain=google.com
nonce=abc123xyz
```

**Step 5: Server Response**
```json
{
  "success": true,
  "data": {
    "html": "<div class='jumbotron'>...</div>"
  }
}
```

**Step 6: Client Update**
```javascript
// Inject HTML into page
$container.html(response.data.html);

// Scroll to results
$('html, body').animate({
  scrollTop: $container.offset().top - 100
}, 500);
```

### Error Handling Example

**Invalid Domain Input**
```http
POST /wp-admin/admin-ajax.php
action=v_wp_seo_audit_validate
domain=invalid..domain..
```

**Error Response**
```json
{
  "success": false,
  "data": {
    "message": "Please enter a valid domain name"
  }
}
```

**Client Display**
```javascript
$errors.html(response.data.message).show();
$progressBar.hide();
$('#submit').prop('disabled', false);
```
