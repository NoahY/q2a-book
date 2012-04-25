<?php
	class qa_book_admin {

		function option_default($option) {
			
			switch($option) {
			case 'book_plugin_sort_q':
				return 0;
			case 'book_plugin_inc':
				return 0;
			case 'book_plugin_req_qv_no':
				return 5;
			case 'book_plugin_req_av_no':
				return 5;
			case 'book_plugin_refresh_last':
				return time();
			case 'book_plugin_refresh_hours':
				return 24;
			case 'book_plugin_loc':
				return dirname(__FILE__).'/book.html';
			case 'book_plugin_loc_pdf':
				return dirname(__FILE__).'/book.pdf';
			case 'book_plugin_request':
				return 'book';
			case 'book_plugin_request_pdf':
				return 'book.pdf';
			case 'book_plugin_css':
				return file_get_contents(dirname(__FILE__).'/book.css');
			case 'book_plugin_template':
				return file_get_contents(dirname(__FILE__).'/template.html');
			case 'book_plugin_template_front':
				return file_get_contents(dirname(__FILE__).'/front.html');
			case 'book_plugin_template_toc':
				return file_get_contents(dirname(__FILE__).'/toc.html');
			case 'book_plugin_template_back':
				return file_get_contents(dirname(__FILE__).'/back.html');
			case 'book_plugin_template_category':
				return file_get_contents(dirname(__FILE__).'/category.html');
			case 'book_plugin_template_questions':
				return file_get_contents(dirname(__FILE__).'/questions.html');
			case 'book_plugin_template_question':
				return file_get_contents(dirname(__FILE__).'/question.html');
			case 'book_plugin_template_answer':
				return file_get_contents(dirname(__FILE__).'/answer.html');
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
				
				if (qa_clicked('book_plugin_process') || qa_clicked('book_plugin_save')) {
			
					qa_opt('book_plugin_active',(bool)qa_post_text('book_plugin_active'));
					
					qa_opt('book_plugin_cats',(bool)qa_post_text('book_plugin_cats'));
					qa_opt('book_plugin_catex',qa_post_text('book_plugin_catex'));
					
					qa_opt('book_plugin_sort_q',(int)qa_post_text('book_plugin_sort_q'));
					
					qa_opt('book_plugin_req_sel',(bool)qa_post_text('book_plugin_req_sel'));
					qa_opt('book_plugin_req_abest',(bool)qa_post_text('book_plugin_req_abest'));
					qa_opt('book_plugin_req_abest_max',(int)qa_post_text('book_plugin_req_abest_max'));
					qa_opt('book_plugin_req_qv',(bool)qa_post_text('book_plugin_req_qv'));
					qa_opt('book_plugin_req_av',(bool)qa_post_text('book_plugin_req_av'));
					
					qa_opt('book_plugin_req_qv_no',(int)qa_post_text('book_plugin_req_qv_no'));
					qa_opt('book_plugin_req_av_no',(int)qa_post_text('book_plugin_req_av_no'));

					qa_opt('book_plugin_static',(bool)qa_post_text('book_plugin_static'));
					qa_opt('book_plugin_pdf',(bool)qa_post_text('book_plugin_pdf'));
					qa_opt('book_plugin_loc',qa_post_text('book_plugin_loc'));
					qa_opt('book_plugin_loc_pdf',qa_post_text('book_plugin_loc_pdf'));

					qa_opt('book_plugin_refresh',(bool)qa_post_text('book_plugin_refresh'));
					qa_opt('book_plugin_refresh_time',(bool)qa_post_text('book_plugin_refresh_time'));
					qa_opt('book_plugin_refresh_cron',(bool)qa_post_text('book_plugin_refresh_cron'));
					qa_opt('book_plugin_refresh_hours',(int)qa_post_text('book_plugin_refresh_hours'));

					
					qa_opt('book_plugin_request',qa_post_text('book_plugin_request'));
					qa_opt('book_plugin_request_pdf',qa_post_text('book_plugin_request_pdf'));
					
					qa_opt('book_plugin_css',qa_post_text('book_plugin_css'));
					
					qa_opt('book_plugin_template',qa_post_text('book_plugin_template'));
					qa_opt('book_plugin_template_front',qa_post_text('book_plugin_template_front'));
					qa_opt('book_plugin_template_back',qa_post_text('book_plugin_template_back'));
					qa_opt('book_plugin_template_toc',qa_post_text('book_plugin_template_toc'));
					qa_opt('book_plugin_template_category',qa_post_text('book_plugin_template_category'));
					qa_opt('book_plugin_template_questions',qa_post_text('book_plugin_template_questions'));
					qa_opt('book_plugin_template_question',qa_post_text('book_plugin_template_question'));
					qa_opt('book_plugin_template_answer',qa_post_text('book_plugin_template_answer'));
					
					if(qa_clicked('book_plugin_process') && qa_opt('book_plugin_static'))
						$ok = qa_book_plugin_createBook();
					else
						$ok = qa_lang('admin/options_saved');
				}
				else if (qa_clicked('book_plugin_reset')) {
					foreach($_POST as $i => $v) {
						$def = $this->option_default($i);
						if($def !== null) qa_opt($i,$def);
					}
					$ok = qa_lang('admin/options_reset');
				} 
			// Create the form for display
				
			$fields = array();
			
			$fields[] = array(
				'label' => 'Activate Plugin',
				'tags' => 'NAME="book_plugin_active"',
				'value' => qa_opt('book_plugin_active'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'type' => 'blank',
			);
			
			$fields[] = array(
				'label' => 'Sort By Categories',
				'tags' => 'onchange="if(this.checked) $(\'#book_plugin_cat_div\').show(); else $(\'#book_plugin_cat_div\').hide();" NAME="book_plugin_cats"',
				'value' => qa_opt('book_plugin_cats'),
				'type' => 'checkbox',
			);

			
			$fields[] = array(
				'value' => '<span style="display:'.(qa_opt('book_plugin_cats')?'block':'none').'" id="book_plugin_cat_div"><i>Categories to exclude (comma seperated categoryid list):</i><br/><input name="book_plugin_catex" id="book_plugin_catex" value="'.qa_opt('book_plugin_catex').'"></span>',
				'type' => 'static',
			);
			
			$sort = array(
				'votes',
				'date',
			);
			
			$fields[] = array(
				'id' => 'book_plugin_sort_q',
				'label' => 'Sort questions by',
				'tags' => 'NAME="book_plugin_sort_q" ID="book_plugin_sort_q"',
				'type' => 'select',
				'options' => $sort,
				'value' => @$sort[qa_opt('book_plugin_sort_q')],
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'value' => '<b>Restrict inclusion to:</b>',
				'type' => 'static',
			);

			$fields[] = array(
				'label' => 'Selected answers',
				'tags' => 'NAME="book_plugin_req_sel"',
				'value' => qa_opt('book_plugin_req_sel'),
				'type' => 'checkbox',
			);

			$fields[] = array(
				'label' => 'Highest voted answers',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_req_abest_max_div\').show(); else $(\'#book_plugin_req_abest_max_div\').hide();" NAME="book_plugin_req_abest"',
				'value' => qa_opt('book_plugin_req_abest'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'value' => '<span id="book_plugin_req_abest_max_div" style="display:'.(qa_opt('book_plugin_req_abest')?'block':'none').'">max number of answers to include: <input name="book_plugin_req_abest_max" size="3" value="'.(qa_opt('book_plugin_req_abest_max')?qa_opt('book_plugin_req_abest_max'):'').'"></span>',
				'type' => 'static',
			);
			
			$fields[] = array(
				'label' => 'Questions with minimum votes',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_req_qv_div\').show(); else $(\'#book_plugin_req_qv_div\').hide();" NAME="book_plugin_req_qv"',
				'value' => qa_opt('book_plugin_req_qv'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'value' => '<span id="book_plugin_req_qv_div" style="display:'.(qa_opt('book_plugin_req_qv')?'block':'none').'">min. votes for inclusion: <input name="book_plugin_req_qv_no" size="3" value="'.qa_opt('book_plugin_req_qv_no').'"></span>',
				'type' => 'static',
			);

			$fields[] = array(
				'label' => 'Answers with minimum votes',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_req_av_div\').show(); else $(\'#book_plugin_req_av_div\').hide();" NAME="book_plugin_req_av"',
				'value' => qa_opt('book_plugin_req_av'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'value' => '<span id="book_plugin_req_av_div" style="display:'.(qa_opt('book_plugin_req_av')?'block':'none').'">min. votes for inclusion: <input name="book_plugin_req_av_no" size="3" value="'.qa_opt('book_plugin_req_av_no').'"></span>',
				'type' => 'static',
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => 'Create Static Book',
				'note' => '<i>if this is unchecked, accessing the book page will recreate the book on every view</i>',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_loc\').show(); else $(\'#book_plugin_loc\').hide();" NAME="book_plugin_static"',
				'value' => qa_opt('book_plugin_static'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'value' => '<span id="book_plugin_loc" style="display:'.(qa_opt('book_plugin_static')?'block':'none').'">Location (must be writable): <input name="book_plugin_loc" value="'.qa_opt('book_plugin_loc').'"></span>',
				'type' => 'static',
			);
			$fields[] = array(
				'type' => 'blank',
			);
			
			$fields[] = array(
				'label' => 'Create Static PDF',
				'note' => '<i>requires wkhtmltopdf - see README.rst</i>',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_loc_pdf\').show(); else $(\'#book_plugin_loc_pdf\').hide();" NAME="book_plugin_pdf"',
				'value' => qa_opt('book_plugin_pdf'),
				'type' => 'checkbox',
			);
			$fields[] = array(
				'value' => '<span id="book_plugin_loc_pdf" style="display:'.(qa_opt('book_plugin_pdf')?'block':'none').'">Location (must be writable): <input name="book_plugin_loc_pdf" value="'.qa_opt('book_plugin_loc_pdf').'"></span>',
				'type' => 'static',
			);
			$fields[] = array(
				'type' => 'blank',
			);
			$fields[] = array(
				'label' => 'Recreate Static Book',
				'tags' => 'onclick="if(this.checked) $(\'#book_plugin_refresh_hours\').show(); else $(\'#book_plugin_refresh_hours\').hide();" NAME="book_plugin_refresh"',
				'value' => qa_opt('book_plugin_refresh'),
				'type' => 'checkbox',
			);
			
			$cron_url = qa_opt('site_url').qa_opt('book_plugin_request').'?cron=true';
			
			$fields[] = array(
				'value' => '<div id="book_plugin_refresh_hours" style="display:'.(qa_opt('book_plugin_refresh')?'block':'none').'">minimum time to recreate:&nbsp;<input name="book_plugin_refresh_hours" value="'.qa_opt('book_plugin_refresh_hours').'" size="3">&nbsp;hours<br/><i>if this is set to zero, the auto-recreate will not run, and the cron url may be called at any time.<br/><br/><input type="checkbox" name="book_plugin_refresh_time" '.(qa_opt('book_plugin_refresh_time')?'checked':'').'> recreate on next access after above interval<br/><br/><input type="checkbox" name="book_plugin_refresh_cron" '.(qa_opt('book_plugin_refresh_cron')?'checked':'').'>recreate via cron url below<br/><span style="font-style:italic;">url is currently <a href="'.$cron_url.'">'.$cron_url.'</a></span></div>',
				'type' => 'static',
			);
			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => 'Book Permalink',
				'note' => '<i>the url used to access the book, either via static file, or on the fly</i>',
				'tags' => 'NAME="book_plugin_request"',
				'value' => qa_opt('book_plugin_request'),
			);

			$fields[] = array(
				'label' => 'Book PDF Permalink',
				'note' => '<i>the url used to access the PDF file; should correspond with static PDF location above</i>',
				'tags' => 'NAME="book_plugin_request_pdf"',
				'value' => qa_opt('book_plugin_request_pdf'),
			);
			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => 'Book CSS',
				'note' => '<i>book.css</i>',
				'tags' => 'NAME="book_plugin_css"',
				'value' => qa_opt('book_plugin_css'),
				'type' => 'textarea',
				'rows' => '10',
			);

			$fields[] = array(
				'type' => 'blank',
			);

			$fields[] = array(
				'label' => 'Book Template',
				'note' => '<i>template.html</i>',
				'tags' => 'NAME="book_plugin_template"',
				'value' => qa_opt('book_plugin_template'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Front Cover Template',
				'note' => '<i>front.html</i>',
				'tags' => 'NAME="book_plugin_template_front"',
				'value' => qa_opt('book_plugin_template_front'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Back Cover Template',
				'note' => '<i>back.html</i>',
				'tags' => 'NAME="book_plugin_template_back"',
				'value' => qa_opt('book_plugin_template_back'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Table of Contents Template',
				'note' => '<i>toc.html</i>',
				'tags' => 'NAME="book_plugin_template_toc"',
				'value' => qa_opt('book_plugin_template_toc'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Category Template',
				'note' => '<i>category.html - used when sorting by categories</i>',
				'tags' => 'NAME="book_plugin_template_category"',
				'value' => qa_opt('book_plugin_template_category'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Questions Template',
				'note' => '<i>questions.html - used when not sorting by categories</i>',
				'tags' => 'NAME="book_plugin_template_questions"',
				'value' => qa_opt('book_plugin_template_questions'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Question Template',
				'note' => '<i>question.html</i>',
				'tags' => 'NAME="book_plugin_template_question"',
				'value' => qa_opt('book_plugin_template_question'),
				'type' => 'textarea',
				'rows' => '10',
			);
			$fields[] = array(
				'label' => 'Answer Template',
				'note' => '<i>answer.html</i>',
				'tags' => 'NAME="book_plugin_template_answer"',
				'value' => qa_opt('book_plugin_template_answer'),
				'type' => 'textarea',
				'rows' => '10',
			);

			return array(		   
				'ok' => ($ok && !isset($error)) ? $ok : null,
					
				'fields' => $fields,
			 
				'buttons' => array(
					array(
						'label' => qa_lang_html('admin/save_options_button'),
						'tags' => 'NAME="book_plugin_save"',
					),
					array(
						'label' => 'Process',
						'tags' => 'NAME="book_plugin_process"',
					),
                    array(
                        'label' => qa_lang_html('admin/reset_options_button'),
                        'tags' => 'NAME="book_plugin_reset"',
                    ),
				),
			);
		}
	}

