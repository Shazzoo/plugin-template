# Plugin Template

This repository is a GitHub template for creating a plugin for Shazzoo's CMS.

## Quick start

1. Click **Use this template** on GitHub and create your new repository.
2. Clone it into your CMS at `storage/app/plugins/<plugin-name>`.
3. Run the initializer command:

```bash
composer template:init -- --vendor=your-vendor --name="Your Plugin"
```

Example:

```bash
composer template:init -- --vendor=shazzoo --name="Cookie Banner"
```

This updates placeholders in file contents and renames template file/folder paths like:

- `plugin-name` -> `cookie-banner`
- `PluginName` -> `CookieBanner`
- `template-vendor/plugin-name` -> `shazzoo/cookie-banner`
- `TemplateVendor\\PluginName` -> `Shazzoo\\CookieBanner`
- `template-vendor-plugin-name` -> `shazzoo-cookie-banner`
- `x-template-vendor-plugin-name::...` -> `x-shazzoo-cookie-banner::...`
- `src/PluginName.php` -> `src/CookieBanner.php`
- `src/PluginNameServiceProvider.php` -> `src/CookieBannerServiceProvider.php`

## Command options

You can also run the script directly:

```bash
php bin/plugin-init.php --vendor=your-vendor --name="Your Plugin"
```

Useful flags:

- `--dry-run` shows what would change without writing files
- `--no-interaction` disables prompts (cleanup defaults to no)
- `--help` shows usage

Dry-run output includes both content replacement counts and path rename counts.

After a successful run, the command asks if you want to remove the initializer (`bin/plugin-init.php`) and its Composer scripts from the new repo.

## Manual setup (if you skip the command)

Replace all occurrences of:

- `plugin-name`
- `PluginName`
- `template-vendor/plugin-name`
- `TemplateVendor\\PluginName`
- `template-vendor-plugin-name`
