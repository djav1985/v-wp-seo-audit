# WordPress Native Database Class - Developer Guide

## Quick Start

The `V_WP_SEO_Audit_DB` class provides WordPress-native database access for the V-WP-SEO-Audit plugin.

### Basic Usage

```php
// Instantiate the database class
$db = new V_WP_SEO_Audit_DB();

// Get a website by domain
$website = $db->get_website_by_domain('example.com');

// Get all report data for a website
$data = $db->get_website_report_data($website['id']);
```

## API Reference

### get_table_name($table_name)
Get the full table name with WordPress prefix.

```php
$table_name = $db->get_table_name('website');
// Returns: 'wp_ca_website' (or with custom prefix)
```

### get_by_wid($table, $wid)
Get a single row from any table by website ID.

```php
$cloud = $db->get_by_wid('cloud', 123);
// Returns: array with all columns, or null if not found
```

**Available tables**: `cloud`, `content`, `document`, `issetobject`, `links`, `metatags`, `w3c`, `misc`, `pagespeed`

### get_website_by_domain($domain, $fields = array('*'))
Get website record by domain name.

```php
// Get all fields
$website = $db->get_website_by_domain('example.com');

// Get specific fields only
$website = $db->get_website_by_domain('example.com', array('id', 'domain', 'modified'));
```

**Returns**: Array with website data, or null if not found

### get_website_by_md5($md5_domain, $fields = array('*'))
Get website record by MD5 hash of domain.

```php
$md5 = md5('example.com');
$website = $db->get_website_by_md5($md5);
```

### delete_website($website_id)
Delete a website and all related records from all tables.

```php
$success = $db->delete_website(123);
// Returns: true on success, false on failure
```

**Note**: This automatically deletes records from all related tables (cloud, content, document, issetobject, links, metatags, w3c, misc, pagespeed).

### upsert_pagespeed($wid, $data, $lang_id)
Insert or update PageSpeed Insights data.

```php
$json_data = json_encode($pagespeed_results);
$success = $db->upsert_pagespeed(123, $json_data, 'en');
// Returns: true on success, false on failure
```

**Parameters**:
- `$wid`: Website ID
- `$data`: JSON-encoded PageSpeed data
- `$lang_id`: Language code (e.g., 'en', 'es', 'fr')

### get_pagespeed_data($wid, $lang_id)
Get PageSpeed Insights data for a website.

```php
$json_data = $db->get_pagespeed_data(123, 'en');
$results = json_decode($json_data, true);
```

**Returns**: JSON string, or null if not found

### get_website_report_data($wid)
Get all related table data for a website in one call (optimized).

```php
$data = $db->get_website_report_data(123);

// Returns array with all related data:
// array(
//     'cloud' => array(...),
//     'content' => array(...),
//     'document' => array(...),
//     'issetobject' => array(...),
//     'links' => array(...),
//     'metatags' => array(...),
//     'w3c' => array(...),
//     'misc' => array(...)
// )
```

**Note**: Missing records are returned as empty arrays, not null.

### get_website_count()
Get the total number of websites in the database.

```php
$count = $db->get_website_count();
```

## Migration Examples

### Example 1: Simple Query

**Before (Yii)**:
```php
$command = Yii::app()->db->createCommand();
$website = $command->select('*')
    ->from('{{website}}')
    ->where('md5domain=:md5', array(':md5' => md5($domain)))
    ->queryRow();
```

**After (WordPress)**:
```php
$db = new V_WP_SEO_Audit_DB();
$website = $db->get_website_by_domain($domain);
```

### Example 2: Multiple Related Queries

**Before (Yii)**:
```php
$command = Yii::app()->db->createCommand();
$cloud = $command->select('*')->from('{{cloud}}')->where('wid=:wid', array(':wid' => $wid))->queryRow();
$command->reset();
$content = $command->select('*')->from('{{content}}')->where('wid=:wid', array(':wid' => $wid))->queryRow();
$command->reset();
$document = $command->select('*')->from('{{document}}')->where('wid=:wid', array(':wid' => $wid))->queryRow();
$command->reset();
// ... 5 more similar queries
```

**After (WordPress)**:
```php
$db = new V_WP_SEO_Audit_DB();
$data = $db->get_website_report_data($wid);
$cloud = $data['cloud'];
$content = $data['content'];
$document = $data['document'];
```

### Example 3: Insert with Duplicate Key Update

