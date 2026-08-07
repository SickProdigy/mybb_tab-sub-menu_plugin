# MyBB Sub Menu Plugin

Standalone configurable forum-category submenu for MyBB 1.8.

This plugin was extracted from the Sick Gaming Revolution theme plugin so the menu can be installed and maintained independently of a specific theme.

## Features

- Per-tab lists of top-level forum category IDs.
- Bundled responsive styling loaded automatically.
- Optional show-all tabs with an empty ID list.
- Enabled/disabled flag for each tab.
- Remembers the visitor's selected tab in `localStorage`.
- Falls back to Home or the first configured tab.
- Original Sick Gaming category mapping as the initial recovery configuration.
- Imports the legacy `revolution_theme_menu_groups` value on first installation when available.

## Install

Copy the contents of `Upload/` into the MyBB installation root:

```text
Upload/inc/plugins/sub_menu.php -> public_html/inc/plugins/sub_menu.php
Upload/css/sub-menu.css -> public_html/css/sub-menu.css
Upload/jscripts/sub-menu/forum-sub-menu.js -> public_html/jscripts/sub-menu/forum-sub-menu.js
```

Then install and activate `Sub Menu` under Admin CP → Configuration → Plugins.

## Theme Integration

Activation automatically inserts `{$sub_menu_assets}` into `headerinclude` and `{$sub_menu}` immediately before `{$forums}` in the `index` template. Deactivation removes both insertions.

If a customized template does not contain `{$stylesheets}` or `{$forums}`, add the corresponding plugin variable manually. Without the plugin, the normal `{$forums}` output continues to show the complete forum list.

The plugin loads its bundled responsive stylesheet and generates this markup:

```html
<div id="forum-sub-menu">
    <ul class="forum-tabs">
        <li data-tab="home" class="active">Home</li>
    </ul>
</div>
```

## Configuration

The plugin creates a `Sub Menu` settings group. Enter one tab per line:

```text
key|Label|comma-separated forum IDs|enabled
```

Initial configuration:

```text
home|Home|115,1,58,99|1
gaming|Gaming|50,119|1
programming|Programming|86,55,76|1
marketplace|MarketPlace|37|1
```

Keys may contain letters, numbers, underscores, and hyphens. The parser does not impose a fixed group-count limit. Set enabled to `1` or `0`. An empty ID list creates a show-all tab when another enabled group contains IDs. If the setting is empty, disabled, or contains no IDs, the plugin recovers to the initial configuration.

## Migration

On first installation, the plugin copies the existing `revolution_theme_menu_groups` value when present. It does not delete the legacy Revolution setting automatically.

## Uninstall

Uninstalling removes the `Sub Menu` setting group and its settings. It does not modify templates or legacy Revolution settings.
