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
        'version' => '1.0.0',
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
            'description' => 'One tab per line using key|Label|comma-separated forum IDs|enabled. Example: gaming|Gaming|2,3,4|1',
            'optionscode' => 'textarea',
            'value' => sub_menu_initial_setting(),
            'disporder' => 1,
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
    $style_url = $asset_url . '/css/sub-menu.css?ver=100';
    $script_url = $asset_url . '/jscripts/sub-menu/forum-sub-menu.js?ver=100';
    $sub_menu_assets = '<link rel="stylesheet" href="' . htmlspecialchars_uni($style_url) . '" />'
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
