# WordPress Native Database Conversion

## Overview

This document describes the conversion from Yii framework database operations (CActiveRecord, CDbCommand) to WordPress native `$wpdb` operations while maintaining the exact same database schema and table structure.

## What Changed

### New Database Class

Created `includes/class-v-wp-seo-audit-db.php` - A WordPress-native database wrapper class that provides:

- **Table management**: Automatic table name prefixing with `wp_ca_`
- **CRUD operations**: Get, insert, update, delete operations using $wpdb
- **Query building**: Safe, prepared SQL queries with proper escaping
- **Report data**: Optimized methods to fetch all related data in one call

### Converted Files

#### 1. `protected/models/Website.php`
**Before:**
```php
$transaction = Yii::app()->db->beginTransaction();
$command = Yii::app()->db->createCommand();
$command->delete('{{website}}', 'id=:id', array(':id' => $website_id));
// ... multiple delete calls with reset()
$transaction->commit();
```

**After:**
```php
$db = new V_WP_SEO_Audit_DB();
$db->delete_website($website_id);
```

#### 2. `protected/models/WebsiteForm.php`
**Before:**
```php
$command = Yii::app()->db->createCommand();
$website = $command->select('modified, id')
                  ->from('{{website}}')
                  ->where('md5domain=:id', array(':id' => md5($this->domain)))
                  ->queryRow();
```

**After:**
```php
$db = new V_WP_SEO_Audit_DB();
$website = $db->get_website_by_domain($this->domain, array('modified', 'id'));
```

#### 3. `protected/controllers/WebsitestatController.php`
**Before:**
```php
$this->command = Yii::app()->db->createCommand();
$this->cloud = $this->command->select('*')->from('{{cloud}}')->where('wid=:wid', array(':wid' => $this->wid))->queryRow();
$this->command->reset();
// ... 8 more similar queries
```

**After:**
```php
$this->command = new V_WP_SEO_Audit_DB();
$data = $this->command->get_website_report_data($this->wid);
$this->cloud = $data['cloud'];
// ... single method call fetches all data
```

#### 4. `protected/controllers/ParseController.php`
**Before:**
```php
$sql = 'INSERT INTO {{pagespeed}} (wid, data, lang_id) VALUES (:wid, :data, :lang_id) ON DUPLICATE KEY UPDATE data=:data';
$command = Yii::app()->db->createCommand($sql);
$command->bindParam(':wid', $wid);
$command->bindParam(':data', $jsonResult);
$command->bindParam(':lang_id', $lang_id);
$command->execute();
```

**After:**
```php
$db = new V_WP_SEO_Audit_DB();
$db->upsert_pagespeed($wid, $jsonResult, $lang_id);
```

## Database Schema

**No changes to database schema**. All tables remain exactly the same:
- `wp_ca_website` - Main website data
- `wp_ca_cloud` - Tag cloud data
- `wp_ca_content` - Content analysis
- `wp_ca_document` - Document structure
- `wp_ca_issetobject` - Feature flags
- `wp_ca_links` - Link analysis
- `wp_ca_metatags` - Meta tag data
- `wp_ca_misc` - Miscellaneous data
- `wp_ca_w3c` - W3C validation
- `wp_ca_pagespeed` - PageSpeed data

## Benefits

1. **WordPress-native**: Uses $wpdb with WordPress best practices
2. **Simplified code**: Single method calls replace multi-line Yii queries
3. **Better performance**: Optimized queries fetch related data in one call
4. **Security**: Proper use of $wpdb->prepare() for all queries
5. **Maintainability**: Easier to understand and debug
6. **No breaking changes**: Same schema, same data, same functionality

## Available Methods

### V_WP_SEO_Audit_DB Class Methods

```php
// Get full table name with prefix
$table_name = $db->get_table_name('website'); // Returns 'wp_ca_website'

// Get row by website ID
$data = $db->get_by_wid('cloud', 123);

// Get website by domain
$website = $db->get_website_by_domain('example.com');
$website = $db->get_website_by_domain('example.com', array('id', 'domain'));

// Get website by MD5 hash
$website = $db->get_website_by_md5(md5('example.com'));

// Delete website and all related records
$success = $db->delete_website(123);

// Insert or update PageSpeed data
$success = $db->upsert_pagespeed(123, $json_data, 'en');

// Get PageSpeed data
$data = $db->get_pagespeed_data(123, 'en');

// Get all report data for a website (optimized)
$data = $db->get_website_report_data(123);
// Returns: array('cloud' => [...], 'content' => [...], etc.)

// Get total website count
$count = $db->get_website_count();
```

## Testing

To test the conversion:

1. **Domain validation**: Submit a domain through the form
2. **Report generation**: Generate a report for a domain
3. **PDF download**: Download a PDF report
4. **PageSpeed**: Fetch PageSpeed data
5. **Cleanup**: Verify daily cleanup still works

All operations should work exactly as before, just using WordPress native database methods.

## Future Work

The following still use Yii:
- ActiveRecord model base class (for backward compatibility)
- View rendering (Yii templates)
- PDF generation (Yii-integrated TCPDF)
- Controller base classes

These can be converted in future phases if needed.
