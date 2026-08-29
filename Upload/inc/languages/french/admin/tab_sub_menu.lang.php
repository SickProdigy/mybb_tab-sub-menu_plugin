<?php

$l['tab_sub_menu_plugin_name'] = 'Sous-menu à onglets';
$l['tab_sub_menu_plugin_description'] = 'Organise les catégories du forum en onglets configurables pour faciliter la navigation.';

$l['tab_sub_menu_setting_group_title'] = 'Sous-menu à onglets';
$l['tab_sub_menu_setting_group_description'] = 'Paramètres du menu à onglets des catégories du forum.';
$l['tab_sub_menu_setting_groups_title'] = 'Groupes du menu des catégories du forum';
$l['tab_sub_menu_setting_groups_description'] = 'Ajoutez une ligne pour chaque onglet du menu. L’ID de l’onglet est un nom interne unique ; le nom affiché est celui que voient les visiteurs ; les ID de forum/catégorie déterminent les éléments affichés par cet onglet.';
$l['tab_sub_menu_setting_hide_empty_title'] = 'Masquer les onglets vides';
$l['tab_sub_menu_setting_hide_empty_description'] = 'Masque les onglets dont les catégories configurées ne figurent pas dans l’index du forum affiché pour le visiteur actuel. Les onglets « tout afficher » restent disponibles.';
$l['tab_sub_menu_setting_default_tab_title'] = 'Onglet par défaut';
$l['tab_sub_menu_setting_default_tab_description'] = 'Onglet sélectionné lorsqu’aucune sélection mémorisée valide n’est disponible. Seuls les onglets configurés et activés sont proposés.';
$l['tab_sub_menu_setting_remember_title'] = 'Mémoriser la sélection du visiteur';
$l['tab_sub_menu_setting_remember_description'] = 'Mémorise l’onglet sélectionné par chaque visiteur dans le stockage du navigateur propre à cette installation.';
$l['tab_sub_menu_setting_url_state_title'] = 'Activer les URL d’onglet partageables';
$l['tab_sub_menu_setting_url_state_description'] = 'Enregistre l’onglet actif dans le paramètre d’URL tsm_tab afin que les liens puissent être partagés et que la navigation Précédent/Suivant du navigateur suive les changements d’onglet.';
$l['tab_sub_menu_setting_custom_css_title'] = 'CSS personnalisé du menu';
$l['tab_sub_menu_setting_custom_css_description'] = 'CSS facultatif ajouté après la feuille de style maintenue par le plugin. Sélecteurs utiles : #forum-tab-sub-menu, .tab-sub-menu li, .tab-sub-menu li.active et .tab-sub-menu li:hover:not(.active).';

$l['tab_sub_menu_editor_help'] = 'Utilisez un ID d’onglet court et unique, comme <code>gaming</code>. Saisissez les ID de forum/catégorie séparés par des virgules, comme <code>2,3,4</code>. Laissez les ID vides pour créer un onglet « tout afficher ».';
$l['tab_sub_menu_editor_tab_id'] = 'ID de l’onglet (interne)';
$l['tab_sub_menu_editor_tab_id_example'] = 'jeux';
$l['tab_sub_menu_editor_display_name'] = 'Nom affiché';
$l['tab_sub_menu_editor_display_name_example'] = 'Jeux';
$l['tab_sub_menu_editor_forum_ids'] = 'ID de forum/catégorie';
$l['tab_sub_menu_editor_forum_ids_example'] = '2,3,4';
$l['tab_sub_menu_editor_enabled'] = 'Activé';
$l['tab_sub_menu_editor_add_tab'] = 'Ajouter un onglet';
$l['tab_sub_menu_editor_remove'] = 'Supprimer';
$l['tab_sub_menu_editor_unnamed_tab'] = 'Onglet sans nom';
$l['tab_sub_menu_editor_missing'] = 'Manquantes : {1}';
$l['tab_sub_menu_editor_all_assigned'] = 'Toutes les catégories de premier niveau sont attribuées.';
$l['tab_sub_menu_editor_categories_unavailable'] = 'La liste des catégories est indisponible ; saisissez les ID manuellement.';
$l['tab_sub_menu_editor_select'] = 'Sélectionner';
$l['tab_sub_menu_editor_used_by'] = 'utilisée par {1}';
$l['tab_sub_menu_editor_missing_marker'] = 'non attribuée';
$l['tab_sub_menu_editor_category_prompt'] = "Catégories de premier niveau :\n\n{1}\n\nSaisissez les ID à ajouter :";
$l['tab_sub_menu_editor_view_css'] = 'Afficher le CSS par défaut maintenu';
$l['tab_sub_menu_editor_css_warning'] = 'Le CSS personnalisé est chargé après cette feuille de style maintenue. Ne la copiez que si vous souhaitez un point de départ entièrement modifiable ; les règles copiées peuvent remplacer les futures mises à jour de style du plugin.';
$l['tab_sub_menu_editor_copy_css'] = 'Copier les valeurs par défaut dans le CSS personnalisé du menu';
$l['tab_sub_menu_editor_replace_css'] = 'Remplacer votre CSS personnalisé du menu existant par les valeurs par défaut maintenues ?';

$l['tab_sub_menu_upgrade_stylesheet_error'] = 'Impossible de charger les fonctions de thème de MyBB ou de synchroniser les feuilles de style du plugin.';
$l['tab_sub_menu_upgrade_template_error'] = 'Impossible de synchroniser les variables de modèle du plugin.';
$l['tab_sub_menu_upgrade_error'] = 'Tab Sub Menu n’a pas pu être mis à niveau vers la version %1$s : %2$s La mise à niveau reste en attente et sera réessayée lors de la prochaine requête dans le panneau d’administration.';
$l['tab_sub_menu_default_home'] = 'Accueil';
