# Update Button Visual Flow Diagram

## High-Level Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                                                             │  │
│  │     Website Review for example.com                          │  │
│  │     Generated on January 15 2025 02:30 PM                   │  │
│  │                                                             │  │
│  │     Old data?  [ UPDATE ]  !                                │  │
│  │                   ↑                                         │  │
│  │                   │ User clicks                             │  │
│  │                   │                                         │  │
│  └───────────────────┼─────────────────────────────────────────┘  │
└────────────────────────┼────────────────────────────────────────┘
                         │
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                      JAVASCRIPT LAYER                            │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  report.php: $('#update_stat').click()                     │  │
│  │    │                                                       │  │
│  │    ├─→ Fill domain input with 'example.com'               │  │
│  │    ├─→ Set force-update flag: data('force-update', true)  │  │
│  │    └─→ Trigger submit button click                        │  │
│  │                                                             │  │
│  │  base.js: $('#submit').click()                             │  │
│  │    │                                                       │  │
│  │    ├─→ Validate domain                                    │  │
│  │    ├─→ Check force-update flag                            │  │
│  │    └─→ Call generateReport(force: true)                   │  │
│  │                                                             │  │
│  │  base.js: generateReport()                                 │  │
│  │    │                                                       │  │
│  │    ├─→ Show progress bar                                  │  │
│  │    ├─→ Prepare AJAX data with force: '1'                  │  │
│  │    └─→ POST to admin-ajax.php                             │  │
│  └────────────────────────┬───────────────────────────────────┘  │
└─────────────────────────────┼────────────────────────────────────┘
                              │
                              │ AJAX REQUEST
                              │ {
                              │   action: 'v_wpsa_generate_report',
                              │   domain: 'example.com',
                              │   force: '1',  ← KEY PARAMETER
                              │   nonce: 'xxx'
                              │ }
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                       PHP BACKEND                                │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  class-v-wpsa-ajax-handlers.php                            │  │
│  │    generate_report()                                       │  │
│  │                                                             │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 1. Verify nonce (security check)         │            │  │
│  │    │    check_ajax_referer('v_wpsa_nonce')    │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  │                         ↓                                  │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 2. Check force parameter                 │            │  │
│  │    │    $force = $_POST['force'] === '1'      │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  │                         ↓                                  │  │
│  │           ┌─────────────────────────┐                      │  │
│  │           │  Is force = true?       │                      │  │
│  │           └───────┬─────────────────┘                      │  │
│  │                   │ YES                                    │  │
│  │                   ↓                                        │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 3. DELETE ALL EXISTING DATA              │            │  │
│  │    │                                          │            │  │
│  │    │  class-v-wpsa-db.php                     │            │  │
│  │    │    delete_website($website_id)           │            │  │
│  │    │      ├─→ wp_ca_website                   │            │  │
│  │    │      ├─→ wp_ca_w3c                       │            │  │
│  │    │      ├─→ wp_ca_pagespeed                 │            │  │
│  │    │      ├─→ wp_ca_misc                      │            │  │
│  │    │      ├─→ wp_ca_metatags                  │            │  │
│  │    │      ├─→ wp_ca_links                     │            │  │
│  │    │      ├─→ wp_ca_issetobject               │            │  │
│  │    │      ├─→ wp_ca_document                  │            │  │
│  │    │      ├─→ wp_ca_content                   │            │  │
│  │    │      └─→ wp_ca_cloud                     │            │  │
│  │    │                                          │            │  │
│  │    │  class-v-wpsa-helpers.php                │            │  │
│  │    │    delete_pdf('example.com')             │            │  │
│  │    │      ├─→ example.com.pdf                 │            │  │
│  │    │      ├─→ example.com_pagespeed.pdf       │            │  │
│  │    │      └─→ Calls delete_thumbnail()        │            │  │
│  │    │                                          │            │  │
│  │    │  class-v-wpsa-thumbnail.php              │            │  │
│  │    │    delete_thumbnail('example.com')       │            │  │
│  │    │      └─→ MD5(example.com).jpg            │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  │                         ↓                                  │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 4. RE-ANALYZE WEBSITE                    │            │  │
│  │    │                                          │            │  │
│  │    │  class-v-wpsa-db.php                     │            │  │
│  │    │    analyze_website($domain, $idn, $ip)   │            │  │
│  │    │      ├─→ Fetch website HTML              │            │  │
│  │    │      ├─→ Parse meta tags                 │            │  │
│  │    │      ├─→ Extract links                   │            │  │
│  │    │      ├─→ Analyze content                 │            │  │
│  │    │      ├─→ Check W3C validation            │            │  │
│  │    │      ├─→ Generate keyword cloud          │            │  │
│  │    │      └─→ Save to database (NEW ID)       │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  │                         ↓                                  │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 5. GENERATE NEW REPORT HTML              │            │  │
│  │    │                                          │            │  │
│  │    │  class-v-wpsa-report-generator.php       │            │  │
│  │    │    generate_html_report($domain)         │            │  │
│  │    │      ├─→ Get full report data from DB    │            │  │
│  │    │      ├─→ Render templates/report.php     │            │  │
│  │    │      └─→ Return HTML string              │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  │                         ↓                                  │  │
│  │    ┌──────────────────────────────────────────┐            │  │
│  │    │ 6. RETURN JSON RESPONSE                  │            │  │
│  │    │                                          │            │  │
│  │    │  wp_send_json_success([                  │            │  │
│  │    │    'html' => '<div>...</div>',           │            │  │
│  │    │    'nonce' => 'fresh_nonce'              │            │  │
│  │    │  ])                                      │            │  │
│  │    └──────────────────────────────────────────┘            │  │
│  └───────────────────────┬───────────────────────────────────┘  │
└─────────────────────────────┼────────────────────────────────────┘
                              │
                              │ AJAX RESPONSE
                              │ {
                              │   success: true,
                              │   data: {
                              │     html: '<div>...</div>',
                              │     nonce: 'fresh_nonce'
                              │   }
                              │ }
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                      JAVASCRIPT LAYER                            │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  base.js: request.done()                                   │  │
│  │    │                                                       │  │
│  │    ├─→ Hide progress bar                                  │  │
│  │    ├─→ Extract HTML from response.data.html               │  │
│  │    ├─→ Update nonce (security token)                      │  │
│  │    ├─→ Replace container HTML                             │  │
│  │    └─→ Scroll to report                                   │  │
│  └───────────────────────┬───────────────────────────────────┘  │
└─────────────────────────────┼────────────────────────────────────┘
                              │
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│                        USER INTERFACE                            │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │                                                             │  │
│  │     Website Review for example.com                          │  │
│  │     Generated on January 15 2025 03:45 PM  ← UPDATED!      │  │
│  │                                                             │  │
│  │     Old data?  [ UPDATE ]  !                                │  │
│  │                                                             │  │
│  │     ┌─────────────────────────────────────────────────┐    │  │
│  │     │  SEO Content                                     │    │  │
│  │     │  • Title: Updated Title                          │    │  │
│  │     │  • Description: Fresh description                │    │  │
│  │     │  • All sections show FRESH DATA                  │    │  │
│  │     └─────────────────────────────────────────────────┘    │  │
│  │                                                             │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Timing Breakdown

