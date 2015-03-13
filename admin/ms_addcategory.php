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
 * @version $Id: ms_addcategory.php,v 1.1 2006/12/18 04:37:10 garrett Exp $
 */

if ( !defined('EQDKP_INC') )
{
    die('Hacking attempt');
}

/**
 * This class handles the addition, update, and deletion of categories
 * @subpackage ManageCategories
 */
class MS_AddCategory extends EQdkp_Admin
{
    var $category     = array();          // Holds category data if URI_NAME is set             @var category
    var $old_category = array();          // Holds category data from before POST               @var old_category

    function MS_AddCategory()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->category = array(
            'category_id'               => 0,
            'category_name'             => post_or_db('category_name'),
            'category_display'          => ( post_or_db('category_display') == "Y" ) ? "Y" : "N",
            'category_display_order'    => post_or_db('category_display_order')
        );

        // Vars used to confirm deletion
        $confirm_text = $user->lang['confirm_delete_categories'];
        $category_names = array();
        if ( isset($_POST['delete']) )
        {
            if ( isset($_POST['compare_ids']) )
            {
                foreach ( $_POST['compare_ids'] as $id )
                {
                    $category_name = $db->query_first('SELECT category_name FROM ' . SP_CATEGORIES_TABLE . " WHERE category_id=" . $id);
                    $category_names[] = $category_name;
                }

                $names = implode(', ', $category_names);

                $confirm_text .= '<br /><br />' . $names;
            }
            else
            {
                message_die($user->lang['no_category_selected_for_deletion']);
            }
        }

