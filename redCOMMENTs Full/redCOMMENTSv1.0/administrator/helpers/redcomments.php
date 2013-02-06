<?php
/**
 * @version     1.0.0
 * @package     com_redcomments
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Ronni K. G. Christiansen <email@redweb.dk> - http://www.redcomponent.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Redcomments helper.
 */
class RedcommentsHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu($vName = '')
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_REDCOMMENTS_TITLE_DISCUSSIONS'),
			'index.php?option=com_redcomments&view=discussions',
			$vName == 'discussions'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_REDCOMMENTS_TITLE_COMMENTS'),
			'index.php?option=com_redcomments&view=comments',
			$vName == 'comments'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_REDCOMMENTS_TITLE_SUBSCRIPTIONS'),
			'index.php?option=com_redcomments&view=subscriptions',
			$vName == 'subscriptions'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_REDCOMMENTS_TITLE_NOTIFICATIONS'),
			'index.php?option=com_redcomments&view=notifications',
			$vName == 'notifications'
		);

	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_redcomments';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}
}