```
Action                                    Time (approx)
────────────────────────────────────────────────────
User clicks UPDATE button                 0ms
JavaScript processes click                10ms
JavaScript prepares AJAX request          20ms
AJAX request sent to server               50ms
Server verifies security                  10ms
Server deletes database records           100ms
Server deletes PDF files                  50ms
Server deletes thumbnail                  20ms
Server re-analyzes website                5,000-25,000ms ← Longest step
Server generates report HTML              500ms
Server returns JSON response              50ms
JavaScript receives response              50ms
JavaScript updates DOM                    100ms
Page scrolls to report                    500ms
────────────────────────────────────────────────────
TOTAL TIME:                               ~6-27 seconds
```

## Key Decision Points

```
┌─────────────────────────────────────────┐
│  Is force parameter set to '1'?        │
└──────────────┬──────────────────────────┘
               │
       ┌───────┴────────┐
       │                │
      YES              NO
       │                │
       ↓                ↓
┌──────────────┐  ┌─────────────────────┐
│ FORCE UPDATE │  │ NORMAL BEHAVIOR     │
│              │  │                     │
│ • Delete ALL │  │ • Check cache age   │
│ • Fresh ID   │  │ • Reuse existing ID │
│ • New record │  │ • Update if stale   │
└──────────────┘  └─────────────────────┘
```

## Database Schema Impact

```
Before UPDATE:
┌─────────────────────────────────────────────────────┐
│ wp_ca_website                                       │
│  id=123, domain='example.com', modified='2025-01-15'│
└──────────────────────┬──────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ↓              ↓              ↓
┌──────────────┐ ┌──────────┐ ┌──────────┐
│ wp_ca_content│ │wp_ca_links│ │wp_ca_meta│
│   wid=123    │ │  wid=123  │ │ wid=123  │
└──────────────┘ └──────────┘ └──────────┘
    + 7 more related tables with wid=123

After UPDATE:
┌─────────────────────────────────────────────────────┐
│ wp_ca_website                                       │
│  id=124, domain='example.com', modified='2025-01-15'│ ← NEW ID!
└──────────────────────┬──────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ↓              ↓              ↓
┌──────────────┐ ┌──────────┐ ┌──────────┐
│ wp_ca_content│ │wp_ca_links│ │wp_ca_meta│
│   wid=124    │ │  wid=124  │ │ wid=124  │ ← NEW RECORDS!
└──────────────┘ └──────────┘ └──────────┘
    + 7 more related tables with wid=124
```

