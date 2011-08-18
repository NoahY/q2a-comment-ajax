<?php
        
/*              
        Plugin Name: Ajax Comment Form
        Plugin URI: https://github.com/NoahY/q2a-comment-ajax
        Plugin Description: Ajax comment submission form
        Plugin Version: 0.1
        Plugin Date: 2011-08-14
        Plugin Author: NoahY
        Plugin Author URI:                              
        Plugin License: GPLv2                           
        Plugin Minimum Question2Answer Version: 1.3
*/                      
                        
                        
        if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                        header('Location: ../../');
                        exit;   
        }               

        qa_register_plugin_module('widget', 'qa-comment-admin.php', 'qa_ajax_comment_admin', 'Ajax Comment Admin');
                
        qa_register_plugin_layer('qa-comment-layer.php', 'Ajax Comment Layer');
                        
                        
/*                              
        Omit PHP closing tag to help avoid accidental output
*/                              
                          

