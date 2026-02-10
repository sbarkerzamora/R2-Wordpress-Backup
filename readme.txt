=== R2 Cloud Backup ===

Contributors: stephanbarker
Tags: backup, cloudflare, s3, database, restore
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.7
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

1. Upload the plugin folder to `wp-content/plugins/` or install via WordPress admin. The plugin folder name must be **r2-cloud-backup** (so the path is `wp-content/plugins/r2-cloud-backup/` with `r2-wordpress-backup.php` inside).
2. Activate the plugin.
3. Go to R2 Cloud Backup > Settings and enter your Cloudflare R2 credentials (Account ID, Access Key ID, Secret Access Key, Bucket name).
4. Use Export to create a backup or Schedules to set up automatic backups.

= Manual update =

1. Deactivate the plugin in Plugins.
2. Replace the plugin folder with the new version, or upload a zip that contains a folder named **r2-cloud-backup** (so WordPress installs to `wp-content/plugins/r2-cloud-backup/`). If the zip uses a different folder name, you may end up with two plugin entries; keep only the **r2-cloud-backup** folder and delete any duplicate (e.g. an old `r2-wordpress-backup` folder) to avoid the menu not showing.
3. Activate the plugin again.

== Frequently Asked Questions ==

= Where are backups stored? =

Backups are stored in your Cloudflare R2 bucket. They are not kept on your server after upload (except temporarily during creation).

= What is included in a backup? =

A full backup includes a SQL dump of the database and a ZIP of the site files (by default wp-content; paths can be excluded in Settings).

= Is the R2 free tier enough? =

R2 free tier includes 10 GB storage and 1 million Class A (write) and 10 million Class B (read) operations per month. For small to medium sites with a few backups, this is usually sufficient.

== Upgrade Notice ==

= 1.0.7 =
Fixes 403 when accessing Settings: menu now registers from every plugin copy. Sidebar donate link updated to buymeacoffee.com/stephanbarker.

= 1.0.6 =
Fixes admin menu not showing when R2/count fails; resilient to Throwable. WP_Error handling in R2 requests. Docs: manual update and r2-cloud-backup folder name.

= 1.0.5 =
WordPress Plugin Directory compliance: support sidebar uses link only (no third-party scripts or images). Readme tested up to 6.7. Docs for plugin submission.

= 1.0.4 =
Landing page redesign: new hero, responsive layout, Phosphor Icons, mobile nav. Plugin: guard against duplicate load (multiple plugin folders).

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

= 1.0.7 =
* Fix: 403 Forbidden when accessing Settings with duplicate plugin folders; menu now registers from every copy (before early return).
* Sidebar: donate link https://buymeacoffee.com/stephanbarker.
* Removed menu badge counter to prevent deprecation warnings.
* Defensive (string) casts for strpos/str_replace to avoid PHP 8.1+ null deprecations.

= 1.0.6 =
* Fix: Admin menu no longer disappears when backup count or R2 request fails; catch Throwable in get_r2_backup_count and add_menu_pages.
* R2 client: handle wp_remote_request WP_Error in request() to avoid warnings.
* Readme: Installation and new "Manual update" section; plugin folder name r2-cloud-backup.
* Docs: SUBMIT-TO-WORDPRESS-ORG.md section "Actualización manual" and stable tag note.

= 1.0.5 =
* Plugin Directory compliance: admin support sidebar no longer loads external scripts or images; text link only (guideline 8). Button styling for text link.
* Readme: Tested up to 6.7.
* Docs: Plugin Directory compliance checklist and submission guide (SUBMIT-TO-WORDPRESS-ORG.md).

= 1.0.4 =
* Landing: hero redesign with CTAs and visual block; full responsive layout; Phosphor Icons throughout; mobile navigation with hamburger menu.
* Plugin: prevent fatal error when two plugin copies are loaded (e.g. old and new folder); only one instance runs.

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
