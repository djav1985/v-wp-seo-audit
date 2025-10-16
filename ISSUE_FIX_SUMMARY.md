# Issue Fix Summary: Scoring and Report Display

## Issues Addressed

### 1. Scoring Inconsistency (Score changed from 77/100 to 53/100)

**Problem:** The score was being recalculated every time a template was rendered, causing inconsistent scores across different views (main page, report page, PDF).

**Root Cause:** The score calculation happens during template rendering when the template calls `$rateprovider->addCompare*()` methods. Each call adds points to the score. If the template renders multiple times, the score gets recalculated each time, potentially with different values.

**Solution:** Modified `includes/class-v-wpsa-report-generator.php` to:
1. Calculate the score BEFORE rendering the template (dry run)
2. Save the calculated score to `$data['website']['score']`
3. Persist the score to the database
4. Create a fresh `RateProvider` instance for the final render to prevent double-counting
5. Render the template with the correct score

**Files Changed:**
- `includes/class-v-wpsa-report-generator.php` - Lines 37-67 (HTML report) and Lines 117-147 (PDF report)

### 2. W3C Validity Messages Not Showing in PDF

**Problem:** W3C validation section in PDF showed only counts (e.g., "Errors: 35, Warnings: 1") without detailed error/warning messages in a table format.

**Root Cause:** The PDF template (`templates/pdf.php`) was missing the detailed messages table that exists in the HTML report template.

**Solution:** Added a detailed W3C messages table to the PDF template, displaying:
- Message type (Error/Warning)
- Line number
- Message text

**Files Changed:**
- `templates/pdf.php` - Lines 715-743 (added W3C messages table)

### 3. Score Showing 0 in Report

**Problem:** The score was showing as 0 in the report view while displaying correctly in the main page and PDF.

**Root Cause:** The score was being persisted to the database AFTER the template was rendered, so the template was showing the old score (0) from the database.

**Solution:** Calculate and persist the score BEFORE rendering the template, so the template always shows the correct, up-to-date score.

## Important Notes for Users

### Existing Reports May Not Show Detailed Data

If you have existing website analyses in your database, they may not show:
- Detailed W3C error/warning messages in a table
- Detailed list of images missing alt attributes

This is because these detailed data were either:
1. Not collected during the original analysis
2. Not stored in the database properly

### To See Detailed Reports

**You must re-analyze the website** by clicking the "UPDATE" button on an existing report. This will:
1. Collect fresh data from the website
2. Store detailed W3C messages and image information
3. Calculate and display the correct score
4. Show detailed tables with specific errors and warnings

### Score Changes

If you notice score changes after re-analyzing:
1. This is expected if the website content has changed
2. The new score reflects the current state of the website
3. The score is now consistent across all views (main page, report, PDF)

## Technical Details

### Score Calculation Flow (After Fix)

1. Fetch website data from database
2. **Calculate score** via dry run template render
3. Extract calculated score from `RateProvider`
4. Update `$data['website']['score']` with calculated score
5. Persist score to database
6. Create fresh `RateProvider` instance
7. Render template for display (uses saved score, fresh RateProvider prevents double-counting)

### W3C Messages Data Structure

W3C messages are stored in the database as JSON in the `messages` field of the `w3c` table. Each message has:
```php
array(
    'type' => 'error' or 'warning',
    'message' => 'The actual error/warning message',
    'line' => 123 // Line number where the issue occurs
)
```

### Image Alt Attributes Data Structure

Images missing alt attributes are stored in the database as JSON in the `images_missing_alt` field of the `content` table. Each entry is the `src` attribute of an image that's missing a non-empty alt attribute.

## Testing Recommendations

1. **Test score consistency:**
   - Analyze a website
   - Check the score on the report page
   - Download the PDF and verify the same score appears
   - Refresh the page and verify the score doesn't change

2. **Test W3C messages:**
   - Analyze a website with W3C validation errors
   - Verify the report shows error/warning counts
   - Verify the report shows a detailed table with specific error messages
   - Download PDF and verify the same messages appear

3. **Test image alt attributes:**
   - Analyze a website with images missing alt attributes
   - Verify the report shows which images are missing alt text
   - Verify the report shows the correct count of images with/without alt attributes

## Files Modified

1. `includes/class-v-wpsa-report-generator.php`
   - Modified `generate_html_report()` method
   - Modified `generate_pdf_report()` method
   - Added score calculation before template rendering
   - Added fresh RateProvider instance creation

2. `templates/pdf.php`
   - Added detailed W3C messages table (lines 715-743)
   - Matches the HTML report template structure

## Backward Compatibility

- **Database schema:** No changes required
- **API:** No changes to public methods
- **Templates:** PDF template enhanced, HTML template unchanged
- **Existing data:** Will continue to work, but may not show detailed messages until re-analyzed
