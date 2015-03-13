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
 * @version $Id: ms_addsetpiece.php,v 1.1 2006/12/18 04:37:10 garrett Exp $
 */

if ( !defined('EQDKP_INC') ) { die("haxzor"); }

/**
 * This class handles the addition, update, and deletion of sets
 * @subpackage ManageSetPieces
 */
class MS_AddSetPiece extends EQdkp_Admin
{
    var $setpiece     = array();          // Holds setpiece data if URI_ID is set             @var setpiece
    var $old_setpiece = array();          // Holds setpiece data from before POST             @var old_setpiece

    function MS_AddSetPiece()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->set_piece = array(
            'set_piece_id'          => 0,
            'set_piece_name'        => post_or_db('set_piece_name'),
            'set_piece_requirement' => post_or_db('set_piece_requirement'),
            'set_piece_set_id'   	=> post_or_db('set_piece_set_id')
        );

        // Vars used to confirm deletion
        $confirm_text = $user->lang['confirm_delete_setpiece'];
        $set_piece_names = array();
        if ( isset($_POST['delete']) )
        {
            if ( isset($_POST['compare_ids']) )
            {
                foreach ( $_POST['compare_ids'] as $id )
                {
                    $set_piece_name = $db->query_first('SELECT set_piece_name FROM ' . SP_SETPIECES_TABLE . " WHERE set_piece_id=" . $id);
                    $set_piece_names[] = $set_piece_name;
                }

                $names = implode(', ', $set_piece_names);

                $confirm_text .= '<br /><br />' . $names;
            }
            else
            {
                message_die($user->lang['no_setpiece_selected_for_deletion']);
            }
        }

        $this->set_vars(array(
            'confirm_text'  => $confirm_text,
            'uri_parameter' => URI_ID,
            'url_id'        => ( sizeof($_POST['compare_ids']) > 0 ) ? implode(",",$_POST['compare_ids']) : (( isset($_GET[URI_ID]) ) ? $_GET[URI_ID] : ''),
            'script_name'   => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece')
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
                      FROM " . SP_SETPIECES_TABLE . "
                     WHERE set_piece_id='" . $this->url_id."'";
            $result = $db->query($sql);
            $row = $db->fetch_record($result);
            $db->free_result($result);

            $this->set_piece = array(
				'set_piece_id'          => $row['set_piece_id'],
				'set_piece_name'        => post_or_db('set_piece_name',$row),
				'set_piece_requirement' => post_or_db('set_piece_requirement',$row),
				'set_piece_set_id'   	=> post_or_db('set_piece_set_id',$row)
            );
        }
    }

