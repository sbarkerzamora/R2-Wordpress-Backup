# R2 WordPress Backup

WordPress plugin for full site backups (files + database) with automatic upload to **Cloudflare R2** using the S3-compatible API. Works with R2's free tier (10 GB storage, 1M Class A / 10M Class B operations per month).

## Features

- **Export** – Run a manual full backup and upload to R2
- **Import** – Restore this site from a backup stored in R2 (this site only)
- **Backups** – List and manage backups in R2 (with count badge in menu)
- **Schedules** – Automatic daily, weekly, or monthly backups via WP-Cron
- **Settings** – R2 credentials (Account ID, Access Key, Secret, Bucket), exclusions, retention
- **Reset Hub** – Reset plugin options and schedules (credentials optional clear)

## Requirements

- WordPress 5.9+
- PHP 7.4+
- Cloudflare account with R2 bucket and API token (Access Key ID + Secret Access Key)

## Installation

1. Upload the plugin folder to `wp-content/plugins/` or install via **Plugins → Add New** (if distributed via WordPress.org).
2. Activate **R2 WordPress Backup**.
3. Go to **R2 Backup → Settings** and enter your Cloudflare R2 credentials:
   - **Account ID** – From Cloudflare dashboard (R2 overview)
   - **Access Key ID** and **Secret Access Key** – From R2 → Manage R2 API Tokens
   - **Bucket name** – Your R2 bucket name
4. Use **Export** to create a backup or **Schedules** to set up automatic backups.

## R2 Setup

1. In [Cloudflare Dashboard](https://dash.cloudflare.com/), go to **R2 Object Storage**.
2. Create a bucket (e.g. `wordpress-backups`).
3. Go to **R2 → Manage R2 API Tokens** and create a token with Object Read & Write.
4. Note your **Account ID** (in R2 overview) and the token’s **Access Key ID** and **Secret Access Key**.

## What’s in a backup?

- **Database** – Full SQL dump (all tables, excluding any you list in Settings).
- **Files** – `wp-content` directory, with optional path exclusions (e.g. `wp-content/cache`).

Backups are stored as ZIP files in R2 under `backups/<site-slug>/YYYY-MM-DD-HHmm-full.zip`. Retention (keep last N backups) is applied when configured in Settings.

## License

GPL v2 or later. See [LICENSE](LICENSE).

## Contributing

Contributions are welcome. Please open an issue or pull request on GitHub.
