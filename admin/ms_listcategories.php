<?php
/**
 * List or delete categories
 *
 * @category SetProgress
 * @package AdminFunctions
 *
 * @author Garrett Hunter <loganfive@blacktower.com>
 * @copyright Copyright &copy; 2006, Garrett Hunter
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id: ms_listcategories.php,v 1.1 2006/12/18 04:37:11 garrett Exp $
 */

if ( !defined('EQDKP_INC') )
{
    die('Hacking attempt');
}

/**
 * This class handles the addition, update, and deletion of categories
 * @subpackage ManageCategories
 */
class MS_ListCategories extends EQdkp_Admin {

    function MS_ListCategories()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();
		
        $this->assoc_buttons(array(
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_members_man'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_members_man'))
        );
	}
	
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
	function display_form() {

        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

		$sort_order = array(
			0 => array('category_name', 'category_name desc')
		);
		
		$current_order = switch_order($sort_order);      

        //
        // Get list of categories
        //
		$sql = "SELECT category_id, category_name, category_display, category_display_order
				  FROM " . SP_CATEGORIES_TABLE . "
		      ORDER BY category_display_order";
		$categories_result = $db->query($sql);

		$category_count = 0;		
		while ( $category = $db->fetch_record($categories_result) ) {

			$category_count++;

			/**
			 * Get all the sets for this category
			 */
			$sql = "SELECT set_id, set_name,
						   class_name
					  FROM " . SP_SETS_TABLE . ",
						   " . CLASS_TABLE . "
					 WHERE set_category_id = ".$category['category_id']."
					   AND set_class_id = class_id
				  ORDER BY set_name";
			$sets_result = $db->query($sql);

			if ($db->num_rows($sets_result) > 0 ) {
				$set_count = 0;
				while ( $set = $db->fetch_record($sets_result) ) {
					$set_count++;
					
					/**
					 * we split out the first row from the others so that we do not duplicate the cateory info for every set
					 */
					if ($set_count == 1) {
						$tpl->assign_block_vars('categories_row', array(
								'ROW_CLASS'     => $eqdkp->switch_row_class(),
								'COUNT'         => $category_count,
		
								'ID'   			=> $category['category_id'],
								'NAME'          => $category['category_name'],
								'DISPLAY'       => $category['category_display'],
								'DISPLAY_ORDER' => $category['category_display_order'],
								'U_VIEW_CATEGORY' => 'manage_sets.php'.$SID . '&amp;mode=addcategory&amp;' . URI_ID . '='.$category['category_id'],
		
								'SET_NAME' 		=> $set['set_name'],
								'CLASS' 		=> $set['class_name'],
								'U_VIEW_SET' 	=> 'manage_sets.php'.$SID . '&amp;mode=addset&amp;' . URI_ID . '='.$set['set_id']
						));
					} else {
						$tpl->assign_block_vars('categories_row', array(
								'ROW_CLASS'     => $eqdkp->switch_row_class(),
		
								'SET_NAME' 		=> $set['set_name'],
								'CLASS' 		=> $set['class_name'],
								'U_VIEW_SET' 	=> 'manage_sets.php'.$SID . '&amp;mode=addset&amp;' . URI_ID . '='.$set['set_id']
						));
					}
				}
			} else {
				/*
				 * This category had no sets
				 */
				$tpl->assign_block_vars('categories_row', array(
						'ROW_CLASS'     => $eqdkp->switch_row_class(),
						'COUNT'         => $category_count,

						'ID'   			=> $category['category_id'],
						'NAME'          => $category['category_name'],
						'DISPLAY'       => $category['category_display'],
						'DISPLAY_ORDER' => $category['category_display_order'],
						'U_VIEW_CATEGORY' => 'manage_sets.php'.$SID . '&amp;mode=addcategory&amp;' . URI_ID . '='.$category['category_id']
				));
			}
			$db->free_result($sets_result);
		}
		$db->free_result($categories_result);

		$tpl->assign_vars(array(
			'F_CATEGORIES' 	=> 'manage_sets.php' . $SID . '&amp;mode=addcategory',
		
			'L_SETS' 			=> $user->lang['title_sets'],
			'L_NAME' 			=> $user->lang['name'],
			'L_DISPLAY' 		=> $user->lang['display'],
			'L_DISPLAY_ORDER' 	=> $user->lang['display_order'],
			'L_SET' 			=> $user->lang['set_name'],
			'L_CLASS' 			=> $user->lang['class'],

			
			'BUTTON_NAME' 	=> 'delete',
			'BUTTON_VALUE' 	=> $user->lang['delete_selected_categories'],
		
			'O_NAME' => $current_order['uri'][0],

			'U_LIST_CATEGORIES' => 'manage_sets.php'.$SID.'&amp;mode=listcategories&amp;',
		
			'LISTCATEGORIES_FOOTCOUNT' => $footcount_text)
		);
		
		$eqdkp->set_vars(array(
			'page_title'    => sprintf($user->lang['admin_title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'].": ".$user->lang['title_list_categories'],
			'template_path' => $pm->get_data('setprogress', 'template_path'),
			'template_file' => 'admin/ms_listcategories.html',
			'display'       => true)
		);
	}

}

?>
