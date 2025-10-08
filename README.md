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
