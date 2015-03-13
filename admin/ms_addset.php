<?php
/**
 * Add, update, or delete a set entry
 *
 * @set SetProgress
 * @package AdminFunctions
 *
 * @author Garrett Hunter <loganfive@blacktower.com>
 * @copyright Copyright &copy; 2006, Garrett Hunter
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: ms_addset.php,v 1.1 2006/12/18 04:37:10 garrett Exp $
 */

if ( !defined('EQDKP_INC') ) { die("haxzor"); }

/**
 * This class handles the addition, update, and deletion of sets
 * @subpackage ManageSets
 */
class MS_AddSet extends EQdkp_Admin
{
    var $set     = array();          // Holds set data if URI_NAME is set             @var set
    var $old_set = array();          // Holds set data from before POST               @var old_set

    function MS_AddSet()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->set = array(
            'set_id'                => 0,
            'set_name'              => post_or_db('set_name'),
            'set_category_id'       => post_or_db('set_category_id'),
            'set_class_id'          => post_or_db('set_class_id')
        );

        // Vars used to confirm deletion
        $confirm_text = $user->lang['confirm_delete_sets'];
        $set_names = array();
        if ( isset($_POST['delete']) )
        {
            if ( isset($_POST['compare_ids']) )
            {
                foreach ( $_POST['compare_ids'] as $id )
                {
                    $set_name = $db->query_first('SELECT set_name FROM ' . SP_SETS_TABLE . " WHERE set_id=" . $id);
                    $set_names[] = $set_name;
                }

                $names = implode(', ', $set_names);

                $confirm_text .= '<br /><br />' . $names;
            }
            else
            {
                message_die($user->lang['no_set_selected_for_deletion']);
            }
        }

        $this->set_vars(array(
            'confirm_text'  => $confirm_text,
            'uri_parameter' => URI_ID,
            'url_id'        => ( sizeof($_POST['compare_ids']) > 0 ) ? implode(",",$_POST['compare_ids']) : (( isset($_GET[URI_ID]) ) ? $_GET[URI_ID] : ''),
            'script_name'   => 'manage_sets.php' . $SID . '&amp;mode=addset')
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

        // Build the set array
        // ---------------------------------------------------------
        if ( !empty($this->url_id) )
        {
            $sql = "SELECT *
                      FROM " . SP_SETS_TABLE . "
                     WHERE set_id='" . $this->url_id."'";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            $db->free_result($result);

            $this->set = array(
                'set_id'            => $row['set_id'],
                'set_name'          => post_or_db('set_name', $row),
                'set_category_id'   => post_or_db('set_category_id',$row),
                'set_class_id'      => post_or_db('set_class_id',$row)
            );
        }
    }

