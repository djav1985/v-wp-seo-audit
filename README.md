<div id="top">

<!-- HEADER STYLE: CLASSIC -->
<div align="center">

<img src="v-wp-seo-audit.jpg" width="60%" alt="project-logo">

# V-WP-SEO-AUDIT

<em></em>

<!-- BADGES -->
<img src="https://img.shields.io/github/license/djav1985/v-wp-seo-audit?style=flat-square&logo=opensourceinitiative&logoColor=white&color=0080ff" alt="license">
<img src="https://img.shields.io/github/last-commit/djav1985/v-wp-seo-audit?style=flat-square&logo=git&logoColor=white&color=0080ff" alt="last-commit">
<img src="https://img.shields.io/github/languages/top/djav1985/v-wp-seo-audit?style=flat-square&color=0080ff" alt="repo-top-language">
<img src="https://img.shields.io/github/languages/count/djav1985/v-wp-seo-audit?style=flat-square&color=0080ff" alt="repo-language-count">

<em>Built with the tools and technologies:</em>

<img src="https://img.shields.io/badge/JSON-000000.svg?style=flat-square&logo=JSON&logoColor=white" alt="JSON">
<img src="https://img.shields.io/badge/Composer-885630.svg?style=flat-square&logo=Composer&logoColor=white" alt="Composer">
<img src="https://img.shields.io/badge/XML-005FAD.svg?style=flat-square&logo=XML&logoColor=white" alt="XML">
<img src="https://img.shields.io/badge/PHP-777BB4.svg?style=flat-square&logo=PHP&logoColor=white" alt="PHP">

</div>
<br>

---

## Table of Contents

