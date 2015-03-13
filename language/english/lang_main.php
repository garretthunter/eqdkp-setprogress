<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: lang_main.php,v 1.7 2006/12/18 04:37:10 garrett Exp $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
     die('Do not access this file directly.');
}

// Plugin name
$lang['setprogress'] = "setprogress";

// permissions
$lang['perm_edit_categories']   = "Edit Categories";
$lang['perm_edit_sets']         = "Edit Sets";

// Page Titles & Headings
$lang['title_setprogress']          = "Set Progress";
$lang['title_categories']           = "Categories";
$lang['title_sets']           		= "Sets";
$lang['title_add_category']         = "Add Category";
$lang['title_update_category']      = "Update Category";
$lang['title_list_sets']      		= "List Sets";
$lang['title_add_set']         		= "Add Set";
$lang['title_update_set']      		= "Update Set";
$lang['title_add_set_piece']   		= "Add Setpiece";
$lang['title_update_set_piece'] 	= "Update Setpiece";

// User Menus
$lang['usermenu_setprogress']   = "Set Progress";

// Admin Menus
$lang['adminmenu_list_edit_del_sets']  	= "List, Edit, or Delete Sets";
$lang['adminmenu_setprogress']          = "Set Progress";
$lang['adminmenu_manage_cats']          = "Manage Categories";
$lang['adminmenu_manage_sets']          = "Manage Sets";
$lang['adminmenu_import']               = "Import Sets";
$lang['adminmenu_export']               = "Export Sets";
$lang['adminmenu_add_category']         = "Add Category";
$lang['adminmenu_add_set']              = "Add Set";
$lang['adminmenu_add_set_piece']        = "Add Setpiece";
$lang['adminmenu_list']     			= "List Sets";

// Category Labels
$lang['delete_selected_categories']         = "Delete Selected Categories";
$lang['no_category_selected_for_deletion']  = "No categories were selected for deletion";
$lang['confirm_delete_categories']          = "Are you sure you want to delete the following categories and all sets associated with each?";
$lang['duplicate_category']                 = "Failed to add <strong>%1\$s</strong>; category exists as ID %2\$s";
$lang['display']                            = "Display";
$lang['display_order']                      = "Order";
$lang['existing_sets']                      = "Member Sets";
$lang['delete_category']                    = "Delete Category";
$lang['update_category']                    = "Update Category";
$lang['category']							= "Category";

// Set Labels
$lang['set_name']                         	= "Set Name";
$lang['listsets_footcount']                 = "... found %1\$d set(s)";
$lang['delete_set']                         = "Delete Set";
$lang['set_requirement']                    = "Required Drop";
$lang['no_set_selected_for_deletion']  		= "No sets were selected for deletion";
$lang['confirm_delete_sets']          		= "Are you sure you want to delete the following sets and all setpieces associated with each?";
$lang['duplicate_set']                 		= "Failed to add <strong>%1\$s</strong>; set exists as ID %2\$s";
$lang['set']								= "Set";
$lang['adminmenu_work_with_same_set']		= "Continue editing this set";

// Set Pieces Labels
$lang['listsetpieces_footcount']            = "... found %1\$d set piece(s)";
$lang['no_setpiece_selected_for_deletion']	= "No setpieces were selected for deletion";
$lang['duplicate_set_piece']                = "Failed to add <strong>%1\$s</strong>; category exists as ID %2\$s";
$lang['delete_set_piece']                   = "Delete Setpiece";
$lang['existing_setpieces']					= "Member Setpieces";
$lang['confirm_delete_setpiece']			= "Are you sure you want to delete the following setpieces?";

// Successs Messages
$lang['admin_add_category_success']         = "Category <strong>%1\$s</strong> has been added.";
$lang['admin_update_category_success']      = "Category <strong>%1\$s</strong> has been updated.";
$lang['admin_delete_category_success']      = "<strong>%1\$s</strong>, including all set and set pieces, has been deleted from the database for your guild.";
$lang['admin_add_set_success']         		= "Set <strong>%1\$s</strong> has been added.";
$lang['admin_update_set_success']      		= "Set <strong>%1\$s</strong> has been updated.";
$lang['admin_delete_set_success']      		= "<strong>%1\$s</strong>, including all set pieces, has been deleted from the database for your guild.";
$lang['admin_add_set_piece_success']         = "Setpiece <strong>%1\$s</strong> has been added.";
$lang['admin_update_set_piece_success']      = "Setpiece <strong>%1\$s</strong> has been updated.";
$lang['admin_delete_set_piece_success']      = "<strong>%1\$s</strong> has been deleted from the database for your guild.";

// Log Actions
$lang['action_category_added']    = "Set Category Added";
$lang['action_category_deleted']  = "Set Category Deleted";
$lang['action_category_updated']  = "Set Category Updated";
$lang['action_set_added']         = "Set Added";
$lang['action_set_deleted']       = "Set Deleted";
$lang['action_set_updated']       = "Set Updated";
$lang['action_set_piece_added']   = "Set Piece Added";
$lang['action_set_piece_deleted'] = "Set Piece Deleted";
$lang['action_set_piece_updated'] = "Set Piece Updated";

// Log Attributes
$lang['display_before']			= "Display Before";
$lang['display_after']			= "Display After";
$lang['display_order_before']	= "Order Before";
$lang['display_order_after']	= "Order After";
$lang['category_before']		= "Category Before";
$lang['category_after']			= "Category After";
$lang['requirement_before']		= "Requirement Before";
$lang['requirement_after']		= "Requirement After";
$lang['set_before']				= "Set Before";
$lang['set_after']				= "Set After";

// Log Messages
$lang['vlog_category_added']    = "%1\$s added the set category '%2\$s'.";
$lang['vlog_category_deleted']  = "%1\$s deleted the set category '%2\$s'.";
$lang['vlog_category_updated']  = "%1\$s updated the set category '%2\$s'.";
$lang['vlog_set_added']         = "%1\$s added the set '%2\$s'.";
$lang['vlog_set_deleted']       = "%1\$s deleted the set '%2\$s'.";
$lang['vlog_set_updated']       = "%1\$s updated the set '%2\$s'.";
$lang['vlog_set_piece_added']   = "%1\$s added the set piece '%2\$s'.";
$lang['vlog_set_piece_deleted'] = "%1\$s deleted the set piece '%2\$s'.";
$lang['vlog_set_piece_updated'] = "%1\$s updated the set piece '%2\$s'.";

// Misc Labels
$lang['smallitemicon']      = "smaller";
$lang['mediumitemicon']     = "default";
$lang['largeitemicon']      = "larger";
$lang['icon_sizes']         = 'Icon Size';
$lang['no_sets_found']		= "There are no sets to display";
$lang['requirement']        = "Required Drop";

?>