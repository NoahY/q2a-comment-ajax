<?php

	class qa_html_theme_layer extends qa_html_theme_base {

		function option_default($option) {
			
			switch($option) {
				default:
					return false;
			}
			
		}
	// theme replacement functions

		function q_view_content($q_view)
		{
			qa_html_theme_base::q_view_content($q_view);
			if (qa_opt('ajax_comment_enable')) {
				if (!empty($q_view['content'])){
					$this->output(qa_page_q_add_c_form($this->content['raw']['postid']));
				}
			}
		}
		function a_item_content($a_item)
		{
			qa_html_theme_base::a_item_content($a_item);
			if (qa_opt('ajax_comment_enable')) {
					$this->output(qa_page_q_add_c_form($this->content['raw']['postid']));
			}
		}

	}

