<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: index.php,v 1.15 2006/12/18 04:37:09 garrett Exp $
 *
 ******************************/

// EQdkp required files/vars
define('EQDKP_INC', true);
define('PLUGIN', 'setprogress');

$eqdkp_root_path = './../../';

include_once($eqdkp_root_path . 'common.php');
include_once('config.php');
include_once('itemstatsfuncs.php');
include_once($eqdkp_root_path . 'eqdkp_config_itemstats.php');
include_once($eqdkp_root_path . path_itemstats . '/eqdkp_itemstats.php');

$setprogress = $pm->get_plugin('setprogress');

if ( !$pm->check(PLUGIN_INSTALLED, 'setprogress') )
{
    message_die('The Set Progress plugin is not installed.');
}

$user->check_auth('u_member_list');

// Local variables
$class_list = array();
$i = $member_idx = 0;
$member_rows = array();

// page options
$show_all = ( (!empty($_GET['show'])) && ($_GET['show'] == 'all') ) ? true : false;
$class_filter = (isset($_GET['class_filter']) && !empty($_GET['class_filter']) ) ? urldecode($_GET['class_filter']) : "";
$icon_size_filter = (isset($_GET['icon_size_filter']) ) ? urldecode($_GET['icon_size_filter']) : 'mediumitemicon';

// Build icon size filter
    foreach( $icon_sizes as $key => $value )
    {
        $tpl->assign_block_vars('icon_size_filter_row', array(
            'VALUE' => $value,
            'SELECTED' => ( $icon_size_filter == $value ) ? ' selected="selected"' : '',
            'OPTION'   => $user->lang[$value])
            );
    }

