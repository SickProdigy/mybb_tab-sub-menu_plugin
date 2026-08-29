<?php

$l['tab_sub_menu_plugin_name'] = 'Tab Sub Menu';
$l['tab_sub_menu_plugin_description'] = 'Organizes forum categories into configurable tabs for easier navigation.';

$l['tab_sub_menu_setting_group_title'] = 'Tab Sub Menu';
$l['tab_sub_menu_setting_group_description'] = 'Settings for the Tab Sub Menu forum menu.';
$l['tab_sub_menu_setting_groups_title'] = 'Forum Category Menu Groups';
$l['tab_sub_menu_setting_groups_description'] = 'Add one row for each menu tab. The Tab ID is an internal, unique name; the Display name is what visitors see; Forum/Category IDs are the IDs shown by that tab.';
$l['tab_sub_menu_setting_hide_empty_title'] = 'Hide Empty Tabs';
$l['tab_sub_menu_setting_hide_empty_description'] = 'Hide tabs whose configured categories are not present in the forum index rendered for the current visitor. Show-all tabs remain available.';
$l['tab_sub_menu_setting_default_tab_title'] = 'Default Tab';
$l['tab_sub_menu_setting_default_tab_description'] = 'Tab selected when no valid remembered selection is available. Only enabled configured tabs are offered.';
$l['tab_sub_menu_setting_remember_title'] = 'Remember Visitor Selection';
$l['tab_sub_menu_setting_remember_description'] = 'Remember each visitor\'s selected tab in installation-scoped browser storage.';
$l['tab_sub_menu_setting_url_state_title'] = 'Enable Shareable Tab URLs';
$l['tab_sub_menu_setting_url_state_description'] = 'Store the active tab in the tsm_tab URL parameter so links are shareable and browser Back/Forward navigation follows tab changes.';
$l['tab_sub_menu_setting_custom_css_title'] = 'Custom Menu CSS';
$l['tab_sub_menu_setting_custom_css_description'] = 'Optional CSS added after the maintained plugin stylesheet. Useful selectors: #forum-tab-sub-menu, .tab-sub-menu li, .tab-sub-menu li.active, and .tab-sub-menu li:hover:not(.active).';

$l['tab_sub_menu_editor_help'] = 'Use a short, unique Tab ID such as <code>gaming</code>. Enter Forum/Category IDs separated by commas, such as <code>2,3,4</code>. Leave the IDs empty for a “show all” tab.';
$l['tab_sub_menu_editor_tab_id'] = 'Tab ID (internal)';
$l['tab_sub_menu_editor_tab_id_example'] = 'gaming';
$l['tab_sub_menu_editor_display_name'] = 'Display name';
$l['tab_sub_menu_editor_display_name_example'] = 'Gaming';
$l['tab_sub_menu_editor_forum_ids'] = 'Forum/Category IDs';
$l['tab_sub_menu_editor_forum_ids_example'] = '2,3,4';
$l['tab_sub_menu_editor_enabled'] = 'Enabled';
$l['tab_sub_menu_editor_add_tab'] = 'Add tab';
$l['tab_sub_menu_editor_remove'] = 'Remove';
$l['tab_sub_menu_editor_unnamed_tab'] = 'Unnamed tab';
$l['tab_sub_menu_editor_missing'] = 'Missing: {1}';
$l['tab_sub_menu_editor_all_assigned'] = 'All top-level categories assigned.';
$l['tab_sub_menu_editor_categories_unavailable'] = 'Category list unavailable; enter IDs manually.';
$l['tab_sub_menu_editor_select'] = 'Select';
$l['tab_sub_menu_editor_used_by'] = 'used by {1}';
$l['tab_sub_menu_editor_missing_marker'] = 'missing';
$l['tab_sub_menu_editor_category_prompt'] = "Top-level categories:\n\n{1}\n\nEnter IDs to add:";
$l['tab_sub_menu_editor_view_css'] = 'View maintained default CSS';
$l['tab_sub_menu_editor_css_warning'] = 'Custom CSS loads after this maintained stylesheet. Copy only when you want a full editable starting point; copied rules can override future plugin style updates.';
$l['tab_sub_menu_editor_copy_css'] = 'Copy defaults to Custom Menu CSS';
$l['tab_sub_menu_editor_replace_css'] = 'Replace your existing Custom Menu CSS with the maintained defaults?';

$l['tab_sub_menu_upgrade_stylesheet_error'] = 'Unable to load MyBB theme functions or synchronize plugin stylesheets.';
$l['tab_sub_menu_upgrade_template_error'] = 'Unable to synchronize plugin template variables.';
$l['tab_sub_menu_upgrade_error'] = 'Tab Sub Menu could not upgrade to version %1$s: %2$s The upgrade remains pending and will be retried on the next Admin CP request.';
$l['tab_sub_menu_default_home'] = 'Home';
