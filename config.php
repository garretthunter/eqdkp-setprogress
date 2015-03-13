<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: config.php,v 1.9 2006/12/18 04:37:09 garrett Exp $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    die('You cannot access this file directly.');
}

// URI Parameters
define('URI_ID',    'id');

// Database Table names
include_once ("table_defs.php");

// Icon sizes used to display set pieces
$icon_sizes = array ("smallitemicon",
					 "mediumitemicon",
					 "largeitemicon"
					 );

///////////////////////////////////////////////////
// CSS Styles
$setprogress_css = "
.mediumitemicon {
    width: 20px; height: 20px;
}
.largeitemicon {
    width: 30px; height: 30px;
}

#setprogress_row {
    padding:.25em 0em;
}";


?>