// build the filter from the list of all classes that have items
    $sql = "SELECT DISTINCT(class_name), class_id
              FROM ".CLASS_TABLE."
             WHERE class_name != \"Unknown\"
                   ORDER BY class_name ASC";
    $results = $db->query($sql);

    $i = 0;
    while ( $row = $db->fetch_record($results) )
    {
        $class_list[$i++] =
                 array( 'name' => $row['class_name'],
                        'id'   => $row['class_id']);
    }
    $db->free_result($results);

    // Populte class_filter if this is our first time through
    if (empty($class_filter)) {
        $class_filter = $class_list[0]['id'];
    }

    foreach( $class_list as $class )
    {
        $tpl->assign_block_vars('filter_row', array(
            'VALUE' => $class['id'],
            'SELECTED' => ( $class_filter == $class['id']) ? ' selected="selected"' : '',
            'OPTION'   => $class['name'])
            );
    }

    // build set item filter
        // NEW //
    $sql = "SELECT set_name, set_piece_name, set_piece_requirement
              FROM ".SP_SETPIECES_TABLE.",
                   ".SP_SETS_TABLE.",
                   ".SP_CATEGORIES_TABLE."
             WHERE set_id = set_piece_set_id
               AND category_display = 'Y'
               AND set_category_id = category_id
               AND set_class_id = ".$class_filter;
    $set_piece_results = $db->query($sql);

    $set_piece_count=0;
    if ($db->num_rows($set_piece_results) > 0) {

        $item_test='';
        $set_pieces = array();
        while ($set_piece = $db->fetch_record($set_piece_results) ) {
            if ($set_piece_count > 0) {
                $item_test .= ",";
            }
            $set_pieces[] = (!empty($set_piece['set_piece_requirement'])) ? $set_piece['set_piece_requirement'] : $set_piece['set_piece_name'];

            $item_test .= "\"".$set_piece['set_piece_requirement']."\"";
            $item_reward .= "\"".$set_piece['set_piece_name']."\"";
            $set_piece_count++;
        }
        $db->free_result($set_piece_results);

        $sql = "SELECT DISTINCT(item_buyer), member_status
                  FROM ".ITEMS_TABLE."
                       ,".MEMBERS_TABLE."
                 WHERE item_name IN (".$item_test.") AND
                       item_buyer = member_name AND
                       member_class_id = ".$class_filter."
              ORDER BY item_buyer ASC";

        $member_list = $db->query($sql);

        // Build progress based on set category, class, and available sets
        // Headings

        // NEW //
        $sql = "SELECT set_id, set_name, set_class_id
                  FROM ".SP_CATEGORIES_TABLE.",
                       ".SP_SETS_TABLE."
                 WHERE category_id = set_category_id
                   AND category_display = 'Y'
                   AND set_class_id = ".$class_filter."
              ORDER BY category_display_order ASC";
        $set_results = $db->query($sql);

        while ($set = $db->fetch_record($set_results) ) {

            // Print out each item being tracked as the second header
            $sql = "SELECT DISTINCT(set_piece_name)
                      FROM ".SP_SETPIECES_TABLE.",
                           ".SP_SETS_TABLE.",
                           ".SP_CATEGORIES_TABLE."
                     WHERE set_piece_set_id = set_id
                       AND category_display = 'Y'
                       AND set_id = ".$set['set_id']."
                  ORDER BY set_name";
            $set_pieces_results = $db->query($sql);

            // Set the Title for the set groups as the first header
            $tpl->assign_block_vars('set_row', array(
                                                     'NAME'  => $set['set_name'],
                                                     'COUNT' => $db->num_rows($set_pieces_results),
                                                     ));

            while ($set_piece = $db->fetch_record($set_pieces_results) ) {
                $tpl->assign_block_vars("cat_header", array(
                        'H_ITEM'    => get_itemstats_decorate_name(stripslashes($set_piece['set_piece_name']),$icon_size_filter,TRUE,TRUE,TRUE)
                    ));
            }
            $db->free_result($set_piece_results);
        }
        $db->free_result($set_results);

        /* build member list & their set items */
        while ( $member = $db->fetch_record($member_list) ) {
            $sql = "SELECT DISTINCT(item_name), item_id
                      FROM ".ITEMS_TABLE."
                     WHERE item_name IN (".$item_test.") AND
                            item_buyer = '".$member['item_buyer']."'";

            $item_result = $db->query($sql);
            $member_setitems = array();
            while ( $item = $db->fetch_record($item_result) ) {
                // lower case everything to be consistent with ItemStats case insensitivity
                $member_setitems[] = strtolower($item['item_name']);
            }
            $db->free_result($item_result);

            /* build set lists */
            $item_stats = new ItemStats();
            $member_rows[$member_idx]['member_name'] = $member['item_buyer'];
            $member_rows[$member_idx]['member_status'] = $member['member_status'];

            foreach ($set_pieces as $setitem) {
                // Perform a case insensative search simimlar to ItemStats
                if(array_search (strtolower($setitem), $member_setitems) !== FALSE) {
                    $member_rows[$member_idx]['items'][] = get_itemstats_decorate_name(stripslashes($setitem),$icon_size_filter,FALSE);
                } else {
                    $member_rows[$member_idx]['items'][] = "&nbsp;";
                }
            }
            $member_idx++;
        }
        $db->free_result($member_list);

        foreach($member_rows as $row) {
            // Show / hide inactive members
            if ((($eqdkp->config['hide_inactive'] == 0) || ($show_all)) || $row['member_status']) {
                $member_count++;

                if ($row['member_status'] == 0) {
                    $member_name = "<em>".$row['member_name']."</em>";
                } else {
                    $member_name = $row['member_name'];
                }
                $member_array = array(
                    'ROW_CLASS'     => $eqdkp->switch_row_class(),
                    'COUNT'         => $member_count,
                    'NAME'          => $member_name,
                    'U_VIEW_MEMBER' => $eqdkp_root_path . 'viewmember.php' . $SID . '&amp;' . URI_NAME . '='.$row['member_name']
                    );
                $tpl->assign_block_vars('members_row', $member_array);

                foreach ($row['items'] as $item)
                {
                    $tpl->assign_block_vars('members_row.item', array(
                            'ITEM'    => $item
                        ));
                }
            }
        }

        if ( ($eqdkp->config['hide_inactive'] == 1) && (!$show_all) )
        {
            $footcount_text = sprintf($user->lang['listmembers_active_footcount'], $member_count,
                                          '<a href="index.php' . $SID . '&amp;show=all&amp;class_filter='.$class_filter.'" class="rowfoot">');
        }
        else
        {
            $footcount_text = sprintf($user->lang['listmembers_footcount'], $member_count);
        }

        $tpl->assign_vars(array(
            'F_ACTION' => 'index.php'.$SID,
			
			'HEADER_COLSPAN' => $set_piece_count+2,

            'S_HAS_SETS'    => true,

            'L_ICON_SIZE' => $user->lang['icon_sizes'],
            'L_NAME'      => $user->lang['name'],
            'L_PAGE_TITLE'=> $user->lang['title_setprogress'],

            'LISTMEMBERS_FOOTCOUNT' => $footcount_text
            ));
    } else { // sets found
        $tpl->assign_vars(array(
            'L_PAGE_TITLE'=> $user->lang['title_setprogress'],
            'L_NO_SETS_FOUND' => $user->lang['no_sets_found']
            ));
    }



// Extra CSS Styles
$eqdkp->extra_css = $setprogress_css;

$eqdkp->set_vars(array(
    'page_title'    => sprintf($user->lang['title_prefix'], $eqdkp->config['guildtag'], $eqdkp->config['dkp_name']).': '.$user->lang['title_setprogress'],
    'template_path' => $pm->get_data('setprogress', 'template_path'),
    'template_file' => 'setprogress.html',
    'display'       => true)
);

///////// NEW SETPROGRESS /////////////
class SetProgress extends EQDKP_Admin {

    function SetProgress()
    {
        global $db, $eqdkp, $user, $tpl, $pm;
        global $SID;

        parent::eqdkp_admin();

        $this->assoc_buttons(array(
            'parse' => array(
                'name'    => 'parse',
                'process' => 'process_parse',
                'check'   => 'a_raid_add'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_add'),
            'import' => array(
                'name'    => 'import',
                'process' => 'import_sets',
                'check'   => 'a_raid_add'),
            'export' => array(
                'name'    => 'export',
                'process' => 'export_sets',
                'check'   => 'a_raid_add'),
                )
        );
    }

}

//$SetProgress = new SetProgress;
//$SetProgress->process();

?>