1. [Table of Contents](#table-of-contents)
2. [Overview](#overview)
3. [Features](#features)
4. [Project Structure](#project-structure)
    4.1. [Project Index](#project-index)
5. [Getting Started](#getting-started)
    5.1. [Prerequisites](#prerequisites)
    5.2. [Installation](#installation)
    5.3. [Usage](#usage)
8. [License](#license)

---

## Overview

**V-WP-SEO-Audit** is a comprehensive WordPress plugin that provides in-depth SEO analysis and reporting for any website. Built with both human users and AI integrations in mind, it delivers detailed insights into website performance, technical SEO compliance, content quality, and optimization opportunities.

### Key Capabilities

- **Comprehensive SEO Analysis**: Evaluates 50+ SEO factors including meta tags, content structure, technical compliance, and performance metrics
- **Automated Scoring System**: Provides weighted scores across multiple categories with actionable recommendations
- **PDF Report Generation**: Creates professional, downloadable PDF reports with full analysis details
- **AI-Ready Function Calling**: Dedicated `V_WPSA_external_generation()` function for seamless AI chatbot and external system integration
- **Caching & Performance**: Intelligent 24-hour caching system to optimize performance and reduce API calls
- **Google PageSpeed Integration**: Real-time performance metrics from Google PageSpeed Insights API
- **Multi-Language Support**: Handles internationalized domain names (IDN) and multiple language content analysis
- **WordPress Native**: Built with WordPress best practices using hooks, filters, and standard database operations

### Use Cases

- **Website Owners**: Get instant SEO audits with actionable recommendations
- **SEO Professionals**: Generate comprehensive reports for clients
- **AI Chatbots**: Integrate SEO analysis capabilities via function calling
- **Development Teams**: Automate SEO monitoring and reporting
- **Content Managers**: Validate content quality and technical compliance

---

## Features

|      | Component          | Details                                                                                     |
| :--- | :----------------- | :------------------------------------------------------------------------------------------ |
| ⚙️  | **Architecture**   | <ul><li>Modular PHP structure with separate classes for SEO analysis</li><li>Uses Composer for dependency management</li></ul> |
| 🔩 | **Code Quality**   | <ul><li>Adheres to PSR-12 coding standards</li><li>Uses PHP_CodeSniffer (`phpcs.xml`) for linting</li></ul> |
| 📄 | **Documentation**  | <ul><li>Basic README with project overview</li><li>Includes inline PHPDoc comments for classes and methods</li></ul> |
| 🔌 | **Integrations**    | <ul><li>Integrates with WordPress via hooks and filters</li><li>Uses Composer for dependency injection</li></ul> |
| 🧩 | **Modularity**      | <ul><li>Separated concerns: SEO analysis, report generation, configuration</li><li>Uses classes and namespaces for organization</li></ul> |
| 🧪 | **Testing**         | <ul><li>Includes PHPUnit tests located in `/tests`</li><li>Tests cover core SEO analysis functions</li></ul> |
| ⚡️  | **Performance**     | <ul><li>Lightweight, minimal external dependencies</li><li>Optimized for WordPress plugin environment</li></ul> |
| 🛡️ | **Security**        | <ul><li>Sanitizes user input and outputs</li><li>Uses nonces and capability checks for WordPress integration</li></ul> |
| 📦 | **Dependencies**    | <ul><li>Primary dependency managed via `composer.json` and `composer.lock`</li><li>Includes PHP packages for code quality and testing</li></ul> |

---

## Project Structure

```sh
└── v-wp-seo-audit/
    ├── .github
    │   └── copilot-instructions.md
    ├── AGENTS.md
    ├── CHANGELOG.md
    ├── LICENSE
    ├── README.md
    ├── SUMMARY.md
    ├── Webmaster
    │   ├── Google
    │   │   └── PageSpeedInsights.php
    │   ├── Matrix
    │   │   └── SearchMatrix.php
    │   ├── Rates
    │   │   ├── RateProvider.php
    │   │   └── rates.php
    │   ├── Source
    │   │   ├── AnalyticsFinder.php
    │   │   ├── Content.php
    │   │   ├── Document.php
    │   │   ├── Favicon.php
    │   │   ├── Image.php
    │   │   ├── Links.php
    │   │   ├── MetaTags.php
    │   │   ├── Optimization.php
    │   │   ├── SeoAnalyse.php
    │   │   └── Validation.php
    │   ├── TagCloud
    │   │   ├── CommonWords
    │   │   │   ├── da.php
    │   │   │   ├── de.php
    │   │   │   ├── en.php
    │   │   │   ├── es.php
    │   │   │   ├── fr.php
    │   │   │   ├── it.php
    │   │   │   ├── pt.php
    │   │   │   ├── ru.php
    │   │   │   └── sv.php
    │   │   └── TagCloud.php
    │   └── Utils
    │       ├── Helper.php
    │       └── IDN.php
    ├── assets
    │   ├── .htaccess
    │   ├── css
    │   │   ├── app.css
    │   │   ├── bootstrap.min.css
    │   │   └── fontawesome.min.css
    │   ├── img
    │   │   ├── advice.png
    │   │   ├── advice_important.png
    │   │   ├── advice_success.png
    │   │   ├── advice_warning.png
    │   │   ├── analytics
    │   │   │   ├── clicky.png
    │   │   │   ├── googleanalytics.png
    │   │   │   ├── liveinternet.png
    │   │   │   ├── piwik.png
    │   │   │   └── quantcast.png
    │   │   ├── computer-monitor.png
    │   │   ├── content.png
    │   │   ├── desktop.png
    │   │   ├── error.png
    │   │   ├── glyphicons-halflings-white.png
    │   │   ├── glyphicons-halflings.png
    │   │   ├── isset_0.png
    │   │   ├── isset_1.png
    │   │   ├── link.png
    │   │   ├── loader.gif
    │   │   ├── logo.png
    │   │   ├── mobile.png
    │   │   ├── neutral.png
    │   │   ├── not-available.png
    │   │   ├── review.png
    │   │   ├── speed.png
    │   │   ├── success.png
    │   │   ├── tags.png
    │   │   └── warning.png
    │   ├── js
    │   │   ├── base.js
    │   │   ├── bootstrap.bundle.min.js
    │   │   ├── jquery.flot.js
    │   │   ├── jquery.flot.pie.js
    │   │   └── jquery.min.js
    │   └── webfonts
    │       ├── fa-brands-400.eot
    │       ├── fa-brands-400.svg
    │       ├── fa-brands-400.ttf
    │       ├── fa-brands-400.woff
    │       ├── fa-brands-400.woff2
    │       ├── fa-regular-400.eot
    │       ├── fa-regular-400.svg
    │       ├── fa-regular-400.ttf
    │       ├── fa-regular-400.woff
    │       ├── fa-regular-400.woff2
    │       ├── fa-solid-900.eot
    │       ├── fa-solid-900.svg
    │       ├── fa-solid-900.ttf
    │       ├── fa-solid-900.woff
    │       ├── fa-solid-900.woff2
    │       ├── quicksand-700.woff2
    │       └── roboto-flex.woff2
    ├── composer.json
    ├── composer.lock
    ├── config
    │   ├── badwords.php
    │   ├── config.php
    │   ├── domain_restriction.php
    │   └── main.php
    ├── deactivation.php
    ├── includes
    │   ├── class-v-wpsa-ajax-handlers.php
    │   ├── class-v-wpsa-config.php
    │   ├── class-v-wpsa-db.php
    │   ├── class-v-wpsa-helpers.php
    │   ├── class-v-wpsa-report-generator.php
    │   ├── class-v-wpsa-report-service.php
    │   ├── class-v-wpsa-score-calculator.php
    │   ├── class-v-wpsa-thumbnail.php
    │   ├── class-v-wpsa-utils.php
    │   ├── class-v-wpsa-validation.php
    │   └── class-v-wpsa-website.php
    ├── install.php
    ├── phpcs.xml
    ├── templates
    │   ├── layout.php
    │   ├── main.php
    │   ├── pdf.php
    │   ├── report.php
    │   └── widgets.php
    ├── uninstall.php
    └── v-wp-seo-audit.php
```

### Project Index

<details open>
	<summary><b><code>V-WP-SEO-AUDIT/</code></b></summary>
	<!-- __root__ Submodule -->
	<details>
		<summary><b>__root__</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ __root__</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/deactivation.php'>deactivation.php</a></b></td>
					<td style='padding: 8px;'>- Handles cleanup procedures during plugin deactivation by unscheduling the daily cleanup cron job, ensuring no residual scheduled tasks remain<br>- Integrates into the broader plugin architecture to maintain system integrity and prevent unnecessary background processes after deactivation<br>- Facilitates smooth plugin lifecycle management within the WordPress environment.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/uninstall.php'>uninstall.php</a></b></td>
					<td style='padding: 8px;'>- Removes all database tables and plugin-specific options associated with the v-wpsa plugin during uninstallation, ensuring complete cleanup of plugin data from the WordPress environment<br>- This process maintains database integrity and prevents residual data from persisting after plugin removal, supporting a clean and efficient uninstallation within the overall WordPress architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/LICENSE'>LICENSE</a></b></td>
					<td style='padding: 8px;'>- Provides the foundational licensing information that governs the entire project, clarifying usage rights and legal protections<br>- Ensures legal clarity for users and contributors, supporting open-source collaboration and distribution within the broader software architecture<br>- Acts as a legal cornerstone, facilitating responsible development and sharing across the project ecosystem.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/phpcs.xml'>phpcs.xml</a></b></td>
					<td style='padding: 8px;'>- Defines coding standards and quality rules for the v-wpsa plugin, ensuring consistent and compliant PHP code across the project<br>- It streamlines code review processes by enforcing WordPress-specific best practices and whitespace conventions, while excluding non-essential directories<br>- This configuration supports maintaining high code quality and adherence to WordPress development guidelines within the overall architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/v-wp-seo-audit.php'>v-wp-seo-audit.php</a></b></td>
					<td style='padding: 8px;'>- Provides the core functionality for a WordPress SEO Audit plugin by managing plugin initialization, asset loading, shortcode rendering, and AJAX report generation<br>- Facilitates user interaction with SEO analysis tools on the frontend, while enabling external systems and AI integrations to request comprehensive or minimal SEO reports through a dedicated external API function.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/composer.json'>composer.json</a></b></td>
					<td style='padding: 8px;'>- Defines the autoloading configuration for the project, specifying the directories containing core classes and components<br>- Facilitates seamless class loading across the codebase, ensuring efficient organization and maintainability of the applications architecture<br>- Supports development tools and standards compliance, contributing to a structured and scalable code environment.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/install.php'>install.php</a></b></td>
					<td style='padding: 8px;'>- Defines installation, activation, and cleanup procedures for the v-wpsa plugin, ensuring proper database schema setup and maintenance<br>- Manages creation of essential tables, upgrades schema as needed, and schedules daily cleanup tasks to remove outdated reports and associated files, maintaining optimal performance and data integrity within the WordPress environment.</td>
				</tr>
			</table>
		</blockquote>
	</details>
	<!-- config Submodule -->
	<details>
		<summary><b>config</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ config</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/config/main.php'>main.php</a></b></td>
					<td style='padding: 8px;'>- Defines core configuration settings for the WordPress plugin, establishing database connections, URL management, caching, and logging mechanisms<br>- Integrates plugin-specific parameters with WordPress environment variables to ensure seamless operation within the WordPress ecosystem<br>- Facilitates consistent application behavior, resource management, and error handling across the plugin’s architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/config/domain_restriction.php'>domain_restriction.php</a></b></td>
					<td style='padding: 8px;'>- Defines domain restriction patterns to filter or block specific websites based on configurable regex rules<br>- Facilitates targeted content moderation by specifying domains and patterns to identify and restrict access to undesirable or adult content, integrating seamlessly into the broader system architecture for maintaining content compliance and security.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/config/badwords.php'>badwords.php</a></b></td>
					<td style='padding: 8px;'>- Provides a comprehensive list of multilingual offensive and inappropriate words for content moderation<br>- Serves as a core component in filtering and preventing the display or submission of harmful language across user-generated content, thereby supporting community safety and compliance within the overall application architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/config/config.php'>config.php</a></b></td>
					<td style='padding: 8px;'>- Defines core configuration settings for the SEO Audit By V platform, establishing application parameters, integration options, and default behaviors<br>- It centralizes key environment variables such as site information, language preferences, and PageSpeed Insights categories, ensuring consistent operation and facilitating customization across the entire codebase.</td>
				</tr>
			</table>
		</blockquote>
	</details>
	<!-- Webmaster Submodule -->
	<details>
		<summary><b>Webmaster</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ Webmaster</b></code>
			<!-- Matrix Submodule -->
			<details>
				<summary><b>Matrix</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.Matrix</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Matrix/SearchMatrix.php'>SearchMatrix.php</a></b></td>
							<td style='padding: 8px;'>- Provides a flexible search matrix utility that evaluates the presence of specified words across various data sources, including strings and nested arrays<br>- Facilitates dynamic configuration of search parameters and generates a comprehensive matrix indicating whether each word exists within each data context, supporting complex search scenarios within the overall application architecture.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- Utils Submodule -->
			<details>
				<summary><b>Utils</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.Utils</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Utils/Helper.php'>Helper.php</a></b></td>
							<td style='padding: 8px;'>- Provides utility functions for sanitizing HTML content and verifying array emptiness, supporting data cleaning and validation tasks within the broader application architecture<br>- Enhances robustness by ensuring safe HTML rendering and reliable data checks, facilitating consistent data handling across various modules of the project.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Utils/IDN.php'>IDN.php</a></b></td>
							<td style='padding: 8px;'>- The <code>IDN.php</code> file within the <code>Webmaster/Utils</code> directory serves as a utility component dedicated to handling Internationalized Domain Names (IDNs)<br>- Its primary purpose is to facilitate the encoding and decoding of domain names that include non-ASCII characters, ensuring compatibility and proper processing across different systems and protocols<br>- This utility plays a crucial role in the overall architecture by enabling the application to support a globalized web environment, allowing users to work seamlessly with domain names in various languages and scripts.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- Rates Submodule -->
			<details>
				<summary><b>Rates</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.Rates</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Rates/RateProvider.php'>RateProvider.php</a></b></td>
							<td style='padding: 8px;'>- Provides a comprehensive rating and scoring mechanism for evaluating web content quality, accessibility, and SEO compliance within the larger architecture<br>- It manages configuration-driven assessments, including legacy and modern evaluation methods, to generate scores and advice that inform content optimization strategies across the platform.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Rates/rates.php'>rates.php</a></b></td>
							<td style='padding: 8px;'>- Defines the configuration and scoring criteria for evaluating website quality across various SEO, accessibility, and compliance metrics<br>- It assigns point values and advice based on specific website attributes, facilitating comprehensive, standardized website reviews within the broader architecture<br>- This setup supports consistent assessment and ranking of website performance and standards adherence.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- TagCloud Submodule -->
			<details>
				<summary><b>TagCloud</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.TagCloud</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/TagCloud.php'>TagCloud.php</a></b></td>
							<td style='padding: 8px;'>- Generates a tag cloud by analyzing and ranking prominent words within HTML content, excluding common and irrelevant terms<br>- Facilitates visual representation of key topics or themes, supporting content summarization and keyword highlighting within the broader web application architecture<br>- Enhances user engagement and content discoverability through dynamic keyword visualization.</td>
						</tr>
					</table>
					<!-- CommonWords Submodule -->
					<details>
						<summary><b>CommonWords</b></summary>
						<blockquote>
							<div class='directory-path' style='padding: 8px 0; color: #666;'>
								<code><b>⦿ Webmaster.TagCloud.CommonWords</b></code>
							<table style='width: 100%; border-collapse: collapse;'>
							<thead>
								<tr style='background-color: #f8f9fa;'>
									<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
									<th style='text-align: left; padding: 8px;'>Summary</th>
								</tr>
							</thead>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/ru.php'>ru.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of common Russian words, including stopwords and offensive terms, to facilitate text processing tasks such as filtering, normalization, or keyword analysis within the broader web analytics and content management architecture<br>- This resource supports accurate language-specific text analysis by identifying words that are typically excluded from meaningful keyword extraction or search indexing.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/de.php'>de.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of common German stop words and offensive terms to facilitate filtering and preprocessing in text analysis workflows<br>- This resource supports the broader architecture by enabling effective keyword extraction, spam detection, and content moderation within multilingual web applications<br>- It ensures that irrelevant or harmful words are systematically identified and managed during data processing.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/fr.php'>fr.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of French words and expressions, including offensive terms and common vocabulary, to support filtering, moderation, or keyword detection within the broader content management system<br>- This resource enhances the language-specific capabilities of the application, enabling effective identification and handling of sensitive or relevant terms in French-language contexts.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/es.php'>es.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of common Spanish words and phrases used for filtering, normalization, or exclusion in text processing within the broader tag cloud and content analysis system<br>- This collection supports accurate keyword extraction and language-specific adjustments, enhancing the effectiveness of multilingual tag cloud generation and search relevance across the platform.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/it.php'>it.php</a></b></td>
									<td style='padding: 8px;'>- Provides an Italian stopword list for text processing within the broader tag cloud and keyword analysis system<br>- It enhances natural language understanding by filtering common, non-informative words, thereby improving the accuracy of keyword extraction and relevance ranking across the web content managed by the architecture.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/da.php'>da.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of common Danish words to facilitate filtering or exclusion in text processing tasks within the broader tag cloud and content analysis system<br>- Enhances the accuracy of keyword extraction by removing frequently used, non-informative words, thereby supporting more meaningful insights and improved user experience across the website’s multilingual architecture.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/en.php'>en.php</a></b></td>
									<td style='padding: 8px;'>- Provides a curated list of common words and stopwords used for text processing within the project<br>- Facilitates filtering out non-essential words during natural language analysis, enhancing the accuracy of features like tag cloud generation and keyword extraction across the codebase<br>- Supports consistent language normalization in content analysis modules.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/pt.php'>pt.php</a></b></td>
									<td style='padding: 8px;'>- Provides a comprehensive list of common Portuguese words and stopwords used for text processing within the project<br>- Facilitates filtering out non-essential words in natural language analysis, enhancing the accuracy of tag cloud generation and keyword extraction across the codebase<br>- Supports multilingual content handling by standardizing language-specific stopword removal.</td>
								</tr>
								<tr style='border-bottom: 1px solid #eee;'>
									<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/TagCloud/CommonWords/sv.php'>sv.php</a></b></td>
									<td style='padding: 8px;'>- Provides a list of common Swedish words to filter out from text analysis, supporting accurate keyword extraction and tag cloud generation within the project<br>- Enhances natural language processing by excluding frequently used, non-informative words, thereby improving the relevance and clarity of content insights across the codebase.</td>
								</tr>
							</table>
						</blockquote>
					</details>
				</blockquote>
			</details>
			<!-- Google Submodule -->
			<details>
				<summary><b>Google</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.Google</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Google/PageSpeedInsights.php'>PageSpeedInsights.php</a></b></td>
							<td style='padding: 8px;'>- Provides an interface to evaluate website performance and optimization opportunities using Google PageSpeed Insights API<br>- It fetches, formats, and categorizes performance metrics for both mobile and desktop strategies, enabling developers to analyze and improve page load times, user experience, and adherence to best practices across different device types within the overall web architecture.</td>
						</tr>
					</table>
				</blockquote>
			</details>
			<!-- Source Submodule -->
			<details>
				<summary><b>Source</b></summary>
				<blockquote>
					<div class='directory-path' style='padding: 8px 0; color: #666;'>
						<code><b>⦿ Webmaster.Source</b></code>
					<table style='width: 100%; border-collapse: collapse;'>
					<thead>
						<tr style='background-color: #f8f9fa;'>
							<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
							<th style='text-align: left; padding: 8px;'>Summary</th>
						</tr>
					</thead>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Optimization.php'>Optimization.php</a></b></td>
							<td style='padding: 8px;'>- Provides mechanisms to retrieve and analyze website optimization data, including sitemap discovery, robots.txt presence, and gzip support detection<br>- Facilitates understanding of site accessibility and crawlability, supporting overall SEO and performance strategies within the broader architecture<br>- Enhances the systems ability to adapt to different server configurations and optimize crawling efficiency.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Document.php'>Document.php</a></b></td>
							<td style='padding: 8px;'>- Provides tools for analyzing HTML documents by identifying doctype declarations, detecting deprecated tags, and extracting metadata such as language and resource links<br>- Facilitates validation and optimization of web pages within the broader architecture, ensuring compliance with standards and enhancing accessibility and performance<br>- Supports maintaining high-quality, standards-compliant web content across the project.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Image.php'>Image.php</a></b></td>
							<td style='padding: 8px;'>- Provides functionality to parse HTML content for image tags, count total images, identify those with missing alt attributes, and extract their source URLs<br>- Supports accessibility auditing by highlighting images lacking descriptive alt text, integrating into larger web content analysis workflows to ensure compliance and improve user experience.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Favicon.php'>Favicon.php</a></b></td>
							<td style='padding: 8px;'>- Provides mechanisms to identify and retrieve a websites favicon by analyzing HTML head tags and verifying favicon URLs through HTTP headers<br>- Enhances the overall architecture by ensuring consistent favicon detection across diverse domains, supporting branding and user experience consistency throughout the platform<br>- Facilitates dynamic favicon management within the broader web crawling and site analysis workflows.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/SeoAnalyse.php'>SeoAnalyse.php</a></b></td>
							<td style='padding: 8px;'>- Provides functionality to analyze HTML content by calculating the proportion of visible text relative to the total HTML size<br>- It supports SEO assessments by measuring how much of the page consists of meaningful content, aiding in optimizing webpage structure and readability within the broader SEO analysis framework.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/AnalyticsFinder.php'>AnalyticsFinder.php</a></b></td>
							<td style='padding: 8px;'>- Provides mechanisms to detect various web analytics providers embedded within HTML content, including Google Analytics, LiveInternet, Clicky, Quantcast, and Piwik<br>- Facilitates identification of tracking scripts and tags, enabling comprehensive analysis of analytics integrations across web pages within the larger architecture focused on monitoring and data collection.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Links.php'>Links.php</a></b></td>
							<td style='padding: 8px;'>- Analyzes and extracts all hyperlinks from HTML content, classifying them as internal or external, and assessing their attributes such as rel and URL structure<br>- Facilitates understanding of link distribution, quality, and URL patterns within a website, supporting SEO audits and site architecture evaluations by providing detailed link metrics and classifications.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Validation.php'>Validation.php</a></b></td>
							<td style='padding: 8px;'>- Provides validation of website HTML compliance by interfacing with the W3C Validator API<br>- It assesses the correctness of a domains HTML structure, returning detailed error and warning reports to ensure web pages meet standards<br>- This functionality supports maintaining high-quality, standards-compliant web content within the overall architecture.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/Content.php'>Content.php</a></b></td>
							<td style='padding: 8px;'>- Provides mechanisms to analyze and extract structural and embedded content from HTML snippets within the broader content management system<br>- It detects embedded objects like flash and iframes, retrieves headings, identifies nested tables, email addresses, and inline CSS, supporting content validation, parsing, and security assessments across the website architecture.</td>
						</tr>
						<tr style='border-bottom: 1px solid #eee;'>
							<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/Webmaster/Source/MetaTags.php'>MetaTags.php</a></b></td>
							<td style='padding: 8px;'>- Extracts and organizes key meta information from HTML content, including title, description, keywords, charset, viewport, Dublin Core, and Open Graph properties<br>- Facilitates comprehensive metadata analysis for web pages, supporting SEO optimization and social media integration within the overall architecture<br>- Enhances content discoverability and ensures consistent metadata handling across the platform.</td>
						</tr>
					</table>
				</blockquote>
			</details>
		</blockquote>
	</details>
	<!-- templates Submodule -->
	<details>
		<summary><b>templates</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ templates</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/templates/main.php'>main.php</a></b></td>
					<td style='padding: 8px;'>- Provides the user interface for initiating website SEO audits within the WordPress environment<br>- It facilitates inputting domain data, displays progress, and presents various SEO analysis features such as content, meta tags, links, speed, and recommendations<br>- Integrates with the broader plugin architecture to deliver comprehensive website health reviews and showcases recent audit results.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/templates/layout.php'>layout.php</a></b></td>
					<td style='padding: 8px;'>- Defines the layout structure for report pages within the WordPress plugin, ensuring consistent presentation by wrapping main content and footer elements in a responsive container<br>- Facilitates seamless integration of report content into the overall site design, maintaining visual coherence and simplifying content rendering across different reports.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/templates/widgets.php'>widgets.php</a></b></td>
					<td style='padding: 8px;'>- Provides WordPress-native functions to display a paginated, visually engaging list of analyzed websites with thumbnails, scores, and links for detailed review<br>- Integrates database retrieval, thumbnail management, and dynamic rendering to enhance user interaction and facilitate easy navigation within the website analysis platform.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/templates/pdf.php'>pdf.php</a></b></td>
					<td style='padding: 8px;'>- The <code>templates/pdf.php</code> file serves as the core presentation layer for generating comprehensive SEO audit reports in PDF format within the project<br>- It transforms pre-analyzed SEO data—such as website metrics, score breakdowns, keyword clouds, and validation results—into a structured, visually coherent document<br>- This template consolidates various data sources and analysis results into a standardized report, enabling users to easily review and share SEO insights<br>- Overall, it plays a pivotal role in delivering a polished, portable summary of website SEO health, integrating seamlessly into the broader architecture that manages data collection, analysis, and report generation.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/templates/report.php'>report.php</a></b></td>
					<td style='padding: 8px;'>- Report.phpThis file serves as the core template for generating comprehensive SEO audit reports within the WordPress-based project<br>- It consolidates pre-analyzed SEO metrics, website data, and diagnostic insights into a structured, human-readable format<br>- By rendering detailed information such as website scores, keyword clouds, validation results, and link analysis, it provides users with a clear overview of their website’s SEO health<br>- This template acts as the presentation layer, transforming raw analytical data into an accessible report that supports informed decision-making and ongoing SEO optimization efforts across the overall architecture.</td>
				</tr>
			</table>
		</blockquote>
	</details>
	<!-- includes Submodule -->
	<details>
		<summary><b>includes</b></summary>
		<blockquote>
			<div class='directory-path' style='padding: 8px 0; color: #666;'>
				<code><b>⦿ includes</b></code>
			<table style='width: 100%; border-collapse: collapse;'>
			<thead>
				<tr style='background-color: #f8f9fa;'>
					<th style='width: 30%; text-align: left; padding: 8px;'>File Name</th>
					<th style='text-align: left; padding: 8px;'>Summary</th>
				</tr>
			</thead>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-helpers.php'>class-v-wpsa-helpers.php</a></b></td>
					<td style='padding: 8px;'>- Provides essential helper functions for managing PDF files, retrieving configuration settings, and loading configuration files within the WordPress plugin<br>- Facilitates cleanup of generated PDFs, access to plugin-specific configurations, and seamless integration of configuration data, supporting the overall architecture by ensuring efficient resource management and flexible customization.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-validation.php'>class-v-wpsa-validation.php</a></b></td>
					<td style='padding: 8px;'>- Provides WordPress-native domain validation functions to ensure domain names are correctly formatted, reachable, and compliant with restrictions<br>- Facilitates sanitization, IDN encoding, format validation, and banned domain checks, supporting secure and reliable domain input handling within the broader application architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-utils.php'>class-v-wpsa-utils.php</a></b></td>
					<td style='padding: 8px;'>- V_WPSA_Utils ClassThis file defines the <code>V_WPSA_Utils</code> class, which serves as a collection of utility functions for the v-wpsa WordPress plugin<br>- Its primary purpose is to provide reusable, core functionalities that facilitate common data manipulations and calculations across the plugin<br>- Specifically, it includes methods for shuffling associative arrays and computing proportional percentages, supporting the plugins broader architecture by simplifying routine operations and ensuring consistency throughout the codebase.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-ajax-handlers.php'>class-v-wpsa-ajax-handlers.php</a></b></td>
					<td style='padding: 8px;'>- Defines AJAX handlers for managing website analysis reports within the WordPress plugin<br>- Facilitates domain validation, report generation, PDF downloads, and report deletion, ensuring secure, efficient, and user-permission-aware interactions<br>- Integrates with database operations and report generation components to support seamless report lifecycle management across the platform.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-report-generator.php'>class-v-wpsa-report-generator.php</a></b></td>
					<td style='padding: 8px;'>- Provides core functionality for generating comprehensive website reports within the WordPress environment<br>- Facilitates creation of both HTML and PDF formats by aggregating website data, rendering templates, and leveraging TCPDF for PDF output<br>- Ensures efficient report caching and handles template rendering, supporting seamless, automated website analysis and review processes across the platform.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-report-service.php'>class-v-wpsa-report-service.php</a></b></td>
					<td style='padding: 8px;'>- Provides a unified service layer for domain analysis and report generation, handling validation, caching, re-analysis, and report assembly<br>- Facilitates seamless integration across AJAX, REST API, and direct calls, ensuring efficient retrieval of comprehensive website reports, including PDFs and deep-linked URLs, while managing cache validity and data sanitization within the overall architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-score-calculator.php'>class-v-wpsa-score-calculator.php</a></b></td>
					<td style='padding: 8px;'>- Calculates comprehensive website scores based on analyzer output and configurable rates, evaluating various aspects such as content quality, technical compliance, and SEO factors<br>- Facilitates consistent, normalized scoring across multiple categories, enabling effective assessment of website health and adherence to best practices within the overall architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-website.php'>class-v-wpsa-website.php</a></b></td>
					<td style='padding: 8px;'>- Provides core database operations related to website management within the WordPress environment, including retrieving total website counts and removing websites by domain<br>- Serves as a foundational component for handling website data, ensuring seamless integration with WordPress native database functions and supporting domain encoding for accurate data manipulation.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-db.php'>class-v-wpsa-db.php</a></b></td>
					<td style='padding: 8px;'>- This code file defines the <code>V_WPSA_DB</code> class, which serves as a dedicated database handler within the v-wpsa WordPress plugin<br>- Its primary purpose is to facilitate seamless, WordPress-native database interactions by encapsulating common database operations while maintaining the plugins specific table schema and structure<br>- This class ensures consistent and secure access to the plugins data tables, supporting the overall architecture by abstracting direct database queries and promoting maintainability across the codebase.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-thumbnail.php'>class-v-wpsa-thumbnail.php</a></b></td>
					<td style='padding: 8px;'>- Provides functionality for generating, caching, and managing website thumbnails within a WordPress environment<br>- Facilitates efficient retrieval and storage of website preview images, supporting social sharing and audit features<br>- Ensures thumbnails are cached for performance, with mechanisms for cache invalidation and bulk thumbnail generation across multiple websites, integrating seamlessly into the broader SEO and website analysis architecture.</td>
				</tr>
				<tr style='border-bottom: 1px solid #eee;'>
					<td style='padding: 8px;'><b><a href='https://github.com/djav1985/v-wp-seo-audit/blob/master/includes/class-v-wpsa-config.php'>class-v-wpsa-config.php</a></b></td>
					<td style='padding: 8px;'>- Provides centralized access to plugin configuration settings within the WordPress environment, enabling efficient retrieval and management of default and custom configurations<br>- Facilitates seamless integration of configuration data across the codebase, ensuring consistent behavior and easy customization of plugin features such as SEO auditing, performance analysis, and user interface elements.</td>
				</tr>
			</table>
		</blockquote>
	</details>
</details>

---

## Getting Started

### Prerequisites

This project requires the following dependencies:

- **Programming Language:** PHP 8.0+
- **Platform:** WordPress 5.0+
- **Package Manager:** Composer

### Installation

Build v-wp-seo-audit from source and install dependencies:

1. **Clone the repository:**
    ```sh
    git clone https://github.com/djav1985/v-wp-seo-audit.git
    ```
2. **Navigate to the project directory:**
    ```sh
    cd v-wp-seo-audit
    ```
3. **Install the dependencies:**
    ```sh
    composer install
    ```

### Usage

**As a WordPress plugin:**
1. Copy the `v-wp-seo-audit` folder into your WordPress `wp-content/plugins/` directory.
2. Activate the plugin from the WordPress admin dashboard under **Plugins**.
3. Add the `[v_wpsa]` shortcode to any page or post to display the SEO audit form.
4. Users can enter a domain name to analyze and receive a comprehensive SEO report with:
   - SEO score and performance metrics
   - Content analysis (meta tags, headings, keyword density)
   - Technical validation (HTML compliance, sitemap, robots.txt)
   - Link analysis (internal/external links)
   - Image optimization review
   - Google PageSpeed Insights integration
   - Downloadable PDF reports

**For AI Integration and Function Calling:**

The plugin provides a dedicated function for AI chatbots and external integrations to generate SEO reports programmatically:

```php
/**
 * Generate an SEO report via function calling
 * 
 * @param string $domain  The domain to analyze (e.g., "example.com")
 * @param bool   $report  true = full report data, false = minimal data (domain, score, URLs)
 * @return string|WP_Error JSON string with report data, or WP_Error on failure
 */
V_WPSA_external_generation( $domain, $report = true );
```

**Example usage:**
```php
// Generate a full report
$full_report = V_WPSA_external_generation( 'example.com', true );
$data = json_decode( $full_report, true );

// Generate minimal report (just score and URLs)
$minimal_report = V_WPSA_external_generation( 'example.com', false );
$quick_data = json_decode( $minimal_report, true );
// Returns: ['domain', 'score', 'pdf_url', 'report_url']
```

**AI Function Calling Features:**
- Validates and sanitizes domain input automatically
- Returns structured JSON data suitable for AI parsing
- Supports both comprehensive and minimal response modes
- Handles caching intelligently (24-hour cache by default)
- Includes error handling with WP_Error objects
- No authentication required for read-only report generation
- Compatible with WordPress REST API and AJAX endpoints

**Standalone (for development/testing):**
For development purposes, you can test PHP components directly:
```sh
php path/to/your/script.php
```

**Note:** Full plugin functionality requires WordPress. The analysis engine uses WordPress database tables and hooks.

---

## License

V-wp-seo-audit is protected under the [LICENSE](https://choosealicense.com/licenses) License. For more details, refer to the [LICENSE](https://choosealicense.com/licenses/) file.

---

[back-to-top]: https://img.shields.io/badge/-BACK_TO_TOP-151515?style=flat-square

---
