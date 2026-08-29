<?php

$l['tab_sub_menu_plugin_name'] = '标签子菜单';
$l['tab_sub_menu_plugin_description'] = '将论坛分类整理为可配置的标签页，以便更轻松地浏览。';

$l['tab_sub_menu_setting_group_title'] = '标签子菜单';
$l['tab_sub_menu_setting_group_description'] = '论坛分类标签菜单的设置。';
$l['tab_sub_menu_setting_groups_title'] = '论坛分类菜单组';
$l['tab_sub_menu_setting_groups_description'] = '为每个菜单标签添加一行。标签 ID 是唯一的内部名称；显示名称是访客看到的名称；论坛/分类 ID 是该标签显示的内容。';
$l['tab_sub_menu_setting_hide_empty_title'] = '隐藏空标签';
$l['tab_sub_menu_setting_hide_empty_description'] = '隐藏其配置分类未出现在当前访客所见论坛首页中的标签。“显示全部”标签仍然可用。';
$l['tab_sub_menu_setting_default_tab_title'] = '默认标签';
$l['tab_sub_menu_setting_default_tab_description'] = '没有有效的已记忆选择时所选的标签。仅提供已启用的配置标签。';
$l['tab_sub_menu_setting_remember_title'] = '记住访客选择';
$l['tab_sub_menu_setting_remember_description'] = '在当前安装专用的浏览器存储中记住每位访客选择的标签。';
$l['tab_sub_menu_setting_url_state_title'] = '启用可分享的标签 URL';
$l['tab_sub_menu_setting_url_state_description'] = '将当前标签保存在 URL 的 tsm_tab 参数中，以便分享链接，并让浏览器的后退/前进操作跟随标签变化。';
$l['tab_sub_menu_setting_custom_css_title'] = '自定义菜单 CSS';
$l['tab_sub_menu_setting_custom_css_description'] = '在插件维护的样式表之后加载的可选 CSS。常用选择器：#forum-tab-sub-menu、.tab-sub-menu li、.tab-sub-menu li.active 和 .tab-sub-menu li:hover:not(.active)。';

$l['tab_sub_menu_editor_help'] = '请使用简短且唯一的标签 ID，例如 <code>gaming</code>。论坛/分类 ID 请用逗号分隔，例如 <code>2,3,4</code>。ID 留空可创建“显示全部”标签。';
$l['tab_sub_menu_editor_tab_id'] = '标签 ID（内部）';
$l['tab_sub_menu_editor_tab_id_example'] = 'gaming';
$l['tab_sub_menu_editor_display_name'] = '显示名称';
$l['tab_sub_menu_editor_display_name_example'] = '游戏';
$l['tab_sub_menu_editor_forum_ids'] = '论坛/分类 ID';
$l['tab_sub_menu_editor_forum_ids_example'] = '2,3,4';
$l['tab_sub_menu_editor_enabled'] = '启用';
$l['tab_sub_menu_editor_add_tab'] = '添加标签';
$l['tab_sub_menu_editor_remove'] = '删除';
$l['tab_sub_menu_editor_unnamed_tab'] = '未命名标签';
$l['tab_sub_menu_editor_missing'] = '未分配：{1}';
$l['tab_sub_menu_editor_all_assigned'] = '所有顶级分类均已分配。';
$l['tab_sub_menu_editor_categories_unavailable'] = '分类列表不可用；请手动输入 ID。';
$l['tab_sub_menu_editor_select'] = '选择';
$l['tab_sub_menu_editor_used_by'] = '已由 {1} 使用';
$l['tab_sub_menu_editor_missing_marker'] = '未分配';
$l['tab_sub_menu_editor_category_prompt'] = "顶级分类：\n\n{1}\n\n输入要添加的 ID：";
$l['tab_sub_menu_editor_view_css'] = '查看维护的默认 CSS';
$l['tab_sub_menu_editor_css_warning'] = '自定义 CSS 会在此维护样式表之后加载。仅当你需要完全可编辑的起点时才复制；复制的规则可能覆盖插件未来的样式更新。';
$l['tab_sub_menu_editor_copy_css'] = '将默认值复制到自定义菜单 CSS';
$l['tab_sub_menu_editor_replace_css'] = '要用维护的默认值替换现有的自定义菜单 CSS 吗？';

$l['tab_sub_menu_upgrade_stylesheet_error'] = '无法加载 MyBB 主题函数或同步插件样式表。';
$l['tab_sub_menu_upgrade_template_error'] = '无法同步插件模板变量。';
$l['tab_sub_menu_upgrade_error'] = 'Tab Sub Menu 无法升级到版本 %1$s：%2$s 升级仍处于待处理状态，并将在下一次管理后台请求时重试。';
$l['tab_sub_menu_default_home'] = '首页';
