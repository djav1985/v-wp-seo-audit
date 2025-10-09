# V-WP-SEO-Audit WordPress Plugin

WordPress SEO Audit plugin - Analyze your website's SEO performance

## Description

This plugin provides comprehensive SEO audit functionality for WordPress. It analyzes websites for SEO issues, performance, meta tags, links, and more.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/v-wp-seo-audit` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add the shortcode `[v_wp_seo_audit]` to any page or post where you want to display the SEO audit tool

## Usage

To display the SEO audit tool on your website, simply add the following shortcode to any page or post:

```
[v_wp_seo_audit]
```

The plugin will display on the front-end where the shortcode is placed.

## Features

- Website SEO analysis
- Meta tags verification
- Link extraction and analysis
- Content analysis
- Performance testing
- PageSpeed Insights integration
- Front-end display via shortcode
- AJAX-based form submission for seamless user experience
- Client-side form validation
- Dynamic content updates without page redirects

## Technical Architecture

### AJAX Implementation

This plugin uses WordPress's admin-ajax.php for all AJAX communication instead of direct PHP file access. This provides:

1. **Better Security**: All requests go through WordPress's authentication system
2. **WordPress Integration**: Leverages WordPress hooks and filters
3. **No Direct File Access**: Eliminates "Direct access not allowed" errors
4. **Single Page Application Feel**: Content updates without page reloads

### AJAX Endpoints

The plugin registers the following AJAX actions:

1. **v_wp_seo_audit_validate**: Validates domain input
   - Action: `v_wp_seo_audit_validate`
   - Method: POST
   - Parameters: `domain`, `nonce`
   - Response: Success with validated domain or error message

2. **v_wp_seo_audit_generate_report**: Generates SEO audit report
   - Action: `v_wp_seo_audit_generate_report`
   - Method: POST
   - Parameters: `domain`, `nonce`
   - Response: HTML content of the audit report

3. **v_wp_seo_audit_pagepeeker**: Proxies thumbnail requests
   - Action: `v_wp_seo_audit_pagepeeker`
   - Method: GET
   - Parameters: `method`, `url`, `size`
   - Response: JSON data from PagePeeker API

### Form Workflow

1. User enters domain in the form
2. Client-side validation checks for valid domain format
3. AJAX request to `v_wp_seo_audit_validate` validates the domain
4. If valid, AJAX request to `v_wp_seo_audit_generate_report` generates the report
5. Report HTML is injected into the page without reload
6. Page scrolls to the report section automatically

## PHP_CodeSniffer (phpcs) Setup

This project uses [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) for code linting.

### Installation

Install globally (recommended):

```
composer global require squizlabs/php_codesniffer
```

Or install locally in your project:

```
composer require --dev squizlabs/php_codesniffer
```

### Configuration

Rules are defined in `phpcs.xml` in the project root. It uses PSR12 and WordPress standards, excluding asset and static folders.

### Usage

Run phpcs from the project root:

```
phpcs .
```

To auto-fix issues (where possible):

```
phpcbf .
```

### VS Code Integration

Install the following extension for linting in VS Code:

```vscode-extensions
wongjn.php-sniffer
```

Configure the extension to use your global or local phpcs path if needed.

## Testing

### Manual Testing

To test the plugin functionality:

1. **Install and Activate**: 
   - Upload the plugin to WordPress
   - Activate it through the Plugins menu

2. **Create a Test Page**:
   - Create a new page in WordPress
   - Add the shortcode `[v_wp_seo_audit]`
   - Publish the page

3. **Test Form Submission**:
   - Visit the page with the shortcode
   - Enter a domain name (e.g., "google.com")
   - Click "Analyze"
   - Verify that:
     - Progress bar shows while processing
     - No page redirect occurs
     - Report appears on the same page
     - Page scrolls to the report

4. **Test Validation**:
   - Try submitting without a domain - should show error
   - Try invalid domain format - should show error
   - Try valid domain - should show report

5. **Browser Console Check**:
   - Open browser developer tools
   - Check Console tab for JavaScript errors
   - Check Network tab to verify AJAX calls to admin-ajax.php
   - Verify no calls to index.php (old behavior)

### Expected Behavior

**Before Changes (Problem)**:
- Form would redirect to URLs like `http://localhost/wp-content/plugins/v-wp-seo-audit/index.php/www/example.com`
- "Direct access not allowed" error
- Page reloads on every action

**After Changes (Fixed)**:
- Form uses AJAX calls to admin-ajax.php
- No page redirects
- Content updates dynamically on the same page
- Smooth user experience with progress indicators
