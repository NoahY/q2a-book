<?php

	class qa_html_theme_layer extends qa_html_theme_base {
		
		function html(){
			if(@$this->request && $this->request === qa_opt('book_plugin_request')) {
				$this->getBook();
			}
			else
				qa_html_theme_base::html();
		}
		
		function getBook() {
			if(qa_opt('book_plugin_static'))
				include(qa_opt('book_plugin_loc'));
			else
				echo qa_book_plugin_createBook(true);
		}
	}

