# Includes Directory

This directory contains WordPress-native helper classes and utilities for the v-wpsa plugin.

## Files

### class-v-wpsa-db.php

WordPress-native database wrapper class that replaces Yii framework database operations (CActiveRecord, CDbCommand).

**Purpose**: Provide WordPress-standard database access using `$wpdb` while maintaining the existing database schema.

**Features**:

- All queries use `$wpdb->prepare()` for security
- Maintains exact same database schema as Yii version
- Optimized methods reduce database roundtrips
- Follows WordPress coding standards

**Usage**:

```php
$db = new V_WPSA_DB();
$website = $db->get_website_by_domain('example.com');
```

**Documentation**:

- See `../DB_CLASS_GUIDE.md` for complete API reference
- See `../DB_CONVERSION.md` for conversion examples

## Standards

All files in this directory follow:

- WordPress Coding Standards (WPCS)
- WordPress Plugin Development Best Practices
- WordPress Security Guidelines
- PSR-4 autoloading conventions (class file naming)

## Testing

Files in this directory can be syntax-checked with:

```bash
vendor/bin/phpcs includes/ --standard=WordPress
```

## Future Files

Additional WordPress-native helper classes may be added here as more Yii components are converted, such as:

- Form validation helpers
- View rendering utilities
- PDF generation wrappers
- API client classes
