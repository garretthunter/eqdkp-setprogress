<?php
/**
 * Add, update, or delete a category entry
 *
 * @category SetProgress
 * @package AdminFunctions
 *
 * @author Garrett Hunter <loganfive@blacktower.com>
 * @copyright Copyright &copy; 2006, Garrett Hunter
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: manage_sets.php,v 1.1 2006/12/18 04:37:10 garrett Exp $
 */

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'setprogress');

$eqdkp_root_path = './../../';

include_once($eqdkp_root_path . 'common.php');
include_once('config.php');

$setprogress = $pm->get_plugin('setprogress');

if ( !$pm->check(PLUGIN_INSTALLED, 'setprogress') )
{
    message_die('The Set Progress plugin is not installed.');
}

$user->check_auth('u_member_list');

/**
 * Display a menu of category operations
 * @subpackage ManageSetProgress
 */
class ManageSetProgress_Admin extends EQdkp_Admin {

    function ManageSetProgress_Admin()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_menu',
                'check'   => 'a_members_man'))
        );

        $this->assoc_params(array(
            'addcategory' => array(
                'name'    => 'mode',
                'value'   => 'addcategory',
                'process' => 'ms_addcategory',
                'check'   => 'a_members_man'),
            'listcategories' => array(
                'name'    => 'mode',
                'value'   => 'listcategories',
                'process' => 'ms_listcategories',
                'check'   => 'a_members_man'),
            'addset' => array(
                'name'    => 'mode',
                'value'   => 'addset',
                'process' => 'ms_addset',
                'check'   => 'a_members_man'),
            'addsetpiece' => array(
                'name'    => 'mode',
                'value'   => 'addsetpiece',
                'process' => 'ms_addsetpiece',
                'check'   => 'a_members_man')
        ));
    }

    // ---------------------------------------------------------
    // Display menu
    // ---------------------------------------------------------
    function display_menu()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $menu = array(
                     array ($user->lang['adminmenu_add_category'],
                            'manage_sets.php' . $SID . '&amp;mode=addcategory'),
                     array ($user->lang['adminmenu_list_edit_del_categories'],
                            'manage_sets.php' . $SID . '&amp;mode=listcategories'),
                     array ($user->lang['adminmenu_add_set'],
                            'manage_sets.php' . $SID . '&amp;mode=addset'),
                     array ($user->lang['adminmenu_add_set_piece'],
                            'manage_sets.php' . $SID . '&amp;mode=addsetpiece')
                    );

        foreach ($menu as $entry) {
            $tpl->assign_block_vars('menu_row', array (
                'L_MENU_ENTRY' => $entry[0],
                'U_MENU_LINK'  => $entry[1]
            ));
        }

        $tpl->assign_vars(array(
            'L_MANAGE_SETS' => $user->lang['title_manage_sets'],

            'F_MANAGE_SETS' => 'manage_sets.php' . $SID,

        ));

        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'].": ".$user->lang['title_manage_sets'],
            'template_path' => $pm->get_data('setprogress', 'template_path'),
            'template_file' => 'admin/ms_menu.html',
            'display'       => true)
        );
    }

    function ms_addcategory () {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        include('admin/ms_addcategory.php');
        $mc_extension = new MS_AddCategory;
        $mc_extension->process();
    }

    function ms_listcategories () {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        include('admin/ms_listcategories.php');
        $mc_extension = new MS_ListCategories;
        $mc_extension->process();
    }

    function ms_addset () {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        include('admin/ms_addset.php');
        $mc_extension = new MS_AddSet;
        $mc_extension->process();
    }

    function ms_addsetpiece () {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        include('admin/ms_addsetpiece.php');
        $mc_extension = new MS_AddSetPiece;
        $mc_extension->process();
    }
}

$ManageSetProgress_Admin = new ManageSetProgress_Admin;
$ManageSetProgress_Admin->process();

?>