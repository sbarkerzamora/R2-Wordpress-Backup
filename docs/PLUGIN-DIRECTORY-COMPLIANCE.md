# WordPress Plugin Directory – Compliance Checklist

This document summarizes how **R2 Cloud Backup** aligns with the [Plugin Directory Developer Guidelines](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/).

## Summary

| Guideline | Status | Notes |
|-----------|--------|--------|
| 1. GPL compatible | OK | GPLv2 or later (readme + license file). |
| 2. Developer responsibility | OK | No third-party code with unclear licensing; R2 API use documented. |
| 3. Stable version in directory | Pending | Once on WordPress.org, keep SVN in sync with stable releases. |
| 4. Human readable code | OK | No obfuscation; source in repo. |
| 5. No trialware | OK | All functionality available; no paywall. |
| 6. Service (R2) | OK | Readme describes Cloudflare R2; external service clearly documented. |
| 7. No tracking without consent | OK | No analytics; R2 API used only after user configures credentials (opt-in). |
| 8. No executable code via third parties | OK | **Fixed:** Removed Buy Me a Coffee widget script and external button image; sidebar uses a local text link only. No CDN JS/CSS. |
| 9. Legal / honest | OK | No black-hat SEO, fake reviews, or misleading claims. |
| 10. Credits on public site | OK | Plugin does not add any “Powered by” or credits to the frontend. |
| 11. No admin hijacking | OK | Support/donate appears only on plugin admin pages as a small sidebar; no site-wide nags or non-dismissible notices. |
| 12. Readme not spam | OK | 5 tags (max allowed); no affiliate links; written for users. |
| 13. WordPress default libraries | OK | No bundled jQuery or other libs; uses WordPress APIs. |
| 14. Avoid frequent commits | N/A | Use SVN only for release-ready code when hosting on WordPress.org. |
| 15. Version increment | OK | Version bumped for each release. |
| 16. Complete plugin at submission | OK | Zip is complete and working. |
| 17. Trademarks | OK | “R2 Cloud Backup” describes functionality (backup to Cloudflare R2); slug avoids misleading use of others’ brands. |
| 18. Directory maintenance | N/A | Reserved rights of WordPress.org. |

## Change made for guideline 8

- **Before:** Admin support sidebar loaded Buy Me a Coffee’s script from `cdnjs.buymeacoffee.com` and an image from their button API.
- **After:** Sidebar shows only a text link “Buy me a coffee” (no external JS, no external images). Donate link remains in readme and on the plugin’s admin pages.

This keeps the plugin within the rule that non–service-related JavaScript and CSS must be included locally and avoids offloading assets to third parties.

## Submitting to the Plugin Directory

1. Create an account at [WordPress.org](https://wordpress.org/support/register.php) if needed.
2. Submit the plugin at [WordPress.org Plugin Directory – Add your plugin](https://wordpress.org/plugins/developers/add/).
3. Use a zip of the plugin (e.g. from GitHub Releases); ensure `readme.txt` and main plugin file version match.
4. After approval, maintain the plugin via [SVN](https://developer.wordpress.org/plugins/developers/how-to-use-subversion/) and keep the directory version in sync with stable releases.
5. Keep contact details on your WordPress.org profile up to date so the plugins team can reach you.
