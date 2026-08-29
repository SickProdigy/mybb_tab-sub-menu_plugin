<?php

$l['tab_sub_menu_plugin_name'] = 'Submenú de pestañas';
$l['tab_sub_menu_plugin_description'] = 'Organiza las categorías del foro en pestañas configurables para facilitar la navegación.';

$l['tab_sub_menu_setting_group_title'] = 'Submenú de pestañas';
$l['tab_sub_menu_setting_group_description'] = 'Configuración del menú de pestañas de categorías del foro.';
$l['tab_sub_menu_setting_groups_title'] = 'Grupos del menú de categorías del foro';
$l['tab_sub_menu_setting_groups_description'] = 'Añade una fila por cada pestaña del menú. El ID de pestaña es un nombre interno único; el nombre visible es lo que ven los visitantes; los ID de foro/categoría son los que muestra esa pestaña.';
$l['tab_sub_menu_setting_hide_empty_title'] = 'Ocultar pestañas vacías';
$l['tab_sub_menu_setting_hide_empty_description'] = 'Oculta las pestañas cuyas categorías configuradas no aparecen en el índice del foro mostrado al visitante actual. Las pestañas que muestran todo permanecen disponibles.';
$l['tab_sub_menu_setting_default_tab_title'] = 'Pestaña predeterminada';
$l['tab_sub_menu_setting_default_tab_description'] = 'Pestaña seleccionada cuando no hay una selección recordada válida. Solo se ofrecen las pestañas configuradas y habilitadas.';
$l['tab_sub_menu_setting_remember_title'] = 'Recordar la selección del visitante';
$l['tab_sub_menu_setting_remember_description'] = 'Recuerda la pestaña seleccionada por cada visitante en el almacenamiento del navegador específico de esta instalación.';
$l['tab_sub_menu_setting_url_state_title'] = 'Habilitar URL de pestañas compartibles';
$l['tab_sub_menu_setting_url_state_description'] = 'Guarda la pestaña activa en el parámetro tsm_tab de la URL para poder compartir enlaces y seguir los cambios de pestaña con Atrás/Adelante del navegador.';
$l['tab_sub_menu_setting_custom_css_title'] = 'CSS personalizado del menú';
$l['tab_sub_menu_setting_custom_css_description'] = 'CSS opcional añadido después de la hoja de estilos mantenida por el plugin. Selectores útiles: #forum-tab-sub-menu, .tab-sub-menu li, .tab-sub-menu li.active y .tab-sub-menu li:hover:not(.active).';

$l['tab_sub_menu_editor_help'] = 'Usa un ID de pestaña corto y único, como <code>gaming</code>. Introduce los ID de foro/categoría separados por comas, como <code>2,3,4</code>. Deja los ID vacíos para crear una pestaña que “muestre todo”.';
$l['tab_sub_menu_editor_tab_id'] = 'ID de pestaña (interno)';
$l['tab_sub_menu_editor_tab_id_example'] = 'juegos';
$l['tab_sub_menu_editor_display_name'] = 'Nombre visible';
$l['tab_sub_menu_editor_display_name_example'] = 'Juegos';
$l['tab_sub_menu_editor_forum_ids'] = 'ID de foro/categoría';
$l['tab_sub_menu_editor_forum_ids_example'] = '2,3,4';
$l['tab_sub_menu_editor_enabled'] = 'Habilitada';
$l['tab_sub_menu_editor_add_tab'] = 'Añadir pestaña';
$l['tab_sub_menu_editor_remove'] = 'Eliminar';
$l['tab_sub_menu_editor_unnamed_tab'] = 'Pestaña sin nombre';
$l['tab_sub_menu_editor_missing'] = 'Faltan: {1}';
$l['tab_sub_menu_editor_all_assigned'] = 'Todas las categorías de nivel superior están asignadas.';
$l['tab_sub_menu_editor_categories_unavailable'] = 'La lista de categorías no está disponible; introduce los ID manualmente.';
$l['tab_sub_menu_editor_select'] = 'Seleccionar';
$l['tab_sub_menu_editor_used_by'] = 'usada por {1}';
$l['tab_sub_menu_editor_missing_marker'] = 'sin asignar';
$l['tab_sub_menu_editor_category_prompt'] = "Categorías de nivel superior:\n\n{1}\n\nIntroduce los ID que quieras añadir:";
$l['tab_sub_menu_editor_view_css'] = 'Ver el CSS predeterminado mantenido';
$l['tab_sub_menu_editor_css_warning'] = 'El CSS personalizado se carga después de esta hoja de estilos mantenida. Cópialo solo si quieres un punto de partida completamente editable; las reglas copiadas pueden sobrescribir futuras actualizaciones de estilo del plugin.';
$l['tab_sub_menu_editor_copy_css'] = 'Copiar valores predeterminados al CSS personalizado del menú';
$l['tab_sub_menu_editor_replace_css'] = '¿Quieres reemplazar el CSS personalizado del menú existente por los valores predeterminados mantenidos?';

$l['tab_sub_menu_upgrade_stylesheet_error'] = 'No se pudieron cargar las funciones de temas de MyBB o sincronizar las hojas de estilos del plugin.';
$l['tab_sub_menu_upgrade_template_error'] = 'No se pudieron sincronizar las variables de plantilla del plugin.';
$l['tab_sub_menu_upgrade_error'] = 'Tab Sub Menu no pudo actualizarse a la versión %1$s: %2$s La actualización permanece pendiente y se volverá a intentar en la próxima solicitud del Panel de administración.';
$l['tab_sub_menu_default_home'] = 'Inicio';
