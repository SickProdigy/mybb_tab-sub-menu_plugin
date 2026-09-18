<?php
define('IN_MYBB', 1);
define('MYBB_ROOT', __DIR__ . '/');
class TestPlugins { public function add_hook($hook, $callback) {} }
class TestDb {
    public function simple_select($table, $fields, $where = '', $options = array()) { return array(); }
    public function fetch_field($query, $field) { return ''; }
    public function escape_string($value) { return $value; }
}
$plugins = new TestPlugins();
$db = new TestDb();
$lang = null;
require_once __DIR__ . '/../Upload/inc/plugins/tab_sub_menu.php';

function fail_test($message) { fwrite(STDERR, "FAIL {$message}.\n"); exit(1); }

if (TAB_SUB_MENU_VERSION !== '1.1.0') fail_test('plugin version mismatch');
$info = tab_sub_menu_info();
if ($info['website'] !== 'https://github.com/sickprodigy/mybb_tab-sub-menu_plugin' || $info['authorsite'] !== 'https://www.sickgaming.net') fail_test('plugin metadata links');
$groups = tab_sub_menu_parse_menu_groups("games|Games|2,7|1\ndisabled|Hidden|9|0\nall|Everything||1");
if (count($groups) !== 2 || $groups[0]['forum_ids'] !== array(2, 7) || $groups[1]['forum_ids'] !== array()) fail_test('menu group parsing');
$settings = tab_sub_menu_settings(1); $selectionMode = null;
foreach ($settings as $setting) if ($setting['name'] === 'tab_sub_menu_selection_mode') $selectionMode = $setting;
if (!$selectionMode || $selectionMode['value'] !== 'top' || strpos($selectionMode['optionscode'], 'all=') === false) fail_test('selection mode upgrade default');

$admin = file_get_contents(__DIR__ . '/../Upload/jscripts/tab-sub-menu/tab-sub-menu-admin-settings.js');
$frontend = file_get_contents(__DIR__ . '/../Upload/jscripts/tab-sub-menu/tab-sub-menu.js');
foreach (array('showModal', 'sm-picker-search', 'sm-picker-item', 'usedBy') as $boundary) if (strpos($admin, $boundary) === false) fail_test('hierarchical picker boundary ' . $boundary);
if (strpos($admin, 'prompt(') !== false) fail_test('legacy prompt remains');
foreach (array('data-tab-sub-menu-forum', 'forumRows', 'selectionMode', 'setForumVisibility') as $boundary) if (strpos($frontend, $boundary) === false) fail_test('individual forum boundary ' . $boundary);
$pluginSource = file_get_contents(__DIR__ . '/../Upload/inc/plugins/tab_sub_menu.php');
if (substr_count($pluginSource, "'forumbit_depth2_forum'") < 3 || strpos($pluginSource, '<tr data-tab-sub-menu-forum=') === false) fail_test('stock MyBB forum-row template synchronization');

echo "PASS Tab Sub Menu 1.1.0 checks.\n";
