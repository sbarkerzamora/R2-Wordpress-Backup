## R2 WordPress Backup

Full-site WordPress backups (files + database) with automatic upload to **Cloudflare R2** using its S3-compatible API.

This plugin is focused on being:

- **Simple**: clear UI with Export, Import, Backups, Schedules, and Settings screens.
- **Reliable**: backups are created as a SQL dump + ZIP archive before being uploaded.
- **Cost‑friendly**: designed to work well with Cloudflare R2’s free tier.

---

## Features

- **Export**: Run a manual full backup and upload it to your R2 bucket.
- **Import**: Restore the current site from a backup stored in R2.
- **Backups list**: Browse and manage backups in R2 (with a count badge in the admin menu).
- **Schedules**: Automatic daily, weekly, or monthly backups via WP‑Cron.
- **Settings**:
  - R2 Account ID, Access Key ID, Secret Access Key.
  - Bucket name and region/endpoint.
  - Exclusions for paths you don’t want in the backup.
  - Retention rules to limit how many backups are kept.
- **Reset Hub**: Quickly reset plugin options and schedules if needed.

---

## Requirements

- **WordPress**: 5.9 or higher.
- **PHP**: 7.4 or higher.
- **Cloudflare account** with R2 enabled.
- An **R2 bucket** and **API token** (Access Key ID + Secret Access Key).

Cloudflare R2 free tier currently includes:

- 10 GB storage
- 1M Class A (write) operations / month
- 10M Class B (read) operations / month

This is usually enough for small and medium sites with a reasonable number of backups.

---

## Installation & Configuration

1. Copy the plugin folder `r2-wordpress-backup` into `wp-content/plugins/`
   or install it as a ZIP through the WordPress admin.
2. Activate **R2 WordPress Backup** from **Plugins → Installed Plugins**.
3. In the WordPress admin, go to **R2 Backup → Settings**.
4. Enter your:
   - Cloudflare **Account ID**
   - **Access Key ID**
   - **Secret Access Key**
   - **Bucket name**
5. Save changes.
6. Use **Export** to create your first backup, or configure **Schedules** for automatic backups.

---

## Development (Docker)

This repository includes a simple `docker-compose.yml` to help you spin up a local WordPress + MySQL environment for development and testing.

Typical flow:

1. Start the stack:

   ```bash
   docker compose up -d
   ```

2. Open WordPress (usually at `http://localhost:8000` or whatever port you configured).
3. Install and activate the **R2 WordPress Backup** plugin.
4. Configure R2 credentials and test exports/imports locally.

Check the `docker-compose.yml` file for exact service names, ports, and volumes.

---

## Contributing & Issues

Pull requests and issues are welcome.

- If you find a bug, please open an issue with:
  - WordPress and PHP versions.
  - Any relevant error messages (from logs or browser console).
  - Steps to reproduce.
- If you want to add a feature, describe the use case and any UI ideas you have.

---

## Donate / Buy Me a Beer

If this plugin saves you time or helps keep your WordPress backups safe, you can support its development by **buying me a beer**:

https://buymeacoffee.com/stephanbarker

Muchas gracias por tu apoyo 🤝

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
