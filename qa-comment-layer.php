<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		var $idx = 0;
		var $idx2 = 0;

		function option_default($option) {
			
			switch($option) {
				default:
					return false;
			}
			
		}
		
		// check for post
		
		function doctype()
		{
			if(!isset($_POST['ajax_comment_content'])) qa_html_theme_base::doctype();
		}

		function html()
		{
			if(isset($_POST['ajax_comment_content'])) $this->ajaxPostComment($_POST['ajax_comment_content'],(isset($_POST['ajax_comment_id'])?$_POST['ajax_comment_id']:null));
			else qa_html_theme_base::html();
		}
		
	// theme replacement functions

		function head_script()
		{
			qa_html_theme_base::head_script();
			$this->output_raw("
	<style>
		.ajax-comment-hidden {
			display:none;
		}
	</style>");
			
			$this->output_raw("
	<script>
		function toggleComment(idx) {
			jQuery('.ajax-comment').attr('disabled', 'disabled');
			jQuery('.ajax-comment').hide();
			jQuery('#ajax-comment-'+idx).removeAttr('disabled');
			jQuery('#ajax-comment-'+idx).show();
		}
		function ajaxPost(idx,id) {

			var content = escape(jQuery('textarea#comment').eq(idx).val());
			var dataString = 'ajax_comment_content='+content+(id?'&ajax_comment_id='+id:'')+'&notify=true&email=yuttadhammo@gmail.com';  

			jQuery.ajax({  
			  type: 'POST',  
			  url: '../1/this-is-a-test',  
			  data: dataString,  
			  success: function(data) {
				if(/^###/.exec(data)) {
					var error = data.substring(4);
					window.alert(error);
				}
				else if(!idx) {
					if(jQuery('.qa-q-view-c-list').length == 0) jQuery('<div class=\"qa-q-view-c-list\">'+data+'</div>').insertBefore('.qa-q-view-main .ajax-comment').show('slow');
					else jQuery('.qa-q-view-c-list').append(data).find('div.qa-c-list-item:last').show('slow');
				}
				else {
					if(jQuery('.qa-a-item-c-list').eq(idx-1).length == 0) jQuery('<div class=\"qa-q-view-c-list\">'+data+'</div>').insertBefore('.ajax-comment:eq('+idx+')').show('slow');
					else jQuery('.qa-a-item-c-list').eq(idx-1).append(data).find('div.qa-c-list-item:last').show('slow');
				}
				toggleComment();
			  }  
			});
		}
	</script>");
		}

		function q_view_main($q_view)
		{
			if (qa_opt('ajax_comment_enable')) {
				
				if (!empty($q_view['content'])){
					$q_view['c_form'] = $this->qa_page_q_add_c_form(null);
				}
				$this->idx++;
			}
			qa_html_theme_base::q_view_main($q_view);
		}
		function a_item_main($a_item)
		{
			if (qa_opt('ajax_comment_enable')) {
				$a_item['c_form'] = $this->qa_page_q_add_c_form($a_item['raw']['postid']);
				$this->idx++;
			}
			qa_html_theme_base::a_item_main($a_item);
		}
		function form($form)
		{
			if (!empty($form)) {
				if(isset($form['ajax_comment'])) {
					unset($form['ajax_comment']);
					$this->output('<div class="ajax-comment" style="display:none" id="ajax-comment-'.($this->idx++).'">');
				
					qa_html_theme_base::form($form);
					
					$this->output('</div>');
				}
				else qa_html_theme_base::form($form);
			}
		}		
		function form_button_data($button, $key, $style)
		{
			if (qa_opt('ajax_comment_enable')) {
				if($key === 'comment') {
					$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
					$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
					if(isset($button['ajax_comment'])) $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');	
					else  $this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="toggleComment('.$this->idx.');" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');
				}
				else if ($key == 'cancel' && isset($button['ajax_comment'])) {
					$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
					$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
					$this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="toggleComment('.$button['ajax_comment'].');" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');					
				}
				else qa_html_theme_base::form_button_data($button, $key, $style);
			}
			else qa_html_theme_base::form_button_data($button, $key, $style);
		}
		
		function qa_page_q_add_c_form($answerid)
	/*
		Return form for adding a comment on $answerid (or the question if $answerid is null), and set up $qa_content accordingly
	*/
		{
			global $qa_content, $incomment, $informat, $errors, $questionid, $ineditor, $innotify, $inemail, $jumptoanchor, $focusonid, $usecaptcha, $qa_login_userid;
			
			$jumptoanchor=isset($answerid) ? qa_anchor('A', $answerid) : qa_anchor('Q', $questionid);
			$focusonid='comment';
			
			$editorname=isset($ineditor) ? $ineditor : qa_opt('editor_for_cs');
			$editor=qa_load_editor(@$incomment, @$informat, $editorname);

			$form=array(
				'title' => qa_lang_html(isset($answerid) ? 'question/your_comment_a' : 'question/your_comment_q'),

				'style' => 'tall',
				
				'fields' => array(
					'content' => array_merge(
						$editor->get_field($qa_content, @$incomment, @$informat, 'comment', 4, true),
						array(
							'error' => qa_html(@$errors['content']),
						)
					),
				),
				
				'buttons' => array(
					'comment' => array(
						'tags' => 'NAME="'.(isset($answerid) ? ('docommentadda_'.$answerid) : 'docommentaddq').'" onclick="ajaxPost('.$this->idx2.','.($answerid?$answerid:0).')"',
						'label' => qa_lang_html('question/add_comment_button'),
						'ajax_comment' => $this->idx2,
					),
					
					'cancel' => array(
						'tags' => 'NAME="docancel"',
						'label' => qa_lang_html('main/cancel_button'),
						'ajax_comment' => $this->idx2,
					),
				),
				
				'hidden' => array(
					'editor' => qa_html($editorname),
				),
			);
			qa_set_up_notify_fields($qa_content, $form['fields'], 'C', qa_get_logged_in_email(),
				isset($innotify) ? $innotify : qa_opt('notify_users_default'), @$inemail, @$errors['email']);
			
			if ($usecaptcha)
				qa_set_up_captcha_field($qa_content, $form['fields'], @$errors,
					qa_insert_login_links(qa_lang_html(isset($qa_login_userid) ? 'misc/captcha_confirm_fix' : 'misc/captcha_login_fix')));
					
			$form['ajax_comment'] = 1;
			$this->idx2++;
			return $form;
		}

		function ajaxPostComment($text,$aid=null)
		{
			if($aid) $answer = qa_db_single_select(qa_db_full_post_selectspec(null, $aid));
					
			require_once QA_INCLUDE_DIR.'qa-page-question-post.php';
			
			global $qa_login_userid, $qa_cookieid, $question, $questionid, $formtype, $formpostid,
				$errors, $reloadquestion, $pageerror, $qa_request, $ineditor, $incomment, $informat, $innotify, $inemail, $commentsfollows, $jumptoanchor, $usecaptcha;
			
			$parent=$answer ? $answer : $question;
			
			switch (qa_user_permit_error('permit_post_c', 'C')) {
				case 'login':
					$pageerror=qa_insert_login_links(qa_lang_html('question/comment_must_login'), $qa_request);
					break;
					
				case 'confirm':
					$pageerror=qa_insert_login_links(qa_lang_html('question/comment_must_confirm'), $qa_request);
					break;
					
				case 'limit':
					$pageerror=qa_lang_html('question/comment_limit');
					break;
					
				default:
					$pageerror=qa_lang_html('users/no_permission');
					break;
					
				case false:
					$incomment=$text;
		
					if (!isset($incomment)) {
						$pageerror=qa_lang_html('question/comment_limit');
					} else {
						$intext=$text;
						$innotify=qa_post_text('notify') ? true : false;
						$inemail=qa_post_text('email');
						$informat = '';
		
						$errors=qa_comment_validate($incomment, '', $intext, $innotify, $inemail);
						
						if ($usecaptcha)
							qa_captcha_validate($_POST, $errors);
		
						if (empty($errors)) {
							$isduplicate=false;
							foreach ($commentsfollows as $comment)
								if (($comment['basetype']=='C') && ($comment['parentid']==$parent['postid']) && (!$comment['hidden']))
									if (implode(' ', qa_string_to_words($comment['content'])) == implode(' ', qa_string_to_words($incomment)))
										$isduplicate=true;
										
							if (!$isduplicate) {
								if (!isset($qa_login_userid))
									$qa_cookieid=qa_cookie_get_create(); // create a new cookie if necessary
								
								$commentid=qa_comment_create($qa_login_userid, qa_get_logged_in_handle(), $qa_cookieid, $incomment, $informat, $intext, $innotify, $inemail, $question, $answer, $commentsfollows);
								qa_report_write_action($qa_login_userid, $qa_cookieid, 'c_post', $questionid, @$answer['postid'], $commentid);
							
							} else {
								$pageerror=qa_lang_html('question/duplicate_content');
							}
						} 
					}
					break;
			}
			if($pageerror) $this->output('### '.$pageerror);
			else {
				
			// return c_item
				$c_item = $this->ajaxCommentCreate($parent,$commentid);
				if(isset($c_item['classes'])) $c_item['classes'] .= ' ajax-comment-hidden';
				else $c_item['classes'] = ' ajax-comment-hidden';
				$this->c_list_item($c_item);
				
			}
				
		}
		
		function ajaxCommentCreate($parent,$cid)
	/*
		Return a theme-ready structure with all the comments and follow-on questions to show for post $parent (question or answer)
	*/
		{
			global $qa_login_userid, $qa_cookieid, $usershtml, $formtype, $formpostid, $formrequested;
			
			$comment = qa_db_single_select(qa_db_full_post_selectspec(null, $cid));
	
			$htmloptions=qa_post_html_defaults('C', true);
			$htmloptions['avatarsize']=qa_opt('avatar_q_page_c_size');
			$c_view=qa_post_html_fields($comment, $qa_login_userid, $qa_cookieid, $usershtml, null, $htmloptions);
				

		//	Buttons for operating on this comment
			
			$c_view['form']=array(
				'style' => 'light',
				'buttons' => array(),
			);

			$c_view['form']['buttons']['edit']=array(
				'tags' => 'NAME="doeditc_'.qa_html($cid).'"',
				'label' => qa_lang_html('question/edit_button'),
				'popup' => qa_lang_html('question/edit_c_popup'),
			);
				
			$comment['hideable']=(!$comment['hidden']) && !$permiterror_hide_show;
			
			if ($comment['hideable'])
				$c_view['form']['buttons']['hide']=array(
					'tags' => 'NAME="dohidec_'.qa_html($cid).'"',
					'label' => qa_lang_html('question/hide_button'),
					'popup' => qa_lang_html('question/hide_c_popup'),
				);

			$comment['claimable']=(!isset($comment['userid'])) && isset($qa_login_userid) && (strcmp(@$comment['cookieid'], $qa_cookieid)==0) && !$permiterror_post_c;

			if ($comment['claimable'])
				$c_view['form']['buttons']['claim']=array(
					'tags' => 'NAME="doclaimc_'.qa_html($cid).'"',
					'label' => qa_lang_html('question/claim_button'),
				);
				
			if ($parent['commentbutton'] && qa_opt('show_c_reply_buttons') && !$comment['hidden'])
				$c_view['form']['buttons']['comment']=array(
					'tags' => 'NAME="'.(($parent['basetype']=='Q') ? 'docommentq' : ('docommenta_'.qa_html($parent['postid']))).'"',
					'label' => qa_lang_html('question/reply_button'),
					'popup' => qa_lang_html('question/reply_c_popup'),
				);

			return @$c_view;
		}

			
				
	}

