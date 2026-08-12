<?php
/**
 * Sub Menu
 *
 * MyBB plugin for Sick Gaming's configurable forum category submenu output and assets.
 */

if (!defined('IN_MYBB')) {
    die('Direct initialization of this file is not allowed.');
}

function sub_menu_info()
{
    return array(
        'name' => 'Sub Menu',
        'description' => 'Provides configurable forum category tabs and filtering for MyBB.',
        'website' => 'https://www.sickgaming.net',
        'author' => 'Sick Gaming',
        'authorsite' => 'https://www.sickgaming.net',
        'version' => '1.3.0',
        'compatibility' => '18*'
    );
}

function sub_menu_install()
{
    sub_menu_ensure_settings();
}

function sub_menu_ensure_settings()
{
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('sub_menu') . "'");
    $group = $db->fetch_array($query);

    if (!empty($group['gid'])) {
        $gid = (int)$group['gid'];
    } else {
        $gid = (int)$db->insert_query('settinggroups', array(
            'name' => 'sub_menu',
            'title' => 'Sub Menu',
            'description' => 'Settings for the Sub Menu forum menu.',
            'disporder' => 1,
            'isdefault' => 0
        ));
    }

    foreach (sub_menu_settings($gid) as $setting) {
        $query = $db->simple_select('settings', 'sid', "name='" . $db->escape_string($setting['name']) . "'");
        $existing = $db->fetch_array($query);

        if (empty($existing['sid'])) {
            $db->insert_query('settings', $setting);
        } else {
            // Keep the administrator's value, but refresh plugin-owned metadata on upgrade.
            $sid = (int)$existing['sid'];
            unset($setting['value']);
            $db->update_query('settings', $setting, "sid='{$sid}'", 1);
        }
    }

    sub_menu_rebuild_settings();
}

function sub_menu_settings($gid)
{
    return array(
        array(
            'name' => 'sub_menu_groups',
            'title' => 'Forum Category Menu Groups',
            'description' => 'Add one row for each menu tab. The Tab ID is an internal, unique name; the Display name is what visitors see; Forum/Category IDs are the IDs shown by that tab.',
            'optionscode' => 'textarea',
            'value' => sub_menu_initial_setting(),
            'disporder' => 1,
            'gid' => $gid
        ),
        array(
            'name' => 'sub_menu_custom_css',
            'title' => 'Custom Menu CSS',
            'description' => 'Optional CSS added after the plugin stylesheet. Useful selectors: #forum-sub-menu, .forum-tabs li, .forum-tabs li.active, and .forum-tabs li:hover:not(.active).',
            'optionscode' => 'textarea',
            'value' => '',
            'disporder' => 2,
            'gid' => $gid
        )
    );
}

function sub_menu_is_installed()
{
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('sub_menu') . "'");
    $group = $db->fetch_array($query);

    return !empty($group['gid']);
}

function sub_menu_uninstall()
{
    sub_menu_remove_stylesheets();
    global $db;

    $query = $db->simple_select('settinggroups', 'gid', "name='" . $db->escape_string('sub_menu') . "'");
    $group = $db->fetch_array($query);
    if (!empty($group['gid'])) {
        $gid = (int)$group['gid'];
        $db->delete_query('settinggroups', "gid='{$gid}'");
        $db->delete_query('settings', "gid='{$gid}'");
        sub_menu_rebuild_settings();
    }
}

function sub_menu_activate()
{
    sub_menu_ensure_settings();
    sub_menu_sync_stylesheets();

    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$sub_menu}') . '#i',
        ''
    );
    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$forums}') . '#i',
        '{$sub_menu}{$forums}'
    );

    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$sub_menu_assets}') . '#i',
        ''
    );
    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$stylesheets}') . '#i',
        '{$stylesheets}{$sub_menu_assets}'
    );
}

function sub_menu_deactivate()
{
    sub_menu_remove_stylesheets();
    require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';

    find_replace_templatesets(
        'index',
        '#' . preg_quote('{$sub_menu}') . '#i',
        ''
    );
    find_replace_templatesets(
        'headerinclude',
        '#' . preg_quote('{$sub_menu_assets}') . '#i',
        ''
    );
}

function sub_menu_stylesheet()
{
    return <<<'CSS'
#forum-sub-menu {
    margin: 20px 0 30px;
    text-align: center;
}

.forum-tabs {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 18px;
    padding: 0;
    margin: 0;
    list-style: none;
}