    function error_check()
    {
        global $user, $SID, $db;

        if ( (isset($_POST['add'])) || (isset($_POST['update'])) )
        {
            $this->fv->is_filled('set_name', $user->lang['fv_required_name']);
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
        // Make sure that each set name is properly capitalized
        $set_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_name']));
        $set_name = ucwords($set_name);

        // Check for existing member name
        $sql = "SELECT set_id FROM " . SP_SETS_TABLE ." WHERE set_name = '".$set_name."'";
        $set_id = $db->query_first($sql);

        // Error out if set name exists
        if ( isset($set_id) && $set_id > 0 ) {

            $failure_message = sprintf($user->lang['duplicate_set'], $set_name, $set_id);

            $link_list = array(
                $user->lang['adminmenu_add_set']            => 'manage_sets.php' . $SID . '&amp;mode=addset',
                $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listsets');

            message_die($failure_message, $link_list);
        }

        $query = $db->build_query('INSERT', array(
            'set_id'            => '',
            'set_name'          => $set_name,
            'set_category_id'   => $_POST['set_category_id'],
            'set_class_id'      => $_POST['set_class_id'],
        ));
        $db->query('INSERT INTO ' . SP_SETS_TABLE . $query);

        /**
         * Logging
         */
        $new_set = $this->get_set_data($_POST[URI_ID]);
        $log_action = array(
            'header'         => '{L_ACTION_SET_ADDED}',
            '{L_NAME}'       => $set_name,
            '{L_CATEGORY}'   => $new_set['category_name'],
            '{L_CLASS}' 	 => $new_set['class_name'],
            '{L_ADDED_BY}'   => $this->admin_user);

        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_add_set_success'], $set_name);
        $link_list = array(
            $user->lang['adminmenu_add_set_piece'] => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece&amp;set_piece_set_id='.$new_set['set_id'],
            $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories'
			);
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
        // Get old set data
        //
        $this->old_set = $this->get_set_data($_POST[URI_ID]);
        $set_id = $this->old_set['set_id'];
        $old_set_name = $this->old_set['set_name'];

        /**
         * Clean the input data
         */
        // Make sure that each set name is properly capitalized
        $set_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_name']));
        $set_name = ucwords($set_name);

        $set_display = ( $_POST['set_display'] == "Y" ) ? "Y" : "N";

        //
        // Update the set
        //
        $query = $db->build_query('UPDATE', array(
            'set_name'          => $set_name,
            'set_category_id'   => $_POST['set_category_id'],
            'set_class_id' 		=> $_POST['set_class_id'],
        ));
        $db->query('UPDATE ' . SP_SETS_TABLE . ' SET ' . $query . " WHERE set_id=" . $set_id);

        /**
         * Get our current row & log the results of the update
         */
        $new_set = $this->get_set_data($set_id);
        $log_action = array(
            'header'                => '{L_ACTION_SET_UPDATED}',
            '{L_NAME_BEFORE}'       => $this->old_set['set_name'],
            '{L_CATEGORY_BEFORE}'   => $this->old_set['category_name'],
            '{L_CLASS_BEFORE}'  	=> $this->old_set['class_name'],

            '{L_NAME_AFTER}'        => $this->find_difference($this->old_set['set_name'], $set_name),
            '{L_CATEGORY_AFTER}'    => $this->find_difference($this->old_set['category_name'], $new_set['category_name']),
            '{L_CLASS_AFTER}'   	=> $this->find_difference($this->old_set['class_name'], $new_set['class_name']),

            '{L_UPDATED_BY}'            => $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_set_success'], $this->old_set['set_name']);
        $link_list = array(
            $user->lang['adminmenu_work_with_same_set'] => 'manage_sets.php' . $SID . '&amp;mode=addset&amp;id='.$this->old_set['set_id'],
            $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories'
			);
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
        $sets = explode(',', $_POST[URI_ID]);
        foreach ( $sets as $set_id )
        {
            if ( empty($set_id) )
            {
                continue;
            }

            //
            // Get old member data
            //
            $this->old_set = $this->get_set_data($set_id);

            /**
             * Delete all setpieces along with the set.
             */
			$sql = 'DELETE FROM ' . SP_SETPIECES_TABLE . "
					WHERE set_piece_set_id = ". $set_id;
			$db->query($sql);

            //
            // Delete the set
            //
            $sql = 'DELETE FROM ' . SP_SETS_TABLE . "
                    WHERE set_id=" . $set_id;
            $db->query($sql);

            //
            // Logging
            //
            $log_action = array(
                'header'        => '{L_ACTION_SET_DELETED}',
                '{L_NAME}'      => $this->old_set['set_name'],
                '{L_CATEGORY}'  => $this->old_set['category_name'],
                '{L_CLASS}' 	=> $this->old_set['class_name']);
            $this->log_insert(array(
                'log_type'   => $log_action['header'],
                'log_action' => $log_action)
            );

            //
            // Append success message
            //
            $success_message .= sprintf($user->lang['admin_delete_set_success'], $this->old_set['set_name']) . '<br />';
        }

        //
        // Success message
        //
        $this->admin_die($success_message);
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    function get_set_data($set_id)
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

		$set_data = array();
		
        $sql = "SELECT sets.*, category_name, class_name
                FROM " . SP_SETS_TABLE . " sets,
				     " . SP_CATEGORIES_TABLE .",
				     " . CLASS_TABLE ."
                WHERE set_category_id = category_id
				  AND set_class_id = class_id
				  AND set_id=" . $set_id;
        $result = $db->query($sql);
		
        while ( $row = $db->fetch_record($result) )
        {
            $set_data = array(
                'set_id'            => $row['set_id'],
                'set_name'          => addslashes($row['set_name']),
                'set_category_id'	=> $row['set_category_id'],
                'category_name'		=> $row['category_name'],
                'set_class_id'    	=> $row['set_class_id'],
                'class_name'    	=> $row['class_name']
                );
        }
        $db->free_result($result);
		
		return $set_data;
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        /**
         * Get class & category drop-down info
         */
        $sql = "SELECT class_id, class_name
                  FROM " . CLASS_TABLE ."
				 WHERE class_name != \"Unknown\"
              ORDER BY class_name ASC";
        $classes_result = $db->query($sql);

        while ( $class = $db->fetch_record($classes_result) )
        {
            $tpl->assign_block_vars('class_row', array(
                'VALUE' => $class['class_id'],
                'SELECTED' => ( $this->set['set_class_id'] == $class['class_id'] ) ? ' selected="selected"' : '',
                'OPTION'   => $class['class_name'] )
            );
        }
        $db->free_result($classes_result);

        $sql = "SELECT category_id, category_name
                  FROM " . SP_CATEGORIES_TABLE ."
              ORDER BY category_name";
        $categories_result = $db->query($sql);

        while ( $category = $db->fetch_record($categories_result) )
        {
            $tpl->assign_block_vars('category_row', array(
                'VALUE' => $category['category_id'],
                'SELECTED' => ( $this->set['set_category_id'] == $category['category_id'] ) ? ' selected="selected"' : '',
                'OPTION'   => $category['category_name'] )
            );
        }
        $db->free_result($categories_result);

        /**
         * Get any setpieces associated with this set
         */
        if (isset($this->set['set_id'])) {

            $sql = "SELECT *
                      FROM ".SP_SETPIECES_TABLE."
                     WHERE set_piece_set_id=".$this->set['set_id']."
				  ORDER BY set_piece_name";
            $setpieces_result = $db->query($sql);

            $setpiece_count=0;
            while ($setpiece = $db->fetch_record($setpieces_result)) {
                $setpiece_count++;
                $tpl->assign_block_vars('setpieces_row', array(
                    'ROW_CLASS' 		=> $eqdkp->switch_row_class(),
                    'COUNT'     		=> $setpiece_count,

                    'ID'                => $setpiece['set_piece_id'],
                    'NAME'              => $setpiece['set_piece_name'],
                    'REQUIREMENT'       => $setpiece['set_piece_requirement'],
                    'U_VIEW_SETPIECE'   => 'manage_sets.php'.$SID . '&amp;mode=addsetpiece&amp;' . URI_ID . '='.$setpiece['set_piece_id']
                ));
            }
            $footcount_text = sprintf($user->lang['listsetpieces_footcount'], $setpiece_count);

        }

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_SET' 		=> 'manage_sets.php' . $SID . '&amp;mode=addset',
            'F_ADD_SET_PIECE' 	=> 'manage_sets.php' . $SID . '&amp;mode=addsetpiece',

            // Form values
            'SET_ID'                => $this->set['set_id'],
            'V_SET_ID'              => ( isset($_POST['add']) ) ? '' : $this->set['set_id'],
            'SET_NAME'              => $this->set['set_name'],
            'URI_ID'                => URI_ID,

            // Language
            'L_ADD_SET_TITLE'     	=> $user->lang['title_add_set'],
            'L_NAME'                => $user->lang['name'],
            'L_CATEGORY'     		=> $user->lang['category'],
            'L_CLASS'     			=> $user->lang['class'],

			// Setpiece headings
            'L_EXISTING_SETPIECES'  => $user->lang['existing_setpieces'],
            'L_REQUIREMENT'  		=> $user->lang['set_requirement'],
            'L_ADD_SETPIECE'  		=> $user->lang['title_add_set_piece'],
            'L_DELETE_SET_PIECE' 	=> $user->lang['delete_set_piece'],

			// Buttons
            'L_RESET'       => $user->lang['reset'],
            'L_ADD_SET'     => $user->lang['title_add_set'],
            'L_UPDATE_SET'  => $user->lang['title_update_set'],
            'L_DELETE_SET'  => $user->lang['delete_set'],

            // Form validation
            'FV_SET_NAME'   => $this->fv->generate_error('set_name'),

            // Javascript messages
            'MSG_NAME_EMPTY'    => $user->lang['fv_required_name'],

            // Buttons
            'S_HAS_SETPIECES' 	=> ($setpiece_count > 0 ? true : false),
            'S_ADD' 			=> ( !empty($this->url_id) ) ? false : true,

			'U_EDIT_CATEGORY'			=> "manage_sets.php".$SID.'&amp;mode=addcategory&amp;'.URI_ID.'='.$this->set['set_category_id'],
			'LISTSETPIECES_FOOTCOUNT' => $footcount_text
			
        ));

        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'].": ".$user->lang['title_add_set'],
            'template_path' => $pm->get_data('setprogress', 'template_path'),
            'template_file' => 'admin/ms_addset.html',
            'display'       => true)
        );
    }
}
?>
