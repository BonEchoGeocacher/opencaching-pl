<?php
/***************************************************************************
												  ./tpl/stdstyle/viewlogs.inc.php
															-------------------
		begin                : July 9 2004
		copyright            : (C) 2004 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

	***************************************************************************/

/***************************************************************************
	*                                         				                                
	*   This program is free software; you can redistribute it and/or modify  	
	*   it under the terms of the GNU General Public License as published by  
	*   the Free Software Foundation; either version 2 of the License, or	    	
	*   (at your option) any later version.
	*
	***************************************************************************/

/****************************************************************************
	        
   Unicode Reminder ??
                                 				                                
	 set template specific language variables
	
 ****************************************************************************/

	$functions_start = '<br/><img src="images/trans.gif" alt="" title="" class="icon16" />&nbsp;';
	$functions_middle = '&nbsp;';
	$functions_end = '';
	
	$edit_log = '<a href="editlog.php?logid={logid}"><img src="tpl/stdstyle/images/action/16x16-properties.png" border="0" align="middle" border="0" width="16" height="16" alt="" title=""/></a>&nbsp;<a href="editlog.php?logid={logid}">Edycja</a>&nbsp;';
	$remove_log = '<a href="removelog.php?logid={logid}"><img src="tpl/stdstyle/images/log/16x16-trash.png" class="icon16" alt="Trash" title=""/></a>&nbsp;<a href="removelog.php?logid={logid}">Usuń</a>&nbsp;';
	$upload_picture = '<a href="newpic.php?objectid={logid}&type=1"><img src="tpl/stdstyle/images/action/16x16-addimage.png" class="icon16" alt="" title=""/></a>&nbsp;<a href="newpic.php?objectid={logid}&type=1">Dodaj obrazek</a>&nbsp;';
	
	$remove_picture = ' <a href="removepic.php?uuid={uuid}">Usuń</a>';

	$rating_picture = '<img src="images/rating-star.png" alt="Rekomendacja" /> '
?>