.forum-tabs li {
    display: inline-block;
    min-height: 48px;
    padding: 14px 28px;
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

.forum-tabs li.active {
    border-color: #a5a7f2;
    background: linear-gradient(180deg, #a5a7f2 0%, #7c7edc 100%);
    box-shadow: 0 2px 16px #a5a7f2, 0 2px 8px rgba(165, 167, 242, 0.18);
    color: #fff;
}

.forum-tabs li:hover:not(.active) {
    background: linear-gradient(180deg, #bfc2f7 0%, #a0a3f2 100%);
    box-shadow: 0 2px 12px rgba(160, 163, 242, 0.18);
    color: #333;
}
CSS;
}

function sub_menu_load_theme_functions()
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

function sub_menu_sync_stylesheets()
{
    global $db;

    if (!sub_menu_load_theme_functions()) {
        return false;
    }

    $name = 'sub_menu_plugin.css';
    $stylesheet = sub_menu_stylesheet();
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

function sub_menu_remove_stylesheets()
{
    global $db;

    if (!sub_menu_load_theme_functions()) {
        return false;
    }

    $name = 'sub_menu_plugin.css';
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

function sub_menu_sync_stylesheets_after_theme_change()
{
    sub_menu_sync_stylesheets();
}

$plugins->add_hook('admin_style_themes_add_commit', 'sub_menu_sync_stylesheets_after_theme_change');
$plugins->add_hook('admin_style_themes_import_commit', 'sub_menu_sync_stylesheets_after_theme_change');
$plugins->add_hook('admin_style_themes_duplicate_commit', 'sub_menu_sync_stylesheets_after_theme_change');

$plugins->add_hook('admin_config_settings_change', 'sub_menu_admin_settings_editor');

function sub_menu_admin_settings_editor()
{
    global $mybb, $page, $db;
    $query = $db->simple_select('settinggroups', 'gid', "name='sub_menu'", array('limit' => 1));
    if ((int)$mybb->get_input('gid') !== (int)$db->fetch_field($query, 'gid')) { return; }
    $url = rtrim($mybb->asset_url, '/') . '/jscripts/sub-menu/admin-settings.js?ver=130';
    $page->extra_header .= '<script type="text/javascript" src="' . htmlspecialchars_uni($url) . '"></script>';
}

$plugins->add_hook('global_start', 'sub_menu_menu_output');
function sub_menu_menu_output()
{
    global $mybb, $sub_menu_assets, $sub_menu;

    $groups = sub_menu_get_menu_groups();
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
    $script_url = $asset_url . '/jscripts/sub-menu/forum-sub-menu.js?ver=110';
    $custom_css = isset($mybb->settings['sub_menu_custom_css'])
        ? trim((string)$mybb->settings['sub_menu_custom_css'])
        : '';
    $custom_style = $custom_css !== ''
        ? '<style type="text/css" id="sub-menu-custom-css">' . str_ireplace('</style', '<\/style', $custom_css) . '</style>'
        : '';

    $sub_menu_assets = $custom_style
        . '<script type="text/javascript">window.subMenuGroups = ' . $json . ';</script>'
        . '<script type="text/javascript" src="' . htmlspecialchars_uni($script_url) . '"></script>';

    $sub_menu = sub_menu_build_sub_menu($group_labels);
}

function sub_menu_get_menu_groups()
{
    global $mybb;

    $setting = isset($mybb->settings['sub_menu_groups'])
        ? $mybb->settings['sub_menu_groups']
        : '';

    $groups = sub_menu_parse_menu_groups($setting);

    $has_forum_ids = false;

    foreach ($groups as $group) {
        if (!empty($group['forum_ids'])) {
            $has_forum_ids = true;
            break;
        }
    }

    if (empty($groups) || !$has_forum_ids) {
        $groups = sub_menu_default_menu_groups();
    }

    return $groups;
}

function sub_menu_parse_menu_groups($value)
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
            'forum_ids' => sub_menu_parse_forum_ids(isset($parts[2]) ? $parts[2] : '')
        );
    }

    return $groups;
}

function sub_menu_parse_forum_ids($value)
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

function sub_menu_default_menu_groups()
{
    return array(
        array('key' => 'home', 'label' => 'Home', 'forum_ids' => array(115, 1, 58, 99)),
        array('key' => 'gaming', 'label' => 'Gaming', 'forum_ids' => array(50, 119)),
        array('key' => 'programming', 'label' => 'Programming', 'forum_ids' => array(86, 55, 76)),
        array('key' => 'marketplace', 'label' => 'MarketPlace', 'forum_ids' => array(37))
    );
}

function sub_menu_initial_setting()
{
    global $db;

    $query = $db->simple_select('settings', 'value', "name='revolution_theme_menu_groups'");
    $legacy = $db->fetch_array($query);

    if (!empty($legacy['value'])) {
        return $legacy['value'];
    }

    return sub_menu_default_menu_setting();
}

function sub_menu_default_menu_setting()
{
    $lines = array();

    foreach (sub_menu_default_menu_groups() as $group) {
        $forum_ids = implode(',', $group['forum_ids']);
        $lines[] = $group['key'] . '|' . $group['label'] . '|' . $forum_ids . '|1';
    }

    return implode("\n", $lines);
}

function sub_menu_build_sub_menu($groups)
{
    $html = '<div id="forum-sub-menu"><ul class="forum-tabs">';
    $first = true;

    foreach ($groups as $key => $label) {
        $active = $first ? ' class="active"' : '';
        $html .= '<li data-tab="' . htmlspecialchars_uni($key) . '"' . $active . '>' . htmlspecialchars_uni($label) . '</li>';
        $first = false;
    }

    $html .= '</ul></div>';

    return $html;
}

function sub_menu_rebuild_settings()
{
    if (!function_exists('rebuild_settings')) {
        require_once MYBB_ROOT . 'inc/functions.php';
    }

    rebuild_settings();
}
