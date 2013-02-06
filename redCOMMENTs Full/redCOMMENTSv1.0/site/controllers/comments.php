<?php
/**
 * @version     1.0.0
 * @package     com_redcomments
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Ronni K. G. Christiansen <email@redweb.dk> - http://www.redcomponent.com
 */

// No direct access.
defined('_JEXEC') or die;	
require_once JPATH_COMPONENT.'/controller.php';


/**
 * Comments list controller class.
 */
class RedcommentsControllerComments extends RedcommentsController
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'Comments', $prefix = 'RedcommentsModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	
	function getLinkURL($post)
	{
		$link = 'index.php';
		if($post['comment_option'] != "" )
		{
			$link .= '?option='.$post['comment_option'];
		}
		if($post['comment_view'] != "" )
		{
			$link .= '&view='.$post['comment_view'];
		}
		if($post['comment_view_key'] != "" )
		{
			$link .= '&'.$post['comment_view_key'].'='.$post['comment_key'];
		}
		if( $post['comment_itemid'] != "")
		{
			$link .= '&Itemid='.$post['comment_itemid'];
		}
		return $link;
	
	}
	
	
	function addComment()
	{
		$user =& JFactory::getUser();
		$user_id = $user->id;
		$post = JRequest::get('post');
		if($user_id > 0)
		{
			//testing link 
			$model = $this->getModel('comments');	
			$post = JRequest::get('post');
			$post['user_id'] = $user_id;
			$link = $this->getLinkURL($post);
			if ($model->addToDiscussionAndComment($post))
			{	
				$msg  = JText::_('COM_REDCOMMENTS_ADD_COMMENT_SUCCESS');
				$this->setRedirect( $link,$msg );
			}
			else
			{
				$msg = JText::_('COM_REDCOMMENTS_ADD_COMMENT_UNSUCCESS');
				$this->setRedirect( $link,$msg );
			}
		}
		else
		{
			$msg = JText::_('COM_REDCOMMENTS_LOGIN');
			$this->setRedirect( $link,$msg );
		}
	}
	
	function addCommentChild()
	{
		$user =& JFactory::getUser();
		$user_id = $user->id;
		$post = JRequest::get('post');
		$post['user_id'] = $user_id;
		$link = $this->getLinkURL($post);
		$model = $this->getModel('comments');
		if($model->addCommentParent($post))
		{
			$msg  = JText::_('COM_REDCOMMENTS_ADD_COMMENT_SUCCESS');
			$this->setRedirect( $link,$msg );
		}
		else
		{
			$msg = JText::_('COM_REDCOMMENTS_LOGIN');
			$this->setRedirect( $link,$msg );
		}	
	}
	
	
}