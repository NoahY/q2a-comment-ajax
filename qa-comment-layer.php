<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		var $idx = 0;
		var $idx2 = 0;
		var $qa_state;
		
		var $can_comment = false;
		
		// check for post
		
		function doctype()
		{
			$this->can_comment = (qa_user_permit_error('permit_post_c') == false);
			global $qa_state;
			$this->qa_state = $qa_state;
			if(!isset($_POST['ajax_comment_content']) || !$this->can_comment) qa_html_theme_base::doctype();
		}

		function html()
		{
			if(isset($_POST['ajax_comment_content']) && $this->can_comment) $this->ajaxPostComment(qa_post_text('ajax_comment_content'),(isset($_POST['ajax_comment_id'])?qa_post_text('ajax_comment_id'):null));
			else qa_html_theme_base::html();
		}
		
	// theme replacement functions

		function head_script()
		{
			qa_html_theme_base::head_script();
			if (qa_opt('ajax_comment_enable') && !$this->qa_state && $this->template == 'question' && $this->can_comment) {
				$this->output_raw("
	<style>
		.ajax-comment-hidden {
			display:none;
		}
		.ajax-comment-reminder {
			font-size:10px;
			font-style:italic;
		}
		.ajax-comment-vote-popup {
			position: absolute; 
			background-color:yellow; 
			padding:10px;
			border:1px solid; 
			margin-left:20px; 
			margin-top:45px; 
			text-align:justify; 
			width:250px; 
			font-weight:bold;
			cursor:pointer;
			display:none;
		}
	</style>");
			
			$this->output_raw("
	<script>
	
		function flashStar(idx) {
		
			var star = jQuery('.qa-a-select-button').eq(idx-1);
			star.attr('class','qa-a-select-hover');
			star.fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow').fadeIn('slow').fadeOut('slow',function(){
				star.attr('class','qa-a-select-button').fadeIn('slow');
			});
		}

		function voteReminderNotice(elem,idx) {
			flashStar(idx);
			jQuery('<div class=\"ajax-comment-vote-popup\" onclick=\"this.style.display=\\'none\\';\">".qa_html(qa_opt('ajax_comment_popup_notice_text'))."</div>').insertAfter(elem.parentNode.parentNode).fadeIn('fast').delay(8000).fadeOut('slow');
		}
	
		var ajax_comment_position = 0;
		var ajax_comment_id = 0;
		var ajax_comment_height = 0;
		function toggleComment(idx,username,flash) {
			jQuery('.ajax-comment').hide('slow');
			
			if(idx === false) {
				jQuery('textarea#comment').val('');
				return false;
			}
			var cDiv = jQuery('#ajax-comment-'+idx);
			
			if(cDiv.length) {
				if(ajax_comment_position != idx) {
					cDiv.append(jQuery('#ajax-comment-'+ajax_comment_position).html());
					jQuery('#ajax-comment-'+ajax_comment_position).html('');
					ajax_comment_position = idx;
				}

				if(!cDiv.is(':visible')) {
					
					// flash star
						
					if(flash && !jQuery('.qa-a-item-selected').length) flashStar(flash);
					
					// check if onscreen
					
					var top = (-1)*jQuery('html').offset().top;
					var cTop = cDiv.prev().offset().top + cDiv.prev().height();
					var bot = top + jQuery(window).height();
					
					if (cTop > top && cTop < bot) {
						cDiv.show('slow',function(){
							jQuery('html').animate({scrollTop:(cDiv.offset().top-jQuery(window).height()+cDiv.height()+20)+'px'},{queue:false, duration:600, easing: 'swing'});
							jQuery('#ajax-comment-'+idx+' textarea#comment').focus();
						});
					}
					else {
						cDiv.show();
						jQuery('html').animate({scrollTop:(cDiv.offset().top-jQuery(window).height()+cDiv.height()+20)+'px'},{queue:false, duration:600, easing: 'swing'});
						jQuery('#ajax-comment-'+idx+' textarea#comment').focus();
					}

					jQuery('#ajax-comment-'+idx+' textarea#comment').val((username?'@'+username+' ':''));

				}
				else {
					if(username) {
						jQuery('#ajax-comment-'+idx+' textarea#comment').val(jQuery('#ajax-comment-'+idx+' textarea#comment').val()+'@'+username+' ');
					}

					jQuery('html').animate({scrollTop:(cDiv.offset().top-jQuery(window).height()+cDiv+20)+'px'},{queue:false, duration:600, easing: 'swing'});
					jQuery('#ajax-comment-'+idx+' textarea#comment').focus();
				}
			}
		}
		function ajaxPostComment() {
			var cText = jQuery('#ajax-comment-'+ajax_comment_position+' textarea#comment');
			var content = cText.val();
			
			var notify = jQuery('#ajax-comment-'+(ajax_comment_position)+' input[name=\"notify\"]').attr('checked');
			var email = jQuery('#ajax-comment-'+(ajax_comment_position)+' input[name=\"email\"]').val();
			var editor = jQuery('#ajax-comment-'+(ajax_comment_position)+' input[name=\"editor\"]').val();
			var oldcss = cText.css('background');
			cText.css('background','url(".QA_HTML_THEME_LAYER_URLTOROOT."ajax-loader.gif) no-repeat scroll center center silver');
			//cText.val('');
			
			var dataString = 'ajax_id='+ajax_comment_position+'&ajax_comment_content='+content+(ajax_comment_position!=0?'&ajax_comment_id='+document.getElementById('ajax-comment-'+ajax_comment_position).getAttribute('value'):'')+(notify?'&notify='+notify:'')+(email?'&email='+email:'')+(editor?'&editor='+editor:'');  
			jQuery.ajax({  
			  type: 'POST',  
			  url: '".qa_self_html()."',  
			  data: dataString,  
			  success: function(data) {
				if(/^[\\t\\n ]*###/.exec(data)) {
					var error = data.replace(/^[\\t\\n ]*### */,'');
					window.alert(error);
					//cText.val(content);
				}
				else if(ajax_comment_position == 0) {
					if(jQuery('.qa-q-view-c-list').length == 0) jQuery('<div class=\"qa-q-view-c-list\">'+data+'</div>').insertBefore('#ajax-comment-'+ajax_comment_position).find('div.qa-c-list-item:last').show('slow',function () {slideToDiv('.qa-q-view-c-list')});
					else jQuery('.qa-q-view-c-list').append(data).find('div.qa-c-list-item:last').show('slow',function () {slideToDiv('.qa-q-view-c-list')});
					toggleComment(false);
				}
				else {
					if(!document.getElementById('ajax-comment-'+ajax_comment_position).getAttribute('comments')) {
						document.getElementById('ajax-comment-'+ajax_comment_position).setAttribute('comments','true');
						jQuery('<div class=\"qa-a-item-c-list\">'+data+'</div>').insertBefore('#ajax-comment-'+ajax_comment_position).find('div.qa-c-list-item:last').show('slow');
					}
					else {
						jQuery('.ajax-comment-idx-'+ajax_comment_position).append(data).find('div.qa-c-list-item:last').show('slow');
					}
					toggleComment(false);
				}
				cText.css('background',oldcss);
			  }  
			});
		}
		
		function slideToDiv(div) {
			var cDiv = jQuery(div);
			if(cDiv.length) {
				jQuery('html').animate({scrollTop:(cDiv.offset().top-jQuery(window).height()+cDiv.height()+20)+'px'},{queue:false, duration:600, easing: 'swing'});
			}			
		}
		
	</script>");
			}
		}

		function q_view_main($q_view)
		{
			if (qa_opt('ajax_comment_enable') && !$this->qa_state && $this->can_comment) {
				$this->output('<img style="display:none" src="'.QA_HTML_THEME_LAYER_URLTOROOT.'ajax-loader.gif" />'); // this preloads the ajax loader gif
				$q_view['c_form'] = $this->qa_ajax_comment_form(null);
				if(isset($q_view['a_form'])) {
					$v = $q_view['a_form'];
					$q_view['a_form'] = $q_view['c_form'];
					$q_view['c_form'] = $v;
				}
			}
			qa_html_theme_base::q_view_main($q_view);
		}
		function a_item_main($a_item)
		{
			if (qa_opt('ajax_comment_enable') && !$this->qa_state && $this->can_comment) {
				$switch = @$a_item['c_form'];
				$a_item['c_form'] = $this->qa_ajax_comment_form_shell($a_item['raw']['postid'],isset($a_item['c_list']));
				$a_item['c_form_2'] = @$switch;
			}
			qa_html_theme_base::a_item_main($a_item);
			$this->form(@$a_item['c_form_2']); // answer form if any
		}
		function form($form)
		{
			if (qa_opt('ajax_comment_enable') && !$this->qa_state && !empty($form) && isset($form['ajax_comment']) && $this->can_comment) {
				
				$this->output('<div class="ajax-comment"'.(isset($form['ajax_comment_comments'])?' comments="true"':'').' style="display:none" value="'.$form['ajax_comment'].'" id="ajax-comment-'.($this->idx++).'">');
				unset($form['ajax_comment']);
				unset($form['ajax_comment_comments']);
			
				qa_html_theme_base::form($form);
				
				$this->output('</div>');
			}
			else qa_html_theme_base::form($form);
		}

	// add @username to comment box
		
		function q_view_buttons($q_view)
		{
			if (qa_opt('ajax_comment_enable') && qa_opt('ajax_comment_username') && isset($q_view['form']['buttons']['comment']) && !$this->qa_state && $this->can_comment) {

				$handle = $this->getHandleFromId($q_view['raw']['userid']);
				$q_view['form']['buttons']['comment']['comment_username'] = $handle;
			}
			qa_html_theme_base::q_view_buttons($q_view);
		}
		
		function a_item_buttons($a_item)
		{
			if (qa_opt('ajax_comment_enable') && qa_opt('ajax_comment_username') && isset($a_item['form']['buttons']['comment']) && !$this->qa_state && $this->can_comment) {

				$handle = $this->getHandleFromId($a_item['raw']['userid']);
				$a_item['form']['buttons']['comment']['comment_username'] = $handle;
			}
			qa_html_theme_base::a_item_buttons($a_item);
		}

		function c_list($c_list, $class)
		{
			$class = 'ajax-comment-idx-'.$this->idx.' '.$class;
			qa_html_theme_base::c_list($c_list, $class);
		}
		
		function c_item_buttons($c_item)
		{
			if (qa_opt('ajax_comment_enable') && qa_opt('ajax_comment_username') && isset($c_item['form']['buttons']['comment']) && !$this->qa_state && $this->can_comment) {
				$handle = $this->getHandleFromId($c_item['raw']['userid']);
				$c_item['form']['buttons']['comment']['comment_username'] = $handle;
			}
			qa_html_theme_base::c_item_buttons($c_item);
		}
		
		function form_button_data($button, $key, $style)
		{
			if (qa_opt('ajax_comment_enable') && !$this->qa_state && $this->can_comment) {
				if($key === 'comment') {

					// insert username
					
					$handle = '';
					if(qa_opt('ajax_comment_username') && isset($button['comment_username']) && $button['comment_username'] != qa_get_logged_in_handle()) {
						$handle = ",'".$button['comment_username']."'";
					}

					// flash star if we are the questioner commenting on an answer
				
					$star = '';
					
					$ourid = qa_get_logged_in_userid();
					
					if(qa_opt('ajax_comment_flash_star') && !$this->content['q_view']['raw']['selchildid'] && $this->idx > 0 && @$button['popup'] == qa_lang_html('question/comment_a_popup') && $this->content['q_view']['raw']['userid'] == $ourid && @$this->content['a_list']['as'][$this->idx-1]['raw']['userid'] != $ourid) {
						$star = ','.$this->idx;
					}
					
					$toggle_opts = ($handle?$handle.$star:',null'.$star);
					
					$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
					$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
					
					if(isset($button['ajax_comment'])) $this->output('<INPUT'.rtrim(' '.@$button['tags']).' VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');	
					else  $this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="toggleComment('.(isset($_POST['ajax_id'])?$_POST['ajax_id']:$this->idx).$toggle_opts.');" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');
				}
				else if ($key === 'cancel' && isset($button['ajax_comment'])) {
					$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
					$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
					$this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="toggleComment(false);" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');					
				}
				else qa_html_theme_base::form_button_data($button, $key, $style);
			}
			else qa_html_theme_base::form_button_data($button, $key, $style);
		}

		// vote button notice

		function post_hover_button($post, $element, $value, $class)
		{
				$ourid = qa_get_logged_in_userid();
				if(isset($this->content['q_view']) && strpos($class, 'vote-up') > 0 && qa_opt('ajax_comment_popup_notice') && !$this->content['q_view']['raw']['selchildid'] && $this->idx > 0 && $this->content['q_view']['raw']['userid'] == $ourid && $this->can_comment)
					$value.='" onmouseup="voteReminderNotice(this,'.$this->idx.')';
			qa_html_theme_base::post_hover_button($post, $element, $value, $class);
		}
		
	// worker functions
		
		function qa_ajax_comment_form_shell($answerid,$comments=false) {
			$form['ajax_comment'] = $answerid;
			if($comments) $form['ajax_comment_comments'] = 1;
			return $form;
		}

		function qa_ajax_comment_form()
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
							'rows' => 8,
						)
					),
				),
				
				'buttons' => array(
					'comment' => array(
						'tags' => 'onclick="ajaxPostComment()"',
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
			
			$form['fields']['content']['tags'] = @$form['fields']['tags'].' id="comment" onkeydown="if(event.keyCode == 27) toggleComment(false);"';


			// add reminder text if commenting on answer to own question

			$ourid = qa_get_logged_in_userid();
			
			if(qa_opt('ajax_comment_answer_reminder') && !$this->content['q_view']['raw']['selchildid'] && isset($answerid) && $this->content['q_view']['raw']['userid'] == $ourid && @$this->content['a_list']['as'][$this->idx-1]['raw']['userid'] != $ourid) {
				$form['fields']['custom_message'] = array(
						'note' => '<div class="ajax-comment-reminder">'.qa_opt('ajax_comment_answer_reminder_text').'</div>',
						'type' => 'static',
				);
				
			}			
			
			qa_set_up_notify_fields($qa_content, $form['fields'], 'C', qa_get_logged_in_email(),
				isset($innotify) ? $innotify : qa_opt('notify_users_default'), @$inemail, @$errors['email']);
			
			if ($usecaptcha)
				qa_set_up_captcha_field($qa_content, $form['fields'], @$errors,
					qa_insert_login_links(qa_lang_html(isset($qa_login_userid) ? 'misc/captcha_confirm_fix' : 'misc/captcha_login_fix')));
					
			$form['ajax_comment'] = 0;
			$this->idx2++;
			return $form;
		}

		function ajaxPostComment($text,$aid=false)
		{
			if($aid) $answer = qa_db_single_select(qa_db_full_post_selectspec(null, $aid));
					
			require_once QA_INCLUDE_DIR.'qa-page-question-post.php';
			
			global $qa_login_userid, $qa_cookieid, $question, $questionid, $formtype, $formpostid,
				$errors, $reloadquestion, $pageerror, $qa_request, $ineditor, $incomment, $informat, $innotify, $inemail, $commentsfollows, $jumptoanchor, $usecaptcha;
			
			$parent=isset($answer) ? $answer : $question;
			
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
					$incomment=qa_post_text('ajax_comment_content');
		
					if (!isset($incomment)) {
						$pageerror=qa_lang_html('bork');
					} else {
						$innotify=qa_post_text('notify') ? true : false;
						$inemail=qa_post_text('email');
						$this->ajaxEditor($ineditor, $incomment, $informat, $intext);
						
						// use our own format types
						
						$formats = array();
						$formats[] = '';
						$editors = qa_list_modules('viewer');
						if(in_array('Markdown Viewer',$editors)) {
							$formats[] = 'markdown';
						}
						$formats[]='html';
												
						$informat = $formats[qa_opt('ajax_comment_format')];
						
						$errors=qa_comment_validate($incomment, $informat, $intext, $innotify, $inemail);
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
								
								// get editor format

								
								$commentid=qa_comment_create($qa_login_userid, qa_get_logged_in_handle(), $qa_cookieid, $incomment, $informat, $intext, $innotify, $inemail, $question, @$answer, $commentsfollows);
								qa_report_write_action($qa_login_userid, $qa_cookieid, 'c_post', $questionid, @$answer['postid'], $commentid);
							
							} else {
								$pageerror=qa_lang_html('question/duplicate_content');
							}
						} 
					}
					break;
			}
			if($pageerror) $this->output_raw('### '.$pageerror);
			else if(!empty($errors)) $this->output_raw('### '.implode(',',$errors));
			else {
				
			// return c_item
				$c_item = $this->ajaxCommentCreate($parent,$commentid);
				if(isset($c_item['classes'])) $c_item['classes'] .= ' ajax-comment-hidden';
				else $c_item['classes'] = ' ajax-comment-hidden';
				$this->c_list_item($c_item);
				
			}
				
		}
		
		function ajaxEditor(&$ineditor, &$incontent, &$informat, &$intext) {
			$ineditor=qa_post_text('editor');
			
			$editor=qa_load_module('editor', $ineditor);
			$readdata=$editor->read_post('ajax_comment_content');
			$incontent=$readdata['content'];
			$informat=$readdata['format'];

			$viewer=qa_load_viewer($incontent, $informat);
			$intext=$viewer->get_text($incontent, $informat, array());
		}
		
		function ajaxCommentCreate($parent,$cid)
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
			
			$parent['commentbutton']=(qa_user_permit_error('permit_post_c')!='level') && qa_opt(($comment['type']=='Q') ? 'comment_on_qs' : 'comment_on_as');
							
			if ($parent['commentbutton'] && qa_opt('show_c_reply_buttons') && !$comment['hidden'])
				$c_view['form']['buttons']['comment']=array(
					'tags' => 'NAME="'.(($parent['basetype']=='Q') ? 'docommentq' : ('docommenta_'.qa_html($parent['postid']))).'"',
					'label' => qa_lang_html('question/reply_button'),
					'popup' => qa_lang_html('question/reply_c_popup'),
				);

			return @$c_view;
		}

		function getHandleFromId($userid) {
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			if (QA_FINAL_EXTERNAL_USERS) {
				$publictohandle=qa_get_public_from_userids(array($userid));
				$handle=@$publictohandle[$userid];
				
			} 
			else {
				$user = qa_db_single_select(qa_db_user_account_selectspec($userid, true));
				$handle = @$user['handle'];
			}
			return $handle;
		}
			
				
	}

