<?php

	class qa_book_widget {

		function allow_template($template)
		{
			return true;
		}

		function allow_region($region)
		{
			return true;
		}

		function output_widget($region, $place, $themeobject, $template, $request, $qa_content)
		{
			if(!qa_opt('book_plugin_active'))
				return;
				
			$themeobject->output('<h2>'.qa_lang('book/widget_title').'</h2>');
			$out = '<li><a href="'.qa_path_html(qa_opt('book_plugin_request')).'">'.qa_lang('book/widget_html').'</a></li>';
			
			if(qa_opt('book_plugin_pdf'))
				$out .= '<li><a href="'.qa_path_html(qa_opt('book_plugin_request_pdf')).'">'.qa_lang('book/widget_pdf').'</a>';
			
			$themeobject->output('<ul class="book-widget" style="padding-top:8px;">',$out,'</ul>');
		}
	};


/*
	Omit PHP closing tag to help avoid accidental output
*/
