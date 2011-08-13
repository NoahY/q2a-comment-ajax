<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		var $idx = 0;

		function option_default($option) {
			
			switch($option) {
				default:
					return false;
			}
			
		}
	// theme replacement functions

		function head_script()
		{
			qa_html_theme_base::head_script();
			$this->output_raw("
	<script>
		function toggleComment(idx) {
			jQuery('input[name^=docomment]').submit(false);
			jQuery('.ajax-comment').attr('disabled', 'disabled');
			jQuery('.ajax-comment').hide();
			jQuery('#ajax-comment-'+idx).removeAttr('disabled');
			jQuery('#ajax-comment-'+idx).show();
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
					$this->output('<div class="ajax-comment" id="ajax-comment-'.($this->idx++).'">');
				
					qa_html_theme_base::form($form);
					
					$this->output('</div>');
				}
				else qa_html_theme_base::form($form);
			}
		}		
		function form_button_data($button, $key, $style)
		{
			if (qa_opt('ajax_comment_enable') && $key == 'comment') {
				
				$baseclass='qa-form-'.$style.'-button qa-form-'.$style.'-button-'.$key;
				$hoverclass='qa-form-'.$style.'-hover qa-form-'.$style.'-hover-'.$key;
				
				$this->output('<INPUT'.rtrim(' '.@$button['tags']).' onclick="toggleComment('.$this->idx.');" VALUE="'.@$button['label'].'" TITLE="'.@$button['popup'].'" TYPE="button" CLASS="'.$baseclass.'" onmouseover="this.className=\''.$hoverclass.'\';" onmouseout="this.className=\''.$baseclass.'\';"/>');	
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
						'tags' => 'NAME="'.(isset($answerid) ? ('docommentadda_'.$answerid) : 'docommentaddq').'"',
						'label' => qa_lang_html('question/add_comment_button'),
					),
					
					'cancel' => array(
						'tags' => 'NAME="docancel"',
						'label' => qa_lang_html('main/cancel_button'),
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

			return $form;
		}

	}

