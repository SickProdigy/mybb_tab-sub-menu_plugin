<?php
/**
 * Tab Sub Menu
 *
 * MyBB plugin for organizing forum categories into configurable tabs.
 *
 * Copyright (C) 2026 SickProdigy
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

function tab_sub_menu_info()
{
    return array(
        'name' => 'Tab Sub Menu',
        'description' => 'Organizes forum categories into configurable tabs for easier navigation.',
        'website' => 'https://www.sickgaming.net',
        'author' => 'SickProdigy',
        'authorsite' => 'https://www.sickgaming.net',
        'license' => 'GPL-3.0-or-later',
        'version' => '0.5.10',
        'compatibility' => '18*'
    );
}

function tab_sub_menu_install()
{
    tab_sub_menu_ensure_settings();
}

function tab_sub_menu_ensure_settings()
{
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('tab_sub_menu') . "'");
    $group = $db->fetch_array($query);

    if (!empty($group['gid'])) {
        $gid = (int)$group['gid'];
    } else {
        $gid = (int)$db->insert_query('settinggroups', array(
            'name' => 'tab_sub_menu',
            'title' => 'Tab Sub Menu',
            'description' => 'Settings for the Tab Sub Menu forum menu.',
            'disporder' => 1,
            'isdefault' => 0
        ));
    }

    foreach (tab_sub_menu_settings($gid) as $setting) {
        $query = $db->simple_select('settings', 'sid, value', "name='" . $db->escape_string($setting['name']) . "'");
        $existing = $db->fetch_array($query);

        if (empty($existing['sid'])) {
            $db->insert_query('settings', tab_sub_menu_escape_setting($setting));
        } else {
            // Keep the administrator's value, but refresh plugin-owned metadata on upgrade.
            $sid = (int)$existing['sid'];
            if ($setting["name"] === "tab_sub_menu_custom_css") {
                $custom_css = trim((string)$existing["value"]);
                $custom_css_fingerprint = sha1(str_replace("\r\n", "\n", $custom_css));
                if ($custom_css === "" || $custom_css_fingerprint === "9a781413fcb7bd37804488d4f743da81714592ea") {
                    // Remove the untouched 0.5.0 default copy so it cannot override maintained responsive CSS.
                    $setting["value"] = "";
                } else {
                    unset($setting["value"]);
                }
            } else {
                unset($setting["value"]);
            }
            $db->update_query('settings', tab_sub_menu_escape_setting($setting), "sid='{$sid}'", 1);
        }
    }

    tab_sub_menu_rebuild_settings();
}

function tab_sub_menu_escape_setting($setting)
{
    global $db;

    foreach (array('name', 'title', 'description', 'optionscode', 'value') as $field) {
        if (isset($setting[$field])) {
            $setting[$field] = $db->escape_string((string)$setting[$field]);
        }
    }

    return $setting;
}

function tab_sub_menu_settings($gid)
{
    return array(
        array(
            'name' => 'tab_sub_menu_groups',
            'title' => 'Forum Category Menu Groups',
            'description' => 'Add one row for each menu tab. The Tab ID is an internal, unique name; the Display name is what visitors see; Forum/Category IDs are the IDs shown by that tab.',
            'optionscode' => 'textarea',
            'value' => tab_sub_menu_initial_setting(),
            'disporder' => 1,
            'gid' => $gid
        ),
        array(
            'name' => 'tab_sub_menu_hide_empty_tabs',
            'title' => 'Hide Empty Tabs',
            'description' => 'Hide tabs whose configured categories are not present in the forum index rendered for the current visitor. Show-all tabs remain available.',
            'optionscode' => 'yesno',
            'value' => '1',
            'disporder' => 2,
            'gid' => $gid
        ),
        array(
            'name' => 'tab_sub_menu_default_tab',
            'title' => 'Default Tab',
            'description' => 'Tab selected when no valid remembered selection is available. Only enabled configured tabs are offered.',
            'optionscode' => tab_sub_menu_default_tab_optionscode(),
            'value' => 'home',
            'disporder' => 3,
            'gid' => $gid
        ),
        array(
            'name' => 'tab_sub_menu_remember_selection',
            'title' => 'Remember Visitor Selection',
            'description' => 'Remember each visitor\'s selected tab in installation-scoped browser storage.',
            'optionscode' => 'yesno',
            'value' => '1',
            'disporder' => 4,
            'gid' => $gid
        ),
        array(
            'name' => 'tab_sub_menu_url_state',
            'title' => 'Enable Shareable Tab URLs',
            'description' => 'Store the active tab in the tsm_tab URL parameter so links are shareable and browser Back/Forward navigation follows tab changes.',
            'optionscode' => 'yesno',
            'value' => '1',
            'disporder' => 5,
            'gid' => $gid
        ),
        array(
            'name' => 'tab_sub_menu_custom_css',
            'title' => 'Custom Menu CSS',
            'description' => 'Optional CSS added after the maintained plugin stylesheet. Useful selectors: #forum-tab-sub-menu, .tab-sub-menu li, .tab-sub-menu li.active, and .tab-sub-menu li:hover:not(.active).',
            'optionscode' => 'textarea',
            'value' => '',
            'disporder' => 6,
            'gid' => $gid
        )
    );
}

function tab_sub_menu_default_tab_optionscode()
{
    global $db;

    $query = $db->simple_select(
        'settings',
        'value',
        "name='" . $db->escape_string('tab_sub_menu_groups') . "'",
        array('limit' => 1)
    );
    $groups_value = $db->fetch_field($query, 'value');
    $groups = tab_sub_menu_parse_menu_groups($groups_value);
    if (empty($groups)) {
        $groups = tab_sub_menu_default_menu_groups();
    }

    $options = array('select');
    foreach ($groups as $group) {
        $label = str_replace(array("\r", "\n"), ' ', (string)$group['label']);
        $options[] = $group['key'] . '=' . $label;
    }

    return implode("\n", $options);
}

function tab_sub_menu_is_installed()
{
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('tab_sub_menu') . "'");
    $group = $db->fetch_array($query);

    return !empty($group['gid']);
}

function tab_sub_menu_uninstall()
{
    tab_sub_menu_remove_stylesheets();
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('tab_sub_menu') . "'");
    $group = $db->fetch_array($query);
    if (!empty($group['gid'])) {
        $gid = (int)$group['gid'];
        $db->delete_query('settinggroups', "gid='{$gid}'");
        $db->delete_query('settings', "gid='{$gid}'");
        tab_sub_menu_rebuild_settings();
    }
}

function tab_sub_menu_activate()
{
    tab_sub_menu_ensure_settings();
    tab_sub_menu_sync_stylesheets();

    tab_sub_menu_sync_template_variables();
}

function tab_sub_menu_sync_template_variables()
{
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$tab_sub_menu}') . '#i',
        ''
    );
    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$forums}') . '#i',
        '{$tab_sub_menu}{$forums}'
    );

    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$tab_sub_menu_assets}') . '#i',
        ''
    );
    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$stylesheets}') . '#i',
        '{$stylesheets}{$tab_sub_menu_assets}'
    );
    return true;
}

function tab_sub_menu_deactivate()
{
    tab_sub_menu_remove_stylesheets();
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$tab_sub_menu}') . '#i',
        ''
    );
    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$tab_sub_menu_assets}') . '#i',
        ''
    );
}

function tab_sub_menu_stylesheet()
{
    return <<<'CSS'
#forum-tab-sub-menu {
    margin: 20px 0 30px;
    text-align: center;
}

html.tab-sub-menu-initializing body {
    visibility: hidden;
}

.tab-sub-menu {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 18px;
    padding: 0;
    margin: 0;
    list-style: none;
}

.tab-sub-menu li {
    display: inline-block;
    min-height: 48px;
    box-sizing: border-box;
    border: 1px solid #a0a3f2;
    border-radius: 6px;
    background: linear-gradient(180deg, #e3eafc 0%, #a0a3f2 100%);
    box-shadow: 0 2px 8px rgba(160, 163, 242, 0.12);
    color: #333;
    cursor: pointer;
    font-size: 1.1em;
    transition: background 0.2s, color 0.2s, box-shadow 0.2s;
}

.tab-sub-menu li[hidden] {
    display: none;
}

.tab-sub-menu button {
    display: block;
    width: 100%;
    min-height: 46px;
    padding: 14px 28px;
    border: 0;
    background: transparent;
    color: inherit;
    cursor: inherit;
    font: inherit;
    line-height: inherit;
}

.tab-sub-menu button:focus-visible {
    outline: 3px solid #222;
    outline-offset: 3px;
}

.tab-sub-menu li.active {
    border-color: #a5a7f2;
    background: linear-gradient(180deg, #a5a7f2 0%, #7c7edc 100%);
    box-shadow: 0 2px 16px #a5a7f2, 0 2px 8px rgba(165, 167, 242, 0.18);
    color: #fff;
}

.tab-sub-menu li:hover:not(.active) {
    background: linear-gradient(180deg, #bfc2f7 0%, #a0a3f2 100%);
    box-shadow: 0 2px 12px rgba(160, 163, 242, 0.18);
    color: #333;
}

@media (max-width: 600px) {
    #forum-tab-sub-menu {
        margin: 14px 0 20px;
    }

    .tab-sub-menu {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        box-sizing: border-box;
        width: calc(100vw - 20px);
        max-width: 100%;
        margin-right: auto;
        margin-left: auto;
    }

    .tab-sub-menu li {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-width: 0;
        min-height: 46px;
        font-size: 1em;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .tab-sub-menu button {
        padding: 11px 10px;
    }
}

@media (max-width: 360px) {
    .tab-sub-menu {
        grid-template-columns: minmax(0, 1fr);
        width: calc(100vw - 16px);
    }
}
CSS;
}

function tab_sub_menu_load_theme_functions()
{
    global $config;

    if (function_exists('cache_stylesheet') && function_exists('update_theme_stylesheet_list')) {
        return true;
    }

    $candidates = array();

    if (!empty($_SERVER['SCRIPT_FILENAME'])) {
        $candidates[] = dirname($_SERVER['SCRIPT_FILENAME']) . '/inc/functions_themes.php';
    }

    if (!empty($config['admin_dir'])) {
        $candidates[] = MYBB_ROOT . trim($config['admin_dir'], '/\\') . '/inc/functions_themes.php';
    }

    $candidates[] = MYBB_ROOT . 'admin/inc/functions_themes.php';

    foreach (array_unique($candidates) as $functions_file) {
        if (file_exists($functions_file)) {
            require_once $functions_file;
            break;
        }
    }

    return function_exists('cache_stylesheet') && function_exists('update_theme_stylesheet_list');
}

function tab_sub_menu_sync_stylesheets()
{
    global $db;

    if (!tab_sub_menu_load_theme_functions()) {
        return false;
    }

    $name = 'tab_sub_menu_plugin.css';
    $stylesheet = tab_sub_menu_stylesheet();
    $theme_ids = array();
    $query = $db->simple_select('themes', 'tid', 'tid > 1');

    while ($theme = $db->fetch_array($query)) {
        $tid = (int)$theme['tid'];
        $theme_ids[] = $tid;
        $existing_query = $db->simple_select(
            'themestylesheets',
            'sid',
            "tid='{$tid}' AND name='" . $db->escape_string($name) . "'",
            array('limit' => 1)
        );
        $existing = $db->fetch_array($existing_query);
        $stylesheet_data = array(
            'name' => $db->escape_string($name),
            'tid' => $tid,
            'attachedto' => '',
            'stylesheet' => $db->escape_string($stylesheet),
            'cachefile' => $db->escape_string($name),
            'lastmodified' => TIME_NOW
        );

        if (!empty($existing['sid'])) {
            $sid = (int)$existing['sid'];
            $db->update_query('themestylesheets', $stylesheet_data, "sid='{$sid}'", 1);
        } else {
            $sid = (int)$db->insert_query('themestylesheets', $stylesheet_data);
        }

        if (!cache_stylesheet($tid, $name, $stylesheet)) {
            $db->update_query(
                'themestylesheets',
                array('cachefile' => "css.php?stylesheet={$sid}"),
                "sid='{$sid}'",
                1
            );
        }
    }

    foreach ($theme_ids as $tid) {
        update_theme_stylesheet_list($tid);
    }

    return true;
}

function tab_sub_menu_remove_stylesheets()
{
    global $db;

    if (!tab_sub_menu_load_theme_functions()) {
        return false;
    }

    $name = 'tab_sub_menu_plugin.css';
    $theme_ids = array();
    $query = $db->simple_select(
        'themestylesheets',
        'sid, tid, cachefile',
        "name='" . $db->escape_string($name) . "'"
    );

    while ($stylesheet = $db->fetch_array($query)) {
        $sid = (int)$stylesheet['sid'];
        $tid = (int)$stylesheet['tid'];
        $cachefile = basename($stylesheet['cachefile']);
        $theme_ids[$tid] = $tid;
        $db->delete_query('themestylesheets', "sid='{$sid}'", 1);

        if ($cachefile !== '' && strpos($cachefile, 'css.php') === false) {
            @unlink(MYBB_ROOT . "cache/themes/theme{$tid}/{$cachefile}");
            @unlink(MYBB_ROOT . 'cache/themes/theme' . $tid . '/' . str_replace('.css', '.min.css', $cachefile));
            @unlink(MYBB_ROOT . "cache/themes/{$tid}_{$cachefile}");
            @unlink(MYBB_ROOT . 'cache/themes/' . $tid . '_' . str_replace('.css', '.min.css', $cachefile));
        }
    }

    foreach ($theme_ids as $tid) {
        update_theme_stylesheet_list($tid);
    }

    return true;
}

function tab_sub_menu_sync_after_theme_change()
{
    tab_sub_menu_sync_stylesheets();
    tab_sub_menu_sync_template_variables();
}

$plugins->add_hook('admin_style_themes_add_commit', 'tab_sub_menu_sync_after_theme_change');
$plugins->add_hook('admin_style_themes_import_commit', 'tab_sub_menu_sync_after_theme_change');
$plugins->add_hook('admin_style_themes_duplicate_commit', 'tab_sub_menu_sync_after_theme_change');

$plugins->add_hook('admin_config_settings_change', 'tab_sub_menu_admin_settings_editor');

function tab_sub_menu_admin_settings_editor()
{
    global $mybb, $page, $db;
    $query = $db->simple_select("settinggroups", "gid", "name='tab_sub_menu'", array("limit" => 1));
    if ((int)$mybb->get_input("gid") !== (int)$db->fetch_field($query, "gid")) { return; }

    $categories = array();
    $query = $db->simple_select("forums", "fid, name", "type='c' AND pid='0'", array("order_by" => "disporder", "order_dir" => "ASC"));
    while ($category = $db->fetch_array($query)) {
        $categories[] = array("id" => (int)$category["fid"], "name" => (string)$category["name"]);
    }
    $json = json_encode($categories);
    if ($json === false) { $json = "[]"; }
    $json = str_replace(array("<", ">", "&"), array("\u003C", "\u003E", "\u0026"), $json);

    $stylesheet_json = json_encode(tab_sub_menu_stylesheet());
    if ($stylesheet_json === false) { $stylesheet_json = '""'; }
    $stylesheet_json = str_replace(array("<", ">", "&"), array("\u003C", "\u003E", "\u0026"), $stylesheet_json);

    $base = rtrim($mybb->asset_url, "/") . "/jscripts/tab-sub-menu/";
    $page->extra_header .= "<script>window.tabSubMenuCategories = " . $json . ";</script>";
    $page->extra_header .= "<script>window.tabSubMenuMaintainedCss = " . $stylesheet_json . ";</script>";
    $page->extra_header .= '<script type="text/javascript" src="' . htmlspecialchars_uni($base . "tab-sub-menu-admin-settings.js?ver=059") . '"></script>';
}

$plugins->add_hook('global_start', 'tab_sub_menu_menu_output');
function tab_sub_menu_menu_output()
{
    global $mybb, $tab_sub_menu_assets, $tab_sub_menu;

    $groups = tab_sub_menu_get_menu_groups();
    $group_ids = array();
    $group_labels = array();

    foreach ($groups as $group) {
        $group_ids[$group['key']] = $group['forum_ids'];
        $group_labels[$group['key']] = $group['label'];
    }

    $json = json_encode($group_ids);
    if ($json === false) {
        $json = '{}';
    }

    $asset_url = rtrim($mybb->asset_url, '/');
    $script_url = $asset_url . '/jscripts/tab-sub-menu/tab-sub-menu.js?ver=0510';
    $hide_empty_tabs = !empty($mybb->settings['tab_sub_menu_hide_empty_tabs']) ? 'true' : 'false';
    $default_tab = isset($mybb->settings['tab_sub_menu_default_tab'])
        ? preg_replace('/[^a-z0-9_-]/i', '', (string)$mybb->settings['tab_sub_menu_default_tab'])
        : 'home';
    $default_tab_json = json_encode($default_tab);
    if ($default_tab_json === false) {
        $default_tab_json = '"home"';
    }
    $remember_selection = !isset($mybb->settings['tab_sub_menu_remember_selection'])
        || !empty($mybb->settings['tab_sub_menu_remember_selection'])
        ? 'true'
        : 'false';
    $url_state = !isset($mybb->settings['tab_sub_menu_url_state'])
        || !empty($mybb->settings['tab_sub_menu_url_state'])
        ? 'true'
        : 'false';
    $board_url = isset($mybb->settings['bburl']) ? (string)$mybb->settings['bburl'] : '';
    $storage_key_json = json_encode(tab_sub_menu_storage_key($board_url));
    if ($storage_key_json === false) {
        $storage_key_json = '"tabSubMenuTab:/"';
    }
    $storage_key_json = str_replace(array('<', '>', '&'), array('\u003C', '\u003E', '\u0026'), $storage_key_json);
    $custom_css = isset($mybb->settings['tab_sub_menu_custom_css'])
        ? trim((string)$mybb->settings['tab_sub_menu_custom_css'])
        : '';
    $custom_style = $custom_css !== ''
        ? '<style type="text/css" id="tab-sub-menu-custom-css">' . str_ireplace('</style', '<\/style', $custom_css) . '</style>'
        : '';
    $initialization_script = defined('THIS_SCRIPT') && THIS_SCRIPT === 'index.php'
        ? '<script type="text/javascript">(function(w,d){d.documentElement.classList.add("tab-sub-menu-initializing");'
            . 'function reveal(){d.documentElement.classList.remove("tab-sub-menu-initializing");}'
            . 'w.addEventListener("load",reveal);w.setTimeout(reveal,10000);}(window,document));</script>'
        : '';

    $tab_sub_menu_assets = $custom_style . $initialization_script
        . '<script type="text/javascript">window.tabSubMenuGroups = ' . $json
        . '; window.tabSubMenuHideEmptyTabs = ' . $hide_empty_tabs
        . '; window.tabSubMenuDefaultTab = ' . $default_tab_json
        . '; window.tabSubMenuRememberSelection = ' . $remember_selection
        . '; window.tabSubMenuUrlState = ' . $url_state
        . '; window.tabSubMenuStorageKey = ' . $storage_key_json . ';</script>'
        . '<script type="text/javascript" src="' . htmlspecialchars_uni($script_url) . '"></script>';

    $tab_sub_menu = tab_sub_menu_build_tab_sub_menu($group_labels);
}

function tab_sub_menu_storage_key($board_url)
{
    $board_path = parse_url((string)$board_url, PHP_URL_PATH);
    $board_path = is_string($board_path) ? '/' . trim($board_path, '/') : '/';

    return 'tabSubMenuTab:' . $board_path;
}

function tab_sub_menu_get_menu_groups()
{
    global $mybb;

    $setting = isset($mybb->settings['tab_sub_menu_groups'])
        ? $mybb->settings['tab_sub_menu_groups']
        : '';

    $groups = tab_sub_menu_parse_menu_groups($setting);

    $has_forum_ids = false;

    foreach ($groups as $group) {
        if (!empty($group['forum_ids'])) {
            $has_forum_ids = true;
            break;
        }
    }

    if (empty($groups) || !$has_forum_ids) {
        $groups = tab_sub_menu_default_menu_groups();
    }

    return $groups;
}

function tab_sub_menu_parse_menu_groups($value)
{
    $groups = array();
    $lines = preg_split('/\r\n|\r|\n/', trim((string)$value));

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        $parts = array_map('trim', explode('|', $line));
        $key = isset($parts[0]) ? preg_replace('/[^a-z0-9_-]/i', '', $parts[0]) : '';
        $label = isset($parts[1]) ? $parts[1] : '';
        $enabled = !isset($parts[3]) || (int)$parts[3] === 1;

        if ($key === '' || $label === '' || !$enabled) {
            continue;
        }

        $groups[] = array(
            'key' => $key,
            'label' => $label,
            'forum_ids' => tab_sub_menu_parse_forum_ids(isset($parts[2]) ? $parts[2] : '')
        );
    }

    return $groups;
}

function tab_sub_menu_parse_forum_ids($value)
{
    $forum_ids = array();

    foreach (explode(',', (string)$value) as $forum_id) {
        $forum_id = (int)trim($forum_id);

        if ($forum_id > 0) {
            $forum_ids[] = $forum_id;
        }
    }

    return $forum_ids;
}

function tab_sub_menu_default_menu_groups()
{
    return array(
        array('key' => 'home', 'label' => 'Home', 'forum_ids' => array())
    );
}

function tab_sub_menu_initial_setting()
{
    return tab_sub_menu_default_menu_setting();
}

function tab_sub_menu_default_menu_setting()
{
    $lines = array();

    foreach (tab_sub_menu_default_menu_groups() as $group) {
        $forum_ids = implode(',', $group['forum_ids']);
        $lines[] = $group['key'] . '|' . $group['label'] . '|' . $forum_ids . '|1';
    }

    return implode("\n", $lines);
}

function tab_sub_menu_build_tab_sub_menu($groups)
{
    $html = '<div id="forum-tab-sub-menu"><ul class="tab-sub-menu" role="tablist" aria-label="Forum categories">';
    $first = true;

    foreach ($groups as $key => $label) {
        $active = $first ? ' class="active"' : '';
        $selected = $first ? 'true' : 'false';
        $tabindex = $first ? '0' : '-1';
        $html .= '<li role="presentation"' . $active . '><button type="button" role="tab" data-tab="'
            . htmlspecialchars_uni($key) . '" aria-selected="' . $selected . '" tabindex="' . $tabindex . '">'
            . htmlspecialchars_uni($label) . '</button></li>';
        $first = false;
    }

    $html .= '</ul></div>';

    return $html;
}

function tab_sub_menu_rebuild_settings()
{
    if (!function_exists('rebuild_settings')) {
        require_once MYBB_ROOT . 'inc/functions.php';
    }

    rebuild_settings();
}
