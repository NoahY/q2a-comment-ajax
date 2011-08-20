<?php
    class qa_ajax_comment_admin {
	function option_default($option) {
	    switch($option) {
		case 'ajax_comment_format':
		    return 0;
		case 'ajax_comment_answer_reminder_text':
		    return '<div class="ajax-comment-reminder">Remember, you can accept an answer by clicking the star in the top right corner.</div>';
		default:
		    return null;
	    }
	}       
        function allow_template($template)
        {
            return ($template!='admin');
        }       
            
        function admin_form(&$qa_content)
        {                       
                            
        // Process form input
            
            $ok = null;
            
            if (qa_clicked('ajax_comment_save')) {
		qa_opt('ajax_comment_enable',(bool)qa_post_text('ajax_comment_enable'));
		qa_opt('ajax_comment_format',(int)qa_post_text('ajax_comment_format'));
		qa_opt('ajax_comment_username',(bool)qa_post_text('ajax_comment_username'));
		qa_opt('ajax_comment_flash_star',(bool)qa_post_text('ajax_comment_flash_star'));
		qa_opt('ajax_comment_answer_reminder',(bool)qa_post_text('ajax_comment_answer_reminder'));
		qa_opt('ajax_comment_answer_reminder_text',qa_post_text('ajax_comment_answer_reminder_text'));
                $ok = 'Settings Saved.';

            }
            
	    $formats = array();
	    $formats[] = 'plain text';
	    
	    $editors = qa_list_modules('viewer');
	    if(in_array('Markdown Viewer',$editors)) {
		$formats[] = 'markdown';
	    }
	    
	    $formats[]='html';
                    
        // Create the form for display

            
            $fields = array();
            
            $fields[] = array(
                'label' => 'Enable ajax comment form',
                'tags' => 'NAME="ajax_comment_enable"',
                'value' => qa_opt('ajax_comment_enable'),
                'type' => 'checkbox',
            );
	    
            $fields[] = array(
		'label' => 'Comment format',
		'tags' => 'NAME="ajax_comment_format"',
		'type' => 'select',
		'options' => $formats,
		'value' => @$formats[qa_opt('ajax_comment_format')],
            );
            
            $fields[] = array(
                'label' => 'Add @username to comment box',
                'tags' => 'NAME="ajax_comment_username"',
                'value' => (int)qa_opt('ajax_comment_username'),
                'type' => 'checkbox',
            );
            
            $fields[] = array(
                'label' => 'Show reminder text if commenting on answer to own question',
                'tags' => 'NAME="ajax_comment_answer_reminder" onclick="if(this.checked) jQuery(\'#ajax_comment_answer_reminder_text\').fadeIn(); else jQuery(\'#ajax_comment_answer_reminder_text\').fadeOut();"',
                'value' => (int)qa_opt('ajax_comment_answer_reminder'),
                'type' => 'checkbox',
            );
            
            
            $fields[] = array(
                'tags' => 'id="ajax_comment_answer_reminder_text" name="ajax_comment_answer_reminder_text" style="display:'.(qa_opt('ajax_comment_answer_reminder')?'block':'none').'"',
                'value' => qa_html(qa_opt('ajax_comment_answer_reminder_text')),
                'type' => 'text',
            );
            
            $fields[] = array(
                'label' => 'Flash select star if commenting on answer to own question',
                'tags' => 'NAME="ajax_comment_flash_star"',
                'value' => (int)qa_opt('ajax_comment_flash_star'),
                'type' => 'checkbox',
            );

            return array(           
                'ok' => ($ok && !isset($error)) ? $ok : null,
                    
                'fields' => $fields,
             
                'buttons' => array(
                    array(
                        'label' => 'Save',
                        'tags' => 'NAME="ajax_comment_save"',
                    )
                ),
            );
        }
    }

