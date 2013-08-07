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
			$out = '<p><a href="'.qa_path_html(qa_opt('book_plugin_request')).'">&lt;/&gt; '.qa_lang('book/widget_html').'</a></p>';
			
			if(qa_opt('book_plugin_pdf'))
				$out .= '<p><a href="'.qa_path_html(qa_opt('book_plugin_request_pdf')).'"><img src="http://www.adobe.com/images/pdficon_small.png"> '.qa_lang('book/widget_pdf').'</a></p>';
			
			$themeobject->output('<div class="book-widget" style="padding-top:0px">',$out,'</div>');
		}
	};


/*
	Omit PHP closing tag to help avoid accidental output
*/
