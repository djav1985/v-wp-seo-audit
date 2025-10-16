# Database Schema Fix - Missing Columns

## Issue
The W3C validation messages and images missing alt text were not being displayed in reports, even though the analyzers were collecting this data. The debug output showed "EMPTY ARRAY" for both fields.

## Root Cause
The database schema was missing two critical columns:
- `images_missing_alt` in the `ca_content` table
- `messages` in the `ca_w3c` table

These columns were referenced in the code but didn't exist in the database schema defined in `install.php`. When the analyzers tried to save data to these columns, the data was being silently discarded by the database.

## Solution
### 1. Updated Database Schema
Added the missing columns to the table creation SQL in `install.php`:

**ca_content table:**
- Added `images_missing_alt` mediumtext NOT NULL

**ca_w3c table:**
- Added `messages` mediumtext NOT NULL

### 2. Created Upgrade Function
Added `v_wpsa_upgrade_database()` function that:
- Checks if the columns exist in existing installations
- Adds the missing columns if they don't exist
- Runs automatically on plugin activation

### 3. Removed Debug Output
Removed temporary debug output from `templates/report.php` that was showing the "EMPTY ARRAY" messages.

## How to Apply the Fix

### For New Installations
The plugin will automatically create tables with the correct schema.

### For Existing Installations
1. **Deactivate and re-activate the plugin** - This will trigger the database upgrade function
2. **Or manually run the upgrade** - If you prefer not to deactivate:
   ```php
   // Run this once in WordPress admin or via WP-CLI
   if (function_exists('v_wpsa_upgrade_database')) {
       v_wpsa_upgrade_database();
   }
   ```

3. **Re-analyze websites** - After the schema is updated, click the UPDATE button on any existing reports to re-analyze websites and populate the new columns with data.

## What Users Will See After the Fix
- **W3C Validation Messages**: A detailed table showing each error and warning with line numbers
- **Images Missing Alt Text**: A table listing all images that are missing alt attributes
- **No More Debug Output**: The "Debug Info: EMPTY ARRAY" messages will be gone

## Technical Details
- The Image analyzer (`Webmaster/Source/Image.php`) extracts images without alt attributes
- The W3C Validation analyzer (`Webmaster/Source/Validation.php`) fetches detailed messages from the W3C validator API
- Data is stored as JSON in mediumtext columns for flexibility
- Data is automatically decoded when retrieved via `V_WPSA_DB::decode_json_fields()`

## Files Modified
1. `install.php` - Added columns to schema and upgrade function
2. `templates/report.php` - Removed debug output (lines 434-451 and 886-903)
