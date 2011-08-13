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
			if (qa_opt('ajax_comment_enable')) {
				require_once QA_INCLUDE_DIR.'qa-page-question-post.php';
				
				if (!empty($q_view['content'])){
					$q_view["c_list"][] = qa_page_q_add_c_form(null);
				}
			}
			qa_html_theme_base::q_view_content($q_view);
		}
		function a_item_content($a_item)
		{
			if (qa_opt('ajax_comment_enable')) {
				$a_item["c_list"][] = qa_page_q_add_c_form($a_item['raw']['postid']);
			}
			qa_html_theme_base::a_item_content($a_item);
		}

	}

