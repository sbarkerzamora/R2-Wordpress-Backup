=== R2 WordPress Backup ===

Contributors: stephanbarker
Tags: backup, cloudflare, r2, s3, database, export, import
Requires at least: 5.9
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Full site backups (files + database) with automatic upload to Cloudflare R2, designed to be simple, reliable, and friendly to Cloudflare's free tier.

== Description ==

R2 WordPress Backup creates complete backups of your WordPress site (files and database) and uploads them to Cloudflare R2 storage using the S3-compatible API. It works with R2's free tier (10 GB storage, 1M Class A / 10M Class B operations per month), so you can back up small and medium sites without extra hosting costs.

Features:

* **Export** – Run a manual full backup and upload to R2
* **Import** – Restore this site from a backup stored in R2
* **Backups** – List and manage backups in R2 (with count badge in menu)
* **Schedules** – Automatic daily, weekly, or monthly backups
* **Settings** – R2 credentials (Account ID, Access Key, Secret, Bucket), exclusions, retention
* **Reset Hub** – Reset plugin options and schedules

You need a Cloudflare account and an R2 bucket with API tokens (Access Key ID and Secret Access Key). Create them in the Cloudflare dashboard under R2 > Manage R2 API Tokens.

This plugin is ideal if you want:

* An off-site backup target you control (your own Cloudflare account).
* To avoid storing large backup archives on your WordPress server.
* A simple UI to run exports, imports, and scheduled backups.

== Installation ==

1. Upload the plugin folder to `wp-content/plugins/` or install via WordPress admin.
2. Activate the plugin.
3. Go to R2 Backup > Settings and enter your Cloudflare R2 credentials (Account ID, Access Key ID, Secret Access Key, Bucket name).
4. Use Export to create a backup or Schedules to set up automatic backups.

== Contributing & Support ==

If this plugin is useful to you and you want to support its development, you can **buy me a beer** here:

https://buymeacoffee.com/stephanbarker

Si este plugin te ayuda con tus copias de seguridad, también puedes invitarme una cerveza en el mismo enlace. ¡Gracias!

== Frequently Asked Questions ==

= Where are backups stored? =

Backups are stored in your Cloudflare R2 bucket. They are not kept on your server after upload (except temporarily during creation).

= What is included in a backup? =

A full backup includes a SQL dump of the database and a ZIP of the site files (by default wp-content; paths can be excluded in Settings).

= Is the R2 free tier enough? =

R2 free tier includes 10 GB storage and 1 million Class A (write) and 10 million Class B (read) operations per month. For small to medium sites with a few backups, this is usually sufficient.

== Changelog ==

= 1.0.0 =
* Initial release.
* Export, Import, Backups list, Schedules, Settings, Reset Hub.
* R2 (S3-compatible) upload and download.
* WP-Cron scheduled backups.
