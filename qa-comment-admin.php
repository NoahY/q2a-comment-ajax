<?php
    class qa_ajax_comment_admin {
	function option_default($option) {
	    switch($option) {
	    default:
		return false;
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
		'options' => array('plain text','markdown','html'),
		'value' => $formats[qa_opt('ajax_comment_format')],
            );
            
            $fields[] = array(
                'label' => 'Add @username to comment box',
                'tags' => 'NAME="ajax_comment_username"',
                'value' => (int)qa_opt('ajax_comment_username'),
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