        $this->set_vars(array(
            'confirm_text'  => $confirm_text,
            'uri_parameter' => URI_ID,
            'url_id'        => ( sizeof($_POST['compare_ids']) > 0 ) ? implode(",",$_POST['compare_ids']) : (( isset($_GET[URI_ID]) ) ? $_GET[URI_ID] : ''),
            'script_name'   => 'manage_sets.php' . $SID . '&amp;mode=addcategory')
        );

        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_members_man'),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_members_man'),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_members_man'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man'))
        );

        // Build the category array
        // ---------------------------------------------------------
        if ( !empty($this->url_id) )
        {
            $sql = "SELECT *
                      FROM " . SP_CATEGORIES_TABLE . "
                     WHERE category_id='" . $this->url_id."'";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            $db->free_result($result);

            $this->category = array(
                'category_id'               => $row['category_id'],
                'category_name'             => post_or_db('category_name', $row),
                'category_display'          => (( post_or_db('category_display',$row) == "Y" ) ? "Y" : "N" ),
                'category_display_order'    => post_or_db('category_display_order', $row),
            );
        }
    }

    function error_check()
    {
        global $user, $SID, $db;

        if ( (isset($_POST['add'])) || (isset($_POST['update'])) )
        {
            $this->fv->is_filled('category_name', $user->lang['fv_required_name']);

            if (empty($_POST['category_display_order'])) {
                $_POST['category_display_order'] = $db->query_first('SELECT MAX(category_display_order) FROM ' . SP_CATEGORIES_TABLE) + 1;
            }
            $this->fv->is_number('category_display_order', $user->lang['fv_number']);
        }

        return $this->fv->is_error();
    }

    // ---------------------------------------------------------
    // Process Add
    // ---------------------------------------------------------
    function process_add()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        /**
         * Clean the input data
         */
        // Make sure that each category name is properly capitalized
        $category_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['category_name']));
        $category_name = ucwords($category_name);

        $category_display = ( $_POST['category_display'] == "Y" ) ? "Y" : "N";

        // Check for existing member name
        $sql = "SELECT category_id FROM " . SP_CATEGORIES_TABLE ." WHERE category_name = '".$category_name."'";
        $category_id = $db->query_first($sql);

        // Error out if category name exists
        if ( isset($category_id) && $category_id > 0 ) {

            $failure_message = sprintf($user->lang['duplicate_category'], $category_name, $category_id);

            $link_list = array(
                $user->lang['adminmenu_add_category']       => 'manage_sets.php' . $SID . '&amp;mode=addcategory',
                $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories');

            message_die($failure_message, $link_list);

        }

        $query = $db->build_query('INSERT', array(
            'category_id'               => '',
            'category_name'             => $category_name,
            'category_display'          => $category_display,
            'category_display_order'    => $_POST['category_display_order'],
        ));
        $db->query('INSERT INTO ' . SP_CATEGORIES_TABLE . $query);

        //
        // Logging
        //
        $log_action = array(
            'header'            => '{L_ACTION_CATEGORY_ADDED}',
            '{L_NAME}'          => $category_name,
            '{L_DISPLAY}'       => $category_display,
            '{L_DISPLAY_ORDER}' => $_POST['category_display_order'],
            '{L_ADDED_BY}'      => $this->admin_user);

        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_add_category_success'], $category_name);
        $link_list = array(
            $user->lang['adminmenu_add_category']       => 'manage_sets.php' . $SID . '&amp;mode=addcategory',
            $user->lang['adminmenu_add_set']          	=> 'manage_sets.php' . $SID . '&amp;mode=addset',
            $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories');
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        //
        // Get old category data
        //
        $this->get_old_data($_POST[URI_ID]);
        $category_id = $this->old_category['category_id'];
        $old_category_name = $this->old_category['category_name'];

        /**
         * Clean the input data
         */
        // Make sure that each category name is properly capitalized
        $category_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['category_name']));
        $category_name = ucwords($category_name);

        $category_display = ( $_POST['category_display'] == "Y" ) ? "Y" : "N";

        //
        // Update the category
        //
        $query = $db->build_query('UPDATE', array(
            'category_name'             => $category_name,
            'category_display'          => $category_display,
            'category_display_order'    => $_POST['category_display_order'],
        ));
        $db->query('UPDATE ' . SP_CATEGORIES_TABLE . ' SET ' . $query . " WHERE category_id=" . $category_id);

        //
        // Logging
        //
        $log_action = array(
            'header'                    => '{L_ACTION_CATEGORY_UPDATED}',
            '{L_NAME_BEFORE}'           => $this->old_category['category_name'],
            '{L_DISPLAY_BEFORE}'        => $this->old_category['category_display'],
            '{L_DISPLAY_ORDER_BEFORE}'  => $this->old_category['category_display_order'],

            '{L_NAME_AFTER}'            => $this->find_difference($this->old_category['category_name'], $category_name),
            '{L_DISPLAY_AFTER}'         => $this->find_difference($this->old_category['category_display'], $category_display),
            '{L_DISPLAY_ORDER_AFTER}'   => $this->find_difference($this->old_category['category_display_order'], $_POST['category_display_order']),

            '{L_UPDATED_BY}'            => $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_category_success'], $this->old_category['category_name']);
        $link_list = array(
            $user->lang['adminmenu_add_set']          	=> 'manage_sets.php' . $SID . '&amp;mode=addset',
            $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories');
        $this->admin_die($success_message, $link_list);
    }

    /**
     *  Process delete (confirmed)
     */
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $success_message = '';
        $categories = explode(',', $_POST[URI_ID]);
        foreach ( $categories as $category_id )
        {
            if ( empty($category_id) )
            {
                continue;
            }

            //
            // Get old member data
            //
            $this->get_old_data($category_id);

            /**
             * Delete all sets & the pieces in each set.
             *
             * With only the category_id at this point we must get the impacted set ids
             */
            $set_results = $db->query("SELECT set_id FROM " . SP_SETS_TABLE ." WHERE set_category_id = ".$category_id);
            $sets_to_delete = array();
            while ($set = $db->fetch_record($set_results)) {
                $sets_to_delete[] = $set['set_id'];
            }
            $db->free_result($set_results);

            if (!empty($sets_to_delete)) {

                /**
                 * Delete set pieces first
                 */
                $sql = 'DELETE FROM ' . SP_SETPIECES_TABLE . "
                        WHERE set_piece_set_id IN (" . implode(",",$sets_to_delete). ")";
                $db->query($sql);

                /**
                 * Delete sets
                 */
                $sql = 'DELETE FROM ' . SP_SETS_TABLE . "
                        WHERE set_category_id=" . $category_id;
                $db->query($sql);
            }

            //
            // Delete the category
            //
            $sql = 'DELETE FROM ' . SP_CATEGORIES_TABLE . "
                    WHERE category_id=" . $category_id;
            $db->query($sql);

            //
            // Logging
            //
            $log_action = array(
                'header'            => '{L_ACTION_CATEGORY_DELETED}',
                '{L_NAME}'          => $this->old_category['category_name'],
                '{L_DISPLAY}'       => $this->old_category['category_display'],
                '{L_DISPLAY_ORDER}' => $this->old_category['category_display_order']);
            $this->log_insert(array(
                'log_type'   => $log_action['header'],
                'log_action' => $log_action)
            );

            //
            // Append success message
            //
            $success_message .= sprintf($user->lang['admin_delete_category_success'], $this->old_category['category_name']) . '<br />';
        }

        //
        // Success message
        //
        $this->admin_die($success_message);
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function get_old_data($category_id)
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        $sql = "SELECT *
                FROM " . SP_CATEGORIES_TABLE . "
                WHERE category_id=" . $category_id;
        $result = $db->query($sql);
        while ( $row = $db->fetch_record($result) )
        {
            $this->old_category = array(
                'category_id'               => $row['category_id'],
                'category_name'             => addslashes($row['category_name']),
                'category_display'          => $row['category_display'],
                'category_display_order'    => $row['category_display_order']
                );
        }
        $db->free_result($result);
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        /**
         * Get any sets associated with this category
         */
        if (isset($this->category['category_id'])) {

            $sql = "SELECT set_id, set_name,
                           class_name
                      FROM ".SP_SETS_TABLE.",
                           ".CLASS_TABLE."
                     WHERE set_category_id=".$this->category['category_id']."
                       AND set_class_id = class_id
                  ORDER BY set_name";
            $sets_result = $db->query($sql);

            $set_count=0;
            while ($set = $db->fetch_record($sets_result)) {
                $set_count++;
                $tpl->assign_block_vars('sets_row', array(
                    'ROW_CLASS' => $eqdkp->switch_row_class(),
                    'COUNT'     => $set_count,

                    'ID'         => $set['set_id'],
                    'NAME'       => $set['set_name'],
                    'CLASS'      => $set['class_name'],
                    'U_VIEW_SET' => 'manage_sets.php'.$SID . '&amp;mode=addset&amp;' . URI_ID . '='.$set['set_id']
                ));
            }
            $footcount_text = sprintf($user->lang['listsets_footcount'], $set_count);

        }

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_CATEGORY' => 'manage_sets.php' . $SID . '&amp;mode=addcategory',
			'F_SETS' 		=> 'manage_sets.php' . $SID . '&amp;mode=addset',

            // Form values
            'CATEGORY_ID'               => $this->category['category_id'],
            'V_CATEGORY_ID'             => ( isset($_POST['add']) ) ? '' : $this->category['category_id'],
            'CATEGORY_NAME'             => $this->category['category_name'],
            'CATEGORY_DISPLAY'          => ( $this->category['category_display'] == 'Y') ? 'checked="checked"' : "",
            'CATEGORY_DISPLAY_ORDER'    => $this->category['category_display_order'],
            'URI_ID'                    => URI_ID,

            // Language
            'L_NAME'            => $user->lang['name'],
            'L_CLASS'           => $user->lang['class'],
            'L_DISPLAY'         => $user->lang['display'],
            'L_DISPLAY_ORDER'   => $user->lang['display_order'],
            'L_EXISTING_SETS'   => $user->lang['existing_sets'],

            'L_RESET'           => $user->lang['reset'],
            'L_ADD_CATEGORY'    => $user->lang['title_add_category'],
            'L_ADD_SET'      	=> $user->lang['title_add_set'],
            'L_UPDATE_CATEGORY' => $user->lang['update_category'],
            'L_DELETE_CATEGORY' => $user->lang['delete_category'],
            'L_DELETE_SET'      => $user->lang['delete_set'],

            // Form validation
            'FV_CATEGORY_NAME'  => $this->fv->generate_error('category_name'),
            'FV_DISPLAY_ORDER'  => $this->fv->generate_error('category_display_order'),

            // Javascript messages
            'MSG_NAME_EMPTY'    => $user->lang['fv_required_name'],

            // Buttons
            'S_HAS_SETS' => ($set_count > 0 ? true : false),
            'S_ADD' => ( !empty($this->url_id) ) ? false : true,
			
			'LISTSETS_FOOTCOUNT' => $footcount_text			
        ));

        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'].": ".$user->lang['title_add_category'],
            'template_path' => $pm->get_data('setprogress', 'template_path'),
            'template_file' => 'admin/ms_addcategory.html',
            'display'       => true)
        );
    }
}
?>
