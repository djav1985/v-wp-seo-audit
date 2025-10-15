# Update Button Functionality

## Overview

The UPDATE button in the SEO audit reports allows users to force a fresh re-analysis of a domain. When clicked, it removes all existing cached data (database records, PDFs, thumbnails) and performs a complete new analysis.

## User Experience

### Button Location
- The UPDATE button appears on every generated report
- Located in the header section near the "Generated on" timestamp
- Visual prompt: "Old data? **UPDATE** !"

### Button Behavior
When a user clicks the UPDATE button:
1. The domain input field is automatically filled with the current domain
2. A progress bar shows the re-analysis is in progress
3. All old data is removed (database, PDFs, thumbnails)
4. A fresh analysis is performed
5. The new report replaces the old one on the screen
6. The page automatically scrolls to the updated report

## Technical Implementation

### Frontend (JavaScript)

#### report.php (Template)
```javascript
$('#update_stat').on('click', function(e) {
    var href = $(this).attr("href");
    // If href points to external location, follow it.
    if (href.indexOf("#") < 0) {
        return true;
    }
    e.preventDefault();
    // Fill domain input and trigger the same flow as the Analyze button, but with force=true.
    var $domain = $("#domain");
    if ($domain.length) {
        $domain.val('<?php echo esc_js( $website['domain'] ); ?>');
        // Trigger the same validation -> generateReport flow wired to #submit
        // but with force update enabled
        $('#submit').data('force-update', true).trigger('click');
    } else {
        // Fallback: call generateReport directly if form is not present, with force=true.
        if (window.vWpSeoAudit && typeof window.vWpSeoAudit.generateReport === 'function') {
            window.vWpSeoAudit.generateReport('<?php echo esc_js( $website['domain'] ); ?>', {
                $container: $('.v-wpsa-container').first(),
                $errors: $('#errors'),
                $progressBar: $('#progress-bar'),
                force: true
            });
        }
    }
});
```

**Key Points:**
- Attaches click handler to `#update_stat` button
- Sets `force-update` data attribute on the submit button
- Triggers the normal analysis flow but with force mode enabled
- Has fallback if form elements are not present

#### base.js (Main JavaScript)
```javascript
// In submit button click handler (line 280)
var forceUpdate = $submit.data('force-update') === true;
if (forceUpdate) {
    $submit.removeData('force-update');
}

// In generateReport call (line 292)
window.vWpSeoAudit.generateReport(response.data.domain, {
    // ... other options
    force: forceUpdate,
    // ...
});

// In generateReport function (lines 132-134)
if (settings.force) {
    ajaxData.force = '1';
}
```

**Key Points:**
- Checks for `force-update` data attribute
- Passes `force: true` to generateReport function
- Adds `force: '1'` to AJAX request data

### Backend (PHP)

#### class-v-wpsa-ajax-handlers.php
```php
public static function generate_report() {
    // ... validation and setup code
    
    // Check if this is a forced update (from update button).
    $force_update = isset( $_POST['force'] ) && '1' === $_POST['force'];
    
    // ... existing website check
    
    if ( ! $website ) {
        // Website doesn't exist - needs analysis.
        $needs_analysis = true;
    } elseif ( $force_update ) {
        // Force update requested - delete everything and re-analyze from scratch.
        $needs_analysis = true;
        $wid            = null; // Set to null to force creation of new record.

        // Delete the complete website record from database.
        $db->delete_website( $website['id'] );

        // Delete old PDFs and thumbnails when force updating.
        V_WPSA_Helpers::delete_pdf( $domain );
        V_WPSA_Helpers::delete_pdf( $domain . '_pagespeed' );
    } elseif ( strtotime( $website['modified'] ) + $cache_time <= time() ) {
        // Website exists but data is stale - needs re-analysis.
        $needs_analysis = true;
        $wid            = $website['id'];

        // Delete old PDFs when re-analyzing.
        V_WPSA_Helpers::delete_pdf( $domain );
        V_WPSA_Helpers::delete_pdf( $domain . '_pagespeed' );
    }
    
    // Perform analysis if needed.
    if ( $needs_analysis ) {
        $result = V_WPSA_DB::analyze_website( $domain, $idn, $ip, $wid );
        // ... error handling
    }
    
    // Generate and return report HTML
    $content = V_WPSA_Report_Generator::generate_html_report( $domain );
    wp_send_json_success( array( 
        'html'  => $content,
        'nonce' => wp_create_nonce( 'v_wpsa_nonce' ),
    ) );
}
```

