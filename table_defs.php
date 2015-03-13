<?php
/******************************
 * [EQDKP Plugin] SetProgress
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: table_defs.php,v 1.1 2006/12/18 04:37:10 garrett Exp $
 *
 ******************************/

global $table_prefix;
if (!defined('SP_CATEGORIES_TABLE'))    { define('SP_CATEGORIES_TABLE', ($table_prefix . 'setprogress_categories')); }
if (!defined('SP_SETS_TABLE'))          { define('SP_SETS_TABLE',       ($table_prefix . 'setprogress_sets')); }
if (!defined('SP_SETPIECES_TABLE'))     { define('SP_SETPIECES_TABLE',  ($table_prefix . 'setprogress_setpieces')); }

?>