<?php
/**
 * @package		redweb.dk
 * @subpackage	mod_articles_latest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
abstract class modFrontendCommentHelper
{
    public function __construct($config = array()) {    
        parent::__construct($config);
    }
	
	public static function getList($foreignkey)
	{
		global $mainframe;
		$db	   = & JFactory::getDBO();
		$query = "SELECT d.* "
				."FROM #__redcomments_discussions AS d "
				."WHERE d.foreignkey = '".$foreignkey."' ";
		$db->setQuery($query);
		$result = $db->loadObject();
		if(count($result) > 0 )
		{
			$query_ex =  " SELECT c.* "
						." FROM #__redcomments_comments AS c "
						." WHERE c.discussion_id = '".$result->id."' AND c.state = 1"
						." ORDER BY c.ordering" ;
			$db->setQuery($query_ex);
			$lists = $db->loadObjectList();	
			if(count($lists) > 0)
			{
				return $lists;
			}
		}		
	}
}
