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