**Key Points:**
- Checks for `force` parameter in POST data
- When force update:
  - Deletes entire website record from database
  - Deletes all PDFs (regular and pagespeed)
  - Deletes cached thumbnail
  - Forces creation of new database record (wid = null)
- Performs fresh analysis with `V_WPSA_DB::analyze_website()`
- Returns new report HTML

#### class-v-wpsa-helpers.php
```php
public static function delete_pdf( $domain ) {
    // Get WordPress upload directory.
    $upload_dir = wp_upload_dir();
    $pdf_base   = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/pdf/';

    // Primary (new) simplified PDF paths.
    $simple_paths = array(
        $pdf_base . $domain . '.pdf',
        $pdf_base . $domain . '_pagespeed.pdf',
    );

    foreach ( $simple_paths as $pdf_path ) {
        if ( file_exists( $pdf_path ) ) {
            wp_delete_file( $pdf_path );
        }
    }

    // Also attempt to remove legacy nested PDF layout for backward compatibility.
    // ... legacy path deletion code
    
    // Also delete the cached thumbnail if the class is available.
    if ( class_exists( 'V_WPSA_Thumbnail' ) ) {
        V_WPSA_Thumbnail::delete_thumbnail( $domain );
    }

    return true;
}
```

**Key Points:**
- Deletes PDFs from both new and legacy paths
- Automatically deletes thumbnail when deleting PDF
- Uses WordPress functions (`wp_upload_dir()`, `wp_delete_file()`)

#### class-v-wpsa-db.php
```php
public function delete_website( $website_id ) {
    $tables = array(
        'website',
        'w3c',
        'pagespeed',
        'misc',
        'metatags',
        'links',
        'issetobject',
        'document',
        'content',
        'cloud',
    );

    foreach ( $tables as $table ) {
        $table_name = $this->get_table_name( $table );
        $where      = ( 'website' === $table ) ? array( 'id' => $website_id ) : array( 'wid' => $website_id );
        $result = $this->wpdb->delete( $table_name, $where, array( '%d' ) );
        if ( false === $result ) {
            return false;
        }
    }

    return true;
}
```

**Key Points:**
- Deletes records from all related tables
- Maintains referential integrity
- Returns false on any failure

## Data Flow

```
User Click UPDATE Button
    ↓
JavaScript: Fill domain input
    ↓
JavaScript: Set force-update flag
    ↓
JavaScript: Trigger #submit click
    ↓
JavaScript: Validate domain
    ↓
JavaScript: Call generateReport(force: true)
    ↓
AJAX: POST to admin-ajax.php
    ↓
    Parameters: {
        action: 'v_wpsa_generate_report',
        domain: 'example.com',
        force: '1',
        nonce: 'xxx'
    }
    ↓
PHP: Check force parameter
    ↓
PHP: Delete database records (10 tables)
    ↓
PHP: Delete PDF files
    ↓
PHP: Delete thumbnail
    ↓
PHP: Re-analyze website
    ↓
PHP: Generate new report HTML
    ↓
AJAX: Return JSON { success: true, data: { html: '...', nonce: '...' } }
    ↓
JavaScript: Replace container HTML
    ↓
JavaScript: Scroll to report
    ↓
User sees fresh report
```

## Differences Between Normal Analysis and Force Update

### Normal Analysis (No Force)
1. **Checks if domain exists in database**
2. **If exists and cache is fresh**: Returns cached report (no re-analysis)
3. **If exists and cache is stale**: Re-analyzes, updates existing record
4. **If not exists**: Analyzes and creates new record

