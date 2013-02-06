<?php
/** 
 * @copyright Copyright (C) 2010 redCOMPONENT.com. All rights reserved. 
 * @license GNU/GPL, see LICENSE.php
 * redCOMMENT can be downloaded from www.redcomponent.com
 * redCOMMENT is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.

 * redCOMMENT is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with redCOMMENT; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Installation file
 */

/* ensure this file is being included by a parent file */
defined( '_JEXEC' ) or die( 'Restricted access' );

function com_install()
{
	//Insert missing fields and data into the database
	createDemoContent();
	
	//Diplay the installation message
	displayInstallMsg();
}

function createDemoContent()
{
	$db = JFactory::getDBO();
	$q = "select count(*) as cnt from #__redcomments_comments";
	/* Get the current columns */
	$db->setQuery($q);
	$row = $db->loadObject();	
	$cnt=$row->cnt;
	if ($cnt==0)
	{	
		/*
		$q="insert into #__redcomments_comments (title,template,templatetype,state,created_by) values (
			'".JText::_('COM_REDCONTENT_DEMOCONTENT_CATEGORY')."',
			'".$template."',
			'category',
			1,
			".$user->id."
			) ";
		$db->setQuery($q);
		$db->query();
		*/
	}
}

function displayInstallMsg()
{
	echo '<img src="components/com_redcomments/images/redcomments_logo.jpg" alt="redcomments Logo" align="left" width="500"><br /><br /><br /><br /><br /><br /><br /><br /><br />';
	echo 'redCOMMENTs is a brand new Joomla 2,5 native MVC component.<br /><br />';
	echo '<br /><br />Remember to check for updates on:<br />';
	echo '<a href="http://www.redcomponent.com/" target="_new"><img src="components/com_redcomments/images/redcomponent_logo.jpg" alt=""></a>';
	
}

?>