## File System Impact

```
Before UPDATE:
wp-content/uploads/seo-audit/
├── pdf/
│   ├── example.com.pdf          (50KB, created 2 days ago)
│   └── example.com_pagespeed.pdf (30KB, created 2 days ago)
└── thumbnails/
    └── 5d41402abc4b2a76b9719d911017c592.jpg (20KB, cached)

During UPDATE (deletion phase):
wp-content/uploads/seo-audit/
├── pdf/
│   ├── (empty - files deleted)
└── thumbnails/
    └── (empty - file deleted)

After UPDATE (recreation phase):
wp-content/uploads/seo-audit/
├── pdf/
│   ├── example.com.pdf          (52KB, FRESH - just created)
│   └── example.com_pagespeed.pdf (31KB, FRESH - just created)
└── thumbnails/
    └── 5d41402abc4b2a76b9719d911017c592.jpg (21KB, FRESH)
```

## Security Flow

```
┌────────────────────────────────────────────────────┐
│ 1. User clicks UPDATE                               │
└───────────────────────┬────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────────┐
│ 2. JavaScript includes nonce in AJAX request       │
│    data: { nonce: 'abc123...' }                    │
└───────────────────────┬────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────────┐
│ 3. PHP verifies nonce                              │
│    check_ajax_referer('v_wpsa_nonce', 'nonce')     │
│                                                    │
│    ┌──────────────┐                               │
│    │ Valid nonce? │                               │
│    └──────┬───────┘                               │
│           │                                        │
│    ┌──────┴────────┐                              │
│    │ YES           │ NO                           │
│    ↓               ↓                               │
│  Continue      Return error                        │
│  processing    "Invalid nonce"                     │
└───────────────────────┬────────────────────────────┘
                        ↓
┌────────────────────────────────────────────────────┐
│ 4. Process force update safely                     │
│    • Sanitize domain input                         │
│    • Validate domain format                        │
│    • Use prepared statements for DB                │
│    • Use WordPress file functions                  │
└────────────────────────────────────────────────────┘
```

## Error Handling Flow

```
┌────────────────────────────────────────────────────┐
│ User clicks UPDATE                                  │
└───────────────────────┬────────────────────────────┘
                        ↓
                ┌───────────────┐
                │ Any error?    │
                └───────┬───────┘
                        │
        ┌───────────────┼───────────────┐
        │ YES           │ NO            │
        ↓               ↓               
┌──────────────┐  ┌──────────────────┐
│ Error Type:  │  │ Success:         │
│              │  │ • Show new report│
│ Network Error│  │ • Update nonce   │
│  → Show error│  │ • Scroll to top  │
│  → Re-enable │  │                  │
│                │                  │
│ Server Error │                  
│  → Show error│                  
│  → Log details│                  
│                │                  
│ Timeout      │                  
│  → Show error│                  
│  → Suggest   │                  
│     retry    │                  
└──────────────┘                  
```

## Performance Optimization Points

```
┌─────────────────────────────────────────────────────┐
│ 1. Database Deletion                                │
│    Optimization: Use single DELETE with JOIN        │
│    Current: 10 separate DELETE queries              │
│    Impact: ~100ms vs ~30ms                          │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 2. File Deletion                                    │
│    Optimization: Already optimal (uses wp_delete)   │
│    Current: Check file_exists, then delete          │
│    Impact: Minimal overhead                         │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 3. Website Analysis                                 │
│    Optimization: Cannot be optimized much           │
│    Current: Fetch remote website, parse HTML        │
│    Impact: 5-25 seconds (network dependent)         │
│    Note: This is the bottleneck                     │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│ 4. Report Generation                                │
│    Optimization: Template caching (already done)    │
│    Current: Load template, populate vars, render    │
│    Impact: ~500ms (acceptable)                      │
└─────────────────────────────────────────────────────┘
```

## User Experience Considerations

### Good UX Elements ✅
- Progress bar shows activity
- No page reload
- Auto-scroll to report
- Clear "UPDATE" button label
- Contextual placement (near timestamp)

### Potential Improvements 💡
- Confirmation dialog before update
- Show estimated time remaining
- Partial progress updates
- Ability to cancel analysis
- Compare old vs new report
- Notification when complete