### Force Update (Force = True)
1. **Always deletes ALL existing data** regardless of age
2. **Deletes database records** from all 10 tables
3. **Deletes PDF files** (regular + pagespeed)
4. **Deletes thumbnail** image
5. **Creates completely new record** (fresh ID)
6. **Performs full re-analysis** from scratch

## Testing the Update Button

### Manual Test Steps
1. Navigate to a page with the `[v_wpsa]` shortcode
2. Analyze a domain (e.g., "example.com")
3. Wait for the report to appear
4. Click the **UPDATE** button in the report header
5. Verify:
   - Progress bar appears
   - Report is regenerated
   - Page scrolls to new report
   - New "Generated on" timestamp is shown
   - All sections are refreshed

### Expected Behavior
- ✅ Old data removed from database
- ✅ Old PDF files deleted
- ✅ Old thumbnail deleted
- ✅ Fresh analysis performed
- ✅ New report displayed
- ✅ No page reload
- ✅ Smooth user experience

### Browser Console Verification
Open browser developer tools and check:
1. **Console tab**: No JavaScript errors
2. **Network tab**: 
   - POST request to `admin-ajax.php`
   - Action: `v_wpsa_generate_report`
   - Parameter: `force=1`
   - Response: JSON with new HTML

## Security Considerations

### Nonce Verification
All AJAX requests include nonce verification:
```php
check_ajax_referer( 'v_wpsa_nonce', 'nonce' );
```

### Input Sanitization
Domain input is validated and sanitized:
```php
$validation = V_WPSA_Validation::validate_domain( $domain_raw );
```

### Permission Checks
- No special permissions required (public-facing feature)
- Rate limiting can be added via WordPress transients if needed

## Performance Considerations

### Analysis Time
- Full re-analysis can take 5-30 seconds depending on:
  - Target website size
  - Network latency
  - Server resources

### Database Operations
Force update performs:
- ~10 DELETE queries (one per table)
- Multiple INSERT queries during re-analysis
- Consider indexing `wid` and `md5domain` columns

### File Operations
- PDF deletion: 2-4 files (regular + pagespeed + legacy paths)
- Thumbnail deletion: 1 file
- All operations use WordPress filesystem functions

## Troubleshooting

### Issue: Update button doesn't work
**Check:**
- JavaScript console for errors
- Network tab for AJAX request
- Verify `force=1` parameter is sent
- Check server error logs

### Issue: Old data not deleted
**Check:**
- `delete_website()` return value
- Database permissions
- File permissions for PDF/thumbnail deletion

### Issue: Analysis fails after update
**Check:**
- Domain is still accessible
- Network connectivity
- Server timeout settings
- Memory limits

## Future Enhancements

### Possible Improvements
1. **Confirmation Dialog**: Ask user to confirm before force update
2. **Loading Indicator**: Show specific message "Removing old data..."
3. **Partial Updates**: Option to refresh only specific sections
4. **Scheduled Updates**: Auto-update old reports via cron
5. **Update History**: Track when reports were last updated
6. **Diff View**: Show what changed between updates

## Related Files

### Frontend
- `templates/report.php` - Update button and click handler
- `assets/js/base.js` - Main JavaScript logic
- `assets/css/app.css` - Button styling

### Backend
- `includes/class-v-wpsa-ajax-handlers.php` - AJAX endpoint
- `includes/class-v-wpsa-db.php` - Database operations
- `includes/class-v-wpsa-helpers.php` - File deletion helpers
- `includes/class-v-wpsa-thumbnail.php` - Thumbnail management
- `includes/class-v-wpsa-report-generator.php` - Report generation

## Changelog

### Version 1.0.0 (Current)
- ✅ Initial implementation of force update functionality
- ✅ Complete data deletion (DB + files)
- ✅ Fresh re-analysis on update
- ✅ Smooth UX without page reload
- ✅ Fixed autoloader issue with V_WPSA_Thumbnail class

## Support

For issues or questions about the update button functionality:
1. Check browser console for JavaScript errors
2. Check WordPress debug.log for PHP errors
3. Verify all files are properly uploaded
4. Ensure proper file/directory permissions
5. Test with different domains to isolate issues
