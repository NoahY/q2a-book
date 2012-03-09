<?php
		
	function qa_get_request_content() {
		$requestlower=strtolower(qa_request());
		if($requestlower && $requestlower === qa_opt('book_plugin_request')) {
			if(qa_opt('book_plugin_static'))
				include(qa_opt('book_plugin_loc'));
			else
				echo qa_book_plugin_createBook(true);
			return false;
		}
		return qa_get_request_content_base();
	}
						
/*							  
		Omit PHP closing tag to help avoid accidental output
*/							  
						  

