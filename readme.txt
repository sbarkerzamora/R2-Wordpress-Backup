=== R2 Cloud Backup ===

Contributors: stephanbarker
Tags: backup, cloudflare, s3, database, restore
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://buymeacoffee.com/stephanbarker

Full site backups (files + database) with automatic upload to Cloudflare R2.

== Description ==

R2 Cloud Backup creates complete backups of your WordPress site (files and database) and uploads them to Cloudflare R2 storage using the S3-compatible API. It works with R2's free tier (10 GB storage, 1M Class A / 10M Class B operations per month).

If you find this plugin useful, consider [buying me a coffee](https://buymeacoffee.com/stephanbarker) to support development.

Features:

* **Export** – Run a manual full backup and upload to R2
* **Import** – Restore this site from a backup stored in R2
* **Backups** – List and manage backups in R2 (with count badge in menu)
* **Schedules** – Automatic daily, weekly, or monthly backups
* **Settings** – R2 credentials (Account ID, Access Key, Secret, Bucket), exclusions, retention
* **Reset Hub** – Reset plugin options and schedules

You need a Cloudflare account and an R2 bucket with API tokens (Access Key ID and Secret Access Key). Create them in the Cloudflare dashboard under R2 > Manage R2 API Tokens.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/` or install via WordPress admin.
2. Activate the plugin.
3. Go to R2 Cloud Backup > Settings and enter your Cloudflare R2 credentials (Account ID, Access Key ID, Secret Access Key, Bucket name).
4. Use Export to create a backup or Schedules to set up automatic backups.

== Frequently Asked Questions ==

= Where are backups stored? =

Backups are stored in your Cloudflare R2 bucket. They are not kept on your server after upload (except temporarily during creation).

= What is included in a backup? =

A full backup includes a SQL dump of the database and a ZIP of the site files (by default wp-content; paths can be excluded in Settings).

= Is the R2 free tier enough? =

R2 free tier includes 10 GB storage and 1 million Class A (write) and 10 million Class B (read) operations per month. For small to medium sites with a few backups, this is usually sufficient.

== Upgrade Notice ==

= 1.0.3 =
Project landing page (GitHub Pages) and documentation updates.

= 1.0.2 =
Fixes export failing in production with 500 error: upload now streams large backups to R2 instead of loading the entire file into memory (fixes memory exhaustion on PHP 256MB limit).

= 1.0.1 =
Initial release. Full site backups to Cloudflare R2 with export, import, schedules, and settings.

== Screenshots ==

1. Settings – R2 credentials and backup options
2. Export – Run a full backup and upload to R2
3. Backups – List and manage backups in R2
4. Schedules – Configure automatic daily, weekly, or monthly backups

== Donate ==

If this plugin helps you, you can support its development: [Buy Me a Coffee](https://buymeacoffee.com/stephanbarker)

== Changelog ==

= 1.0.3 =
* Project landing page for GitHub Pages (docs/) with download, releases, and support links.
* Documentation and project site updates.

= 1.0.2 =
* Fix: Export no longer fails with 500 / memory exhaustion in production. Upload to R2 now uses streaming (cURL) so large backup files are not loaded entirely into memory; works with default PHP memory limits (e.g. 256MB).

= 1.0.1 =
* Initial release.
* Export, Import, Backups list, Schedules, Settings, Reset Hub.
* R2 (S3-compatible) upload and download.
* WP-Cron scheduled backups.
