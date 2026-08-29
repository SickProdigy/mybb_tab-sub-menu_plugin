# MyBB Tab Sub Menu Plugin

A configurable tabbed navigation menu for organizing MyBB 1.8 forum categories.
It works independently of your theme and lets visitors quickly switch between
groups of forums from the board index.

![Tab Sub Menu preview](assets/tab-sub-menu-preview.png)

## Features

- Per-tab lists of top-level forum category IDs.
- Plugin-owned responsive stylesheet installed into every MyBB theme automatically.
- Equal-width two-column phone layout with a single-column fallback for narrow screens.
- Optional show-all tabs with an empty ID list.
- Enabled/disabled flag for each tab.
- Remembers the visitor's selected tab in installation-scoped `localStorage`.
- Configurable default tab and visitor-selection persistence.
- Optional shareable tab URLs with Back and Forward navigation.
- Flash-resistant initialization with a no-JavaScript and script-failure fallback.
- Provides keyboard-operable tab controls with visible focus and selected-state announcements.
- Optionally hides tabs that have no categories rendered for the current visitor.

## Install

Copy the contents of `Upload/` into the MyBB installation root:

```text
Upload/inc/plugins/tab_sub_menu.php -> public_html/inc/plugins/tab_sub_menu.php
Upload/jscripts/tab-sub-menu/tab-sub-menu.js -> public_html/jscripts/tab-sub-menu/tab-sub-menu.js
```

Then install and activate `Tab Sub Menu` under Admin CP → Configuration → Plugins. Activation installs `tab_sub_menu_plugin.css` and the required template variables into every existing theme, then rebuilds the MyBB stylesheet caches.

## Upgrade

Replace the plugin files, then visit any Admin CP page. The plugin detects a newer file version and runs pending setting migrations, template synchronization, stylesheet refreshes, and theme cache rebuilds once for that version. Deactivation and reactivation remain supported but are no longer required for routine upgrades.

The installed version and last upgrade error are stored in MyBB's data cache. A failed upgrade is not marked complete, is written to the PHP error log, displays an Admin CP error, and retries on the next administrative request. Administrator settings and genuine custom CSS are preserved. Version 0.5.1 also removes the untouched legacy default from **Custom Menu CSS** so it cannot override maintained responsive rules.

## Theme Integration

Activation installs and maintains `tab_sub_menu_plugin.css` in every theme, then automatically inserts `{$tab_sub_menu_assets}` into `headerinclude` and `{$tab_sub_menu}` immediately before `{$forums}` in the `index` template. Deactivation removes both insertions and the plugin-owned stylesheet from every theme.

If a customized template does not contain `{$stylesheets}` or `{$forums}`, add the corresponding plugin variable manually. Without the plugin, the normal `{$forums}` output continues to show the complete forum list.

Themes added, imported, or duplicated while the plugin is active receive the stylesheet and required template variables automatically. The plugin generates this markup:

```html
<div id="forum-tab-sub-menu">
    <ul class="tab-sub-menu" role="tablist" aria-label="Forum categories">
        <li role="presentation" class="active">
            <button type="button" role="tab" data-tab="home" aria-selected="true" tabindex="0">Home</button>
        </li>
    </ul>
</div>
```

### Theme Compatibility

The built-in category adapter supports stock MyBB 1.8 and Space Cadet markup by locating `tbody` elements whose IDs use `cat_{fid}_e`, filtering their containing tables, and handling an adjacent `<br>` spacer when present. Missing spacers do not affect filtering.

Substantially customized themes can mark each outer category component directly:

```html
<section data-tab-sub-menu-category="{$forum['fid']}">...</section>
```

For other structures, define `window.tabSubMenuCategoryAdapter` before `{$tab_sub_menu_assets}`. The adapter accepts a CSS `selector` and optional `getId(marker)`, `getContainer(marker)`, and `getSeparators(marker, container)` functions. `getSeparators` should return an array or array-like collection of decorative elements that should follow the category visibility. If the selector is invalid, the adapter throws, or no supported categories are discovered, filtering fails open and leaves the complete forum index visible.

## Configuration

The plugin creates a `Tab Sub Menu` settings group with a row editor. Use **Add tab** and **Remove** to manage rows. Each row has a unique internal Tab ID (such as `gaming`), the Display name visitors see, comma-separated Forum/Category IDs, and an Enabled checkbox. Leave the IDs empty for a show-all tab.

Enable **Hide Empty Tabs** to remove tabs whose configured category IDs are absent from the current visitor's rendered forum index. This respects MyBB forum visibility, ignores deleted or stale IDs, keeps show-all tabs available, and falls back to the configured default or first available tab when a saved selection is unavailable. Disable the setting to keep every configured tab visible.

Choose any enabled row under **Default Tab** and use **Remember Visitor Selection** to control whether browser storage is used as a fallback. The selection order is: a valid URL tab when URL state is enabled, a valid remembered tab when persistence is enabled, the configured default tab, then the first tab available to that visitor. Disabling persistence removes the installation-scoped saved value and starts from URL state, the configured default, or its safe fallback.

Enable **Shareable Tab URLs** to place the active key in the `tsm_tab` query parameter. URL state has priority over remembered and default selections, existing query parameters and hashes are preserved, and Back/Forward navigation restores earlier tab choices. Invalid or visitor-unavailable URL keys fall through safely. Disable the setting to leave the address unchanged and use local selection state only.

On the board index, the plugin briefly applies an initialization state before the page is painted and removes it immediately after filtering. The state preserves layout space to avoid a category flash and cumulative layout shift. The window load event removes the state if the public initializer fails, with a ten-second emergency timeout as a final backstop. With JavaScript disabled, the state is never applied and the complete forum index remains visible.

Remembered selections are scoped to the configured MyBB board path, so multiple installations on one domain keep independent active tabs. The first visit after upgrading migrates a valid legacy `tabSubMenuTab` value to the scoped key. Invalid, removed, or unavailable saved tab IDs are discarded before falling back to Home or the first available tab.

The editor preserves the original format internally for compatibility:

```text
key|Label|comma-separated forum IDs|enabled
```

Initial configuration:

```text
home|Home||1
```

### Custom CSS

The **Custom Menu CSS** setting is added after the plugin-owned stylesheet, allowing appearance overrides without editing plugin files. The setting page includes an expandable read-only copy of the maintained stylesheet and an explicit button to copy those defaults into the custom field when a full editable starting point is preferred. Copied rules can override later plugin style updates, so targeted overrides are recommended. Common selectors include `#forum-tab-sub-menu`, `.tab-sub-menu li`, `.tab-sub-menu li.active`, and `.tab-sub-menu li:hover:not(.active)`.

Keys may contain letters, numbers, underscores, and hyphens. The parser does not impose a fixed group-count limit. Set enabled to `1` or `0`. An empty ID list creates a show-all tab when another enabled group contains IDs. If the setting is empty, disabled, or contains no IDs, the plugin recovers to the initial configuration.

## Uninstall

Uninstalling removes the plugin-owned stylesheets, the `Tab Sub Menu` setting group, and its settings. It does not modify unrelated templates or settings.

## License

Copyright (C) 2026 SickProdigy.

This project is licensed under the GNU General Public License, version 3 or any later version. See [LICENSE](LICENSE) for the complete license terms.