**Before (Yii)**:
```php
$sql = 'INSERT INTO {{pagespeed}} (wid, data, lang_id) VALUES (:wid, :data, :lang_id) ON DUPLICATE KEY UPDATE data=:data';
$command = Yii::app()->db->createCommand($sql);
$command->bindParam(':wid', $wid);
$command->bindParam(':data', $jsonResult);
$command->bindParam(':lang_id', $lang_id);
$command->execute();
```

**After (WordPress)**:
```php
$db = new V_WP_SEO_Audit_DB();
$db->upsert_pagespeed($wid, $jsonResult, $lang_id);
```

### Example 4: Complex Delete with Transaction

**Before (Yii)**:
```php
$transaction = Yii::app()->db->beginTransaction();
$command = Yii::app()->db->createCommand();
try {
    $command->delete('{{website}}', 'id=:id', array(':id' => $website_id));
    $command->reset();
    $command->delete('{{cloud}}', 'wid=:id', array(':id' => $website_id));
    $command->reset();
    // ... 8 more delete statements
    $transaction->commit();
} catch (Exception $e) {
    $transaction->rollback();
    return false;
}
```

**After (WordPress)**:
```php
$db = new V_WP_SEO_Audit_DB();
return $db->delete_website($website_id);
```

## Best Practices

1. **Always instantiate the class**: Create a new instance when needed
   ```php
   $db = new V_WP_SEO_Audit_DB();
   ```

2. **Check for class existence** (in case of loading issues):
   ```php
   if (!class_exists('V_WP_SEO_Audit_DB')) {
       return false;
   }
   $db = new V_WP_SEO_Audit_DB();
   ```

3. **Handle null returns**: Methods return null when records don't exist
   ```php
   $website = $db->get_website_by_domain($domain);
   if (!$website) {
       // Handle not found case
   }
   ```

4. **Use optimized methods**: Use `get_website_report_data()` instead of multiple calls
   ```php
   // Good - one call
   $data = $db->get_website_report_data($wid);
   
   // Bad - multiple calls
   $cloud = $db->get_by_wid('cloud', $wid);
   $content = $db->get_by_wid('content', $wid);
   // ...
   ```

## Error Handling

The class uses WordPress's `$wpdb` error handling. To enable error display during development:

```php
global $wpdb;
$wpdb->show_errors();

$db = new V_WP_SEO_Audit_DB();
$result = $db->get_website_by_domain($domain);

if (!$result) {
    // Check for database errors
    if ($wpdb->last_error) {
        error_log('Database error: ' . $wpdb->last_error);
    }
}
```

## Security

All methods use `$wpdb->prepare()` for SQL injection protection:

- ✅ All user input is properly escaped
- ✅ All queries use prepared statements
- ✅ Table names are validated
- ✅ Field names are escaped with `esc_sql()`

## Performance Tips

1. **Fetch only needed fields**:
   ```php
   // Faster - only fetches 2 fields
   $website = $db->get_website_by_domain($domain, array('id', 'modified'));
   
   // Slower - fetches all fields
   $website = $db->get_website_by_domain($domain);
   ```

2. **Use report data method** for multiple related tables:
   ```php
   // Fast - one optimized query per table
   $data = $db->get_website_report_data($wid);
   ```

3. **Cache results** when appropriate:
   ```php
   $cache_key = 'website_' . md5($domain);
   $website = wp_cache_get($cache_key);
   if (false === $website) {
       $website = $db->get_website_by_domain($domain);
       wp_cache_set($cache_key, $website, '', 3600);
   }
   ```

## Troubleshooting

### Issue: Class not found
```
Fatal error: Class 'V_WP_SEO_Audit_DB' not found
```

**Solution**: Ensure the class is loaded in `v-wp-seo-audit.php`:
```php
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wp-seo-audit-db.php';
```

### Issue: Table not found
```
WordPress database error: Table 'wp_ca_website' doesn't exist
```

**Solution**: Deactivate and reactivate the plugin to create tables, or check the `$wpdb->prefix` value.

### Issue: Empty results
If queries return null or empty arrays unexpectedly, check:

1. Website ID exists: `$db->get_website_by_domain($domain)`
2. Related records exist for that ID
3. Database tables were created correctly
4. Table prefix is correct (`wp_ca_`)

## Future Enhancements

Potential additions to the database class:

- Bulk insert methods
- Query result caching
- Transaction support wrapper
- Batch operations
- Statistics and analytics queries

## Support

For issues or questions about the database class:
1. Check this guide
2. Review `DB_CONVERSION.md` for conversion examples
3. See `ARCHITECTURE.md` for system overview
4. Check `CONVERSION_NOTES.md` for implementation details
