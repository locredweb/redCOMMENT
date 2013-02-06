<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_articles_latest
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;
$module = JModuleHelper::getModule('mod_frontend_comment');
$params = new JRegistry;
$params->loadString($module->params, 'JSON');
$check_mutiple_comment = $params->get( 'multiple_comment' );
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_frontend_comment/css/style.css");
$user 		 = & JFactory::getUser();
$user_id 	 = $user->id;
$jinput 	 = JFactory::getApplication()->input;
$option 	 = $jinput->get('option');
$view   	 = $jinput->get('view');
$Itemid 	 = $jinput->get('Itemid');
$key 		 = "";
$primary_key = "";
$link_xml 	 = JURI::base().'components/com_redcomments/parameters.xml';
$xml 		 = simplexml_load_file($link_xml);
for($i=0;$i<count($xml->component);$i++)
{
	$option_ex = (string)$xml->component[$i]->name; 
	if($option == $option_ex)
	{
		for($j=0;$j<count($xml->component[$i]->views);$j++)
		{
			$view_ex = (string)$xml->component[$i]->views[$j]->view;
			if($view == $view_ex)
			{
				$key = (string)$xml->component[$i]->views[$j]->key;
				break;
			}
		}
	}
}
if($key != "")
{
	$primary_key = $jinput->get($key);
}

//Show all comments
$foreignkey  = "";	
if( $option != "" )
{
	$foreignkey = 'comments.'.$option;
}
if( $view != "" )
{
	$foreignkey .= '.'.$view;
}
if( $primary_key != "" )
{
	$foreignkey .= '.'.$primary_key;
}
$list_comments = modFrontendCommentHelper::getList($foreignkey);


function ScanParentChildComment($parentid,$list_comments,$res = '',$sep = '',$user_id,$option,$view,$Itemid,$key,$primary_key,$check_mutiple_comment)
{	
	foreach($list_comments as $v)
	{
		
		if($v->parent_comment_id == $parentid) 
		{
			if($sep != '')
			{
				$re = '<div id="comment_item" class="'.$sep.'" ><div id="comment_name">'.$v->name;
			
			}
			else
			{
				$re = '<div id="comment_item" ><div id="comment_name">'.$v->name;
			}
			if($v->business != "")
			{
				$re.= '('.$v->business.')';
			}
			$re.= '</div>';
			$re.= '<div id="comment_date">'.$v->checked_out_time.'</div>';
			$re.= '<div id="comment_text">'.$v->commenttext.'</div>';	
			if($user_id > 0)
			{
				if($check_mutiple_comment)
				{
					$re.= '<div id="comment_reply"><a onclick="create_comment('.$v->id.')">'.JText::_('MOD_FRONTEND_COMMENT_REPLY_COMMENT').'</a></div>';
				}
				$re.= '<div class="comment_body_'.$v->id.'">';
				$re.= '<form method="post" 		name="formComment_'.$v->id.'" onsubmit="return validateForm('.$v->id.')">';
				$re.= '<label>'.JText::_('MOD_FRONTEND_COMMENT_NAME').'</label><input type="text"  		name="child_name_'.$v->id.'">&nbsp'.JText::_('MOD_FRONTEND_COMMENT_OPTION').'<br>';
				$re.= '<label>'.JText::_('MOD_FRONTEND_COMMENT_EMAIL').'</label><input type="text"  		name="child_email_'.$v->id.'">&nbsp'.JText::_('MOD_FRONTEND_COMMENT_REQUIRE').'<br>';
				$re.= '<label>'.JText::_('MOD_FRONTEND_COMMENT_BUSINESS').'</label><input type="text"  		name="child_business_'.$v->id.'">&nbsp'.JText::_('MOD_FRONTEND_COMMENT_OPTION').'<br>';
				$re.= '<textarea 		   		name="child_comment_'.$v->id.'" rows="10" cols="50" ></textarea><br>';
				$re.= '<input type="hidden"     name="controller"       value="comments" />';
				$re.= '<input type="hidden"     name="view"             value="comments" />';
				$re.= '<input type="hidden"     name="option"           value="com_redcomments" />';
				$re.= '<input type="hidden"     name="task"             value="comments.addCommentChild" />';
				$re.= '<input type="hidden"     name="comment_option"   value="'.$option.'" />';
				$re.= '<input type="hidden"     name="comment_view"   	value="'.$view.'" />';
				$re.= '<input type="hidden"     name="comment_key"   	value="'.$primary_key.'" />';
				$re.= '<input type="hidden"     name="comment_view_key" value="'.$key.'" />';
				$re.= '<input type="hidden"     name="comment_itemid"   value="'.$Itemid.'" />';
				$re.= '<input type="hidden"     name="comment_parent"   value="'.$v->id.'" />';
				$re.= '<input type="hidden"     name="comment_userid"   value="'.$user_id.'" />';
				$re.= '<input type="submit"     name="submit"			value="Send" />';
				$re.= '</form>';
				$re.= '</div>';
			}
			$re.= '</div>';
			$res.=ScanParentChildComment($v->id,$list_comments,$re,$sep."_child",$user_id,$option,$view,$Itemid,$key,$primary_key,$check_mutiple_comment); 
		}
	}
	return $res;
}
$document = & JFactory::getDocument();
$document->addCustomTag('<script type = "text/javascript" src = "'.JURI::root().'modules/mod_frontend_comment/js/jquery.js"></script>');
?>
<script type="text/javascript">
		$(document).ready(function(){
			$("div[class^='comment_body_']").hide();
		});

		function create_comment(comment_id){
			$(".comment_body_"+comment_id).slideToggle(100);	
		}

		function validateForm(key)
		{
			if(key == 0 )
			{	
				var name    = document.forms["formComment"]["name"].value;
				var comment = document.forms["formComment"]["comment"].value;
				var email   = document.forms["formComment"]["email"].value;
			}
			else
			{
				var name    = document.forms["formComment_"+key]["child_name_"+key].value;
				var comment = document.forms["formComment_"+key]["child_comment_"+key].value;
				var email   = document.forms["formComment_"+key]["child_email_"+key].value;
			}	
			var atpos   = email.indexOf("@");
			var dotpos  = email.lastIndexOf(".");
			if ( name==null || name=="")
			{
			   alert("Name must be filled out");
			   return false;
			}
			if (email==null || email=="")
			{
			   alert("Email must be filled out");
			   return false;
			}
			if ( atpos < 1 || dotpos < atpos+2 || dotpos+2 >= email.length)
			{
			  	alert("Not a valid e-mail address");
			  	return false;
			}
			if (comment==null || comment=="")
			{
			   alert("Comment must be filled out");
			   return false;
			}
			return true;
		}
		