    function error_check()
    {
        global $user, $SID, $db;

        if ( (isset($_POST['add'])) || (isset($_POST['update'])) )
        {
            $this->fv->is_filled('set_piece_name', 			$user->lang['fv_required_name']);
            $this->fv->is_filled('set_piece_requirement', 	$user->lang['fv_required_name']);
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
        // Make sure that each setpiece & requirement names is properly capitalized
        $set_piece_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_piece_name']));
        $set_piece_name = ucwords($set_piece_name);
        $set_piece_requirement = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_piece_requirement']));
        $set_piece_requirement = ucwords($set_piece_requirement);

        // Check for existing setpiece name
        $sql = "SELECT set_piece_id FROM " . SP_SETPIECES_TABLE ." WHERE set_piece_name = '".$set_piece_name."'";
        $set_piece_id = $db->query_first($sql);

        // Error out if set name exists
        if ( isset($set_piece_id) && $set_piece_id > 0 ) {

            $failure_message = sprintf($user->lang['duplicate_set_piece'], $set_piece_name, $set_piece_id);

            $link_list = array(
                $user->lang['adminmenu_add_set_piece']      => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece',
                $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories');

            message_die($failure_message, $link_list);
        }

        $query = $db->build_query('INSERT', array(
            'set_piece_id'            => '',
            'set_piece_name'          => $set_piece_name,
            'set_piece_requirement'   => $set_piece_requirement,
            'set_piece_set_id'        => $_POST['set_piece_set_id'],
        ));
        $db->query('INSERT INTO ' . SP_SETPIECES_TABLE . $query);

        /**
         * Logging
         */
        $new_set = $this->get_setpiece_data($_POST[URI_ID]);
        $log_action = array(
            'header'         	=> '{L_ACTION_SET_PIECE_ADDED}',
            '{L_NAME}'       	=> $set_piece_name,
            '{L_REQUIREMENT}'   => $new_set['set_piece_requirement'],
            '{L_SET}' 	 		=> $new_set['set_name'],
            '{L_ADDED_BY}'   => $this->admin_user);

        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_add_set_piece_success'], $set_piece_name);
        $link_list = array(
                $user->lang['adminmenu_add_set_piece']      => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece',
                $user->lang['adminmenu_list_edit_del_sets'] => 'manage_sets.php' . $SID . '&amp;mode=listcategories');
        $this->admin_die($success_message, $link_list);
    }

    /**
	 * Process Update
	 */
    function process_update()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        /**
		 * Get old set data
		 */
        $this->old_setpiece = $this->get_setpiece_data($_POST[URI_ID]);

        /**
         * Clean the input data
         */
        // Make sure that each set name is properly capitalized
        $set_piece_name = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_piece_name']));
        $set_piece_name = ucwords($set_piece_name);
        $set_piece_requirement = strtolower(preg_replace('/[[:space:]]/i', ' ', $_POST['set_piece_requirement']));
        $set_piece_requirement = ucwords($set_piece_requirement);

        /**
		 * Update the set
		 */
        $query = $db->build_query('UPDATE', array(
            'set_piece_name'          => $set_piece_name,
            'set_piece_requirement'   => $set_piece_requirement,
            'set_piece_set_id' 		  => $_POST['set_piece_set_id'],
        ));
        $db->query('UPDATE ' . SP_SETPIECES_TABLE . ' SET ' . $query . " WHERE set_piece_id=" . $_POST[URI_ID]);

        /**
         * Get our current row & log the results of the update
         */
        $new_data = $this->get_setpiece_data($_POST[URI_ID]);
        $log_action = array(
            'header'                => '{L_ACTION_SET_PIECE_UPDATED}',
            '{L_NAME_BEFORE}'       => $this->old_setpiece['set_piece_name'],
            '{L_REQUIREMENT_BEFORE}'=> $this->old_setpiece['set_piece_requirement'],
            '{L_SET_BEFORE}'  		=> $this->old_setpiece['set_name'],

            '{L_NAME_AFTER}'        => $this->find_difference($this->old_setpiece['set_piece_name'], $set_piece_name),
            '{L_REQUIREMENT_AFTER}' => $this->find_difference($this->old_setpiece['set_piece_requirement'], $set_piece_requirement),
            '{L_SET_AFTER}'   		=> $this->find_difference($this->old_setpiece['set_name'], $new_data['set_name']),

            '{L_UPDATED_BY}'        => $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_set_piece_success'], $this->old_setpiece['set_piece_name']);
        $link_list = array(
                $user->lang['adminmenu_add_set_piece']      => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece',
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
        $set_piece_ids = explode(',', $_POST[URI_ID]);
        foreach ( $set_piece_ids as $set_piece_id )
        {
            if ( empty($set_piece_id) )
            {
                continue;
            }

            //
            // Get old member data
            //
            $this->old_setpiece = $this->get_setpiece_data($set_piece_id);

            /**
             * Delete all setpieces.
             */
			$sql = 'DELETE FROM ' . SP_SETPIECES_TABLE . "
					WHERE set_piece_id = ". $set_piece_id;
			$db->query($sql);

            /**
             * Logging
             */
            $log_action = array(
                'header'        	=> '{L_ACTION_SET_PIECE_DELETED}',
                '{L_NAME}'      	=> $this->old_setpiece['set_piece_name'],
                '{L_REQUIREMENT}'  	=> $this->old_setpiece['set_piece_requirement'],
                '{L_SET}' 			=> $this->old_setpiece['set_name']);
            $this->log_insert(array(
                'log_type'   => $log_action['header'],
                'log_action' => $log_action)
            );

            /**
             * Append success message
             */
            $success_message .= sprintf($user->lang['admin_delete_set_piece_success'], $this->old_setpiece['set_piece_name']) . '<br />';
        }

        //
        // Success message
        //
        $this->admin_die($success_message);
    }

    /**
	 * Process helper methods
	 */
    function get_setpiece_data($set_piece_id)
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

		$set_piece_data = array();
		
        $sql = "SELECT sp.*, set_name
                FROM " . SP_SETS_TABLE . ",
				     " . SP_SETPIECES_TABLE ." sp
                WHERE set_piece_set_id = set_id
				  AND set_piece_id=" . $set_piece_id;
        $result = $db->query($sql);
		
        while ( $row = $db->fetch_record($result) )
        {
            $set_piece_data = array(
                'set_piece_id'    		=> $row['set_piece_id'],
                'set_piece_name'        => addslashes($row['set_piece_name']),
                'set_piece_requirement'	=> addslashes($row['set_piece_requirement']),
                'set_piece_set_id'		=> $row['set_piece_set_id'],
                'set_name'    			=> addslashes($row['set_name'])
                );
        }
        $db->free_result($result);
		
		return $set_piece_data;
    }

    /**
	 * Display form
	 */
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        /**
         * Get Set drop-down info
         */
        $sql = "SELECT set_id, set_name
                  FROM " . SP_SETS_TABLE ."
              ORDER BY set_name ASC";
        $sets_result = $db->query($sql);

        while ( $set = $db->fetch_record($sets_result) )
        {
            $tpl->assign_block_vars('set_row', array(
                'VALUE' => $set['set_id'],
                'SELECTED' => ( $this->set_piece['set_piece_set_id'] == $set['set_id'] ) ? ' selected="selected"' : '',
                'OPTION'   => $set['set_name'] )
            );
        }
        $db->free_result($sets_result);

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_SET_PIECE' => 'manage_sets.php' . $SID . '&amp;mode=addsetpiece',

            // Form values
            'SET_PIECE_ID'          => $this->set_piece['set_piece_id'],
            'V_SET_PIECE_ID'         => ( isset($_POST['add']) ) ? '' : $this->set_piece['set_piece_id'],
            'SET_PIECE_NAME'        => $this->set_piece['set_piece_name'],
            'SET_PIECE_REQUIREMENT' => $this->set_piece['set_piece_requirement'],
            'URI_ID'                => URI_ID,

            // Language
            'L_ADD_SET_PIECE_TITLE' => $user->lang['title_add_set_piece'],
            'L_SET'     			=> $user->lang['set'],
            'L_NAME'                => $user->lang['name'],
            'L_REQUIREMENT'  		=> $user->lang['set_requirement'],

			// Buttons
            'L_RESET'       		=> $user->lang['reset'],
            'L_ADD_SET_PIECE'   	=> $user->lang['title_add_set_piece'],
            'L_UPDATE_SET_PIECE'  	=> $user->lang['title_update_set_piece'],
            'L_DELETE_SET_PIECE'  	=> $user->lang['delete_set_piece'],

            // Form validation
            'FV_SET_PIECE_NAME'   	=> $this->fv->generate_error('set_piece_name'),
            'FV_REQUIREMENT'   		=> $this->fv->generate_error('set_piece_requirement'),

            // Javascript messages
            'MSG_NAME_EMPTY'    => $user->lang['fv_required_name'],

            // Buttons
            'S_ADD' => ( !empty($this->url_id) ) ? false : true,
			
			'U_EDIT_SET'		=> "manage_sets.php" . $SID . "&amp;mode=addset&amp;".URI_ID."=".$this->set_piece['set_piece_set_id']
			
        ));

        $eqdkp->set_vars(array(
            'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'].": ".$user->lang['title_add_set_piece'],
            'template_path' => $pm->get_data('setprogress', 'template_path'),
            'template_file' => 'admin/ms_addsetpiece.html',
            'display'       => true)
        );
    }
}
?>
