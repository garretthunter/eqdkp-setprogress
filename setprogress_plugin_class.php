<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Rev$ $Date$
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    die('You cannot access this file directly.');
}

// We have to define the table names here since EQDKP calls this page from every other page. This is also done in the config.php file for the normal plugin processing.
include_once ("table_defs.php");

class setprogress_Plugin_Class extends EQdkp_Plugin
{
    /**
     * @var $_permIndex starting permissions index. permissions indicies are incremented by 1
     * @access private
     */
    var $_permIndex = 700;

    function setprogress_Plugin_Class($pm)
    {
        global $eqdkp_root_path, $user, $SID;

        $this->eqdkp_plugin($pm);
        $this->pm->get_language_pack('setprogress');

        $this->add_data(array(
            'name'          => 'Set Progress',
            'code'          => 'setprogress',
            'path'          => 'setprogress',
            'contact'       => 'loganfive@blacktower.com',
            'template_path' => 'plugins/setprogress/templates/',
            'version'       => '2.0.4')
        );

        // Register our permissions
// @todo implment premissions
//        $this->add_permission($this->_permIndex,   'a_edit_categories', 'N', $user->lang['perm_edit_categories']);
//        $this->add_permission($this->_permIndex+2, 'a_edit_sets',       'N', $user->lang['perm_edit_sets']);

        /**
         * Register our menu
         */
        $this->add_menu('main_menu1', $this->gen_main_menu1());
        $this->add_menu('main_menu2', array());
        $this->add_menu('admin_menu', $this->gen_admin_menu());

        /**
         * Register our log events for:
         *  - categories
         *  - sets
         *  - set pieces
         */
        $this->add_log_action('{L_ACTION_CATEGORY_ADDED}',   $user->lang['action_category_added']);
        $this->add_log_action('{L_ACTION_CATEGORY_DELETED}', $user->lang['action_category_deleted']);
        $this->add_log_action('{L_ACTION_CATEGORY_UPDATED}', $user->lang['action_category_updated']);
        $this->add_log_action('{L_ACTION_SET_ADDED}',   $user->lang['action_set_added']);
        $this->add_log_action('{L_ACTION_SET_DELETED}', $user->lang['action_set_deleted']);
        $this->add_log_action('{L_ACTION_SET_UPDATED}', $user->lang['action_set_updated']);
        $this->add_log_action('{L_ACTION_SET_PIECE_ADDED}',   $user->lang['action_set_piece_added']);
        $this->add_log_action('{L_ACTION_SET_PIECE_DELETED}', $user->lang['action_set_piece_deleted']);
        $this->add_log_action('{L_ACTION_SET_PIECE_UPDATED}', $user->lang['action_set_piece_updated']);

        /**
         * SQL instructions to execute upon installation
         */
        $this->add_sql(SQL_INSTALL,"CREATE TABLE IF NOT EXISTS `".SP_SETPIECES_TABLE."` (
                                      `set_piece_id` mediumint(9) NOT NULL auto_increment,
                                      `set_piece_name` varchar(50) NOT NULL default '',
                                      `set_piece_requirement` varchar(50) NOT NULL default '',
                                      `set_piece_set_id` smallint(3) NOT NULL default '0',
                                      PRIMARY KEY  (`set_piece_id`));");

        $this->add_sql(SQL_INSTALL,"CREATE TABLE IF NOT EXISTS `".SP_SETS_TABLE."` (
                                      `set_id` mediumint(9) NOT NULL auto_increment,
                                      `set_name` varchar(50) NOT NULL default '',
                                      `set_category_id` smallint(3) NOT NULL default '0',
                                      `set_class_id` smallint(3) NOT NULL default '0',
                                      PRIMARY KEY  (`set_id`));");

        $this->add_sql(SQL_INSTALL,"CREATE TABLE IF NOT EXISTS `".SP_CATEGORIES_TABLE."` (
                                      `category_id` smallint(3) NOT NULL auto_increment,
                                      `category_name` varchar(50) NOT NULL default '',
                                      `category_display` enum('Y','N') NOT NULL default 'Y',
                                      `category_display_order` smallint(3) NOT NULL default '0',
                                      PRIMARY KEY  (`category_id`));");

        /**
         * SQL instructions to execute upon uninstallation
         */
        $this->add_sql(SQL_UNINSTALL, "DROP TABLE IF EXISTS`".SP_CATEGORIES_TABLE.";");
        $this->add_sql(SQL_UNINSTALL, "DROP TABLE IF EXISTS`".SP_SETS_TABLE.";");
        $this->add_sql(SQL_UNINSTALL, "DROP TABLE IF EXISTS`".SP_SETPIECES_TABLE.";");
    }

    function gen_main_menu1()
    {
        if ( $this->pm->check(PLUGIN_INSTALLED, 'setprogress') )
        {
            global $db, $user;

            $main_menu1 = array(
                array('link' => "plugins/" . $this->get_data('path') . '/' . $SID,
                      'text' => $user->lang['usermenu_setprogress'],
                      'check' => 'u_item_list')
            );

            return $main_menu1;
        }
        return;
    }

    function gen_admin_menu()
    {
        global $db, $user, $SID, $eqdkp;

        if ( $this->pm->check(PLUGIN_INSTALLED, 'setprogress') )
        {
            $url_prefix = ( EQDKP_VERSION < '1.3.2' ) ? $eqdkp_root_path : '';

            $admin_menu = array(
                    'setprogress' => array(
                    0 => $user->lang['adminmenu_setprogress'],
                    1 => array('link' => $url_prefix . 'plugins/' . $this->get_data('path') . '/manage_sets.php' . $SID . "&amp;mode=addcategory",
                               'text' => $user->lang['adminmenu_add_category'],
                               'check' => 'a_raid_add'),
                    2 => array('link' => $url_prefix . 'plugins/' . $this->get_data('path') . '/manage_sets.php' . $SID . "&amp;mode=addset",
                               'text' => $user->lang['adminmenu_add_set'],
                               'check' => 'a_raid_'),
                    3 => array('link' => $url_prefix . 'plugins/' . $this->get_data('path') . '/manage_sets.php' . $SID . "&amp;mode=listcategories",
                               'text' => $user->lang['adminmenu_list'],
                               'check' => 'a_raid_'),
/* @todo
                    4 => array('link' => '../plugins/' . $this->get_data('path') . '/import.php' . $SID, 'text' => $user->lang['adminmenu_import'],  'check' => 'a_raid_'),
*/
                )
             );

            return $admin_menu;
        }
        return;
    }

    /**
     * @var $page URI which caused the hook to be called
     */
    function do_hook($page) {
    }
}
?>