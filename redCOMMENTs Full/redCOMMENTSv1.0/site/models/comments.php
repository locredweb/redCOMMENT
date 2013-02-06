<?php

/**
 * @version     1.0.0
 * @package     com_redcomments
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Ronni K. G. Christiansen <email@redweb.dk> - http://www.redcomponent.com
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Redcomments records.
 */
class RedcommentsModelComments extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
	var $_table_prefix = null;
	
    public function __construct($config = array()) {
    	$this->_table_prefix = '#__redcomments_';
        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState($ordering = null, $direction = null) {
        
        // Initialise variables.
        $app = JFactory::getApplication();

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $this->setState('list.limit', $limit);

        $limitstart = JFactory::getApplication()->input->getInt('limitstart', 0);
        $this->setState('list.start', $limitstart);
        
		if(empty($ordering)) {
			$ordering = 'a.ordering';
		}
        
        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
                $this->getState(
                        'list.select', 'a.*'
                )
        );
        
        $query->from('`#__redcomments_comments` AS a');
        

    // Join over the users for the checked out user.
    $query->select('uc.name AS editor');
    $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
    
		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');
		// Join over the foreign key 'discussion_id'
		$query->select('#__redcomments_discussions_344864.foreignkey AS discussions_foreignkey_344864');
		$query->join('LEFT', '#__redcomments_discussions AS #__redcomments_discussions_344864 ON #__redcomments_discussions_344864.id = a.discussion_id');

    // Filter by published state
    $published = $this->getState('filter.state');
    if (is_numeric($published)) {
        $query->where('a.state = '.(int) $published);
    } else {
        $query->where('(a.state = 1)');
    }
    
		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where('( a.commenttext LIKE '.$search.' )');
			}
		}
		//Filtering discussion_id
		$filter_discussion_id = $this->state->get("filter.discussion_id");
		if ($filter_discussion_id) {
			$query->where("a.discussion_id = '".$filter_discussion_id."'");
		}        
        return $query;
    }
    
    
	function getForeignkey($post)
    {
   	    $foreignkey  = "";	
    	if( $post['comment_option'] != "" )
		{
			$foreignkey = 'comments.'.$post['comment_option'];
		}
		if( $post['comment_view'] != "" )
		{
			$foreignkey .= '.'.$post['comment_view'];
		}
		if( $post['comment_key'] != "" )
		{
			$foreignkey .= '.'.$post['comment_key'];
		}
		return $foreignkey;
    }
    
    function getDataBindCommentTable($post,$foreignkey,$key)
    {
    	$q 	  =  " SELECT d.id "
				." FROM ".$this->_table_prefix."discussions AS d "
				." WHERE d.foreignkey = '".$foreignkey."' ";
		$this->_db->setQuery ( $q );
		$rs = $this->_db->loadResult();
		$data 					   		= "";
		$data['discussion_id'] 	   		= $rs;
		$data['created_by']    	   		= $post['user_id'];
		$data['state']         	   		= 0;
		if($key > 0)
		{
			$data['commenttext']   		= $post['child_comment_'.$key];
			$data['email']   	   		= $post['child_email_'.$key];
			$data['business']      		= $post['child_business_'.$key];
			$data['name']      	   		= $post['child_name_'.$key];
			$data['parent_comment_id']  = $post['comment_parent'];
		}
		else
		{
			$data['commenttext']   		= $post['comment'];
			$data['email']   	   		= $post['email'];
			$data['business']      		= $post['business'];
			$data['name']      	   		= $post['name'];
			$data['parent_comment_id']  = 0;
			  
		}
		$data['checked_out_time']  = date("Y-m-d H:i:s");
		return $data;
    }
    
    
    
    //Begin: Add to DiscussionAndComment
    function addToDiscussionAndComment($post)
    {
    	$commentTable = & $this->getTable ('comment','RedcommentsTable' );
    	$foreignkey   = $this->getForeignkey($post);
		$data_cm      = $this->getDataBindCommentTable($post,$foreignkey,0);  
		if($data_cm['discussion_id'] > 0) 		
		{	
			$commentTable->check();
			if (!$commentTable->bind($data_cm)) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			if (!$commentTable->store()) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			//End: Continue Add Comment
			return true;
		}
		else
		{
			$data 	 	 		= "";
			$data['foreignkey'] = $foreignkey;
			$data['created_by'] = $post['user_id'];
			$data['state'] 		= 1;
			$discussionTable = & $this->getTable ('discussion','RedcommentsTable' );
			$discussionTable->check();
			//Begin: Add Discussion
			if (!$discussionTable->bind($data)) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			if (!$discussionTable->store()) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			//End: Add Discussion
			
			$data_ex = $this->getDataBindCommentTable($post,$foreignkey,0);
			$commentTable->check();
			if (!$commentTable->bind($data_ex)) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			if (!$commentTable->store()) 
			{
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			//End: Continue Add Comment
			return true;
		}
    }
    //End: Add to DiscussionAndComment
    
    
    function addCommentParent($post)
    {
    	$commentTable = & $this->getTable ('comment','RedcommentsTable' );
    	$foreignkey   = $this->getForeignkey($post);
		$key 		  = $post['comment_parent'];
		$data 		  = $this->getDataBindCommentTable($post,$foreignkey,$key); 
		$commentTable->check();
		//Add comment to table TableComment
    	if (!$commentTable->bind($data)) 
		{
				$this->setError($this->_db->getErrorMsg());
				return false;
		}
		if (!$commentTable->store()) 
		{
				$this->setError($this->_db->getErrorMsg());
				return false;
		}
		return true;
    }
       
}
