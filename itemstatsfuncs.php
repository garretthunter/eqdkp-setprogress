<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: itemstatsfuncs.php,v 1.6 2006/12/18 04:37:10 garrett Exp $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    die('You cannot access this file directly.');
}
include_once($eqdkp_root_path . 'common.php');
include_once($eqdkp_root_path . 'eqdkp_config_itemstats.php');                                              
include_once($eqdkp_root_path . path_itemstats . '/eqdkp_itemstats.php');

// Customized version of itemstats_decorate_name()
function get_itemstats_decorate_name($name,$icon_size="mediumitemicon",$display_tooltip=TRUE,$display_name=FALSE,$update=FALSE)
{
    $item_stats = new ItemStats();

    // Attempt the get the proper name of this item.
    if ($display_name) {
        $decorated_name = $item_stats->getItemName($name,$update);
    }

    // Add the icon to the name.
    $item_icon_link = $item_stats->getItemIconLink($name,$update);
    if (empty($item_icon_link))
    {
        $item_icon_link = ICON_STORE_LOCATION . DEFAULT_ICON . ICON_EXTENSION;
    }

    $decorated_name = "<img class='".$icon_size."' src='" . $item_icon_link . "'> ";

    if ($display_tooltip) {
        // Wrap everything around tooltip code.
        $item_tooltip_html = $item_stats->getItemTooltipHtml($name, $update);
        if (!empty($item_tooltip_html))
        {
            $decorated_name = "<span " . $item_tooltip_html . ">" . $decorated_name . "</span>";
        }
    }

    return $decorated_name;
}

?>