</script>
<?php 
if($user_id > 0){}
else
{
?>
	<div><?php echo JText::_('MOD_FRONTEND_COMMENT_MESSAGE_LOGIN');  ?> <span><a href="#"><?php echo JText::_('MOD_FRONTEND_COMMENT_LOGIN');  ?></a></span></div>
<?php 
}
if(count($list_comments) > 0)
{
	$allComments = ScanParentChildComment(0,$list_comments,'','',$user_id,$option,$view,$Itemid,$key,$primary_key,$check_mutiple_comment);
?>
	<div id="total_comment"><?php echo count($list_comments)?> <span><?php echo JText::_('MOD_FRONTEND_COMMENT_COMMENT'); ?></span></div>
<?php 
	echo $allComments;	
}
if($user_id > 0)
	{
?>
		<div style="width: 100%;">
		<p><?php echo JText::_('MOD_FRONTEND_COMMENT_WRITE_COMMNENT'); ?></p>
			<form method="post"    name="formComment" onsubmit="return validateForm(0)">
				<label><?php echo JText::_('MOD_FRONTEND_COMMENT_NAME'); ?></label><input type="text" name="name" >&nbsp<?php echo JText::_('MOD_FRONTEND_COMMENT_OPTION');?><br>
				<label><?php echo JText::_('MOD_FRONTEND_COMMENT_EMAIL'); ?></label><input type="text" name="email">&nbsp<?php echo JText::_('MOD_FRONTEND_COMMENT_REQUIRE');?><br>
				<label><?php echo JText::_('MOD_FRONTEND_COMMENT_BUSINESS'); ?></label><input type="text" name="business">&nbsp<?php echo JText::_('MOD_FRONTEND_COMMENT_OPTION');?><br>
				<textarea name="comment"  rows="10" cols="50" ></textarea><br>	
				<input type="hidden"      name="controller"       value="comments" />
				<input type="hidden"      name="view"             value="comments" />
				<input type="hidden"      name="option"           value="com_redcomments" />
				<input type="hidden"      name="task"             value="comments.addComment" />
				<input type="hidden"      name="comment_option"   value="<?php echo $option;  ?>" />
				<input type="hidden"      name="comment_view"     value="<?php echo $view;  ?>">
				<input type="hidden"      name="comment_key"      value="<?php echo $primary_key;  ?>" />
				<input type="hidden"      name="comment_view_key" value="<?php echo $key;  ?>" />
				<input type="hidden"      name="comment_itemid"   value="<?php echo $Itemid;  ?>" />
				<input type="submit"      value="Send" />
			</form>
		</div>	
<?php 		
   }
?>